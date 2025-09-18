@extends('adminlte::page')

@section('title', 'Nueva Reparación')

@section('content_header')
    <h1>Registrar Nueva Reparación</h1>
@stop

@section('content')
    <div class="card">
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

            <form action="{{ route('mantenimiento.reparacion.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="habitacion_id">Habitación</label>
                            <select name="habitacion_id" id="habitacion_id" class="form-control" required>
                                <option value="">Seleccione una habitación</option>
                                @foreach ($habitaciones as $habitacion)
                                    <option value="{{ $habitacion->id }}">
                                        Habitación {{ $habitacion->numero }} - {{ $habitacion->categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control"
                                value="{{ old('fecha', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" name="hora" id="hora" class="form-control"
                                value="{{ old('hora', date('H:i')) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_fin">Fecha Fin</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                value="{{ old('fecha_fin') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hora_fin">Hora Fin</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control"
                                value="{{ old('hora_fin') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo_reparacion">Tipo de Reparación</label>
                            <input type="text" name="tipo_reparacion" id="tipo_reparacion" class="form-control"
                                value="{{ old('tipo_reparacion') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" class="form-control" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="costo">Costo</label>
                            <input type="number" name="costo" id="costo" class="form-control" step="0.01"
                                min="0" value="{{ old('costo', '0.00') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" required>{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('mantenimiento.reparacion.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Aquí puedes agregar cualquier JavaScript necesario
        });
    </script>
@stop
