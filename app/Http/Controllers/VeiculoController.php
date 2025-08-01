<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Laravel\Ui\Presets\React;

class VeiculoController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');

        $veiculos = Veiculo::with('cliente');

        if ($search) {
            $veiculos->where(function ($query) use ($search) {
                $query->where('tipo', 'like', '%' . $search . '%')
                    ->orWhere('marca', 'like', '%' . $search . '%')
                    ->orWhere('modelo', 'like', '%' . $search . '%')
                    ->orWhere('placa', 'like', '%' . $search . '%')
                    ->orWhere('ano', 'like', '%' . $search . '%')
                    ->orWhere('km', 'like', '%' . $search . '%')
                    ->orWhereHas('cliente', function ($q) use ($search) {
                        $q->where('nome', 'like', '%' . $search . '%');
                    });
            });
        }

        $veiculos = $veiculos->orderBy('modelo')->paginate(10)->withQueryString();

        return view('veiculo.index', compact('veiculos'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();

        return view('veiculo.create', compact('clientes'));
    }

    public function createSubmit(Request $request)
    {

    }

    public function desassociarCliente($encryptedId)
    {
        $idVeiculo = Crypt::decrypt($encryptedId);

        $veiculo = Veiculo::findOrFail($idVeiculo);

        $veiculo->cliente_id = null;

        $veiculo->save();

        return redirect()->route('veiculo.index')->with('sucess', 'Cliente desassociado com sucesso!');

    }

}
