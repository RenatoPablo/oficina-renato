<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
