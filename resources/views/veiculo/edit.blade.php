@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Editar Veículo</h2>
            <a href="{{ route('veiculos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('veiculos.update', ['id' => Crypt::encrypt($veiculo->id)]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('veiculo.partials.form_edit')
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-save"></i> Atualizar
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
