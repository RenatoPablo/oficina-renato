<?php

namespace Tests\Unit;

use App\Models\ClienteVeiculo;
use App\Models\Veiculo;
use Tests\TestCase;

class VeiculoClienteRelationshipTest extends TestCase
{
    public function test_relacionamentos_nao_filtram_por_user_id_nulo_da_instancia(): void
    {
        $vinculoAtivo = (new Veiculo())->clienteVinculoAtivo();
        $cliente = (new ClienteVeiculo())->cliente();

        $this->assertStringNotContainsString('user_id', strtolower($vinculoAtivo->toSql()));
        $this->assertStringNotContainsString('user_id', strtolower($cliente->toSql()));
        $this->assertNotContains(null, $vinculoAtivo->getBindings(), true);
        $this->assertNotContains(null, $cliente->getBindings(), true);
    }
}
