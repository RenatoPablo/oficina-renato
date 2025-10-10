<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BridgeAuthFromSession
{
    public function handle($request, Closure $next)
    {
        // Ajusta a chave conforme sua sessão guarda o ID do usuário
        $sessionUserId = session('user_id') ?? session('user.id');

        if (!Auth::check() && $sessionUserId) {
            if ($user = User::find($sessionUserId)) {
                Auth::login($user); // injeta no guard do Laravel
            }
        }

        return $next($request);
    }
}
