@extends('layouts.main_layout')

@section('content')
<div class="container-xxl mt-4">
  {{-- Header --}}
  <div class="row align-items-center gy-2 mb-3">
    <div class="col-12 col-lg">
      <h5 class="mb-0">
        Editar OS #{{ $os->id }}
        <small class="text-muted">— {{ $os->veiculo?->placa }} · {{ $os->veiculo?->marca }} {{ $os->veiculo?->modelo }}</small>
      </h5>
    </div>
    <div class="col-12 col-lg-auto">
      <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-lg-end">
        @if ($os->situacao === 'Finalizada')
          <a class="btn btn-outline-secondary disabled" aria-disabled="true">
            <i class="bi bi-list-check"></i> Itens (Serviços & Peças)
          </a>
        @else
          <a href="{{ route('ordem.itens', Crypt::encrypt($os->id)) }}" class="btn btn-outline-primary">
            <i class="bi bi-list-check"></i> Itens (Serviços & Peças)
          </a>
        @endif

        <a href="{{ route('ordem.show', Crypt::encrypt($os->id)) }}" class="btn btn-outline-secondary">
          <i class="bi bi-eye"></i> Visualizar
        </a>
        <a href="{{ route('ordem.index') }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Voltar
        </a>
      </div>
    </div>
  </div>

  {{-- Wrapper pra centralizar largura --}}
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
      <form action="{{ route('ordem.updateMeta', $os->id) }}" method="POST" class="card border-0 shadow-sm">
        @csrf @method('PUT')

        <div class="card-header bg-light fw-semibold">Dados da OS</div>

        <div class="card-body">
          <div class="row g-4">

            {{-- Proprietário (floating) --}}
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="proprietario"
                       name="proprietario"
                       value="{{ old('proprietario', $os->veiculo?->proprietario_nome ?? 'Desassociado') }}"
                       placeholder="Proprietário" readonly>
                <label for="proprietario" class="fw-semibold">Proprietário</label>
              </div>
            </div>

            {{-- Veículo (select floating) --}}
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <select class="form-select" id="veiculo_id" name="veiculo_id" aria-label="Veículo">
                  <option value="">Selecione um veículo...</option>
                  @foreach($veiculos as $veiculo)
                    <option value="{{ $veiculo->id }}"
                      @selected(old('veiculo_id', $os->veiculo_id) == $veiculo->id)>
                      {{ $veiculo->marca }} - {{ $veiculo->modelo }} ({{ $veiculo->placa }})
                    </option>
                  @endforeach
                </select>
                <label for="veiculo_id" class="fw-semibold">Veículo</label>
              </div>
            </div>

            {{-- Situação --}}
            <div class="col-12 col-md-6 col-lg-3">
              <div class="form-floating">
                <select name="situacao" id="situacao" class="form-select">
                  @foreach (['Aberta','Em andamento','Finalizada','Cancelada'] as $opt)
                    <option value="{{ $opt }}" @selected(old('situacao', $os->situacao)===$opt)>{{ $opt }}</option>
                  @endforeach
                </select>
                <label for="situacao" class="fw-semibold">Situação</label>
              </div>
            </div>

            {{-- Previsão de Entrega --}}
            <div class="col-12 col-md-6 col-lg-3">
              <div class="form-floating">
                <input type="datetime-local" class="form-control" id="data_previsao_entrega" name="data_previsao_entrega"
                       value="{{ old('data_previsao_entrega', $os->data_previsao_entrega? \Carbon\Carbon::parse($os->data_previsao_entrega)->format('Y-m-d\TH:i') : '') }}"
                       placeholder="Previsão de Entrega">
                <label for="data_previsao_entrega" class="fw-semibold">Previsão de Entrega</label>
              </div>
            </div>

            {{-- Observações --}}
            <div class="col-12">
              <label for="observacoes" class="form-label fw-semibold mb-1">Observações</label>
              <textarea id="observacoes" name="observacoes" rows="4" class="form-control">{{ old('observacoes', $os->observacoes) }}</textarea>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> Salvar dados
            </button>

            @if($os->situacao !== 'Finalizada' && $os->situacao !== 'Cancelada')
              <button type="submit"
                      formaction="{{ route('ordem.fechar', Crypt::encrypt($os->id)) }}"
                      class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Finalizar OS
              </button>
            @endif
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
