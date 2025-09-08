@extends('layouts.main_layout')

@section('content')
<div class="container mt-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
      <i class="bi bi-file-earmark-plus me-1"></i> Nova Ordem de Serviço
    </h5>
    <a href="{{ route('ordem.index') }}" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>

  {{-- ALERTAS --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-triangle me-1"></i> <strong>Corrija os campos em destaque.</strong>
    </div>
  @endif

  {{-- FORM --}}
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <form action="{{ route('ordem.store') }}" method="POST" id="form-nova-os">
        @csrf
        @include('ordens.partials.form', ['os' => null])
      </form>
    </div>

    <div class="card-footer d-flex justify-content-end gap-2">
      <a href="{{ route('ordem.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg"></i> Cancelar
      </a>
      <button type="submit" form="form-nova-os" class="btn btn-primary">
        <i class="bi bi-save"></i> Salvar OS
      </button>
    </div>
  </div>
</div>
@endsection
