<?php

namespace Tests\Unit;

use App\Http\Controllers\OrdemServicoController;
use App\Http\Requests\SyncAllRequest;
use App\Models\Estoque;
use App\Models\OrdemServico;
use App\Models\User;
use App\Services\EstoqueOrdemService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EstoqueOrdemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['ordens_servico', 'pecas_ordem', 'estoques', 'servicos_ordem'] as $nome) {
            Schema::create($nome, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->default(7);
                $t->unsignedBigInteger('ordem_servico_id')->nullable();
                $t->unsignedBigInteger('estoque_id')->nullable();
                $t->unsignedBigInteger('veiculo_id')->nullable();
                $t->string('situacao')->default('Aberta');
                foreach (['quantidade', 'qtd', 'qtd_descontada', 'valor_unit', 'valor_total', 'frete', 'total_os', 'total_pecas', 'total_servicos'] as $col) {
                    $t->decimal($col, 15, 2)->default(0);
                }
                $t->string('codigo_cor')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }
        $user = new User;
        $user->forceFill(['id' => 7, 'is_admin' => false]);
        $this->actingAs($user);
        Schema::create('veiculos', fn (Blueprint $t) => $t->id());
        DB::table('veiculos')->insert(['id' => 1]);
    }

    private function sincronizar(OrdemServico $os, array $linhas): void
    {
        app(EstoqueOrdemService::class)->sincronizar($os, $linhas);
    }

    private function linha(int $estoque, float $qtd, ?int $id = null): array
    {
        return ['id' => $id, 'estoque_id' => $estoque, 'qtd' => $qtd, 'valor_unit' => 10];
    }

    public function test_consumo_limitado_cobranca_integral_e_estorno(): void
    {
        foreach ([[10, 3, 7, 3], [2, 5, 0, 2], [0, 4, 0, 0], [2, 0.5, 1.5, 0.5]] as [$saldo, $qtd, $final, $debito]) {
            $os = OrdemServico::create(['situacao' => 'Aberta']);
            $estoque = Estoque::create(['quantidade' => $saldo]);
            $this->sincronizar($os, [$this->linha($estoque->id, $qtd)]);
            $item = $os->pecasItens()->first();
            $this->assertEquals($final, $estoque->fresh()->quantidade);
            $this->assertEquals($debito, $item->qtd_descontada);
            $os->recalcTotais();
            $this->assertEquals($qtd * 10, $os->fresh()->total_os);
            $this->sincronizar($os, [$this->linha($estoque->id, $qtd, $item->id)]);
            $this->assertEquals($final, $estoque->fresh()->quantidade);
            $this->sincronizar($os, []);
            $this->assertEquals($saldo, $estoque->fresh()->quantidade);
        }
    }

    public function test_edicao_apos_reposicao_nao_desconta_falta_antiga_nem_estorna_falta(): void
    {
        $os = OrdemServico::create(['situacao' => 'Aberta']);
        $e = Estoque::create(['quantidade' => 2]);
        $this->sincronizar($os, [$this->linha($e->id, 5)]);
        $id = $os->pecasItens()->first()->id;
        $e->update(['quantidade' => 10]);
        $this->sincronizar($os, [$this->linha($e->id, 5, $id)]);
        $this->assertEquals(10, $e->fresh()->quantidade);
        $this->sincronizar($os, [$this->linha($e->id, 3, $id)]);
        $this->assertEquals(10, $e->fresh()->quantidade);
        $this->sincronizar($os, [$this->linha($e->id, 1, $id)]);
        $this->assertEquals(11, $e->fresh()->quantidade);
        $this->sincronizar($os, [$this->linha($e->id, 4, $id)]);
        $this->assertEquals(8, $e->fresh()->quantidade);
        $this->sincronizar($os, []);
        $this->assertEquals(12, $e->fresh()->quantidade);
    }

    public function test_multiplas_linhas_troca_cancelamento_e_reabertura(): void
    {
        $os = OrdemServico::create(['situacao' => 'Aberta']);
        $e = Estoque::create(['quantidade' => 3]);
        $outro = Estoque::create(['quantidade' => 1]);
        $this->sincronizar($os, [$this->linha($e->id, 2), $this->linha($e->id, 4)]);
        $this->assertEquals(3, $os->pecasItens()->sum('qtd_descontada'));
        $id = $os->pecasItens()->first()->id;
        $this->sincronizar($os, [$this->linha($outro->id, 5, $id)]);
        $this->assertEquals(3, $e->fresh()->quantidade);
        $os->update(['situacao' => 'Cancelada']);
        $linhas = $os->pecasItens()->get()->toArray();
        $this->sincronizar($os, $linhas);
        $this->sincronizar($os, $linhas);
        $this->assertEquals(1, $outro->fresh()->quantidade);
        $os->update(['situacao' => 'Aberta']);
        app(EstoqueOrdemService::class)->sincronizar($os, $linhas, true);
        $this->assertEquals(0, $outro->fresh()->quantidade);
        $os->update(['situacao' => 'Finalizada']);
        $this->expectException(\RuntimeException::class);
        $this->sincronizar($os, []);
    }

    public function test_ids_repetidos_rejeitados_sem_movimentacao(): void
    {
        $os = OrdemServico::create(['situacao' => 'Aberta']);
        $e = Estoque::create(['quantidade' => 10]);
        $this->sincronizar($os, [$this->linha($e->id, 2)]);
        $linha = $this->linha($e->id, 5, $os->pecasItens()->first()->id);
        try {
            $this->sincronizar($os, [$linha, $linha]);
            $this->fail('Deveria rejeitar IDs repetidos.');
        } catch (ValidationException $ex) {
            $this->assertEquals(8, $e->fresh()->quantidade);
        }
    }

    public function test_controller_finaliza_sem_estoque_bloqueia_edicao_e_exclui_com_estorno_real(): void
    {
        $os = OrdemServico::create(['situacao' => 'Aberta']);
        $e = Estoque::create(['quantidade' => 2]);
        $this->sincronizar($os, [$this->linha($e->id, 5)]);
        $os->recalcTotais();
        $controller = app(OrdemServicoController::class);
        $controller->fechar(Crypt::encrypt($os->id));
        $this->assertSame('Finalizada', $os->fresh()->situacao);
        $this->assertEquals(50, $os->fresh()->total_os);
        $controller->syncAll(SyncAllRequest::create('/', 'POST', ['pecas' => []]), $os->id);
        $this->assertSame(1, $os->pecasItens()->count());
        $this->assertEquals(0, $e->fresh()->quantidade);
        $controller->destroy(Crypt::encrypt($os->id));
        $this->assertSoftDeleted('ordens_servico', ['id' => $os->id]);
        $this->assertEquals(2, $e->fresh()->quantidade);
    }

    public function test_controller_cancela_uma_vez_e_reabre_com_novo_desconto_limitado(): void
    {
        $os = OrdemServico::create(['situacao' => 'Aberta', 'veiculo_id' => 1]);
        $e = Estoque::create(['quantidade' => 2]);
        $this->sincronizar($os, [$this->linha($e->id, 5)]);
        $controller = app(OrdemServicoController::class);
        foreach (['Cancelada', 'Cancelada'] as $situacao) {
            $controller->updateMeta(Request::create('/', 'POST', ['veiculo_id' => 1, 'situacao' => $situacao]), $os->id);
            $this->assertEquals(2, $e->fresh()->quantidade);
            $this->assertEquals(0, $os->pecasItens()->sum('qtd_descontada'));
        }
        $controller->updateMeta(Request::create('/', 'POST', ['veiculo_id' => 1, 'situacao' => 'Aberta']), $os->id);
        $this->assertEquals(0, $e->fresh()->quantidade);
        $this->assertEquals(2, $os->pecasItens()->sum('qtd_descontada'));
    }

    public function test_migration_preserva_desconto_integral_das_linhas_antigas(): void
    {
        $os = OrdemServico::create(['situacao' => 'Aberta']);
        $e = Estoque::create(['quantidade' => 10]);
        $this->sincronizar($os, [$this->linha($e->id, 3)]);
        Schema::table('pecas_ordem', fn (Blueprint $t) => $t->dropColumn('qtd_descontada'));
        $migration = require database_path('migrations/2026_10_20_120000_add_qtd_descontada_to_pecas_ordem.php');
        $migration->up();
        $this->assertEquals(3, $os->pecasItens()->first()->qtd_descontada);
        $this->assertEquals(7, $e->fresh()->quantidade);
        $this->sincronizar($os, []);
        $this->assertEquals(10, $e->fresh()->quantidade);
    }
}
