<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PecaOrdem extends Model
{
    use SoftDeletes;
    
    protected $table = 'pecas_ordem';

    protected $fillable = [
        'ordem_servico_id',
        'estoque_id',
        'qtd',
        'valor_unit',
        'valor_total',
        'codigo_cor',
    ];

    // RELAÇÕES
    public function ordem()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function estoque()
    {
        return $this->belongsTo(Estoque::class);
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
