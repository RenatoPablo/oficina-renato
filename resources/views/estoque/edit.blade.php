@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        

        <!-- Título e botão voltar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Item do estoque: {{ $estoque->descricao }} </h2>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Formulário de edicao -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('estoque.update', $estoque->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('estoque.partials.form_edit')

                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-check-circle"></i> Salvar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection