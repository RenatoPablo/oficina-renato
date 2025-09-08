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

   // META (dados gerais) – VIEW
    public function edit($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $veiculos = Veiculo::select('id', 'marca', 'placa', 'modelo')->orderBy('modelo')->get();

        $os = OrdemServico::with('veiculo')->findOrFail($id);
        return view('ordens.edit', compact('os', 'veiculos'));        // <- usa ordens/edit.blade.php
    }



    public function itens($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $os       = OrdemServico::with(['servicosItens','pecasItens','veiculo'])->findOrFail($id);
        $servicos = Servico::orderBy('descricao')->get();
        $estoques = Estoque::orderBy('descricao')->get();
        return view('ordens.itens', compact('os','servicos','estoques')); // <- usa ordens/itens.blade.php
    }

    public function syncAll(SyncAllRequest $r, $id)
    {
        $os = OrdemServico::findOrFail($id);

        try {
            DB::transaction(function () use ($r, $os) {
                // ---------- payloads ----------
                $servicos = $r->input('servicos', []);
                $pecas    = $r->input('pecas',    []);

                // ============ SERVIÇOS (mantém teu código) ============
                $existentesServIds = ServicoOrdem::where('ordem_servico_id', $os->id)->pluck('id')->all();
                $enviadosServIds   = collect($servicos)->pluck('id')->filter()->map(fn($v)=>(int)$v)->all();
                $idsParaApagarServ = array_diff($existentesServIds, $enviadosServIds);
                if (!empty($idsParaApagarServ)) {
                    ServicoOrdem::whereIn('id', $idsParaApagarServ)->delete();
                }
                foreach ($servicos as $row) {
                    if (empty($row['servico_id'])) continue;
                    $q   = (float) ($row['qtd'] ?? 1);
                    $u   = (float) ($row['valor_unit'] ?? 0);
                    $tot = round($q * $u, 2);

                    $data = [
                        'ordem_servico_id' => $os->id,
                        'servico_id'       => (int) $row['servico_id'],
                        'qtd'              => $q,
                        'valor_unit'       => $u,
                        'valor_total'      => $tot,
                        'tecnico'          => $row['tecnico']    ?? null,
                        'codigo_cor'       => $row['codigo_cor'] ?? null,
                    ];

                    if (!empty($row['id'])) {
                        $item = ServicoOrdem::where('id', (int)$row['id'])
                            ->where('ordem_servico_id', $os->id)
                            ->first();

                        $item ? $item->update($data) : ServicoOrdem::create($data);
                    } else {
                        ServicoOrdem::create($data);
                    }
                }

                // ===================== PEÇAS (AJUSTE POR DELTA AGREGADO) =====================
                // Snapshot atual (não-trashed)
                $atuais = PecaOrdem::where('ordem_servico_id', $os->id)->get()->keyBy('id');

                // Mapa de ajustes por estoque_id
                // Regra: valor POSITIVO => DEVOLVE; valor NEGATIVO => CONSUME
                $ajustes = [];

                // 1) Itens removidos na UI -> devolve tudo
                $idsEnviados = collect($pecas)->pluck('id')->filter()->map(fn($v)=>(int)$v)->all();
                $removidos   = $atuais->keys()->diff($idsEnviados);

                foreach ($removidos as $remId) {
                    $item  = $atuais[$remId];
                    $eid   = (int) $item->estoque_id;
                    $qDevolve = (float) $item->qtd;
                    if (!isset($ajustes[$eid])) $ajustes[$eid] = 0;
                    $ajustes[$eid] += $qDevolve; // devolve
                }

                // 2) Itens enviados (novos ou existentes)
                $linhasParaSalvar = [];   // guarda payload normalizado pra persistir depois
                $idsProcessados   = [];

                foreach ($pecas as $row) {
                    if (empty($row['estoque_id'])) continue;

                    $idLinha    = !empty($row['id']) ? (int)$row['id'] : null;
                    $estoqueNew = (int) $row['estoque_id'];
                    $qNova      = (float) ($row['qtd'] ?? 1);
                    $u          = (float) ($row['valor_unit'] ?? 0);
                    $tot        = round($qNova * $u, 2);

                    $linhasParaSalvar[] = [
                        'id'               => $idLinha,
                        'ordem_servico_id' => $os->id,
                        'estoque_id'       => $estoqueNew,
                        'qtd'              => $qNova,
                        'valor_unit'       => $u,
                        'valor_total'      => $tot,
                        'codigo_cor'       => $row['codigo_cor'] ?? null,
                    ];
                    if ($idLinha) $idsProcessados[] = $idLinha;

                    if ($idLinha && isset($atuais[$idLinha])) {
                        // EXISTENTE
                        $ant = $atuais[$idLinha];
                        $estoqueOld = (int) $ant->estoque_id;
                        $qAnt       = (float) $ant->qtd;

                        if ($estoqueOld === $estoqueNew) {
                            // mesmo produto: consumir/devolver apenas o delta
                            $delta = $qNova - $qAnt;
                            if ($delta !== 0.0) {
                                if (!isset($ajustes[$estoqueNew])) $ajustes[$estoqueNew] = 0;
                                $ajustes[$estoqueNew] += (-$delta); // delta>0 consome (-); delta<0 devolve (+)
                            }
                        } else {
                            // mudou de produto: devolve tudo do antigo e consome tudo do novo
                            if (!isset($ajustes[$estoqueOld])) $ajustes[$estoqueOld] = 0;
                            if (!isset($ajustes[$estoqueNew])) $ajustes[$estoqueNew] = 0;
                            $ajustes[$estoqueOld] += $qAnt;     // devolve
                            $ajustes[$estoqueNew] += (-$qNova); // consome
                        }
                    } else {
                        // NOVO
                        if (!isset($ajustes[$estoqueNew])) $ajustes[$estoqueNew] = 0;
                        $ajustes[$estoqueNew] += (-$qNova); // consome total
                    }
                }

                // 3) Aplica TODOS os ajustes de estoque primeiro (com trava)
                //    Se algum ficar negativo, lança e aborta a transação inteira
                foreach ($ajustes as $estoqueId => $ajuste) {
                    if ($ajuste == 0.0) continue;
                    Estoque::ajustarQuantidade((int)$estoqueId, (float)$ajuste);
                }

                // 4) Agora persiste as linhas (seguro pois estoque já conferido)
                //    4.1 Deleta os removidos (já devolvidos no passo 1)
                foreach ($removidos as $remId) {
                    $atuais[$remId]->delete();
                }

                //    4.2 Upsert dos existentes e criação dos novos
                foreach ($linhasParaSalvar as $data) {
                    if (!empty($data['id'])) {
                        PecaOrdem::where('id', $data['id'])
                            ->where('ordem_servico_id', $os->id)
                            ->update([
                                'estoque_id'  => $data['estoque_id'],
                                'qtd'         => $data['qtd'],
                                'valor_unit'  => $data['valor_unit'],
                                'valor_total' => $data['valor_total'],
                                'codigo_cor'  => $data['codigo_cor'],
                            ]);
                    } else {
                        PecaOrdem::create($data);
                    }
                }

                // ============ FRETE & TOTAIS ============
                $os->frete = (float) $r->input('frete', 0);
                $os->save();
                $os->recalcTotais(); // teu método no model
            });

            return back()->with('success', 'Itens e estoque atualizados com sucesso!');
        } catch (\RuntimeException $e) {
            // Erros de estoque (negativo etc.)
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            // Qualquer outra exceção
            report($e);
            return back()->with('error', 'Falha ao salvar a OS.')->withInput();
        }
    }

    public function updateMeta(Request $r, $id)
    {
        $os = OrdemServico::findOrFail($id);

        $data = $r->validate([
            'proprietario'          => ['nullable','string','max:255'],
            'situacao'              => ['required','in:Aberta,Em andamento,Finalizada,Cancelada'],
            'data_previsao_entrega' => ['nullable','date'],
            'observacoes'           => ['nullable','string'],
        ]);

        $os->fill($data)->save();

        return back()->with('success', 'Dados da OS atualizados.');
    }

    public function fechar($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $os = OrdemServico::findOrFail($id);

        // regra: só fecha se tiver itens, por exemplo
        if ($os->total_os <= 0) {
            return back()->with('error', 'Não é possível finalizar uma OS sem itens.');
        }

        $os->situacao = 'Finalizada';
        $os->save();

        // Trave edição de itens no front (exiba disabled) e no back (guards no syncAll)
        return back()->with('success', 'OS finalizada!');
    }

    //FUNCAO PARA VALIDAR INPUTS DE VALOR
    function normalizeCurrency(?string $v): float {
        if ($v === null) return 0.0;
        $v = preg_replace('/[^\d,\.]/', '', $v);
        if (str_contains($v, ',')) $v = str_replace('.', '', $v); // tira milhares
        $v = str_replace(',', '.', $v); // vírgula -> ponto
        return (float) $v;
    }

    function show($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $os = OrdemServico::with([
            'veiculo',
            'servicosItens.servico',
            'pecasItens.estoque',
        ])->findOrFail($id);

        return view('ordens.show', compact('os'));
    }
    




}
