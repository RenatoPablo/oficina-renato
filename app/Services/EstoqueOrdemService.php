<?php

namespace App\Services;

use App\Models\Estoque;
use App\Models\OrdemServico;
use App\Models\PecaOrdem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstoqueOrdemService
{
    public function sincronizar(OrdemServico $ordem, array $linhas, bool $reativar = false, bool $excluir = false): void
    {
        DB::transaction(function () use ($ordem, $linhas, $reativar, $excluir) {
            $os = OrdemServico::whereKey($ordem->id)->lockForUpdate()->firstOrFail();
            if ($os->situacao === 'Finalizada' && ! $excluir && ! $reativar) {
                throw new \RuntimeException('OS finalizada não permite alterações.');
            }
            $atuais = $os->pecasItens()->lockForUpdate()->get()->keyBy('id');
            $ids = collect($linhas)->pluck('id')->filter();
            if ($ids->duplicates()->isNotEmpty() || $ids->diff($atuais->keys())->isNotEmpty()) {
                throw ValidationException::withMessages(['pecas' => 'Itens da OS inválidos. Recarregue a página.']);
            }
            $estoqueIds = $atuais->pluck('estoque_id')->merge(collect($linhas)->pluck('estoque_id'))->unique()->sort();
            $estoques = Estoque::withTrashed()->whereIn('id', $estoqueIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $devolucoes = [];
            $salvar = [];
            foreach ($linhas as $linha) {
                $antigo = empty($linha['id']) ? null : $atuais->get($linha['id']);
                $mesma = $antigo && (int) $antigo->estoque_id === (int) $linha['estoque_id'];
                $debito = $mesma ? (float) $antigo->qtd_descontada : 0;
                $qtd = (float) $linha['qtd'];
                $manter = $os->situacao === 'Cancelada' ? 0 : min($debito, $qtd);
                if ($antigo) {
                    $devolucoes[$antigo->estoque_id] = ($devolucoes[$antigo->estoque_id] ?? 0)
                        + (float) $antigo->qtd_descontada - $manter;
                }
                // Reposição posterior não cobre retroativamente faltas de uma linha inalterada.
                $consumir = $os->situacao === 'Cancelada' ? 0 : ($mesma && ! $reativar ? max(0, $qtd - (float) $antigo->qtd) : $qtd);
                $salvar[] = [$linha, $antigo, $manter, $consumir];
            }
            foreach ($atuais->except($ids->all()) as $antigo) {
                $devolucoes[$antigo->estoque_id] = ($devolucoes[$antigo->estoque_id] ?? 0) + (float) $antigo->qtd_descontada;
                $antigo->delete();
            }
            foreach ($devolucoes as $id => $qtd) {
                $estoques[$id]->quantidade = max(0, (float) $estoques[$id]->quantidade) + $qtd;
            }
            foreach ($salvar as [$linha, $antigo, $manter, $consumir]) {
                $estoque = $estoques->get($linha['estoque_id']);
                if (! $estoque) {
                    throw ValidationException::withMessages(['pecas' => 'Peça não encontrada.']);
                }
                $descontar = min(max(0, (float) $estoque->quantidade), $consumir);
                $estoque->quantidade = max(0, (float) $estoque->quantidade - $descontar);
                $dados = collect($linha)->only(['estoque_id', 'qtd', 'valor_unit', 'codigo_cor'])->all();
                $dados['qtd_descontada'] = $manter + $descontar;
                $dados['valor_total'] = round((float) $linha['qtd'] * (float) $linha['valor_unit'], 2);
                $dados['ordem_servico_id'] = $os->id;
                $antigo ? $antigo->update($dados) : PecaOrdem::create($dados);
            }
            foreach ($estoques as $estoque) {
                $estoque->save();
            }
        });
    }
}
