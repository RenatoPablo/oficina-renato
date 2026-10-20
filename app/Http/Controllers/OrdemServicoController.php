<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncAllRequest;
use App\Models\ClienteVeiculo;
use App\Models\Estoque;
use App\Models\OrdemServico;
use App\Models\Servico;
use App\Models\ServicoOrdem;
use App\Models\Veiculo;
use App\Services\EstoqueOrdemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

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
        // Carrega também cliente pra fallback; NÃO buscar dono ativo aqui!
        $ordens = OrdemServico::with(['veiculo', 'cliente'])
            ->when($request->filled('numero'), fn ($q) => $q->whereKey($request->numero))
            ->when($request->filled('placa'), fn ($q) => $q->whereHas('veiculo', fn ($v) => $v->where('placa', 'like', '%'.$request->placa.'%')))
            ->when($request->filled('situacao'), fn ($q) => $q->where('situacao', $request->situacao))
            // Filtro por cliente usando snapshot OU relacionamento (histórico!)
            ->when($request->filled('cliente'), function ($q) use ($request) {
                $term = '%'.$request->cliente.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('cliente_nome_snapshot', 'like', $term)
                        ->orWhereHas('cliente', fn ($c) => $c->where('nome', 'like', $term));
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
        $data = $r->validate($this->validaInputRules, $this->validaInputMessage);

        // Vínculo ATUAL do veículo (pra CONGELAR na OS)
        $vinculo = ClienteVeiculo::with('cliente')
            ->where('veiculo_id', $data['veiculo_id'])
            ->where('ativo', true)
            ->first();

        $snapshot = [
            'cliente_id' => $vinculo?->cliente_id,
            'cliente_veiculo_id' => $vinculo?->id,
            'cliente_nome_snapshot' => $vinculo?->cliente?->nome,
            'cliente_documento_snapshot' => $vinculo?->cliente?->cnpj_cpf
                                         ?? $vinculo?->cliente?->cpf
                                         ?? null,
        ];

        $os = OrdemServico::create($data + $snapshot + [
            'total_servicos' => 0,
            'total_pecas' => 0,
            'total_os' => 0,
        ]);

        return redirect()
            ->route('ordem.itens', Crypt::encrypt($os->id))
            ->with('success', 'OS criada. Agora adicione serviços e peças.');
    }

    public function edit($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $veiculos = Veiculo::select('id', 'marca', 'placa', 'modelo')->orderBy('modelo')->get();
        // carrega cliente também se for exibir na tela
        $os = OrdemServico::with(['veiculo', 'cliente'])->findOrFail($id);

        return view('ordens.edit', compact('os', 'veiculos'));
    }

    public function itens($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $os = OrdemServico::with(['servicosItens', 'pecasItens', 'veiculo', 'cliente'])->findOrFail($id);
        $servicos = Servico::orderBy('descricao')->get();
        $estoques = Estoque::orderBy('descricao')->get();

        return view('ordens.itens', compact('os', 'servicos', 'estoques'));
    }

    public function syncAll(SyncAllRequest $r, $id)
    {
        $os = OrdemServico::findOrFail($id);
        if ($os->situacao === 'Finalizada') {
            return back()->with('error', 'Os finalizada não permite alterações');
        }

        try {
            DB::transaction(function () use ($r, $os) {
                $os = OrdemServico::whereKey($os->id)->lockForUpdate()->firstOrFail();
                if ($os->situacao === 'Finalizada') {
                    throw new \RuntimeException('OS finalizada não permite alterações.');
                }
                // ---------- payloads ----------
                $servicos = $r->input('servicos', []);
                $pecas = $r->input('pecas', []);

                // ============ SERVIÇOS (mantém teu código) ============
                $existentesServIds = ServicoOrdem::where('ordem_servico_id', $os->id)->pluck('id')->all();
                $enviadosServIds = collect($servicos)->pluck('id')->filter()->map(fn ($v) => (int) $v)->all();
                $idsParaApagarServ = array_diff($existentesServIds, $enviadosServIds);
                if (! empty($idsParaApagarServ)) {
                    ServicoOrdem::whereIn('id', $idsParaApagarServ)->delete();
                }
                foreach ($servicos as $row) {
                    if (empty($row['servico_id'])) {
                        continue;
                    }
                    $q = (float) ($row['qtd'] ?? 1);
                    $u = (float) ($row['valor_unit'] ?? 0);
                    $tot = round($q * $u, 2);

                    $data = [
                        'ordem_servico_id' => $os->id,
                        'servico_id' => (int) $row['servico_id'],
                        'qtd' => $q,
                        'valor_unit' => $u,
                        'valor_total' => $tot,
                        'tecnico' => $row['tecnico'] ?? null,
                        'codigo_cor' => $row['codigo_cor'] ?? null,
                    ];

                    if (! empty($row['id'])) {
                        $item = ServicoOrdem::where('id', (int) $row['id'])
                            ->where('ordem_servico_id', $os->id)
                            ->first();

                        $item ? $item->update($data) : ServicoOrdem::create($data);
                    } else {
                        ServicoOrdem::create($data);
                    }
                }

                app(EstoqueOrdemService::class)->sincronizar($os, $pecas);

                // ============ FRETE & TOTAIS ============
                $os->frete = $this->normalizeCurrency($r->input('frete', '0'));
                $os->save();
                $os->recalcTotais();
            });

            return back()->with('success', 'Itens e estoque atualizados com sucesso!');
        } catch (\RuntimeException $e) {
            // Bloqueios de alteração da OS.
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            // Qualquer outra exceção
            report($e);

            return back()->with('error', 'Falha ao salvar a OS.')->withInput();
        }
    }

    public function updateMeta(Request $r, $id)
    {
        return DB::transaction(function () use ($r, $id) {
            $os = OrdemServico::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($os->situacao === 'Finalizada') {
                return back()->with('error', 'OS finalizada não permite alterações.');
            }

            $data = $r->validate([
                'proprietario' => ['nullable', 'string', 'max:255'],
                'veiculo_id' => ['required', 'exists:veiculos,id'],
                'situacao' => ['required', 'in:Aberta,Em andamento,Finalizada,Cancelada'],
                'data_previsao_entrega' => ['nullable', 'date'],
                'observacoes' => ['nullable', 'string'],
            ]);

            $anterior = $os->situacao;
            $os->fill($data);

            // Se trocou o veículo, refaz o snapshot do dono do NOVO veículo
            if ($os->isDirty('veiculo_id')) {
                $vinculo = ClienteVeiculo::with('cliente')
                    ->where('veiculo_id', $os->veiculo_id)
                    ->where('ativo', true)
                    ->first();

                if (! $vinculo) {
                    return back()->with('error', 'O veículo selecionado não possui proprietário ativo.')->withInput();
                }

                $os->cliente_id = $vinculo->cliente_id;
                $os->cliente_veiculo_id = $vinculo->id;
                $os->cliente_nome_snapshot = $vinculo->cliente?->nome;
                $os->cliente_documento_snapshot = $vinculo->cliente?->cnpj_cpf
                                            ?? $vinculo->cliente?->cpf
                                            ?? null;
            }

            $os->save();
            if (($anterior === 'Cancelada') !== ($os->situacao === 'Cancelada')) {
                app(EstoqueOrdemService::class)->sincronizar($os, $os->pecasItens()->get()->toArray(), $anterior === 'Cancelada');
            }

            return back()->with('success', 'Dados da OS atualizados.');
        });
    }

    public function fechar($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        return DB::transaction(function () use ($id) {
            $os = OrdemServico::whereKey($id)->lockForUpdate()->firstOrFail();

            // regra: só fecha se tiver itens, por exemplo
            if ($os->total_os <= 0) {
                return back()->with('error', 'Não é possível finalizar uma OS sem itens.');
            }

            if ($os->situacao === 'Cancelada') {
                $os->situacao = 'Aberta';
                $os->save();
                app(EstoqueOrdemService::class)->sincronizar($os, $os->pecasItens()->get()->toArray(), true);
            }

            $os->situacao = 'Finalizada';
            $os->save();

            // Trave edição de itens no front (exiba disabled) e no back (guards no syncAll)
            return back()->with('success', 'OS finalizada!');
        });
    }

    // FUNCAO PARA VALIDAR INPUTS DE VALOR
    public function normalizeCurrency(?string $v): float
    {
        if ($v === null) {
            return 0.0;
        }
        $v = preg_replace('/[^\d,\.]/', '', $v);
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
        } // tira milhares
        $v = str_replace(',', '.', $v); // vírgula -> ponto

        return (float) $v;
    }

    public function show($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $os = OrdemServico::with([
            'veiculo',
            'cliente',                     // 👈 precisa disto
            'servicosItens.servico',
            'pecasItens.estoque',
        ])->findOrFail($id);

        return view('ordens.show', compact('os'));
    }

    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            DB::transaction(function () use ($id) {
                $os = OrdemServico::whereKey($id)->lockForUpdate()->firstOrFail();
                app(EstoqueOrdemService::class)->sincronizar($os, [], false, true);
                // apaga itens e a OS
                $os->servicosItens()->delete();
                $os->pecasItens()->delete();
                $os->delete();
            });

            return redirect()->route('ordem.index')->with('success', 'OS excluída e estoque revertido.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('ordem.index')->with('error', 'Falha ao excluir OS.');
        }
    }
}
