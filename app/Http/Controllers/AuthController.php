<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        // Se já estiver logado, manda pra home
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function loginSubmit(Request $request)
    {
        // 1) Validação básica
        $credentials = $request->validate(
            [
                'email'    => ['required','email'],
                'password' => ['required','min:6','max:16'],
            ],
            [
                'email.required'    => 'O email é obrigatório.',
                'email.email'       => 'Informe um email válido.',
                'password.required' => 'A senha é obrigatória.',
                'password.min'      => 'A senha deve ter pelo menos :min caracteres.',
                'password.max'      => 'A senha deve ter no máximo :max caracteres.',
            ]
        );

        // (opcional) remember me via checkbox "remember"
        $remember = (bool) $request->boolean('remember');

        // 2) Tenta logar pelo guard padrão
        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('loginError', 'Email ou senha incorretos');
        }

        // 3) Regenera o ID da sessão (anti session fixation)
        $request->session()->regenerate();

        // 4) Atualiza o last_login
        /** @var User $user */
        $user = Auth::user();
        $user->last_login = now();
        $user->save();

        // 5) Redireciona pra home
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Invalida e regenera token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
