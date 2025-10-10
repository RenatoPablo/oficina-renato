<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// IMPORTA teus middlewares:
use App\Http\Middleware\BridgeAuthFromSession;
use App\Http\Middleware\CheckIsLogged; // o seu já existente
use App\Http\Middleware\CheckIsAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1) Cria aliases de rota (pra usar no routes/web.php)
        $middleware->alias([
            'bridge-auth'  => BridgeAuthFromSession::class,
            'check-logged' => CheckIsLogged::class,
            'is_admin' => CheckIsAdmin::class
        ]);

        // 2) (OPÇÃO A) Aplicar em TODAS as rotas (global)
        // Descomente se quiser que a ponte rode sempre:
        // $middleware->append(BridgeAuthFromSession::class);

        // 3) (OPÇÃO B) Aplicar só no grupo "web"
        // $middleware->appendToGroup('web', BridgeAuthFromSession::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
