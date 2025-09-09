<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use function PHPUnit\Framework\throwException;

class Estoque extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'descricao',
        'quantidade',
        'preco_rs',
        'medida'
    ];

    /**
     * Ajusta quantidade com trava pessimista.
     * $delta > 0  => devolve (soma)
     * $delta < 0  => consome (subtrai)
     * Lança RuntimeException se ficar negativo.
     */
    public static function ajustarQuantidade(int $id, float $delta): void
    {
    $item = static::lockForUpdate()->findOrFail($id);
    $novo = (float)$item->qtd + $delta;

    if ($novo < 0) {
        throw new \RuntimeException("Estoque insuficiente para o item #{$id}.");
    }

    $item->qtd = $novo;
    $item->save();
    }
}
