<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIsAdmin
{
    public function handle(Request $request, Closure $next):  Response
    {
        $user = Auth::user();

        //se nao estiver logado
        if (!$user)
        {
            return redirect('/login')->with('error', 'Você precisa estar logado');
        }

        //se estiver logado mas nao for admin
        if(!$user->is_admin)
        {
            return redirect('/')->with('error', 'Acesso negado: somente administradores.');
        }

        //se estiver logado e for admin
        return $next($request);
    }
}
