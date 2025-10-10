<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
                    ->ignore($veiculoId),
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
        'ano.digits'      => 'O ano deve conter apenas :digits números.',
    ];

    public function index(Request $request)
    {
        // ⚠️ IMPORTANTÍSSIMO: começa pela Model (escopo por user_id já entra)
        $veiculos = Veiculo::query()
            // eager pra evitar N+1 e já trazer o cliente ativo
            ->with(['clienteVinculoAtivo.cliente']);

        // filtros simples (todos passam pelo escopo global)
        if ($request->filled('tipo'))   $veiculos->where('tipo', 'like', '%'.$request->tipo.'%');
        if ($request->filled('marca'))  $veiculos->where('marca', 'like', '%'.$request->marca.'%');
        if ($request->filled('modelo')) $veiculos->where('modelo','like','%'.$request->modelo.'%');
        if ($request->filled('placa'))  $veiculos->where('placa', 'like', '%'.strtoupper($request->placa).'%');

        // ✅ filtro por cliente ATIVO pelo nome — usando relações Eloquent
        // isso garante o filtro por user_id automaticamente (escopo do BelongsToUser)
        if ($request->filled('cliente')) {
            $busca = $request->cliente;
            $veiculos->whereHas('clienteVinculoAtivo.cliente', function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%");
            });
        }

        $veiculos = $veiculos
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // adiciona campo “clienteAtivo” só pra facilitar na view (já veio no eager)
        $veiculos->getCollection()->transform(function (Veiculo $v) {
            $v->clienteAtivo = $v->clienteVinculoAtivo?->cliente?->nome;
            return $v;
        });

        return view('veiculo.index', compact('veiculos'));
    }

    public function create()
    {
        // Model Cliente tem escopo => já vem só do usuário
        $clientes = Cliente::orderBy('nome')->get();
        return view('veiculo.create', compact('clientes'));
    }

    public function requestForObject($request)
    {
        $veiculo = new Veiculo();
        $veiculo->tipo   = $request->tipo;
        $veiculo->marca  = $request->marca;
        $veiculo->modelo = $request->modelo;
        $veiculo->placa  = $request->placa;
        $veiculo->km     = $request->km;
        $veiculo->ano    = $request->ano;
        $veiculo->save(); // BelongsToUser preenche user_id
    }

    public function createSubmit(Request $request)
    {
        $request->validate($this->validaInputRules(), $this->validaInputMessages);

        $veiculo = Veiculo::create([
            'tipo'   => $request->tipo,
            'marca'  => $request->marca,
            'modelo' => $request->modelo,
            'placa'  => $request->placa,
            'km'     => $request->km,
            'ano'    => $request->ano,
            // user_id é setado pelo trait no creating()
        ]);

        if ($request->filled('cliente_id')) {
            ClienteVeiculo::create([
                'cliente_id'  => $request->cliente_id,
                'veiculo_id'  => $veiculo->id,
                'data_inicio' => date('Y-m-d'),
                'ativo'       => true,
                // user_id setado pelo trait
            ]);
        }

        return redirect()->route('veiculo.index')->with('success', 'Veículo cadastrado com sucesso.');
    }

    public function edit($encryptedId)
    {
        try {
            $id      = Crypt::decrypt($encryptedId);
            $veiculo = Veiculo::findOrFail($id); // escopo garante ser do usuário

            $clienteVeiculo = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->where('ativo', true)
                ->latest('data_inicio')
                ->first();

            $clientes = Cliente::orderBy('nome')->get();

            return view('veiculo.edit', compact('veiculo', 'clienteVeiculo', 'clientes'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $th) {
            abort(404);
        }
    }

    public function editSubmit(Request $request)
    {
        $request->validate($this->validaInputRules($request->id), $this->validaInputMessages);

        $veiculo = Veiculo::findOrFail($request->id); // escopo garante dono
        $veiculo->update([
            'tipo'   => $request->tipo,
            'marca'  => $request->marca,
            'modelo' => $request->modelo,
            'placa'  => $request->placa,
            'km'     => $request->km,
            'ano'    => $request->ano,
        ]);

        // relacionamento ativo atual
        $relAtual = ClienteVeiculo::where('veiculo_id', $veiculo->id)
            ->where('ativo', true)
            ->first();

        // se mudou o cliente
        if (!$relAtual || $relAtual->cliente_id != $request->cliente_id) {

            $existeAtivo = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->where('cliente_id', $request->cliente_id)
                ->where('ativo', true)
                ->exists();

            $encryptedId = Crypt::encrypt($veiculo->id);

            if ($existeAtivo) {
                return redirect()
                    ->route('veiculo.edit', ['id' => $encryptedId])
                    ->with('warning', 'Este cliente já está vinculado ativamente a este veículo.');
            }

            if ($relAtual) {
                $relAtual->update([
                    'ativo'    => false,
                    'data_fim' => date('Y-m-d'),
                ]);
            }

            ClienteVeiculo::create([
                'cliente_id'  => $request->cliente_id,
                'veiculo_id'  => $veiculo->id,
                'data_inicio' => date('Y-m-d'),
                'ativo'       => true,
                // user_id via trait
            ]);
        }

        return redirect()->route('veiculo.index')->with('success', 'Veículo atualizado com sucesso.');
    }

    public function desassociarCliente($encryptedId)
    {
        try {
            $idVeiculo = Crypt::decrypt($encryptedId);
            $veiculo   = Veiculo::findOrFail($idVeiculo); // escopo garante dono

            $cliente_veiculo = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->where('ativo', true)
                ->first();

            if ($cliente_veiculo) {
                $cliente_veiculo->update([
                    'ativo'    => false,
                    'data_fim' => date('Y-m-d'),
                ]);
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
            $veiculo   = Veiculo::findOrFail($idVeiculo); // escopo garante dono

            $historico = ClienteVeiculo::where('veiculo_id', $veiculo->id)
                ->with('cliente')
                ->orderByDesc('created_at')
                ->paginate(10);

            return view('veiculo.historico_proprietario', [
                'veiculo'   => $veiculo,
                'historico' => $historico,
                'id'        => $encryptedId,
            ]);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()
                ->route('veiculo.edit', ['id' => $encryptedId])
                ->with('error', 'ID inválido para visualizar histórico.');
        }
    }

    public function destroy($encryptedId)
    {
        try {
            $idVeiculo = Crypt::decrypt($encryptedId);
            $veiculo   = Veiculo::findOrFail($idVeiculo); // escopo garante dono

            $cliente_veiculo = ClienteVeiculo::where('veiculo_id', $idVeiculo)
                ->where('ativo', true)
                ->first();

            $veiculo->delete();
            if ($cliente_veiculo) $cliente_veiculo->delete();

            return redirect()->route('veiculo.index')->with('success', 'Veículo excluído com sucesso.');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $th) {
            return redirect()->route('veiculo.index')->with('error', 'ID inválido para exclusão.');
        }
    }
}
