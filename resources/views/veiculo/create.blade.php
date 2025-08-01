@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Cadastrar Novo Veículo</h2>
            <a href="{{ route('veiculo.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('veiculo.create.submit') }}" method="POST">
                    @csrf
                    @include('veiculo.partials.form')
                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-check-circle"></i> Cadastrar
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection