<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends TenantModel
{
    use SoftDeletes, BelongsToUser;

    protected $fillable = [
        'nome','contato','ie_rg','cnpj_cpf','endereco','bairro','municipio','uf','cep',
        'telefone','celular','email','observacao','user_id'
    ];

    // histórico de vínculos (se usar na UI)
    public function veiculosVinculos()
    {
        return $this->hasMany(ClienteVeiculo::class, 'cliente_id')
                    ->where('user_id', $this->user_id);
    }

    // formatador de CPF/CNPJ (ótimo!)
    public function getCnpjCpfFormatadoAttribute()
    {
        $valor = preg_replace('/\D/', '', (string) $this->cnpj_cpf);
        if (strlen($valor) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $valor);
        } elseif (strlen($valor) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $valor);
        }
        return $this->cnpj_cpf;
    }
}
