<?php

namespace Tests\Unit;

use App\Http\Controllers\ClienteController;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ClienteSemDocumentoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('clientes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('nome');
            $t->string('cnpj_cpf', 20);
            foreach (['contato', 'ie_rg', 'endereco', 'bairro', 'municipio', 'uf', 'cep', 'telefone', 'celular', 'email', 'observacao'] as $campo) {
                $t->string($campo)->nullable();
            }
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['user_id', 'cnpj_cpf']);
        });
        $migration = require database_path('migrations/2026_10_30_130000_permitir_cliente_sem_documento.php');
        $migration->up();
        $user = new User;
        $user->forceFill(['id' => 7, 'is_admin' => false]);
        $this->actingAs($user);
    }

    public function test_varios_clientes_podem_ser_cadastrados_sem_documento(): void
    {
        foreach ([[], ['cnpj_cpf' => ''], ['cnpj_cpf' => '   '], ['cnpj_cpf' => null]] as $dados) {
            app(ClienteController::class)->createSubmit(Request::create('/', 'POST', ['nome' => 'Cliente'] + $dados));
        }
        $this->assertEquals(4, Cliente::whereNull('cnpj_cpf')->count());
    }

    public function test_edicao_pode_apagar_manter_ou_preencher_documento(): void
    {
        Cliente::create(['nome' => 'Outro', 'cnpj_cpf' => null]);
        $cliente = Cliente::create(['nome' => 'Cliente', 'cnpj_cpf' => '12345678901']);
        foreach (['   ', null, '12345678901', '12345678901'] as $documento) {
            app(ClienteController::class)->editSubmit(Request::create('/', 'PUT', [
                'id' => $cliente->id, 'nome' => 'Cliente atualizado', 'cnpj_cpf' => $documento,
            ]));
            $this->assertSame($documento === '   ' ? null : $documento, $cliente->fresh()->cnpj_cpf);
        }
    }

    public function test_documento_preenchido_duplicado_continua_bloqueado(): void
    {
        Cliente::create(['nome' => 'Existente', 'cnpj_cpf' => '12345678901']);
        $this->expectException(ValidationException::class);
        app(ClienteController::class)->createSubmit(Request::create('/', 'POST', [
            'nome' => 'Novo', 'cnpj_cpf' => '12345678901',
        ]));
    }

    public function test_edicao_respeita_unicidade_por_usuario(): void
    {
        Cliente::create(['nome' => 'Outra oficina', 'cnpj_cpf' => '12345678901', 'user_id' => 8]);
        $cliente = Cliente::create(['nome' => 'Cliente', 'cnpj_cpf' => null]);
        app(ClienteController::class)->editSubmit(Request::create('/', 'PUT', [
            'id' => $cliente->id, 'nome' => 'Cliente', 'cnpj_cpf' => '12345678901',
        ]));
        $this->assertSame('12345678901', $cliente->fresh()->cnpj_cpf);
    }
}
