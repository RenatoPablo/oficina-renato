<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteVeiculo extends TenantModel
{
    use SoftDeletes, BelongsToUser;

    protected $table = 'cliente_veiculo';
    protected $fillable = ['cliente_id','veiculo_id','ativo','data_inicio','data_fim','user_id'];
    public $timestamps = true;

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id')
                    ->where('user_id', $this->user_id);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id')
                    ->where('user_id', $this->user_id);
    }
}
