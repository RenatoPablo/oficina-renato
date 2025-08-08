@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-truck"></i> Veículos
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-dark">Lista de Veículos</h4>
                    <a href="{{ route('veiculo.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Novo Veículo
                    </a>
                </div>

                <form method="GET" action="" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <input type="text" name="tipo" class="form-control" placeholder="Tipo" value="{{ request()->tipo }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="marca" class="form-control" placeholder="Marca" value="{{ request()->marca }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="modelo" class="form-control" placeholder="Modelo" value="{{ request()->modelo }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="placa" class="form-control" placeholder="Placa" value="{{ request()->placa }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="cliente" class="form-control" placeholder="Cliente" value="{{ request()->cliente }}">
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>

                            @if(request()->anyFilled(['tipo','marca','modelo','placa','cliente']))
                                <a href="{{ route('veiculo.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>


                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Placa</th>
                            <th>KM</th>
                            <th>Ano</th>
                            <th>Cliente</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse ($veiculos as $veiculo)


                            <tr>
                                <td>{{ $veiculo->tipo }}</td>
                                <td>{{ $veiculo->marca }}</td>
                                <td>{{ $veiculo->modelo }}</td>
                                <td>{{ $veiculo->placa }}</td>
                                <td>{{ $veiculo->km }}</td>
                                <td>{{ $veiculo->ano }}</td>
                                <td>
                                    @if ($veiculo->clienteAtivo)
                                        {{ $veiculo->clienteAtivo }}
                                    @else
                                        <span class="text-muted fst-italic">Desassociado</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('veiculo.edit', ['id' => Crypt::encrypt($veiculo->id)]) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Botão Excluir -->
                                    <form action="{{ route('veiculo.destroy', ['id' => Crypt::encrypt($veiculo->id)]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este veiculo?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Nenhum veículo cadastrado.</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>

                {{ $veiculos->links() }}
            </div>
        </div>
    </div>
@endsection