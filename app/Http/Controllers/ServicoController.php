<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ServicoController extends Controller
{
    private $validaInputRules = [
        'descricao' => 'required|string|max:255',
        'valor_unitario' => 'required|min:0'
    ];

    private $validaInputMessages = [
        'descricao.required' => 'A descrição é obrigatória.',
        'descricao.max' => 'A descrição pode ter no máximo :max caracteres.',
        'valor_unitario.required' => 'O valor unitário é obrigatório.',
        'valor_unitario.min' => 'O valor deve ser no mínimo :min.' 
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');

        $servicos = Servico::query();

        if ($search)
        {
            $servicos->where(function($query) use ($search) 
            {
                $query->where('descricao', 'like', '%'. $search . '%');
            }
            );
        }

        $servicos = $servicos->orderBy('descricao')->paginate(10)->withQueryString();

        return view('servico.index', compact('servicos'));
    }

    public function create()
    {
        return view('servico.create');
    }

    public function createSubmit(Request $request)
    {
        $request->validate(
            $this->validaInputRules,
            $this->validaInputMessages
        );

        $servico = new Servico();

        $servico->descricao = $request->descricao;

        $servico->valor_unitario = (float) $request->valor_unitario;

        $servico->save();

        return redirect(route('servico.index'))->with('sucess', $request->descricao . 'cadastrado com sucesso!');
    }

    public function edit($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $servico = Servico::findOrFail($id);
            return view('servico.edit', compact('servico'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
    }

    public function editSubmit(Request $request)
    {
        $request->validate(
            $this->validaInputRules,
            $this->validaInputMessages
        );

        $servico = Servico::findOrFail($request->id);

        //atualizando valores
        $servico->descricao = $request->descricao;
        $servico->valor_unitario = (float) $request->valor_unitario;
        
        $servico->save();

        return redirect()->route('servico.index')->with('success', 'Serviço atualizado com sucesso!');
    }

    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $servico = Servico::findOrFail($id);
            $servico->delete();

            return redirect()->route('servico.index')->with('sucess', 'Serviço excluído com sucesso!');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $th) {
            redirect()->route('servico.index')->with('error', 'ID invalido para exclusão.');
        }
    }
}
