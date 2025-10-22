@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <!-- Título e botão voltar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark">Nova Entrada de Usuario</h2>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Formulário de cadastro -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('admin.user.create.submit') }}" method="POST">
                    @csrf

                    @include('admin.users.partials.form')

                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-check-circle"></i> Cadastrar Nova entrada
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection