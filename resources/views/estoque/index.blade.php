@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <!-- Título e botão para adicionar novo estoque -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-people"></i> Estoque
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-dark">Lista de Estoque</h4>
                    <a href="{{ route('estoque.create') }}" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Nova entrada no estoque
                    </a>
                </div>

                <!-- Barra de pesquisa -->
                <form method="GET" action="{{ route('estoque.index') }}" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por descrição do estoque" value="{{ request()->search }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request()->search)
                            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">
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
                                <th>Código</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                                <th>Preço R$</th>
                                <th>Medida</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($estoques as $estoque)
                                <tr>
                                    <td>{{ $estoque->codigo }}</td>
                                    <td>{{ $estoque->descricao }}</td>
                                    <td>{{ $estoque->quantidade }}</td>
                                    <td>{{ $estoque->preco_rs }}</td>
                                    <td>{{ $estoque->medida }}</td>
                                    <td class="text-center">
                                        <!-- Botão Editar -->
                                        <a href="{{ route('estoque.edit', ['id' => Crypt::encrypt($estoque->id)]) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Botão Excluir -->
                                        <form action="{{ route('estoque.destroy', ['id' => Crypt::encrypt($estoque->id)]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este item do estoque?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum item de estoque cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $estoques->links() }}
            </div>
        </div>
    </div>
@endsection