<?php

namespace Tests\Unit;

use App\Http\Controllers\VeiculoController;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VeiculoSemPlacaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('veiculos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            foreach (['tipo', 'marca', 'modelo', 'placa'] as $campo) $t->string($campo);
            $t->string('km')->nullable();
            $t->string('ano')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['user_id', 'placa']);
        });
        $migration = require database_path('migrations/2026_10_30_120000_permitir_veiculo_sem_placa.php');
        $migration->up();
        Schema::create('cliente_veiculo', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('veiculo_id');
            $t->unsignedBigInteger('cliente_id');
            $t->boolean('ativo');
            $t->softDeletes();
        });
        $user = new User;
        $user->forceFill(['id' => 7, 'is_admin' => false]);
        $this->actingAs($user);
    }

    private function dados(array $extras = []): array
    {
        return array_merge(['tipo' => 'Carro', 'marca' => 'VW', 'modelo' => 'Gol'], $extras);
    }

    public function test_cadastra_varios_veiculos_sem_placa(): void
    {
        foreach ([[], ['placa' => ''], ['placa' => '   '], ['placa' => null]] as $extras) {
            app(VeiculoController::class)->createSubmit(Request::create('/', 'POST', $this->dados($extras)));
        }
        $this->assertEquals(4, Veiculo::whereNull('placa')->count());
    }

    public function test_remove_e_preenche_placa_na_edicao(): void
    {
        $veiculo = Veiculo::create($this->dados(['placa' => 'ABC1234']));
        DB::table('cliente_veiculo')->insert(['user_id' => 7, 'veiculo_id' => $veiculo->id, 'cliente_id' => 1, 'ativo' => true]);
        foreach (['' => null, 'abc1234' => 'ABC-1234', 'abc1d23' => 'ABC1D23'] as $entrada => $esperada) {
            $r = Request::create('/', 'PUT', $this->dados(['id' => $veiculo->id, 'cliente_id' => 1, 'placa' => $entrada]));
            app(VeiculoController::class)->editSubmit($r);
            $this->assertSame($esperada, $veiculo->fresh()->placa);
        }
    }

    public function test_placa_preenchida_continua_unica_apos_normalizacao(): void
    {
        Veiculo::create($this->dados(['placa' => 'ABC1234']));
        $this->expectException(ValidationException::class);
        app(VeiculoController::class)->createSubmit(Request::create('/', 'POST', $this->dados(['placa' => 'abc1234'])));
    }
}
