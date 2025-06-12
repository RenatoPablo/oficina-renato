@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <!-- Título e botão para adicionar novo cliente -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-people"></i> Clientes
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-dark">Lista de Clientes</h4>
                    <a href="{{ route('clientes.create') }}" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Novo Cliente
                    </a>
                </div>

                <!-- Tabela de clientes -->
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Contato</th>
                            <th>Celular</th>
                            <th>Email</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->nome }}</td>
                                <td>{{ $cliente->contato }}</td>
                                <td>{{ $cliente->celular }}</td>
                                <td>{{ $cliente->email }}</td>
                                <td class="text-center">
                                    <!-- Botão Editar -->
                                    <a href="{{ route('clientes.edit', ['id' => Crypt::encrypt($cliente->id)]) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Botão Excluir -->
                                    <form action="{{ route('clientes.index', $cliente->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum cliente cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection