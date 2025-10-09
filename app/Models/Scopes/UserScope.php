<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Se o usuário estiver logado e NÃO for admin, filtra pelo user_id
        if (Auth::check() && !optional(Auth::user())->is_admin) {
            $builder->where($model->getTable() . '.user_id', Auth::id());
        }
    }
}
