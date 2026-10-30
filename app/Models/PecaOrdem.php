<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class PecaOrdem extends TenantModel
{
    use BelongsToUser;
    use SoftDeletes;

    protected $table = 'pecas_ordem';

    protected $fillable = [
        'ordem_servico_id',
        'estoque_id',
        'qtd',
        'qtd_descontada',
        'valor_unit',
        'valor_total',
        'codigo_cor',
        'user_id',
    ];

    protected $casts = [
        'qtd_descontada' => 'decimal:2',
        'qtd' => 'decimal:5',
        'valor_unit' => 'decimal:5',
        'valor_total' => 'decimal:2',
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
        // Os valores da linha são calculados por ValoresItemOrdem antes de salvar.
        static::saved(function (self $model) {
            $model->ordem?->recalcTotais();
        });

        static::deleted(function (self $model) {
            $model->ordem?->recalcTotais();
        });
    }
}
