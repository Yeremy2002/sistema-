@extends('adminlte::auth.login')

@section('title', 'Iniciar Sesión')

@php
    $nombreHotel = $hotel->nombre ?? 'el sistema';
@endphp
@section('auth_header', "Iniciar Sesión en $nombreHotel")

@section('auth_body')
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="Correo electrónico" value="{{ old('email') }}" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Contraseña" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="row">
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Recuérdame</label>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    @if (Route::has('password.request'))
        <a class="btn btn-link" href="{{ route('password.request') }}">
            ¿Olvidaste tu contraseña?
        </a>
    @endif
    
    <div class="text-center mt-3">
        <a href="{{ url('/') }}" class="btn btn-outline-primary">
            <i class="fas fa-home"></i> Página Inicio
        </a>
    </div>
@endsection

@push('css')
    <style>
        body.login-page,
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #3a8dde 0%, #6f42c1 100%);
            background: url('/img/login-bg.jpg') no-repeat center center fixed, linear-gradient(135deg, #3a8dde 0%, #6f42c1 100%);
            background-size: cover;
        }
    </style>
@endpush
