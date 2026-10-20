<?php

namespace Tests\Unit;

use App\Http\Controllers\EstoqueController;
use App\Models\Estoque;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EstoqueCodigoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('estoques', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('codigo')->nullable();
            $t->string('descricao');
            $t->decimal('quantidade')->default(0);
            $t->decimal('preco_rs')->default(0);
            $t->string('medida')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $user = new User;
        $user->forceFill(['id' => 7, 'is_admin' => false]);
        $this->actingAs($user);
    }

    private function atualizar(Estoque $peca, ?string $codigo): void
    {
        app(EstoqueController::class)->editSubmit(Request::create('/', 'PUT', [
            'id' => $peca->id, 'codigo' => $codigo, 'descricao' => 'Filtro atualizado',
            'quantidade' => 2, 'preco_rs' => 15,
        ]));
    }

    public function test_permite_varias_pecas_sem_codigo_e_limpar_codigo_existente(): void
    {
        Estoque::create(['descricao' => 'Outra peça', 'codigo' => null]);
        $peca = Estoque::create(['descricao' => 'Filtro', 'codigo' => 'ABC']);
        $this->atualizar($peca, null);
        $this->assertNull($peca->fresh()->codigo);
        $this->assertSame('Filtro atualizado', $peca->fresh()->descricao);
        $this->atualizar($peca, null);
        $this->assertNull($peca->fresh()->codigo);
    }

    public function test_permite_manter_codigo_da_propria_peca(): void
    {
        $peca = Estoque::create(['descricao' => 'Filtro', 'codigo' => 'ABC']);
        $this->atualizar($peca, 'ABC');
        $this->assertSame('ABC', $peca->fresh()->codigo);
    }

    public function test_rejeita_codigo_preenchido_ja_usado_por_outra_peca(): void
    {
        Estoque::create(['descricao' => 'Outra peça', 'codigo' => 'ABC']);
        $peca = Estoque::create(['descricao' => 'Filtro', 'codigo' => null]);
        $this->expectException(ValidationException::class);
        $this->atualizar($peca, 'ABC');
    }
}
