@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
  <h1>Bem-vindo, {{ Auth::user()->name ?? 'Usuário' }}!</h1>
  <div class="row mt-4">
    <div class="col-md-3">
      <div class="small-box bg-primary text-white p-3 rounded">
        <div class="inner">
          <h3>16</h3>
          <p>Clientes</p>
        </div>
        <div class="icon">
          <i class="fas fa-users"></i>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
