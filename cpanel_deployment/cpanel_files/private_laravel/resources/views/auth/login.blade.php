@extends('auth.login_adminlte')

@section('content')
    <div class="login-box">
        <div class="login-logo">
            @php use Illuminate\Support\Str; @endphp
            @if ($hotel && $hotel->logo)
                @if (Str::startsWith($hotel->logo, ['http://', 'https://']))
                    <img src="{{ $hotel->logo }}" alt="Logo Hotel"
                        style="width:100px;height:100px;object-fit:cover;border-radius:50%;box-shadow:0 4px 16px rgba(0,0,0,0.25);border:4px solid #fff;filter:blur(0.5px);margin-bottom:10px;">
                @else
                    <img src="{{ asset('storage/' . $hotel->logo) }}" alt="Logo Hotel"
                        style="width:100px;height:100px;object-fit:cover;border-radius:50%;box-shadow:0 4px 16px rgba(0,0,0,0.25);border:4px solid #fff;filter:blur(0.5px);margin-bottom:10px;">
                @endif
            @else
                <img src="{{ asset('img/logo-default.png') }}" alt="Logo Hotel"
                    style="width:100px;height:100px;object-fit:cover;border-radius:50%;box-shadow:0 4px 16px rgba(0,0,0,0.25);border:4px solid #fff;filter:blur(0.5px);margin-bottom:10px;">
            @endif
            <a href="{{ url('/') }}"><b>{{ config('app.name', 'Laravel') }}</b></a>
        </div>

        <div class="login-box-body">
            <p class="login-box-msg">Inicia sesión para comenzar</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group has-feedback">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                        placeholder="Correo electrónico">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group has-feedback">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required autocomplete="current-password" placeholder="Contraseña">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-xs-8">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                Recuérdame
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">
                            Ingresar
                        </button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <a href="{{ route('password.request') }}" class="text-center">¿Olvidaste tu contraseña?</a><br><br>
            
            <div class="text-center">
                <a href="{{ url('/') }}" class="btn btn-default btn-block">
                    <i class="fa fa-home"></i> Página Inicio
                </a>
            </div>
        </div>
        <!-- /.login-box-body -->
    </div>
@endsection
