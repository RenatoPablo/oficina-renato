@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <!-- Título e botão para adicionar novo estoque -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-people"></i> Usuarios
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-dark">Lista de Usuarios</h4>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Novo Usuario
                    </a>
                </div>

                <!-- Barra de pesquisa -->
                <form method="GET" action="{{ route('admin.users') }}" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nome do usuario" value="{{ request()->search }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request()->search)
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                Limpar
                            </a>
                        @endif
                    </div>
                </form>

                <div class="table-wrap">
                    <!-- Tabela de estoque -->
                    <table class="table table-hover table-striped align-middle table-padrao">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Ultimo Login</th>
                                <th>Permissão</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ \Carbon\Carbon::parse($user->last_login)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{ $user->is_admin ? 'Administrador' : 'Usuário' }}
                                    </td>
                                    <td class="text-center">
                                        <!-- Botão Editar -->
                                        {{-- <a href="{{ route('admin.users.edit', ['id' => Crypt::encrypt($user->id)]) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Botão Excluir -->
                                        <form action="{{ route('admin.users.destroy', ['id' => Crypt::encrypt($user->id)]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form> --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum usuário cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection