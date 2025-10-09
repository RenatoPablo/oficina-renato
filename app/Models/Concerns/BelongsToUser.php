<?php

namespace App\Models\Concerns;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    public static function bootBelongsToUser(): void
    {
        // aplica o escopo global automaticamente
        static::addGlobalScope(new UserScope);

        // preenche user_id ao criar
        static::creating(function ($model) {
            if (Auth::check() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });
    }

    // atalho pra filtrar manualmente pelo usuário atual
    public function scopeForCurrentUser(Builder $query): Builder
    {
        return Auth::check()
            ? $query->withoutGlobalScope(UserScope::class)
                    ->where($query->getModel()->getTable().'.user_id', Auth::id())
            : $query;
    }

    // relação com o dono
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
