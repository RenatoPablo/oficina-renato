@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        

        <!-- Título e botão voltar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Cliente: {{ $cliente->nome }} </h2>
            <a href="{{ url('/clientes') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Formulário de cadastro -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('clientes.partials.form_edit')

                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-check-circle"></i> Salvar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection