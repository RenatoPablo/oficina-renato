@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <!-- Título e botão para adicionar novo serviço -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-tools"></i> Serviços
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-dark">Lista de Serviços</h4>
                    <a href="{{ route('servico.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Novo Serviço
                    </a>
                </div>

                <!-- Barra de pesquisa -->
                <form method="GET" action="{{ route('servico.index') }}" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por descrição do serviço" value="{{ request()->search }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request()->search)
                            <a href="{{ route('servico.index') }}" class="btn btn-secondary">
                                Limpar
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Tabela de serviços -->
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Descrição</th>
                            <th>Valor Unitário (R$)</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($servicos as $servico)
                            <tr>
                                <td>{{ $servico->descricao }}</td>
                                <td>{{ number_format($servico->valor_unitario, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <!-- Botão Editar -->
                                    <a href="{{ route('servico.edit', ['id' => Crypt::encrypt($servico->id)]) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Botão Excluir -->
                                    <form action="{{ route('servico.destroy', ['id' => Crypt::encrypt($servico->id)]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Nenhum serviço cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $servicos->links() }}
            </div>
        </div>
    </div>
@endsection
