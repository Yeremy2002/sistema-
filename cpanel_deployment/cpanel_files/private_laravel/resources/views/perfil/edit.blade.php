@extends('adminlte::page')

@section('title', 'Mi Perfil')

@section('content_header')
    <h1>Mi Perfil</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-user-circle fa-2x mr-2"></i>
                    <h4 class="mb-0 ml-2">Mi Perfil</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('perfil.update') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="mb-4">
                            <h5 class="text-secondary"><i class="fas fa-id-card mr-1"></i> Información personal</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nombre</label>
                                        <input type="text" name="name" id="name" class="form-control"
                                            value="{{ old('name', $user->name) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            value="{{ old('email', $user->email) }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h5 class="text-secondary"><i class="fas fa-key mr-1"></i> Cambiar contraseña</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="current_password">Contraseña actual</label>
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control" autocomplete="new-password">
                                        <small class="form-text text-muted">Requerida solo si deseas cambiar la
                                            contraseña.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password">Nueva contraseña</label>
                                        <input type="password" name="password" id="password" class="form-control"
                                            autocomplete="new-password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirmar nueva contraseña</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Actualizar
                                    Perfil</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
