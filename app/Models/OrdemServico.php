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
               
}
