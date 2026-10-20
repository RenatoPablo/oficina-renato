{{-- ======= SERVIÇOS ======= --}}
<div class="card mb-4 border-0 servicos-section">
  <div class="card-header bg-light d-flex align-items-center flex-nowrap">
    <div class="fw-semibold me-3">Serviços</div>

    <div class="ms-auto d-flex align-items-center gap-2">
      <select id="sel-servico" class="form-select form-select-sm">
        <option value="">-- Selecionar serviço --</option>
        @foreach($servicos as $s)
          <option value="{{ $s->id }}" data-preco="{{ number_format($s->valor_unitario ?? 0, 2, '.', '') }}">
            {{ $s->descricao }}
          </option>
        @endforeach
      </select>
      <button type="button" class="btn btn-sm btn-primary" id="add-servico">Adicionar</button>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0" id="tbl-servicos">
        <thead class="table-light">
          <tr>
            <th style="width:45%">Serviço</th>
            <th style="width:10%">Qtd</th>
            <th style="width:15%">Vlr Unit</th>
            <th style="width:15%">Total</th>
            <th style="width:15%"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($os->servicosItens as $i => $item)
            <tr>
              <td>
                <input type="hidden" name="servicos[{{ $i }}][id]" value="{{ $item->id }}">
                <select name="servicos[{{ $i }}][servico_id]" class="form-select">
                  @foreach($servicos as $s)
                    <option value="{{ $s->id }}"
                            data-preco="{{ number_format($s->valor_unitario ?? 0, 2, '.', '') }}"
                            @selected($item->servico_id == $s->id)>
                      {{ $s->descricao }}
                    </option>
                  @endforeach
                </select>
              </td>

              <td>
                <input name="servicos[{{ $i }}][qtd]" type="number" step="1" class="form-control"
                  value="{{ number_format((float)$item->qtd, 0, ',', '') }}">
              </td>

              <td>
                <input name="servicos[{{ $i }}][valor_unit]" type="text" class="form-control money" inputmode="decimal"
                  value="{{ number_format((float)$item->valor_unit, 2, ',', '') }}">
              </td>

              <td>
                <input name="servicos[{{ $i }}][valor_total]" type="text" class="form-control money" inputmode="decimal"
                  value="{{ number_format((float)$item->valor_total, 2, ',', '') }}" readonly>
              </td>

              <td class="text-end">
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
              </td>
            </tr>
          @endforeach
        </tbody>

        <tfoot class="table-light">
          <tr>
            <th colspan="3" class="text-end">Subtotal Serviços:</th>
            <th><input type="text" class="form-control form-control-sm" id="subtotal-servicos" value="0,00" readonly></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- ======= PEÇAS / ESTOQUE ======= --}}
@if($os->situacao !== 'Cancelada' && $os->pecasItens->contains(fn($item) => (float)$item->qtd_descontada < (float)$item->qtd))
  <div class="alert alert-info" role="status">Há peças utilizadas além do estoque cadastrado. A cobrança considera toda a quantidade da OS; somente o estoque disponível foi descontado.</div>
@endif
<div class="small text-muted mb-2">Peças podem ser utilizadas mesmo sem estoque suficiente. O saldo será mantido em zero e a cobrança será integral.</div>
<div class="card mb-2 border-0 pecas-section">
  <div id="aviso-estoque" class="alert alert-info mb-0" role="status" hidden>Quantidade superior ao estoque cadastrado. Você pode salvar e cobrar normalmente; o estoque será descontado somente até zero.</div>
  <div class="card-header bg-light d-flex align-items-center flex-nowrap">
    <div class="fw-semibold me-3">Peças / Estoque</div>

    <div class="ms-auto d-flex align-items-center gap-2">
      <select id="sel-peca" class="form-select">
        <option value="">-- Selecionar peça --</option>
        @foreach($estoques as $p)
          <option value="{{ $p->id }}" data-estoque="{{ $p->quantidade }}" data-preco="{{ number_format($p->preco_rs ?? 0, 2, '.', '') }}">
            {{ $p->descricao }} (estoque: {{ $p->quantidade }})
          </option>
        @endforeach
      </select>
      <button type="button" class="btn btn-sm btn-primary" id="add-peca">Adicionar</button>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0" id="tbl-pecas">
        <thead class="table-light">
          <tr>
            <th style="width:45%">Peça</th>
            <th style="width:10%">Qtd</th>
            <th style="width:15%">Vlr Unit</th>
            <th style="width:15%">Total</th>
            <th style="width:15%"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($os->pecasItens as $j => $item)
            <tr>
              <td>
                <input data-estoque-original="{{ $item->estoque_id }}" data-descontada="{{ $item->qtd_descontada }}" type="hidden" name="pecas[{{ $j }}][id]" value="{{ $item->id }}">
                <select name="pecas[{{ $j }}][estoque_id]" class="form-select">
                  @foreach($estoques as $p)
                    <option value="{{ $p->id }}"
                            data-estoque="{{ $p->quantidade }}" data-preco="{{ number_format($p->preco_rs ?? 0, 2, '.', '') }}"
                            @selected($item->estoque_id == $p->id)>
                      {{ $p->descricao }} (estoque: {{ $p->quantidade }})
                    </option>
                  @endforeach
                </select>
              </td>

              <td>
                <input name="pecas[{{ $j }}][qtd]" type="number" step="1" class="form-control"
                  value="{{ number_format((float)$item->qtd, 0, ',', '') }}">
              </td>

              <td>
                <input name="pecas[{{ $j }}][valor_unit]" type="text" class="form-control money" inputmode="decimal"
                  value="{{ number_format((float)$item->valor_unit, 2, ',', '') }}">
              </td>

              <td>
                <input name="pecas[{{ $j }}][valor_total]" type="text" class="form-control money" inputmode="decimal"
                  value="{{ number_format((float)$item->valor_total, 2, ',', '') }}" readonly>
              </td>

              <td class="text-end">
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
              </td>
            </tr>
          @endforeach
        </tbody>

        <tfoot class="table-light">
          <tr>
            <th colspan="3" class="text-end">Subtotal Peças:</th>
            <th><input type="text" class="form-control form-control-sm" id="subtotal-pecas" value="0,00" readonly></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- Templates ocultos para o JS clonar --}}
<select id="servico_master" class="d-none">
  @foreach($servicos as $s)
    <option value="{{ $s->id }}" data-preco="{{ number_format($s->valor_unitario ?? $s->valor ?? 0, 2, '.', '') }}">
      {{ $s->descricao }}
    </option>
  @endforeach
</select>

<select id="peca_master" class="d-none">
  @foreach($estoques as $p)
    <option value="{{ $p->id }}" data-estoque="{{ $p->quantidade }}" data-preco="{{ number_format($p->preco_rs ?? 0, 2, '.', '') }}">
      {{ $p->descricao }} (estoque: {{ $p->quantidade }})
    </option>
  @endforeach
</select>
