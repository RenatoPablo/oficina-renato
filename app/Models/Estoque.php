<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Estoque extends TenantModel
{
    use SoftDeletes, BelongsToUser;

    protected $table = 'estoques';

    protected $fillable = [
        'codigo','codigo_barras','descricao','medida','preco_rs','preco_usd',
        'custo_compra','custo_medio','quantidade','qtd_minima','comissao','lucro','user_id'
    ];

    protected $casts = [
        'preco_rs' => 'float',
        'preco_usd' => 'float',
        'custo_compra' => 'float',
        'custo_medio' => 'float',
        'qtd_minima' => 'float',
        'comissao' => 'float',
        'lucro' => 'float',
        'quantidade' => 'float',
    ];

    /**
     * Ajusta quantidade com trava pessimista.
     * $delta > 0  => adiciona
     * $delta < 0  => consome
     */
    public static function ajustarQuantidade(int $id, float $delta): void
    {
        DB::transaction(function () use ($id, $delta) {
            /** @var self $item */
            $item = static::whereKey($id)->lockForUpdate()->firstOrFail();

            $novo = (float) $item->quantidade + $delta;
            if ($novo < 0) {
                throw new \RuntimeException("Estoque insuficiente para o item #{$id}.");
            }

            $item->quantidade = $novo;
            $item->save();
        });
    }
}
