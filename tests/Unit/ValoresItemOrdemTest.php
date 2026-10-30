<?php

namespace Tests\Unit;

use App\Services\ValoresItemOrdem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValoresItemOrdemTest extends TestCase
{
    public static function valores(): array
    {
        return [
            'total com divisão exata' => [['qtd' => 4, 'valor_total' => 100], 25, 100],
            'divisão com dízima' => [['qtd' => 3, 'valor_total' => 100], 33.33333, 100],
            'zero' => [['qtd' => 3, 'valor_total' => 0], 0, 0],
            'quantidade fracionária' => [['qtd' => 0.5, 'valor_total' => 20], 40, 20],
            'preço unitário informado' => [['qtd' => 3, 'valor_unit' => 12.50], 12.50, 37.50],
            'total prevalece' => [['qtd' => 2, 'valor_unit' => 99, 'valor_total' => 60], 30, 60],
        ];
    }

    #[DataProvider('valores')]
    public function test_calcula_sem_alterar_total(array $linha, float $unitario, float $total): void
    {
        $this->assertSame(['valor_total' => $total, 'valor_unit' => $unitario], ValoresItemOrdem::calcular($linha));
    }
}
