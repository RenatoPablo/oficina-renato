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
        'password' => 'required|string|min:6',
        'verifyPassword' => 'required|string|same:password',
    ];

    private $inputValidaMessage = [
        'name.required' => 'O nome é obrigatório.',
        'name.max' => 'Digite ate :max caracteres.',
        'email.required' => 'O email é obrigatório.',
        'password.required' => 'A senha é obrigatória.',
        'password.min' => 'A senha deve ter pelo menos :min caracteres.',
        'verifyPassword.required' => 'É obrigatório confirmar a senha.',
        'verifyPassword.same' => 'As senhas não coincidem.',
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

        $users->password = Hash::make($request->password);

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
            'password' => 'nullable|string|min:6|required_with:verifyPassword',
            'verifyPassword' => 'nullable|string|required_with:password|same:password',
        ];

        $request->validate(
            $inputValidaRulesEdit,
            $this->inputValidaMessage
        );
        
        $user = User::findOrFail($request->id);

        $user->name = $request->name;

        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

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
