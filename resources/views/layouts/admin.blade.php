<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Painel - Oficina Renato')</title>

  {{-- Bootstrap e AdminLTE (sem Vite) --}}
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}">

  {{-- Font Awesome (se instalou @fortawesome) --}}
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">


  <link rel="stylesheet" href="{{ asset('assets/bootstrap/dist/css/bootstrap.min.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">

  {{-- 🔥 Print global: aplica em TODAS as páginas quando for imprimir --}}
    <link rel="stylesheet" href="{{ asset('assets/css/print.css') }}" media="print">

    {{-- 🔥 Agora o stack funciona para CSS inline por página --}}
    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

  {{-- Navbar --}}
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link">👤 {{ Auth::user()->name ?? 'Usuário' }}</span>
        </li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="{{ route('logout') }}">Sair</a>
        </li>
      </ul>
    </div>
  </nav>

  {{-- Sidebar --}}
  <aside class="app-sidebar bg-dark text-white">
    <div class="sidebar-brand text-center py-3">
      🚗 <strong>Oficina Renato</strong>
    </div>
    <nav class="mt-3">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="{{ route('admin.users') }}" class="nav-link text-white">👤⚙️ Usuários</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('dashboard') }}" class="nav-link text-white">🏠 Dashboard</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('clientes.index') }}" class="nav-link text-white">👥 Clientes</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('veiculo.index') }}" class="nav-link text-white">🚘 Veículos</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('estoque.index') }}" class="nav-link text-white">📦 Estoque</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('ordem.index') }}" class="nav-link text-white">🧾 Ordens</a>
        </li>
      </ul>
    </nav>
  </aside>

  {{-- Conteúdo principal --}}
  <main class="app-main p-4">
    <div class="container mt-4">
      @include('partials.alerts')
    </div>
    @yield('content')
  </main>

</div>

{{-- Scripts JS --}}
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/popper/popper.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>

<script src="{{ asset('assets/js/alerts.js') }}"></script>
<script src="{{ asset('assets/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/js/masks.js') }}"></script>

</body>
</html>
