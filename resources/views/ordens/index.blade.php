@extends('layouts.main_layout')

@section('content')
<div class="container mt-4">
  <div class="card shadow-sm border-0">

    {{-- Cabeçalho azul + botão à direita --}}
    <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span><i class="bi bi-file-earmark-text me-1"></i> Ordens de Serviço</span>
      <a href="{{ route('ordem.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Nova OS
      </a>
    </div>

    <div class="card-body">

      {{-- Título --}}
      <h4 class="text-dark mb-3">Lista de Ordens de Serviço</h4>

      {{-- FILTROS responsivos --}}
      <form method="GET" action="{{ route('ordem.index') }}" class="os-toolbar mb-3">
        <div class="row g-2">
          <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <input name="numero" value="{{ request('numero') }}" class="form-control" placeholder="Nº OS">
          </div>
          <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <input name="placa" value="{{ request('placa') }}" class="form-control text-uppercase" placeholder="ABC-1234">
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <input name="cliente" value="{{ request('cliente') }}" class="form-control" placeholder="Cliente">
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <select name="situacao" class="form-select">
              <option value="">Situação (todas)</option>
              @foreach (['Aberta','Em andamento','Finalizada','Cancelada'] as $opt)
                <option value="{{ $opt }}" @selected(request('situacao') === $opt)>{{ $opt }}</option>
              @endforeach
            </select>
          </div>

          {{-- Ações de busca/limpar – ocupam a última linha no mobile --}}
          <div class="col-12 col-lg-2 d-flex flex-wrap gap-2 justify-content-lg-end">
            <button class="btn btn-primary flex-fill flex-lg-grow-0">
              <i class="bi bi-search me-1"></i> Buscar
            </button>
            @if(request()->anyFilled(['numero','placa','cliente','situacao']))
              <a href="{{ route('ordem.index') }}"
                 class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                 title="Limpar filtros" aria-label="Limpar filtros">
                <i class="bi bi-x-lg"></i>
              </a>
            @endif
          </div>
        </div>
      </form>

      {{-- Tabela --}}
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
                // Nome do cliente: prioridade pro snapshot congelado; se não tiver, usa relação
                $clienteNome = $os->cliente_nome_snapshot ?? ($os->cliente->nome ?? 'Desassociado');
              @endphp
              <tr class="{{ $os->situacao === 'Aberta' ? '' : 'table-light' }}">
                <td class="text-muted">#{{ $os->id }}</td>
                <td class="text-nowrap">{{ \Carbon\Carbon::parse($os->data_chamado)->format('d/m/Y H:i') }}</td>

                <td class="text-truncate" style="max-width:260px;">
                  <div class="small text-muted">{{ $os->veiculo->marca ?? '' }} {{ $os->veiculo->modelo ?? '' }}</div>
                  <strong class="text-uppercase">{{ $os->veiculo->placa ?? '—' }}</strong>
                </td>

                <td class="text-truncate" style="max-width:260px;">{{ $clienteNome }}</td>

                <td class="text-end">{{ number_format($os->total_servicos ?? 0, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($os->total_pecas ?? 0, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($os->frete ?? 0, 2, ',', '.') }}</td>
                <td class="text-end fw-semibold">{{ number_format($os->total_os ?? 0, 2, ',', '.') }}</td>

                <td>
                  @php
                    $map = ['Aberta'=>'success','Em andamento'=>'warning','Finalizada'=>'secondary','Cancelada'=>'danger'];
                    $badge = $map[$os->situacao] ?? 'light';
                  @endphp
                  <span class="badge rounded-pill text-bg-{{ $badge }}">{{ $os->situacao }}</span>
                </td>

                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="{{ route('ordem.show', Crypt::encrypt($os->id)) }}" title="Ver">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ route('ordem.edit', Crypt::encrypt($os->id)) }}" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </a>
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

      <div class="d-flex justify-content-end mt-3">
        {{ $ordens->links() }}
      </div>

    </div>
  </div>
</div>
@endsection
