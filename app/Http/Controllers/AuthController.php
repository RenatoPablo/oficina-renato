<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login() 
    {
        return view('login');
    }

    public function loginSubmit(Request $request)
    {
        // form validation 
        $request->validate(
            //rules
            [
                'email' => 'required|email',
                'password' => 'required|min:6|max:16'
            ],
            //error messages
            [
                'email.required' => 'O username é obrigatório.',
                'email.email' => 'Username deve ser um email valido.',
                'password.required' => 'A password é obrigatório',
                'password.min' => 'A password deve conter pelo menos :min caracteres',
                'password.max' => 'A password deve ter no máximo :max caracteres',
            ]
        );

        //get user input
        $email = $request->input('email');
        $password = $request->input('password'); 


        //check if user exists
        $user = User::where('email', $email)->first();
                      
        
        if(!$user)
        {
            return redirect()->back()->withInput()->with('loginError', 'Email ou senha incorretos');
        }


        //check if password is correct
        if(!Hash::check($password, $user->password))
        {
            return redirect()->back()->withInput()->with('loginError', 'Email ou senha incorretos');
        }

        //update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        //login user
        session([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role ?? 'usuario'
            ]
        ]);

        //redirect to home
        return redirect()->to('/');
        
    }

    public function logout() 
    {
        //logout from application 
        session()->forget('user');
        return redirect()->to('/login');
    }
}

