@extends('adminlte::page')

@section('title', 'Check-in')

@section('content_header')
    <h1>Check-in para Habitación {{ $habitacione->numero }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('reservas.store', $habitacione) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_cliente">Nombre del Cliente</label>
                            <input type="text" name="nombre_cliente" id="nombre_cliente"
                                class="form-control @error('nombre_cliente') is-invalid @enderror"
                                value="{{ old('nombre_cliente') }}" required>
                            @error('nombre_cliente')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="documento_identidad">Documento de Identidad</label>
                            <input type="text" name="documento_identidad" id="documento_identidad"
                                class="form-control @error('documento_identidad') is-invalid @enderror"
                                value="{{ old('documento_identidad') }}" required>
                            @error('documento_identidad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono"
                                class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}"
                                required>
                            @error('telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="adelanto">Adelanto</label>
                            <input type="number" name="adelanto" id="adelanto"
                                class="form-control @error('adelanto') is-invalid @enderror" value="{{ old('adelanto') }}"
                                required min="0" step="0.01">
                            @error('adelanto')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_entrada">Fecha de Entrada</label>
                            <input type="date" name="fecha_entrada" id="fecha_entrada"
                                class="form-control @error('fecha_entrada') is-invalid @enderror"
                                value="{{ old('fecha_entrada', date('Y-m-d')) }}" required>
                            @error('fecha_entrada')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_salida">Fecha de Salida</label>
                            <input type="date" name="fecha_salida" id="fecha_salida"
                                class="form-control @error('fecha_salida') is-invalid @enderror"
                                value="{{ old('fecha_salida') }}" required>
                            @error('fecha_salida')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror"
                        rows="3">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="card bg-light mt-3">
                    <div class="card-body">
                        <h5>Detalles de la Habitación</h5>
                        <p><strong>Número:</strong> {{ $habitacione->numero }}</p>
                        <p><strong>Categoría:</strong> {{ $habitacione->categoria->nombre }}</p>
                        <p><strong>Nivel:</strong> {{ $habitacione->nivel->nombre }}</p>
                        <p><strong>Precio por día:</strong> S/. {{ number_format($habitacione->precio, 2) }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Realizar Check-in</button>
                    <a href="{{ route('habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Constantes para las horas de check-in y check-out
            const CHECKIN_HOUR = '14:00';
            const CHECKOUT_HOUR = '12:30';

            const fechaEntrada = document.getElementById('fecha_entrada');
            const fechaSalida = document.getElementById('fecha_salida');

            // Función para establecer la hora de check-in
            function setCheckInTime(date) {
                const [hours, minutes] = CHECKIN_HOUR.split(':');
                date.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                return date;
            }

            // Función para establecer la hora de check-out
            function setCheckOutTime(date) {
                const [hours, minutes] = CHECKOUT_HOUR.split(':');
                date.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                return date;
            }

            // Configurar fecha de entrada
            let now = new Date();
            // Si es después de la hora de check-in, programar para el día siguiente
            if (now.getHours() >= 14 || (now.getHours() === 14 && now.getMinutes() > 0)) {
                now.setDate(now.getDate() + 1);
            }
            now = setCheckInTime(now);
            fechaEntrada.min = now.toISOString().split('T')[0];
            fechaEntrada.value = now.toISOString().split('T')[0];

            // Configurar fecha de salida mínima
            fechaEntrada.addEventListener('change', function() {
                const entrada = new Date(this.value);
                setCheckInTime(entrada);

                // La fecha de salida debe ser al menos un día después
                const minSalida = new Date(entrada);
                minSalida.setDate(minSalida.getDate() + 1);
                setCheckOutTime(minSalida);

                fechaSalida.min = minSalida.toISOString().split('T')[0];
                if (fechaSalida.value && new Date(fechaSalida.value) <= entrada) {
                    fechaSalida.value = minSalida.toISOString().split('T')[0];
                }
            });

            // Asegurar que la fecha de salida tenga la hora de check-out correcta
            fechaSalida.addEventListener('change', function() {
                const salida = new Date(this.value);
                setCheckOutTime(salida);
            });

            // Trigger inicial para establecer las fechas correctamente
            fechaEntrada.dispatchEvent(new Event('change'));
        });
    </script>
@stop
