<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder as QueryBuilder;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // 🔒 Macro global para Query Builder (DB::table) — aqui o escopo não atua
        QueryBuilder::macro('forCurrentUser', function () {
            /** @var \Illuminate\Database\Query\Builder $this */
            if (Auth::check()) {
                $this->where('user_id', Auth::id());
            }
            return $this;
        });
    }
}
