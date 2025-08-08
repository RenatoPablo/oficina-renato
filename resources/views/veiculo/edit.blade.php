@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Editar Veículo</h2>
            <a href="{{ route('veiculo.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('veiculo.update', $veiculo->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('veiculo.partials.form_edit')
                    <div class="d-flex justify-content-between mt-4">
                        {{-- Botão de Salvar --}}
                        <button type="submit" class="btn btn-primary w-50 me-2">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>

                        {{-- Botão Desassociar Cliente (sem outro <form>) --}}
                        @if (!empty($clienteVeiculo->cliente_id) && $clienteVeiculo->ativo)
                            <button 
                                type="submit" 
                                class="btn btn-outline-danger w-50"
                                formaction="{{ route('veiculo.desassociar.cliente', ['id' => Crypt::encrypt($veiculo->id)]) }}"
                                formmethod="POST"
                                onclick="return confirm('Deseja mesmo desassociar este cliente?')"
                            >
                                @csrf
                                @method('PUT')
                                <i class="bi bi-person-dash"></i> Desassociar Cliente
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
