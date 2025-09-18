@extends('adminlte::page')

@section('title', 'Asignar Caja')

@section('content_header')
    <h1>Asignar Caja a Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Asignación de Caja</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('cajas.asignar.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id">Usuario</label>
                            <select class="form-control select2 @error('user_id') is-invalid @enderror" id="user_id"
                                name="user_id" required>
                                <option value="">Seleccione un usuario</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}"
                                        {{ old('user_id') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }} - {{ $usuario->roles->first()->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="turno">Turno</label>
                            <select class="form-control @error('turno') is-invalid @enderror" id="turno" name="turno"
                                required>
                                <option value="">Seleccione un turno</option>
                                <option value="matutino" {{ old('turno') == 'matutino' ? 'selected' : '' }}>Matutino (7:00
                                    AM - 5:00 PM)</option>
                                <option value="nocturno" {{ old('turno') == 'nocturno' ? 'selected' : '' }}>Nocturno (5:00
                                    PM - 7:00 AM)</option>
                            </select>
                            @error('turno')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Asignar Caja
                    </button>
                    <a href="{{ route('cajas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cajas Asignadas Actualmente</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Turno</th>
                            <th>Estado</th>
                            <th>Saldo Actual</th>
                            <th>Fecha Apertura</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cajasActivas as $caja)
                            <tr>
                                <td>{{ $caja->user->name }}</td>
                                <td>{{ $caja->user->roles->first()->name }}</td>
                                <td>{{ ucfirst($caja->turno) }}</td>
                                <td>
                                    <span class="badge badge-{{ $caja->estado ? 'success' : 'danger' }}">
                                        {{ $caja->estado ? 'Abierta' : 'Cerrada' }}
                                    </span>
                                </td>
                                <td>${{ number_format($caja->saldo_actual, 2) }}</td>
                                <td>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</td>
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
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione un usuario'
            });

            // Formatear el saldo inicial mientras se escribe
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

@section('css')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop
