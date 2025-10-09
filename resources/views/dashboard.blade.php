@extends('layouts.main_layout')

@section('content')
    <div class="container mt-4">
        <!-- Frase de boas-vindas -->
        <div class="row">
            <div class="col-12">
                <div class="card p-4 shadow-sm bg-light">
                    <h2 class="text-dark">Bem-vindo, <span class="fw-bold">{{ session('user.name') ?? 'Usuário' }}</span></h2>
                    <p class="text-muted mb-0">{{ now()->format('d/m/Y H:i') }}</p>
                     
                </div>
            </div>
        </div>

        <!-- Alertas visuais (valores fictícios) -->
        <div class="row mt-4">
            <div class="col-md-4">
                @if ($estoqueBaixoCount > 0)
                    <div class="alert alert-warning shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        <strong>{{ $estoqueBaixoCount }}</strong> produtos com estoque baixo.
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                {{-- Substitua 2 por $ordensAtrasadas no futuro --}}
                @if ($osAtrasadaCount > 0)
                    <div class="alert alert-danger shadow-sm">
                        <i class="bi bi-x-circle-fill"></i> <strong>{{ $osAtrasadaCount }}</strong> ordens de serviço atrasadas.
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                {{-- Substitua 1 por $veiculosAguardando no futuro --}}
                @if ($osAbertaCount > 0)
                    <div class="alert alert-warning shadow-sm">
                        <i class="bi bi-clock-history"></i> <strong>{{ $osAbertaCount }}</strong> Ordem de serviço estão abertas.
                    </div>
                @endif
            </div>
        </div>

        <!-- Últimas ordens de serviço (dados simulados) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-list-check"></i> Últimas Ordens de Serviço
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Veículo</th>
                                    <th>Situação</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Exemplo fixo, depois substitua por @foreach ($ordensRecentes as $ordem) --}}
                                {{-- <tr>
                                    <td>João Silva</td>
                                    <td>Palio - ABC1234</td>
                                    <td><span class="badge bg-info">Em andamento</span></td>
                                    <td>10/06/2025</td>
                                </tr>
                                <tr>
                                    <td>Maria Costa</td>
                                    <td>Gol - XYZ5678</td>
                                    <td><span class="badge bg-success">Finalizada</span></td>
                                    <td>09/06/2025</td>
                                </tr>
                                <tr>
                                    <td>Pedro Santos</td>
                                    <td>Civic - HJK3456</td>
                                    <td><span class="badge bg-warning text-dark">Aguardando aprovação</span></td>
                                    <td>08/06/2025</td>
                                </tr> --}}
                                
                            @foreach ($ordens as $ordem)
                                <tr>
                                    <td>{{ $ordem->cliente_nome_snapshot }}</td>
                                    <td>
                                        {{ $ordem->veiculo?->marca ?? '—' }}
                                        -
                                        {{ $ordem->veiculo?->placa ?? '—' }}
                                    </td>
                                    <td><span class="badge bg-info">{{ $ordem->situacao }}</span></td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($ordem->data_chamado)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                       
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Atalhos rápidos -->
        <div class="row mt-4 g-3">
            <div class="col-md-4">
                <a href="{{ route('ordem.create') }}" class="btn btn-success w-100 p-3 shadow-sm">
                    <i class="bi bi-file-earmark-plus"></i> Nova Ordem de Serviço
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('clientes.index') }}" class="btn btn-primary w-100 p-3 shadow-sm">
                    <i class="bi bi-person-plus"></i> Gerenciar Clientes
                </a>
            </div>
            <div class="col-md-4">
                <a href="/estoque" class="btn btn-warning w-100 p-3 shadow-sm">
                    <i class="bi bi-box-seam"></i> Ver Estoque
                </a>
            </div>
        </div>
    </div>
@endsection
