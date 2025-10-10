<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\OrdemServico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $ordens = OrdemServico::query()
        ->where('user_id', Auth::id())
            ->select([
                'id','veiculo_id','cliente_id',
                'cliente_nome_snapshot',
                'situacao','total_os','data_chamado','created_at',
            ])
            ->with([
                'veiculo:id,placa,marca,modelo',
                'cliente:id,nome,telefone', // só carrega o necessário
            ])
            ->latest('data_chamado')   // ou ->orderByDesc('created_at')
            ->limit(5)                // ou ->paginate(10)
            ->get();

        $estoqueBaixoCount = Estoque::where('quantidade', '<', 5)->count();

        $osAtrasadaCount = OrdemServico::where('data_previsao_entrega', '<', Carbon::today())->count();

        $osAbertaCount = OrdemServico::where('situacao', '=', 'aberta')->count();

        // var_dump(Auth::id());
        // exit;


        return view('dashboard', compact('ordens', 'estoqueBaixoCount', 'osAtrasadaCount', 'osAbertaCount'));
    }
}
