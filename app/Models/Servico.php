<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servico extends TenantModel
{
    use SoftDeletes, BelongsToUser;

    protected $table = 'servicos';

    protected $fillable = ['descricao','valor_unitario','user_id'];

    protected $casts = [
        'valor_unitario' => 'float',
    ];
}
