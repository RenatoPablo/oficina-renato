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
        'veiculo_id',
        'data_chamado',
        'data_previsao_entrega',
        'tipo_atendimento',
        'situacao',
        'atendente',
        'problema_reclamado',
        'revisao_ate',
        'frete',
        'total_servicos',
        'total_pecas',
        'total_os',
        'observacoes',
    ];

    // RELAÇÕES
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function servicosItens()
    {
        return $this->hasMany(ServicoOrdem::class, 'ordem_servico_id');
    }

    public function pecasItens()
    {
        return $this->hasMany(PecaOrdem::class, 'ordem_servico_id');
    }

    // (Opcional) Recalcula totais
    public function recalcTotais(): void
    {
        $totalServ = $this->servicosItens()->sum('valor_total');
        $totalPecs = $this->pecasItens()->sum('valor_total');
        $this->total_servicos = $totalServ;
        $this->total_pecas    = $totalPecs;
        $this->total_os       = $totalServ + $totalPecs + (float)($this->frete ?? 0);
        $this->save();
    }
               
}
