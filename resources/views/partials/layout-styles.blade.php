    {{-- condição para OS CSS --}}
    @if (request()->routeIs('ordem.*'))
      <link rel="stylesheet" href="{{ asset('assets/css/os/table-itens-os.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/css/os/index-os.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/css/os/create-os.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/css/os/forms-override.css') }}">
    @endif

    {{-- para carregar estoque --}}
    @if (request()->routeIs('estoque.*'))
      <link rel="stylesheet" href="{{ asset('assets/css/estoque/create-estoque.css') }}">
    @endif

    {{-- 🔥 Print global: aplica em TODAS as páginas quando for imprimir --}}
    <link rel="stylesheet" href="{{ asset('assets/css/print.css') }}" media="print">

    {{-- 🔥 Agora o stack funciona para CSS inline por página --}}
    @stack('styles')
