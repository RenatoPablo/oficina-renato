<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends TenantModel
{
    use SoftDeletes, BelongsToUser;

    protected $fillable = ['tipo', 'marca', 'modelo', 'placa', 'km', 'ano', 'user_id'];

    // Se quiser que proprietario_nome apareça quando serializar pra JSON:
    // protected $appends = ['proprietario_nome'];

    protected $casts = [
        'ano' => 'string',   // teu schema usa varchar; muda pra int se alterar no banco
    ];

    /** Histórico de vínculos (um-para-muitos) — blindado por user_id */
    public function clientesVinculos()
    {
        return $this->hasMany(ClienteVeiculo::class, 'veiculo_id')
                    ->where('user_id', $this->user_id);
    }

    /** Vínculo ATIVO (um só) + já traz o cliente junto — blindado por user_id */
    public function clienteVinculoAtivo()
    {
        return $this->hasOne(ClienteVeiculo::class, 'veiculo_id')
                    ->where('user_id', $this->user_id)
                    ->where('ativo', true)
                    ->latest('id')
                    ->with(['cliente' => function ($q) {
                        $q->where('user_id', $this->user_id);
                    }]);
    }

    /** Acessor: $veiculo->proprietario_nome */
    public function getProprietarioNomeAttribute(): ?string
    {
        return $this->clienteVinculoAtivo?->cliente?->nome;
    }

    /** Relacionamento com OS (se usar) — opcional */
    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'veiculo_id')
                    ->where('user_id', $this->user_id);
    }

    /** Setter da placa (aceita padrão antigo ABC-1234 e Mercosul ABC1D23) */
    public function setPlacaAttribute($value): void
    {
        $val = strtoupper(trim((string) $value));

        // formata ABC1234 -> ABC-1234
        if (preg_match('/^[A-Z]{3}\d{4}$/', $val)) {
            $val = substr($val, 0, 3) . '-' . substr($val, 3);
        }

        // aceita Mercosul (ABC1D23) sem alterar
        // (se quiser normalizar também, podemos combinar um formato único)

        $this->attributes['placa'] = $val;
    }

    /** Scope de filtros básicos pro index (opcional) */
    public function scopeFiltro($q, array $f = [])
    {
        return $q
            ->when($f['tipo']   ?? null, fn($qq,$v) => $qq->where('tipo', $v))
            ->when($f['marca']  ?? null, fn($qq,$v) => $qq->where('marca','like',"%$v%"))
            ->when($f['modelo'] ?? null, fn($qq,$v) => $qq->where('modelo','like',"%$v%"))
            ->when($f['placa']  ?? null, fn($qq,$v) => $qq->where('placa','like',"%".strtoupper($v)."%"));
    }
}
