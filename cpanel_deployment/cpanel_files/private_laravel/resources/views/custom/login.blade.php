@extends('adminlte::auth.login')

@section('auth_header')
    @if($hotel && $hotel->logo)
        <div class="text-center mb-3">
            @if(\Str::startsWith($hotel->logo, ['http://', 'https://']))
                <img src="{{ $hotel->logo }}" 
                     alt="{{ $hotel->nombre ? $hotel->nombre . ' Logo' : 'Hotel Logo' }}" 
                     class="brand-image" style="max-width: 80px; max-height: 80px;">
            @else
                <img src="{{ asset('storage/' . $hotel->logo) }}" 
                     alt="{{ $hotel->nombre ? $hotel->nombre . ' Logo' : 'Hotel Logo' }}" 
                     class="brand-image" style="max-width: 80px; max-height: 80px;">
            @endif
        </div>
    @endif
    
    @if($hotel && $hotel->nombre)
        <div class="text-center mb-2">
            <h4><b>{{ $hotel->nombre }}</b></h4>
        </div>
    @endif
    
    Inicia sesión para comenzar
@stop

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
@stop

@section('auth_footer')
    @if (Route::has('password.request'))
        <p class="mb-1">
            <a href="{{ route('password.request') }}">
                ¿Olvidaste tu contraseña?
            </a>
        </p>
    @endif
    
    <p class="mb-0">
        <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-home"></i> Página Inicio
        </a>
    </p>
@stop
