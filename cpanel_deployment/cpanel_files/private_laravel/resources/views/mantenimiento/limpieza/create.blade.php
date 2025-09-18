@extends('adminlte::page')

@section('title', 'Nueva Limpieza')

@section('content_header')
    <h1>Registrar Nueva Limpieza</h1>
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

            <form action="{{ route('mantenimiento.limpieza.store') }}" method="POST">
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('mantenimiento.limpieza.index') }}" class="btn btn-secondary">Cancelar</a>
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
