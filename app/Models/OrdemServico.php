<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemServico extends TenantModel
{
    use SoftDeletes, BelongsToUser;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'veiculo_id','situacao','data_chamado','data_previsao_entrega','tipo_atendimento',
        'atendente','problema_reclamado','revisao_ate','frete','observacoes',
        'cliente_id','cliente_veiculo_id','cliente_nome_snapshot','cliente_documento_snapshot',
        'total_servicos','total_pecas','total_os','user_id'
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id')
                    ->withTrashed()                 // ✅ traz mesmo se tiver deleted_at
                    ->where('user_id', $this->user_id);
    }

    public function servicosItens()
    {
        return $this->hasMany(ServicoOrdem::class, 'ordem_servico_id')
                    ->where('user_id', $this->user_id);
    }

    public function pecasItens()
    {
        return $this->hasMany(PecaOrdem::class, 'ordem_servico_id')
                    ->where('user_id', $this->user_id);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id')
                    ->withTrashed()                 // idem se você deletar clientes
                    ->where('user_id', $this->user_id);
    }

    public function clienteVeiculo()
    {
        return $this->belongsTo(ClienteVeiculo::class, 'cliente_veiculo_id')
                    ->withTrashed()
                    ->where('user_id', $this->user_id);
    }

    public function recalcTotais(): void
    {
        $totalServ = $this->servicosItens()->sum('valor_total');
        $totalPec  = $this->pecasItens()->sum('valor_total');
        $frete     = (float) ($this->frete ?? 0);
        $desconto  = (float) ($this->desconto_valor ?? 0); // se um dia existir

        $this->forceFill([
            'total_servicos' => $totalServ,
            'total_pecas'    => $totalPec,
            'total_os'       => max(0, $totalServ + $totalPec + $frete - $desconto),
        ])->save();
    }
}
