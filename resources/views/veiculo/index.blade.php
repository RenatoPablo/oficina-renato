@extends('layouts.main_layout')

@section('content')
<div class="container mt-4">
  <div class="card shadow-sm border-0">
    {{-- Cabeçalho azul --}}
    <div class="card-header bg-primary text-white fw-bold">
      <i class="bi bi-receipt"></i> Ordens de Serviço
    </div>

    <div class="card-body">
      {{-- Toolbar / filtros --}}
      <form method="GET" class="mb-4">
        <div class="row g-2 align-items-end">
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Nº OS</label>
            <input name="numero" value="{{ request('numero') }}" class="form-control" placeholder="Nº OS">
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Placa</label>
            <input name="placa" value="{{ request('placa') }}" class="form-control text-uppercase" placeholder="ABC-1234">
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label small mb-1">Cliente</label>
            <input name="cliente" value="{{ request('cliente') }}" class="form-control" placeholder="Cliente">
          </div>

          <div class="col-12 col-md-2">
            <label class="form-label small mb-1">Situação</label>
            <select name="situacao" class="form-select">
              <option value="">Situação (todas)</option>
              @foreach (['Aberta','Em andamento','Finalizada','Cancelada'] as $opt)
                <option value="{{ $opt }}" @selected(request('situacao')===$opt)>{{ $opt }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-md-3 d-flex gap-2 justify-content-start justify-content-md-end">
            <button class="btn btn-primary px-3">
              <i class="bi bi-search me-1"></i> Buscar
            </button>

            @if(request()->anyFilled(['numero','placa','cliente','situacao']))
              <a href="{{ route('ordem.index') }}" class="btn btn-secondary px-3" title="Limpar filtros" aria-label="Limpar filtros">
                <i class="bi bi-x-lg"></i>
              </a>
            @endif

            <a href="{{ route('ordem.create') }}" class="btn btn-success">
              <i class="bi bi-plus-lg me-1"></i> Nova OS
            </a>
          </div>
        </div>
      </form>

      {{-- Lista --}}
      <div class="table-wrap">
        <table class="table table-hover table-striped align-middle table-padrao">
          <thead class="table-dark">
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
              <th class="text-center text-nowrap">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($ordens as $os)
              @php
                $clienteAtivo = \App\Models\ClienteVeiculo::where('veiculo_id', $os->veiculo_id)
                  ->where('ativo', true)->with('cliente')->first();
                $clienteNome = $clienteAtivo?->cliente?->nome ?? 'Desassociado';
              @endphp
              <tr>
                <td class="text-muted">#{{ $os->id }}</td>
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
                  @php
                    $map = ['Aberta'=>'success','Em andamento'=>'warning','Finalizada'=>'secondary','Cancelada'=>'danger'];
                    $badge = $map[$os->situacao] ?? 'light';
                  @endphp
                  <span class="badge rounded-pill text-bg-{{ $badge }}">{{ $os->situacao }}</span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="#" title="Ver"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-outline-secondary" href="{{ route('ordem.edit', Crypt::encrypt($os->id)) }}" title="Editar"><i class="bi bi-pencil"></i></a>
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

      <div class="mt-3">
        {{ $ordens->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
