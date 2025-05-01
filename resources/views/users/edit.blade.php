@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1>Editar Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información del Usuario</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name', $usuario->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ old('email', $usuario->email) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="roles">Roles</label>
                            <select name="roles[]" id="roles" class="form-control select2" multiple required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ in_array($role->name, old('roles', $userRoles)) ? 'selected' : '' }}>
                                        {{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="active">Estado</label>
                            <select name="active" id="active" class="form-control" required>
                                <option value="1" {{ old('active', $usuario->active) == '1' ? 'selected' : '' }}>Activo
                                </option>
                                <option value="0" {{ old('active', $usuario->active) == '0' ? 'selected' : '' }}>
                                    Inactivo
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($usuario->hasRole('Administrador') && auth()->user()->can('asignar caja'))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Asignación de Caja</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('usuarios.asignar-caja', $usuario) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="turno">Turno</label>
                                <select class="form-control @error('turno') is-invalid @enderror" id="turno"
                                    name="turno" required>
                                    <option value="">Seleccione un turno</option>
                                    <option value="matutino" {{ old('turno') == 'matutino' ? 'selected' : '' }}>
                                        Matutino (7:00 AM - 5:00 PM)
                                    </option>
                                    <option value="nocturno" {{ old('turno') == 'nocturno' ? 'selected' : '' }}>
                                        Nocturno (5:00 PM - 7:00 AM)
                                    </option>
                                </select>
                                @error('turno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="saldo_inicial">Saldo Inicial</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" step="0.01"
                                        class="form-control @error('saldo_inicial') is-invalid @enderror" id="saldo_inicial"
                                        name="saldo_inicial" value="{{ old('saldo_inicial') }}" required>
                                    @error('saldo_inicial')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones"
                            rows="3">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-cash-register"></i> Asignar Caja
                        </button>
                    </div>
                </form>

                @if ($usuario->cajas && $usuario->cajas->count() > 0)
                    <div class="table-responsive mt-4">
                        <h4>Historial de Cajas Asignadas</h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Turno</th>
                                    <th>Estado</th>
                                    <th>Saldo Actual</th>
                                    <th>Fecha Apertura</th>
                                    <th>Fecha Cierre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usuario->cajas->sortByDesc('created_at') as $caja)
                                    <tr>
                                        <td>{{ ucfirst($caja->turno) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $caja->estado ? 'success' : 'danger' }}">
                                                {{ $caja->estado ? 'Abierta' : 'Cerrada' }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($caja->saldo_actual, 2) }}</td>
                                        <td>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</td>
                                        <td>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '-' }}</td>
                                        <td>
                                            <a href="{{ route('cajas.show', $caja) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($caja->estado)
                                                <a href="{{ route('cajas.edit', $caja) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-lock"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
@stop

@section('css')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            $('#saldo_inicial').on('input', function() {
                let value = $(this).val();
                if (value) {
                    value = parseFloat(value).toFixed(2);
                    $(this).val(value);
                }
            });
        });
    </script>
@stop
