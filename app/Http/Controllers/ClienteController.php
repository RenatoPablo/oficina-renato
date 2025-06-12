<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{

    private $validaInputRules = [
        'nome' => 'required|string|max:255',
        'contato' => 'nullable|string|max:255',
        'ie_rg' => 'nullable|string|max:50',
        'cnpj_cpf' => 'nullable|string|max:20|unique:clientes,cnpj_cpf,',
        'endereco' => 'nullable|string|max:255',
        'bairro' => 'nullable|string|max:100',
        'municipio' => 'nullable|string|max:100',
        'uf' => 'nullable|string|size:2',
        'cep' => 'nullable|string|max:9',
        'telefone' => 'nullable|string|max:20',
        'celular' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'observacao' => 'nullable|string'
    ];

    private $validaInputMessages = [
        'nome.required' => 'O nome é obrigatório.',
        'nome.max' => 'O nome pode ter no máximo :max caracteres.',
        'email.email' => 'Informe um email válido.',
        'email.max' => 'O email pode ter no máximo :max caracteres.',
        'uf.size' => 'UF deve conter exatamente :size caracteres.',
        'cep.max' => 'CEP deve conter exatamente :max caracteres.',
        'cnpj_cpf.unique' => 'CNPJ ou CPF já existem em outro cadastro.'
    ];


    public function index() 
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    public function create() 
    {
        return view('clientes.create');
    }

    public function validadeInput()
    {

    }

    public function createSubmit(Request $request)
    {
        // validate request
        $request->validate(
            $this->validaInputRules,
            $this->validaInputMessages
        );

        $cliente = new Cliente();
        $cliente->nome = $request->nome;

        //contato
        if ($request->filled('contato'))
        {
            $cliente->contato = $request->contato;
        }

        // IE/RG
        if ($request->filled('ie_rg'))
        {
            $cliente->ie_rg = $request->ie_rg;
        }

        // CNPJ / CPF
        if ($request->filled('cnpj_cpf'))
        {
            $cliente->cnpj_cpf = $request->cnpj_cpf;
        }

        // endereco
        if ($request->filled('endereco'))
        {
            $cliente->endereco = $request->endereco;
        }

        //bairro
        if ($request->filled('bairro'))
        {
            $cliente->bairro = $request->bairro;
        }

        // municipio
        if ($request->filled('municipio'))
        {
            $cliente->municipio = $request->municipio;
        }

        // uf
        if ($request->filled('uf'))
        {
            $cliente->uf = $request->uf;
        }

        //cep
        if ($request->filled('cep'))
        {
            $cliente->cep = $request->cep;
        }

        //telefone
        if ($request->filled('telefone'))
        {
            $cliente->telefone = $request->telefone;
        }

        //celular
        if ($request->filled('celular'))
        {
            $cliente->celular = $request->celular;
        }

        //email
        if ($request->filled('email'))
        {
            $cliente->email = $request->email;
        }

        //observacao
        if ($request->filled('observacao'))
        {
            $cliente->observacao = $request->observacao;
        }

        $cliente->save();

        return redirect()->route('clientes.index');
    }

    public function edit($encryptedId) 
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $cliente = Cliente::findOrFail($id);
            return view('clientes.edit', compact('cliente'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404); // ID inválido ou alterado
        }
    }

    public function editSubmit(Request $request) 
    {
        // validate request
        $rules = $this->validaInputRules;
        $rules['cnpj_cpf'] = [
            'nullable',
            'string',
            'max:20',
            Rule::unique('clientes')->ignore($request->id),
        ];

        $request->validate($rules, $this->validaInputMessages);

        $cliente = Cliente::findOrFail($request->id);

        //atualizando valores
        $cliente->nome = $request->nome;
        $cliente->contato = $request->contato;
        $cliente->ie_rg = $request->ie_rg;
        $cliente->cnpj_cpf = $request->cnpj_cpf;
        $cliente->endereco = $request->endereco;
        $cliente->bairro = $request->bairro;
        $cliente->municipio = $request->municipio;
        $cliente->uf = $request->uf;
        $cliente->cep = $request->cep;
        $cliente->telefone = $request->telefone;
        $cliente->celular = $request->celular;
        $cliente->email = $request->email;
        $cliente->observacao = $request->observacao;

        $cliente->save();

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }
}
