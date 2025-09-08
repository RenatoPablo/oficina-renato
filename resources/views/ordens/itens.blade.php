@extends('layouts.main_layout')

@section('content')
<div class="container mt-4">

  {{-- Título + ações --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
      Itens da OS #{{ $os->id }}
      <small class="text-muted">— {{ $os->veiculo?->placa }} · {{ $os->veiculo?->marca }} {{ $os->veiculo?->modelo }}</small>
    </h5>

    <div class="d-flex gap-2">
      <a href="{{ route('ordem.edit', Crypt::encrypt($os->id)) }}" class="btn btn-outline-primary">
        <i class="bi bi-gear"></i> Dados da OS
      </a>
      <a href="{{ route('ordem.show', Crypt::encrypt($os->id)) }}" class="btn btn-outline-secondary">
        <i class="bi bi-eye"></i> Visualizar
      </a>
      <a href="{{ route('ordem.edit', Crypt::encrypt($os->id)) }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>Corrija os campos em destaque.</strong>
    </div>
  @endif

  {{-- ====== CARD: ITENS (FORM ÚNICO) ====== --}}
  <form action="{{ route('ordem.syncAll', $os->id) }}" method="POST" id="form-sync-all" class="card border-0 shadow-sm">
    @csrf

    <div class="card-header bg-light fw-semibold">Itens (Serviços & Peças)</div>

    <div class="card-body">
      {{-- Parcial reaproveitável (sem <form>) --}}
      @include('ordens.partials.itens', [
        'os'        => $os,
        'servicos'  => $servicos,
        'estoques'  => $estoques
      ])
    </div>

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="d-flex gap-2 align-items-center">
        <label class="me-2">Frete</label>
        <input type="text" class="form-control form-control-sm money" style="max-width:140px"
          name="frete" id="frete" inputmode="decimal"
          value="{{ number_format($os->frete ?? 0, 2, ',', '.') }}">
      </div>

      <div class="ms-auto d-flex align-items-center gap-3">
        <div class="text-end">
          <div class="small text-muted">Total da OS:</div>
          <div class="fw-bold fs-5" id="total-os">0,00</div>
        </div>
        <button class="btn btn-primary">
          <i class="bi bi-save"></i> Salvar tudo
        </button>
      </div>
    </div>
  </form>

</div>
@endsection
