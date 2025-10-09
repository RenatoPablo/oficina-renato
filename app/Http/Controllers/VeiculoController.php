<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteVeiculo;
use App\Models\Veiculo;
use GuzzleHttp\Client;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Laravel\Ui\Presets\React;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class VeiculoController extends Controller
{
    private function validaInputRules($veiculoId = null): array
    {
        return [
            'tipo'   => 'required|string|max:255',
            'marca'  => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'placa'  => [
                'required',
                Rule::unique('veiculos', 'placa')
                    ->where(fn($q) => $q->where('user_id', Auth::id()))
                    ->ignore($veiculoId), // pra editar sem dar erro
            ],
            'km'  => 'nullable|numeric',
            'ano' => 'nullable|digits:4',
        ];
    }

    private $validaInputMessages = [
        'tipo.required'   => 'O tipo deve ser preenchido.',
        'tipo.max'        => 'O tipo não deve conter mais do que :max caracteres.',
        'marca.required'  => 'A marca deve ser preenchida.',
        'marca.max'       => 'A marca não pode conter mais do que :max caracteres.',
        'modelo.required' => 'O modelo deve ser preenchido.',
        'modelo.max'      => 'O modelo não pode conter mais do que :max caracteres.',

        'placa.required'  => 'A placa deve ser preenchida.',
        'placa.regex'     => 'A placa deve estar no formato AAA-1234 ou AAA1A23.',

        'km.numeric'      => 'O KM deve conter somente números.',
        'ano.numeric'     => 'O ano deve conter somente números.',
        'ano.digits'      => 'O ano deve conter apenas :digits números.'
    ];


// metodo index antigo
    // public function index(Request $request)
    // {
    //     $search = $request->input('search');

    //     $veiculos = Veiculo::with('cliente');

    //     if ($search) {
    //         $veiculos->where(function ($query) use ($search) {
    //             $query->where('tipo', 'like', '%' . $search . '%')
    //                 ->orWhere('marca', 'like', '%' . $search . '%')
    //                 ->orWhere('modelo', 'like', '%' . $search . '%')
    //                 ->orWhere('placa', 'like', '%' . $search . '%')
    //                 ->orWhere('ano', 'like', '%' . $search . '%')
    //                 ->orWhere('km', 'like', '%' . $search . '%')
    //                 ->orWhereHas('cliente', function ($q) use ($search) {
    //                     $q->where('nome', 'like', '%' . $search . '%');
    //                 });
    //         });
    //     }

    //     $veiculos = $veiculos->orderBy('modelo')->paginate(10)->withQueryString();

    //     return view('veiculo.index', compact('veiculos'));
    // }

    public function index(Request $request)
    {
        $veiculos = Veiculo::query();

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $veiculos->where('tipo', 'like', '%' . $request->tipo . '%');
        }

        // Filtro por marca
        if ($request->filled('marca')) {
            $veiculos->where('marca', 'like', '%' . $request->marca . '%');
        }

        // Filtro por modelo
        if ($request->filled('modelo')) {
            $veiculos->where('modelo', 'like', '%' . $request->modelo . '%');
        }

        // Filtro por placa (simples)
        if ($request->filled('placa')) {
            $veiculos->where('placa', 'like', '%' . $request->placa . '%');
        }

        // Filtro por cliente ativo
        if ($request->filled('cliente')) {
            $clienteSearch = $request->cliente;
            $veiculos->whereIn('id', function ($sub) use ($clienteSearch) {
                $sub->select('veiculo_id')
                    ->from('cliente_veiculo')
                    ->join('clientes', 'cliente_veiculo.cliente_id', '=', 'clientes.id')
                    ->where('cliente_veiculo.ativo', true)
                    ->where('clientes.nome', 'like', "%{$clienteSearch}%");
            });
        }

        $veiculos = $veiculos->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Adiciona o cliente ativo no objeto
        $veiculos->getCollection()->transform(function ($veiculo) {
            $clienteAtivo = \App\Models\ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->where('ativo', true)
                ->with('cliente')
                ->first();

            $veiculo->clienteAtivo = $clienteAtivo?->cliente?->nome ?? null;
            return $veiculo;
        });

        return view('veiculo.index', compact('veiculos'));
    }


    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();

        return view('veiculo.create', compact('clientes'));
    }

    public function requestForObject($request)
    {
        $veiculo = new Veiculo();

        $veiculo->tipo = $request->tipo;

        $veiculo->marca = $request->marca;

        $veiculo->modelo = $request->modelo;

        $veiculo->placa = $request->placa;

        $veiculo->km = $request->km;

        $veiculo->ano = $request->ano;

        $veiculo->save();
    }

    public function createSubmit(Request $request)
    {
        $request->validate(
            $this->validaInputRules(),
            $this->validaInputMessages
        );

        $veiculo = new Veiculo();

        $veiculo->tipo = $request->tipo;

        $veiculo->marca = $request->marca;

        $veiculo->modelo = $request->modelo;

        $veiculo->placa = $request->placa;

        $veiculo->km = $request->km;

        $veiculo->ano = $request->ano;

        $veiculo->save();

        if ($request->filled('cliente_id'))
        {
            $cliente_veiculo = new ClienteVeiculo();
    
            $cliente_veiculo->cliente_id = $request->cliente_id;
    
            $cliente_veiculo->veiculo_id = $veiculo->id;
    
            $cliente_veiculo->data_inicio = date('Y-m-d');
    
            $cliente_veiculo->save();
        }


        return redirect(route('veiculo.index'))->with('success', 'Veiculo cadastrado com sucesso.');      
    }

    public function edit($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $veiculo = Veiculo::findOrFail($id);

            $clienteVeiculo = ClienteVeiculo::where('veiculo_id', $veiculo->id)
            ->where('ativo', true)
            ->latest('data_inicio') // opcional, pra pegar o mais recente se tiver vários ativos (caso bugado)
            ->first();

            $clientes = Cliente::orderBy('nome')->get();

            return view('veiculo.edit', compact('veiculo', 'clienteVeiculo', 'clientes'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $th) {
            abort(404);
        }
    }

    public function editSubmit(Request $request)
    {

        
        $request->validate(
            $this->validaInputRules($request->id),
            $this->validaInputMessages
        );

        $veiculo = Veiculo::findOrFail($request->id);

        $veiculo->tipo = $request->tipo;

        $veiculo->marca = $request->marca;

        $veiculo->modelo = $request->modelo;

        $veiculo->placa = $request->placa;

        $veiculo->km = $request->km;

        $veiculo->ano = $request->ano;

        $veiculo->save();
        
        //busca o relacionamenteo ativo atual (se existir)
        $relacionamentAtual = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                                            ->where('ativo', true)
                                            ->first();
        
        //verifica se o cliente mudou
        if (
            !$relacionamentAtual ||
            $relacionamentAtual->cliente_id != $request->cliente_id
        )
        {
            // 🔍 Verifica se já existe relacionamento ativo com esse mesmo cliente e veículo
            $existeRelacionamentoAtivo = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->where('cliente_id', $request->cliente_id)
                ->where('ativo', true)
                ->exists();

            //cryptografando id do veiculo
            $encryptedId = Crypt::encrypt($veiculo->id);

            if ($existeRelacionamentoAtivo) {
                return redirect()
                        ->route('veiculo.edit', ['id' => $encryptedId])
                        ->with('warning', 'Este cliente já está vinculado ativamente a este veículo.');
            }

            //desativa o antig, se tiver
            if ($relacionamentAtual) 
            {
                $relacionamentAtual->ativo = false;
                $relacionamentAtual->data_fim = date('Y-m-d');
                $relacionamentAtual->save();
            }


            //cria novo relacionamento
            $novoRelacionemento = new ClienteVeiculo();
            $novoRelacionemento->cliente_id = $request->cliente_id;
            $novoRelacionemento->veiculo_id = $veiculo->id;
            $novoRelacionemento->data_inicio = date('Y-m-d');
            $novoRelacionemento->ativo = true;
            $novoRelacionemento->save();
        }

        return redirect()->route('veiculo.index')->with('success', 'Veiculo atualizado com sucesso.');
        
    }

    public function desassociarCliente($encryptedId)
    {
        try {
            $idVeiculo = Crypt::decrypt($encryptedId);

            $veiculo = Veiculo::findOrFail($idVeiculo);

            $cliente_veiculo = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                                            ->where('ativo', true)
                                            ->first();

        
        if($cliente_veiculo)
        {
            $cliente_veiculo->ativo = false;
            $cliente_veiculo->data_fim = date('Y-m-d');
            $cliente_veiculo->save();
        }


        return redirect()
                ->route('veiculo.edit', ['id' => $encryptedId])
                ->with('success', 'Cliente desassociado com sucesso!');

        } catch (\Illuminate\Contracts\Encryption\DecryptException $th) {
            abort(404);
        }
        

    }

    public function historicoProprietario(string $encryptedId)
    {
        try {
            $idVeiculo = Crypt::decrypt($encryptedId);

            $veiculo = Veiculo::findOrFail($idVeiculo);

            // se quiser paginação, troque get() por paginate(10)->withQueryString()
            $historico = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->with('cliente')                 // pra já trazer o nome
                ->orderByDesc('created_at')      // mais recente primeiro
                ->paginate(10);

            return view('veiculo.historico_proprietario', [
                'veiculo'   => $veiculo,
                'historico' => $historico,
                'id'        => $encryptedId,      // útil pra links de voltar
            ]);

        } catch (DecryptException $e) {
            return redirect()
                ->route('veiculo.edit', ['id' => $encryptedId])
                ->with('error', 'ID inválido para visualizar histórico.');
        }
    }

    public function destroy($encryptedId)
    {
        try {
            $idVeiculo = Crypt::decrypt($encryptedId);
            $veiculo = Veiculo::findOrFail($idVeiculo);

            $cliente_veiculo = ClienteVeiculo::where('veiculo_id', $idVeiculo)
                                             ->where('ativo', true)
                                             ->first();

            $veiculo->delete();
            if ($cliente_veiculo)
            {
                $cliente_veiculo->delete();
            }

            return redirect()->route('veiculo.index')->with('success', 'Veiculo foi excluído com sucesso');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $th) {
            return redirect()->route('veiculo.index')->with('error', 'ID invalido para exclusão.');
        }

    }

}
