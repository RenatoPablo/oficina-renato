<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\OrdemServico;
use App\Models\PecaOrdem;
use App\Models\Servico;
use App\Models\ServicoOrdem;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SyncAllRequest;

class OrdemServicoController extends Controller
{
    private $validaInputRules = [
            'veiculo_id' => 'required|exists:veiculos,id',
            'data_chamado' => 'required|date',
            'data_previsao_entrega' => 'nullable|date',
            'tipo_atendimento' => 'nullable|string|max:255',
            'situacao' => 'required|string|in:Aberta,Em andamento,Finalizada,Cancelada',
            'atendente' => 'nullable|string|max:255',
            'problema_reclamado' => 'nullable|string',
            'revisao_ate' => 'nullable|string|max:255',
            'frete' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable|string',
    ];

    private $validaInputMessage = [
        'veiculo_id.required' => 'Selecione um veículo.',
        'veiculo_id.exists' => 'O veículo selecionado não foi encontrado no sistema.',

        'data_chamado.required' => 'Informe a data do chamado.',
        'data_chamado.date' => 'A data do chamado deve ser uma data válida.',

        'data_previsao_entrega.date' => 'A previsão de entrega deve ser uma data válida.',

        'tipo_atendimento.string' => 'O tipo de atendimento deve ser um texto.',
        'tipo_atendimento.max' => 'O tipo de atendimento não pode ultrapassar :max caracteres.',

        'situacao.required' => 'Informe a situação da OS.',
        'situacao.string' => 'A situação deve ser um texto.',
        'situacao.in' => 'A situação informada é inválida.',

        'atendente.string' => 'O nome do atendente deve ser um texto.',
        'atendente.max' => 'O nome do atendente não pode ultrapassar :max caracteres.',

        'problema_reclamado.string' => 'A descrição do problema deve ser um texto.',

        'revisao_ate.string' => 'O campo "Revisão até" deve ser um texto.',
        'revisao_ate.max' => 'O campo "Revisão até" não pode ultrapassar :max caracteres.',

        'frete.numeric' => 'O valor do frete deve ser numérico.',
        'frete.min' => 'O valor do frete não pode ser negativo.',

        'observacoes.string' => 'As observações devem ser um texto.',
    ];


    public function index(Request $request)
    {
        $ordens = \App\Models\OrdemServico::with('veiculo') // só o essencial
            ->when($request->filled('numero'), fn($q) => $q->where('id', $request->numero))
            ->when($request->filled('placa'),  fn($q) => $q->whereHas('veiculo', fn($v) => $v->where('placa','like','%'.$request->placa.'%')))
            ->when($request->filled('situacao'), fn($q) => $q->where('situacao', $request->situacao))
            // filtro por cliente via pivot ativo
            ->when($request->filled('cliente'), function($q) use ($request) {
                $q->whereIn('veiculo_id', function($sub) use ($request) {
                    $sub->select('veiculo_id')->from('cliente_veiculo')
                        ->join('clientes','cliente_veiculo.cliente_id','=','clientes.id')
                        ->where('cliente_veiculo.ativo', true)
                        ->where('clientes.nome','like','%'.$request->cliente.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('ordens.index', compact('ordens'));
    }

    public function create()
    {
        $veiculos = Veiculo::select('id', 'placa', 'marca', 'modelo')
                            ->orderBy('modelo')
                            ->get();

        return view('ordens.create', compact('veiculos'));
    }

    public function store(Request $r)
    {
        $data = $r->validate(
            $this->validaInputRules,
            $this->validaInputMessage
        );

        $os = OrdemServico::create($data + [
            'total_servicos' => 0,
            'total_pecas'    => 0,
            'total_os'       => 0,
        ]);



        return redirect()
            ->route('ordem.edit', Crypt::encrypt($os->id))
            ->with('success', 'OS criada. Agora adicione serviços e peças.');
    }

    public function edit($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $os = OrdemServico::with(['veiculo','servicosItens.servico','pecasItens.estoque'])->findOrFail($id);
        
        // Carregamos catálogos aqui pra não mexer no controller
        $servicos = Servico::orderBy('descricao')->get(['id','descricao','valor_unitario']);
        $estoques =  Estoque::orderBy('descricao')->get(['id','descricao','preco_rs']);


        return view('ordens.itens', compact('os', 'servicos', 'estoques'));

    }

    public function syncAll(SyncAllRequest $r, $id)
    {
        $os = OrdemServico::findOrFail($id);

        DB::transaction(function () use ($r, $os) {
            // ---------- PAYLOADS (já validados/normalizados pela SyncAllRequest) ----------
            $servicos = $r->input('servicos', []);
            $pecas    = $r->input('pecas', []);

            // ===================== SERVIÇOS =====================
            // ids existentes no banco (não-trashed)
            $existentesServIds = ServicoOrdem::where('ordem_servico_id', $os->id)->pluck('id')->all();

            // ids enviados na tela
            $enviadosServIds = collect($servicos)->pluck('id')->filter()->map(fn($v) => (int) $v)->all();

            // soft-delete dos que foram removidos da UI
            $idsParaApagarServ = array_diff($existentesServIds, $enviadosServIds);
            if (!empty($idsParaApagarServ)) {
                ServicoOrdem::whereIn('id', $idsParaApagarServ)->delete(); // SoftDeletes
            }

            // upsert linha a linha
            foreach ($servicos as $row) {
                if (empty($row['servico_id'])) continue;

                $q   = (float) ($row['qtd'] ?? 1);
                $u   = (float) ($row['valor_unit'] ?? 0);
                $tot = round($q * $u, 2); // calcula no servidor

                $data = [
                    'ordem_servico_id' => $os->id,
                    'servico_id'       => (int) $row['servico_id'],
                    'qtd'              => $q,
                    'valor_unit'       => $u,
                    'valor_total'      => $tot,
                    'tecnico'          => $row['tecnico']    ?? null,
                    'codigo_cor'       => $row['codigo_cor'] ?? null,
                ];

                // se veio id, atualiza garantindo que pertence à mesma OS; senão, cria
                if (!empty($row['id'])) {
                    $item = ServicoOrdem::where('id', (int) $row['id'])
                        ->where('ordem_servico_id', $os->id)
                        ->first();

                    if ($item) $item->update($data);
                    else       ServicoOrdem::create($data);
                } else {
                    ServicoOrdem::create($data);
                }
            }

            // ===================== PEÇAS =====================
            $existentesPecaIds = PecaOrdem::where('ordem_servico_id', $os->id)->pluck('id')->all();
            $enviadosPecaIds   = collect($pecas)->pluck('id')->filter()->map(fn($v) => (int) $v)->all();

            $idsParaApagarPecas = array_diff($existentesPecaIds, $enviadosPecaIds);
            if (!empty($idsParaApagarPecas)) {
                PecaOrdem::whereIn('id', $idsParaApagarPecas)->delete(); // SoftDeletes
            }

            foreach ($pecas as $row) {
                if (empty($row['estoque_id'])) continue;

                $q   = (float) ($row['qtd'] ?? 1);
                $u   = (float) ($row['valor_unit'] ?? 0);
                $tot = round($q * $u, 2);

                $data = [
                    'ordem_servico_id' => $os->id,
                    'estoque_id'       => (int) $row['estoque_id'],
                    'qtd'              => $q,
                    'valor_unit'       => $u,
                    'valor_total'      => $tot,
                    'codigo_cor'       => $row['codigo_cor'] ?? null,
                ];

                if (!empty($row['id'])) {
                    $item = PecaOrdem::where('id', (int) $row['id'])
                        ->where('ordem_servico_id', $os->id)
                        ->first();

                    if ($item) $item->update($data);
                    else       PecaOrdem::create($data);
                } else {
                    PecaOrdem::create($data);
                }
            }

            // ===================== FRETE & TOTAIS =====================
            $os->frete = (float) $r->input('frete', 0);

            // soma direto do banco (exclui soft-deletados por padrão)
            $totalServ = ServicoOrdem::where('ordem_servico_id', $os->id)->sum('valor_total');
            $totalPec  = PecaOrdem::where('ordem_servico_id', $os->id)->sum('valor_total');

            $os->update([
                'total_servicos' => $totalServ,
                'total_pecas'    => $totalPec,
                'total_os'       => $totalServ + $totalPec + $os->frete,
            ]);
        });

        return back()->with('success', 'Itens salvos com sucesso!');
    }

    //FUNCAO PARA VALIDAR INPUTS DE VALOR
    function normalizeCurrency(?string $v): float {
        if ($v === null) return 0.0;
        $v = preg_replace('/[^\d,\.]/', '', $v);
        if (str_contains($v, ',')) $v = str_replace('.', '', $v); // tira milhares
        $v = str_replace(',', '.', $v); // vírgula -> ponto
        return (float) $v;
    }




}
