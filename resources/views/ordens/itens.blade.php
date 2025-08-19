@extends('layouts.main_layout')

@section('content')



<h5 class="mb-3">
  Editar Itens da OS #{{ $os->id }}
  <small class="text-muted">— {{ $os->veiculo?->placa }} · {{ $os->veiculo?->marca }} {{ $os->veiculo?->modelo }}</small>
</h5>

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger">
    <strong>Corrija os campos em destaque.</strong>
  </div>
@endif

<form action="{{ route('ordem.syncAll', $os->id) }}" method="POST" id="form-sync-all" class="card border-0 shadow-sm">
  @csrf

  <div class="card-body">

    {{-- ======= SERVIÇOS ======= --}}
    <div class="card mb-4 border-0">
      <div class="card-header bg-light d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="fw-semibold">Serviços</div>

        <div class="d-flex flex-wrap gap-2">
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
                  <td><input name="servicos[{{ $i }}][qtd]" type="number" step="1" class="form-control" value="{{ $item->qtd }}"></td>
                  <td><input name="servicos[{{ $i }}][valor_unit]" type="number" step="0.01" class="form-control" value="{{ $item->valor_unit }}"></td>
                  <td><input name="servicos[{{ $i }}][valor_total]" type="number" step="0.01" class="form-control" value="{{ $item->valor_total }}" readonly></td>
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
    <div class="card mb-2 border-0">
      <div class="card-header bg-light d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="fw-semibold">Peças / Estoque</div>

        <div class="d-flex flex-wrap gap-2">
          <select id="sel-peca" class="form-select form-select-sm">
            <option value="">-- Selecionar peça --</option>
            @foreach($estoques as $p)
              <option value="{{ $p->id }}" data-preco="{{ number_format($p->preco_rs ?? 0, 2, '.', '') }}">
                {{ $p->descricao }}
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
                    <input type="hidden" name="pecas[{{ $j }}][id]" value="{{ $item->id }}">
                    <select name="pecas[{{ $j }}][estoque_id]" class="form-select">
                      @foreach($estoques as $p)
                        <option value="{{ $p->id }}"
                                data-preco="{{ number_format($p->preco_rs ?? 0, 2, '.', '') }}"
                                @selected($item->estoque_id == $p->id)>
                          {{ $p->descricao }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td><input name="pecas[{{ $j }}][qtd]" type="number" step="0.01" class="form-control" value="{{ $item->qtd }}"></td>
                  <td><input name="pecas[{{ $j }}][valor_unit]" type="number" step="0.01" class="form-control" value="{{ $item->valor_unit }}"></td>
                  <td><input name="pecas[{{ $j }}][valor_total]" type="number" step="0.01" class="form-control" value="{{ $item->valor_total }}" readonly></td>
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

  </div>

  <div class="card-footer d-flex justify-content-between align-items-center">
    <div class="d-flex gap-2 align-items-center">
      <label class="me-2">Frete</label>
      <input type="number" step="0.01" class="form-control form-control-sm" style="max-width:140px"
             name="frete" id="frete" value="{{ number_format($os->frete ?? 0, 2, '.', '') }}">
    </div>

    <div class="d-flex gap-2 align-items-center">
      <div class="text-end me-3">
        <div class="small text-muted">Total da OS</div>
        <div class="fw-bold fs-5" id="total-os">0,00</div>
      </div>

      <a href="{{ route('ordem.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
      <button class="btn btn-primary">
        <i class="bi bi-save"></i> Salvar tudo
      </button>
    </div>
  </div>
</form>

{{-- templates ocultos p/ criar novas linhas via JS --}}
<select id="servico_master" class="d-none">
  @foreach($servicos as $s)
    <option value="{{ $s->id }}" data-preco="{{ number_format($s->valor ?? 0, 2, '.', '') }}">{{ $s->descricao }}</option>
  @endforeach
</select>

<select id="peca_master" class="d-none">
  @foreach($estoques as $p)
    <option value="{{ $p->id }}" data-preco="{{ number_format($p->preco_rs ?? 0, 2, '.', '') }}">{{ $p->descricao }}</option>
  @endforeach
</select>

@endsection
