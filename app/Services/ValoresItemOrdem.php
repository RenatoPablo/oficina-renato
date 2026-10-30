<?php

namespace App\Services;

class ValoresItemOrdem
{
    public static function calcular(array $linha): array
    {
        $qtd = (float) $linha['qtd'];
        $unitario = (float) ($linha['valor_unit'] ?? 0);
        $total = round((float) ($linha['valor_total'] ?? $qtd * $unitario), 2);

        return [
            'valor_total' => $total,
            // O total informado é a referência; arredondar o unitário não muda a cobrança.
            'valor_unit' => $qtd > 0 ? round($total / $qtd, 5) : 0,
        ];
    }
}
