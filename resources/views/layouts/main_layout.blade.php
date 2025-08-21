<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auto Mecânica Renato')</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/dist/css/bootstrap.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">
    
    {{-- condição para OS CSS --}}
    @if (request()->routeIs('ordem.*'))
    <link rel="stylesheet" href="{{ asset('assets/css/os/edit-os.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/os/index-os.css') }}">
    @endif
    
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar p-3">
            <h4 class="text-center mb-3">Oficina</h4>
            <a href="{{ route('clientes.index') }}"><i class="bi bi-people"></i> Clientes</a>
            <a href="{{ route('veiculo.index') }}"><i class="bi bi-truck"></i> Veículos</a>
            <a href="{{ route('ordem.index') }}"><i class="bi bi-card-checklist"></i> Ordens de Serviço</a>
            <a href="{{ route('estoque.index') }}"><i class="bi bi-box"></i> Estoque</a>
            <a href="{{ route('servico.index') }}"><i class="bi bi-tools"></i> Serviços</a>
            <a href="{{ route('logout') }}"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>

        <!-- Conteúdo -->
        <div class="flex-grow-1">
            <nav class="navbar navbar-light px-3 d-flex align-items-center">
                
                <span class="hamburger" id="hamburger">
                    <i class="bi bi-list"></i>
                </span>
                <span class="ms-3 fw-bold">@yield('page_title', 'Painel da Oficina')</span>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2" title="Ir para Home">
                    <i class="bi bi-house-door"></i>
                </a>
            </nav>

            <div class="container mt-4">
                @include('partials.alerts')
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/alerts.js') }}"></script>
    <script src="{{ asset('assets/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/masks.js') }}"></script>
    {{-- <script src="https://unpkg.com/imask"></script> --}}
    <script src="{{ asset('assets/js/maskPlaca.js') }}"></script>

    {{-- CONFERIR DEPOIS SE ESTA SENDO USADO POR ALGUMA PAGINA --}}
    {{-- <script src="{{ asset('assets/js/os-itens.js') }}"></script> --}}

    {{-- ESTA SENDO USADO NA PAGINA ordens/itens.blade.php --}}
    <script src="{{ asset('assets/js/os-itens-all.js') }}"></script>

</body>
</html>
