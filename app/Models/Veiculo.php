<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use SoftDeletes;

    protected $fillable = ['tipo', 'marca', 'modelo', 'placa', 'km', 'ano'];

     
    public function cliente()
    {
        return $this->hasOneThrough(
            \App\Models\Cliente::class,
            \App\Models\ClienteVeiculo::class,
            'veiculo_id',   // Foreign key on cliente_veiculo
            'id',           // Foreign key on cliente
            'id',           // Local key on veiculo
            'cliente_id'    // Local key on cliente_veiculo
        );
    }

    //Validacao de placa
    public function setPlacaAttribute($value)
    {
        $val = strtoupper(trim($value));

        // Se vier sem hífen e for padrão antigo, insere
        if (preg_match('/^[A-Z]{3}\d{4}$/', $val)) {
            $val = substr($val,0,3) . '-' . substr($val,3);
        }

        $this->attributes['placa'] = $val;
    }


}
