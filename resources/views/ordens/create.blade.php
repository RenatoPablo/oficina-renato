@extends('layouts.main_layout')

@section('content')
<h5 class="mb-3">Nova Ordem de Serviço</h5>

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger">
    <strong>Corrija os campos em destaque.</strong>
  </div>
@endif

<form action="{{ route('ordem.store') }}" method="POST" class="card p-3 shadow-sm border-0">
  @include('ordens.partials.form', ['os' => null])
</form>
@endsection
