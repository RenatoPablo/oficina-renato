@extends("layouts.auth_layout")

@section('content')
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Bem-vindo!</h2>
            <form action="/loginSubmit" method="post">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" id="email" placeholder="Digite seu email" value="{{ old('email') }}">
                    {{-- show error --}}
                    @error('text_username')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input name="password" type="password" class="form-control" id="password" placeholder="Digite sua senha" value="{{ old('password') }}">
                    {{-- show error --}}
                    @error('text_username')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Lembrar-me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="#" class="text-decoration-none">Esqueceu sua senha?</a>
            </div>

            {{-- invalid login --}}
            @if(session('loginError'))
                <div class="alert alert-danger text-center">
                    {{ session('loginError') }}
                </div>
            @endif
        </div>
    </div>
@endsection