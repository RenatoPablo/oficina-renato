<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'contato',
        'ie_rg',
        'cnpj_cpf',
        'endereco',
        'bairro',
        'municipio',
        'uf',
        'cep',
        'telefone',
        'celular',
        'email',
        'observacao',
    ];
}
?>