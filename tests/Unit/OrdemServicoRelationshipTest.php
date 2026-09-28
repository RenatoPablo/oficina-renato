<?php

namespace Tests\Unit;

use App\Models\OrdemServico;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrdemServicoRelationshipTest extends TestCase
{
    public function test_nota_carrega_pecas_e_preserva_filtro_do_usuario(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        foreach (['ordens_servico', 'pecas_ordem', 'estoques'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('ordem_servico_id')->nullable();
                $table->unsignedBigInteger('estoque_id')->nullable();
                $table->string('descricao')->nullable();
                $table->softDeletes();
            });
        }

        $user = new User;
        $user->forceFill(['id' => 7, 'is_admin' => false]);
        $this->actingAs($user);

        DB::table('ordens_servico')->insert(['id' => 1, 'user_id' => 7]);
        DB::table('estoques')->insert(['id' => 1, 'user_id' => 7, 'descricao' => 'Filtro de oleo']);
        DB::table('pecas_ordem')->insert([
            ['id' => 1, 'user_id' => 7, 'ordem_servico_id' => 1, 'estoque_id' => 1],
            ['id' => 2, 'user_id' => 8, 'ordem_servico_id' => 1, 'estoque_id' => 1],
        ]);

        $os = OrdemServico::with('pecasItens.estoque')->findOrFail(1);

        $this->assertCount(1, $os->pecasItens);
        $this->assertSame(1, $os->pecasItens->first()->id);
        $this->assertSame('Filtro de oleo', $os->pecasItens->first()->estoque->descricao);
    }

    public function test_relacionamentos_suportam_instancia_vazia_do_eager_loading(): void
    {
        foreach (['pecasItens', 'servicosItens', 'veiculo', 'cliente', 'clienteVeiculo'] as $relation) {
            $query = OrdemServico::query()->getRelation($relation);
            $this->assertStringNotContainsString('"user_id" is null', $query->toSql());
        }
    }
}
