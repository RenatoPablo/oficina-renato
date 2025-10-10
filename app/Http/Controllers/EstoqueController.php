<?php

namespace App\Http\Controllers;

use App\Helpers\FormHelper;
use App\Models\Estoque;
use Illuminate\Cache\RedisTagSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Laravel\Ui\Presets\React;
use Illuminate\Support\Facades\Auth;

class EstoqueController extends Controller
{
    private function getValidaInputRules(?int $estoqueId = null): array
    {
        return [
            'codigo' => [
                'nullable','string','max:255',
                Rule::unique('estoques','codigo')
                    ->where(fn($q) => $q->where('user_id', Auth::id()))
                    ->ignore($estoqueId), // <- faz funcionar no edit
            ],
            'descricao'  => 'required|string|max:255',
            'quantidade' => 'required|numeric|min:0',
            'preco_rs'   => 'required|min:0',
            'medida'     => 'nullable|string|max:10',
        ];
    }


    private $validaInputMessages = [
        'descricao.required' => 'O nome é obrigatório.',
        'descricao.max' => 'O nome pode ter no máximo :max caracteres.',
        'quantidade.required' => 'A quantidade é obrigatória.',
        'quantidade.numeric' => 'A quantidade deve ser um número.',
        'quantidade.min' => 'A quantidade deve ser no mínimo :min.',
        'preco_rs.required' => 'O preço é obrigatório.',
        'preco_rs.min' => 'O preço deve ser no mínimo :min.',
        'medida.max' => 'A medida pode ter no máximo :max caracteres.',
        'codigo.max' => 'O código pode ter no máximo :max caracteres.'
    ];



    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $estoques = Estoque::query()->where('user_id', Auth::id());

        if($search)
        {
            $estoques->where(function($query) use ($search) 
            {
                $query->where('descricao', 'like', '%' . $search . '%');
            });
        }

        $estoques = $estoques->orderByDesc('created_at')->paginate(10)->withQueryString();

        // var_dump(Auth::id());
        // exit;

        return view('estoque.index', compact('estoques'));
    }

    public function create() 
    {
        return view('estoque.create');
    }

    public function createSubmit(Request $request)
    {
        $request->validate(
            $this->getValidaInputRules(),
            $this->validaInputMessages
        );

        $estoque = new Estoque();

        //descricao
        $estoque->descricao = $request->descricao;

        $estoque->quantidade = $request->quantidade;

        $estoque->preco_rs = (float) $request->preco_rs;

        //codigo
        FormHelper::preencherCampoSeTiver($request, 'codigo', $estoque);

        //medida
        FormHelper::preencherCampoSeTiver($request, 'medida', $estoque);

        $estoque->save();

        return redirect(route('estoque.index'))->with('success', $request->descricao . 'cadastrado com sucesso!');
    }

    public function edit($encrypdtedId)
    {
        try {
            $id = Crypt::decrypt($encrypdtedId);
            $estoque = Estoque::findOrFail($id);
            return view('estoque.edit', compact('estoque'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
    }

    public function editSubmit(Request $request)
    {
        // validate request
        $rules = $this->getValidaInputRules($request->id);
        $rules['codigo'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('estoques')->ignore($request->id),
        ];

        $request->validate($rules, $this->validaInputMessages);

        $estoque = Estoque::findOrFail($request->id);

        $estoque->codigo = $request->codigo;
        $estoque->descricao = $request->descricao;
        $estoque->quantidade = $request->quantidade;
        $estoque->preco_rs = $request->preco_rs;
        $estoque->medida = $request->medida;

        
        $estoque->save();
        
        
        
        return redirect(route('estoque.index'))->with('success', 'Alteração feita com sucesso!');
    }

    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $cliente = Estoque::findOrFail($id);
            $cliente->delete();

            return redirect()->route('estoque.index')->with('success', 'Item removido com sucesso.');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()->route('estoque.index')->with('error', 'ID inválido para exclusão.');
        }
    }
}
