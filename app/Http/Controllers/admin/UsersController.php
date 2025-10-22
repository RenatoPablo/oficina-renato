<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\error;

class UsersController extends Controller
{
    private $inputValidaRules = [
        'name' => 'required|string|max:100',
        'email' => 'required|email',
        'password' => 'required|string',
        'verifyPassword' => 'required|string',
    ];

    private $inputValidaMessage = [
        'name.required' => 'O nome é obrigatório.',
        'name.max' => 'Digite ate :max caracteres.',
        'email.required' => 'O email é obrigatório.',
        'password.required' => 'A senha é obrigatória.',
        'verifyPassword' => 'É obrigatório confirmar a senha.' 
    ];


    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::query();

        if($search)
        {
            $users->where(function($query) use ($search)
            {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $users = $users->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));

    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function createSubmit(Request $request)
    {
        $request->validate(
            $this->inputValidaRules,
            $this->inputValidaMessage
        );

        $users = new User();

        $users->name = $request->name;

        $users->email = $request->email;

        if ($request->password === $request->verifyPassword)
        {
            $hashPassword = Hash::make($request->password);
        }

        $users->password = $hashPassword;

        $users->is_admin = (int) $request->permissao;

        $users->ativo = (int) $request->ativo;

        $users->save();

        return redirect()->route('admin.users')->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function edit($encryptedId) 
    {
        try {
            $id = Crypt::decrypt($encryptedId);

            $user = User::findOrFail($id);
            return view('admin.users.edit', compact('user'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
    }

    public function editSubmit(Request $request) 
    {
        $inputValidaRulesEdit = [
            'name' => 'required|string|max:100',
            'email' => 'required|email',            
        ];

        $request->validate(
            $inputValidaRulesEdit,
            $this->inputValidaMessage
        );
        
        $user = User::findOrFail($request->id);

        $user->name = $request->name;

        $user->email = $request->email;

        if ($request->password === $request->verifyPassword)
        {
            $passwordHash = Hash::make($request->password);
        } else {
            return redirect()->route('admin.users.edit', $user->id)->with('error', 'Senhas não coincidem.');
        }

        $user->password = $passwordHash;

        $user->is_admin = (int) $request->permissao;

        $user->ativo = (int) $request->ativo;

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Usuário alterado com sucesso!');
    }

    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('admin.users')->with('success', 'Usuário removido com sucesso..');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()->route('admin.users')->with('error', 'Erro ao remover usuário: '.$e);
        }
    }
}
