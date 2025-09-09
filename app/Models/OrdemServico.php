<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemServico extends Model
{
    use SoftDeletes;
    
        // forçar a tabela correta
    protected $table = 'ordens_servico';

    // (opcional) defina os preenchíveis
    protected $fillable = [
        // seus campos...
        'veiculo_id',
        'situacao',
        'data_chamado',
        'data_previsao_entrega',
        'tipo_atendimento',
        'atendente',
        'problema_reclamado',
        'revisao_ate',
        'frete',
        'observacoes',

        // ⚠️ ADICIONE ESTES:
        'cliente_id',
        'cliente_veiculo_id',
        'cliente_nome_snapshot',
        'cliente_documento_snapshot',

        'total_servicos',
        'total_pecas',
        'total_os',
    ];

    // RELAÇÕES
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function servicosItens()
    {
        return $this->hasMany(ServicoOrdem::class, 'ordem_servico_id');
    }

    public function pecasItens()
    {
        return $this->hasMany(PecaOrdem::class, 'ordem_servico_id');
    }

    public function recalcTotais(): void
    {
        $totalServ = ServicoOrdem::where('ordem_servico_id', $this->id)->sum('valor_total');
        $totalPec  = PecaOrdem::where('ordem_servico_id', $this->id)->sum('valor_total');

        // se futuramente tiver desconto, aplique aqui
        $frete = (float) ($this->frete ?? 0);
        $desconto = (float) ($this->desconto_valor ?? 0); // opcional, ver passo 3

        $this->forceFill([
            'total_servicos' => $totalServ,
            'total_pecas'    => $totalPec,
            'total_os'       => max(0, $totalServ + $totalPec + $frete - $desconto),
        ])->save();
    }

    public function cliente() {
        return $this->belongsTo(\App\Models\Cliente::class, 'cliente_id');
    }

    public function clienteVeiculo() {
        return $this->belongsTo(\App\Models\ClienteVeiculo::class, 'cliente_veiculo_id');
    }
                
}
