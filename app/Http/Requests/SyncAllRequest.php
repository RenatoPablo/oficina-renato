<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncAllRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza moedas (aceita vírgula/ponto), remove linhas vazias
     * e garante tipos corretos antes das regras rodarem.
     */
    protected function prepareForValidation(): void
    {
        $servicos = collect($this->input('servicos', []))
            // remove linhas sem servico_id (usuário adicionou e apagou)
            ->filter(fn($row) => !empty($row['servico_id']))
            ->map(function ($row) {
                $row['qtd']         = $this->toNumber($row['qtd'] ?? 1);
                $row['valor_unit']  = $this->toNumber($row['valor_unit'] ?? 0);
                $row['valor_total'] = $this->toNumber(($row['valor_total'] ?? ($row['qtd'] ?? 1) * ($row['valor_unit'] ?? 0)));
                return $row;
            })
            ->values()
            ->all();

        $pecas = collect($this->input('pecas', []))
            ->filter(fn($row) => !empty($row['estoque_id']))
            ->map(function ($row) {
                $row['qtd']         = $this->toNumber($row['qtd'] ?? 1);
                $row['valor_unit']  = $this->toNumber($row['valor_unit'] ?? 0);
                $row['valor_total'] = $this->toNumber(($row['valor_total'] ?? ($row['qtd'] ?? 1) * ($row['valor_unit'] ?? 0)));
                return $row;
            })
            ->values()
            ->all();

        $this->merge([
            'frete'   => $this->toNumber($this->input('frete', 0)),
            'servicos'=> $servicos,
            'pecas'   => $pecas,
        ]);
    }

    public function rules(): array
    {
        $osId = $this->route('id'); // /ordem/{id}/...

        return [
            // campo simples
            'frete' => ['nullable', 'numeric', 'min:0'],

            // --- SERVIÇOS ---
            'servicos' => ['array'],
            'servicos.*.id' => [
                'nullable', 'integer',
                // existe e pertence à própria OS
                Rule::exists('servicos_ordem', 'id')->where('ordem_servico_id', $osId),
            ],
            'servicos.*.servico_id' => ['required', 'integer', Rule::exists('servicos', 'id')],
            'servicos.*.qtd'        => ['required', 'numeric', 'gt:0'],
            'servicos.*.valor_unit' => ['required', 'numeric', 'gte:0'],
            'servicos.*.valor_total'=> ['required', 'numeric', 'gte:0'],
            'servicos.*.tecnico'    => ['nullable', 'string', 'max:100'],
            'servicos.*.codigo_cor' => ['nullable', 'string', 'max:20'],

            // --- PEÇAS ---
            'pecas' => ['array'],
            'pecas.*.id' => [
                'nullable', 'integer',
                Rule::exists('pecas_ordem', 'id')->where('ordem_servico_id', $osId),
            ],
            'pecas.*.estoque_id' => ['required', 'integer', Rule::exists('estoques', 'id')],
            'pecas.*.qtd'        => ['required', 'numeric', 'gt:0'],
            'pecas.*.valor_unit' => ['required', 'numeric', 'gte:0'],
            'pecas.*.valor_total'=> ['required', 'numeric', 'gte:0'],
            'pecas.*.codigo_cor' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'servicos.*.servico_id.required' => 'Selecione o serviço.',
            'servicos.*.qtd.gt'              => 'Quantidade do serviço deve ser maior que zero.',
            'pecas.*.estoque_id.required'    => 'Selecione a peça/estoque.',
            'pecas.*.qtd.gt'                 => 'Quantidade da peça deve ser maior que zero.',
        ];
    }

    public function attributes(): array
    {
        return [
            'frete'                     => 'frete',
            'servicos.*.servico_id'     => 'serviço',
            'servicos.*.qtd'            => 'quantidade (serviço)',
            'servicos.*.valor_unit'     => 'valor unitário (serviço)',
            'servicos.*.valor_total'    => 'valor total (serviço)',
            'pecas.*.estoque_id'        => 'peça/estoque',
            'pecas.*.qtd'               => 'quantidade (peça)',
            'pecas.*.valor_unit'        => 'valor unitário (peça)',
            'pecas.*.valor_total'       => 'valor total (peça)',
        ];
    }

    /** Converte string com vírgula/ponto em float */
    private function toNumber($value): float
    {
        if ($value === null || $value === '') return 0.0;
        $s = preg_replace('/[^\d,\.]/', '', (string) $value);
        if (str_contains($s, ',')) $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        return (float) $s;
    }
}
