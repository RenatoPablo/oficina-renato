@extends('layouts.main_layout')

@section('content')
@php
    use Illuminate\Support\Facades\Crypt;
    $encId = Crypt::encrypt($veiculo->id);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Histórico de Proprietários</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('veiculo.edit', ['id' => $encId]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Editar veículo
        </a>
        <a href="{{ route('veiculo.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar para lista
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body pb-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold">{{ $veiculo->modelo }}</span>
            <span class="badge text-bg-secondary">{{ $veiculo->marca }}</span>
            <span class="badge text-bg-dark">{{ $veiculo->placa }}</span>
            @if(!empty($veiculo->ano))
                <span class="badge text-bg-light border">Ano {{ $veiculo->ano }}</span>
            @endif
            @if(!empty($veiculo->km))
                <span class="badge text-bg-light border">{{ number_format($veiculo->km, 0, ',', '.') }} km</span>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">Cliente</th>
                    <th class="text-nowrap">Início</th>
                    <th class="text-nowrap">Fim</th>
                    <th class="text-nowrap">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historico as $rel)
                    <tr class="{{ $rel->ativo ? 'table-success' : '' }}">
                        <td class="text-truncate" style="max-width: 380px;">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ $rel->cliente?->nome ?? '—' }}
                        </td>
                        <td class="text-nowrap">
                            {{ \Carbon\Carbon::parse($rel->data_inicio)->format('d/m/Y') }}
                        </td>
                        <td class="text-nowrap">
                            {{ $rel->data_fim ? \Carbon\Carbon::parse($rel->data_fim)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @if($rel->ativo)
                                <span class="badge text-bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Ativo
                                </span>
                            @else
                                <span class="badge text-bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Encerrado
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bi bi-clock-history fs-5 d-block mb-1"></i>
                            Sem histórico para este veículo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $historico->links() }}
        </div>
    </div>
</div>
@endsection
