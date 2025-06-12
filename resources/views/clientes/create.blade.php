@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <!-- Título e botão voltar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Novo Cliente</h2>
            <a href="{{ url('/clientes') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Formulário de cadastro -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('clientes.create.submit') }}" method="POST">
                    @csrf

                    @include('clientes.partials.form')

                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-check-circle"></i> Cadastrar Cliente
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection