<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicoOrdem extends TenantModel
{
    use SoftDeletes;
    use BelongsToUser;
    
    protected $table = 'servicos_ordem';

    protected $fillable = [
        'ordem_servico_id',
        'servico_id',
        'qtd',
        'valor_unit',
        'valor_total',
        'tecnico',
        'codigo_cor',
        'user_id',
    ];

    protected $casts = [
        'qtd'         => 'decimal:5',
        'valor_unit'  => 'decimal:5',
        'valor_total' => 'decimal:2',
    ];

    // RELAÇÕES
    public function ordem()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    // (Opcional) Sempre manter total coerente
    protected static function booted()
    {
        static::saving(function (self $model) {
            $q = (float)($model->qtd ?? 0);
            $u = (float)($model->valor_unit ?? 0);
            $model->valor_total = $q * $u;
        });

        static::saved(function (self $model) {
            $model->ordem?->recalcTotais();
        });

        static::deleted(function (self $model) {
            $model->ordem?->recalcTotais();
        });
    }
}
