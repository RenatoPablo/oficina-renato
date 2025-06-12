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
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar p-3">
            <h4 class="text-center mb-3">Oficina</h4>
            <a href="/clientes"><i class="bi bi-people"></i> Clientes</a>
            <a href="/veiculos"><i class="bi bi-truck"></i> Veículos</a>
            <a href="/ordens"><i class="bi bi-card-checklist"></i> Ordens de Serviço</a>
            <a href="/estoque"><i class="bi bi-box"></i> Estoque</a>
            <a href="/logout"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>

        <!-- Conteúdo -->
        <div class="flex-grow-1">
            <nav class="navbar navbar-light px-3 d-flex align-items-center">
                <span class="hamburger" id="hamburger">
                    <i class="bi bi-list"></i>
                </span>
                <span class="ms-3 fw-bold">@yield('page_title', 'Painel da Oficina')</span>
            </nav>

            <div class="container mt-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/masks.js') }}"></script>

</body>
</html>
