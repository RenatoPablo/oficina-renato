@extends('layouts.main_layout')

@section('content')
{{-- FILTROS --}}
<form method="GET" class="mb-3">
  <div class="row g-2">
    <div class="col-md-2">
      <input name="numero" value="{{ request('numero') }}" class="form-control" placeholder="Nº OS">
    </div>
    <div class="col-md-2">
      <input name="placa" value="{{ request('placa') }}" class="form-control text-uppercase" placeholder="Placa (ABC-1234)">
    </div>
    <div class="col-md-3">
      <input name="cliente" value="{{ request('cliente') }}" class="form-control" placeholder="Cliente">
    </div>
    <div class="col-md-2">
      <select name="situacao" class="form-select">
        <option value="">Situação (todas)</option>
        @foreach (['Aberta','Em andamento','Finalizada','Cancelada'] as $opt)
          <option value="{{ $opt }}" @selected(request('situacao')===$opt)>{{ $opt }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
      @if(request()->anyFilled(['numero','placa','cliente','situacao']))
        <a href="{{ route('ordens.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
      @endif
      <a href="{{ route('ordem.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nova OS</a>
    </div>
  </div>
</form>

{{-- LISTA --}}
<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="text-nowrap">Nº</th>
          <th class="text-nowrap">Aberta em</th>
          <th>Veículo</th>
          <th>Cliente</th>
          <th class="text-end text-nowrap">Serviços</th>
          <th class="text-end text-nowrap">Peças</th>
          <th class="text-end text-nowrap">Frete</th>
          <th class="text-end text-nowrap">Total</th>
          <th class="text-nowrap">Situação</th>
          <th class="text-center" style="width:120px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($ordens as $os)
          @php
            // cliente ativo do veículo (sem mexer em model/controller)
            $clienteAtivo = \App\Models\ClienteVeiculo::where('veiculo_id', $os->veiculo_id)
              ->where('ativo', true)->with('cliente')->first();
            $clienteNome = $clienteAtivo?->cliente?->nome ?? 'Desassociado';
          @endphp
          <tr class="{{ $os->situacao === 'Aberta' ? '' : 'table-light' }}">
            <td>#{{ $os->id }}</td>
            <td class="text-nowrap">{{ \Carbon\Carbon::parse($os->data_chamado)->format('d/m/Y H:i') }}</td>
            <td class="text-truncate" style="max-width:260px;">
              <div class="small text-muted">{{ $os->veiculo->marca ?? '' }} {{ $os->veiculo->modelo ?? '' }}</div>
              <strong class="text-uppercase">{{ $os->veiculo->placa ?? '—' }}</strong>
            </td>
            <td class="text-truncate" style="max-width:260px;">{{ $clienteNome }}</td>
            <td class="text-end">{{ number_format($os->total_servicos, 2, ',', '.') }}</td>
            <td class="text-end">{{ number_format($os->total_pecas, 2, ',', '.') }}</td>
            <td class="text-end">{{ number_format($os->frete, 2, ',', '.') }}</td>
            <td class="text-end fw-semibold">{{ number_format($os->total_os, 2, ',', '.') }}</td>
            <td>
              @switch($os->situacao)
                @case('Aberta')       @php $badge='success'; @endphp @break
                @case('Em andamento') @php $badge='warning'; @endphp @break
                @case('Finalizada')   @php $badge='secondary'; @endphp @break
                @case('Cancelada')    @php $badge='danger'; @endphp @break
                @default              @php $badge='light'; @endphp
              @endswitch
              <span class="badge text-bg-{{ $badge }}">{{ $os->situacao }}</span>
            </td>
            <td class="text-center">
              <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-primary" href="#"><i class="bi bi-eye"></i></a>
                <a class="btn btn-outline-secondary" href="{{ route('ordem.edit', Crypt::encrypt($os->id)) }}"><i class="bi bi-pencil"></i></a>
                <a class="btn btn-outline-dark" target="_blank" href="#"><i class="bi bi-printer"></i></a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-4">
              <i class="bi bi-clipboard-x fs-5 d-block mb-1"></i>
              Nenhuma OS encontrada.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card-footer bg-white py-2">
    {{ $ordens->links() }}
  </div>
</div>
@endsection
