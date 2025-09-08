<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use SoftDeletes;

    protected $fillable = ['tipo', 'marca', 'modelo', 'placa', 'km', 'ano'];

    /** Histórico de vínculos (um-para-muitos) */
    public function clientesVinculos()
    {
        return $this->hasMany(ClienteVeiculo::class, 'veiculo_id');
    }

    /** Vínculo ATIVO (um só) + já traz o cliente junto */
    public function clienteVinculoAtivo()
    {
        return $this->hasOne(ClienteVeiculo::class, 'veiculo_id')
            ->where('ativo', true)
            ->latest('id')          // se houver mais de um marcado por engano
            ->with('cliente');      // eager do cliente
    }

    /** Acessor: $veiculo->proprietario_nome */
    public function getProprietarioNomeAttribute(): ?string
    {
        return $this->clienteVinculoAtivo?->cliente?->nome;
    }

    // Validacao de placa (mantido)
    public function setPlacaAttribute($value)
    {
        $val = strtoupper(trim($value));

        if (preg_match('/^[A-Z]{3}\d{4}$/', $val)) {
            $val = substr($val,0,3) . '-' . substr($val,3);
        }

        $this->attributes['placa'] = $val;
    }
}

