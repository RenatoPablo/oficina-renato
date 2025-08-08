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

    public function getCnpjCpfFormatadoAttribute()
    {
        $valor = preg_replace('/\D/', '', $this->cnpj_cpf);

        if (strlen($valor) === 11) {
            // CPF
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $valor);
        } elseif (strlen($valor) === 14) {
            // CNPJ
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $valor);
        }

        return $this->cnpj_cpf; // Retorna como está se não for nenhum dos dois
    }



}
?>