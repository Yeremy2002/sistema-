@extends('adminlte::page')

@section('title', 'Check-in')

@section('content_header')
    <h1>Check-in para Habitación {{ $habitacione->numero }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('reservas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="habitacion_id" value="{{ $habitacione->id }}">

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
                            <label for="documento_identidad">Documento de Identidad (DPI)</label>
                            <div class="input-group">
                                <input type="text" name="documento_identidad" id="documento_identidad"
                                    class="form-control @error('documento_identidad') is-invalid @enderror"
                                    value="{{ old('documento_identidad') }}" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="buscar-por-dpi">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Será usado también como NIT si no se especifica otro</small>
                            @error('documento_identidad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nit">NIT (Opcional)</label>
                            <div class="input-group">
                                <input type="text" name="nit" id="nit"
                                    class="form-control @error('nit') is-invalid @enderror" value="{{ old('nit') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="buscar-por-nit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Si se deja vacío, se usará el DPI como NIT</small>
                            @error('nit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
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
                            <label for="fecha_entrada">Fecha de Entrada <span class="text-muted">(Check-in
                                    14:00)</span></label>
                            <input type="date" name="fecha_entrada" id="fecha_entrada"
                                class="form-control @error('fecha_entrada') is-invalid @enderror"
                                value="{{ old('fecha_entrada', $fechaActual ?? date('Y-m-d')) }}" required>
                            <small class="form-text text-info">
                                <i class="fas fa-info-circle"></i> El check-in siempre se realiza a las 14:00 horas
                            </small>
                            <small class="form-text text-danger">
                                <i class="fas fa-calendar-day"></i> Fecha actual del sistema: {{ date('d/m/Y') }}
                                ({{ date('l') }})
                            </small>
                            @error('fecha_entrada')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_salida">Fecha de Salida <span class="text-muted">(Check-out
                                    12:30)</span></label>
                            <input type="date" name="fecha_salida" id="fecha_salida"
                                class="form-control @error('fecha_salida') is-invalid @enderror"
                                value="{{ old('fecha_salida') }}" required>
                            <small class="form-text text-info">
                                <i class="fas fa-info-circle"></i> El check-out debe realizarse a las 12:30 horas
                            </small>
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

            // Usar la fecha actual del sistema sin modificaciones
            let fechaPhp = '{{ $fechaActual ?? date('Y-m-d') }}';
            let now = new Date(fechaPhp);

            // Establecer la hora de check-in sin cambiar la fecha
            now = setCheckInTime(now);
            fechaEntrada.min = fechaPhp;
            fechaEntrada.value = fechaPhp;

            // Mostrar fecha seleccionada para debug
            console.log('Fecha de entrada seleccionada (PHP):', fechaPhp);
            console.log('Fecha JavaScript:', now.toISOString().split('T')[0]);

            // Configurar fecha de salida mínima
            fechaEntrada.addEventListener('change', function() {
                const entrada = new Date(this.value);
                setCheckInTime(entrada);

                // La fecha de salida debe ser al menos un día después
                const minSalida = new Date(entrada);
                minSalida.setDate(minSalida.getDate() + 1);
                setCheckOutTime(minSalida);

                // Convertir a formato YYYY-MM-DD sin dependencia de toISOString
                const salida_año = minSalida.getFullYear();
                const salida_mes = String(minSalida.getMonth() + 1).padStart(2, '0');
                const salida_dia = String(minSalida.getDate()).padStart(2, '0');
                const fechaSalidaStr = `${salida_año}-${salida_mes}-${salida_dia}`;

                console.log('Fecha salida calculada:', fechaSalidaStr);

                fechaSalida.min = fechaSalidaStr;
                if (!fechaSalida.value || new Date(fechaSalida.value) <= entrada) {
                    fechaSalida.value = fechaSalidaStr;
                }
            });

            // Asegurar que la fecha de salida tenga la hora de check-out correcta
            fechaSalida.addEventListener('change', function() {
                const salida = new Date(this.value);
                setCheckOutTime(salida);
            });

            // Buscar cliente por DPI o NIT
            document.getElementById('buscar-por-dpi').addEventListener('click', function() {
                buscarCliente('dpi', document.getElementById('documento_identidad').value);
            });

            document.getElementById('buscar-por-nit').addEventListener('click', function() {
                buscarCliente('nit', document.getElementById('nit').value);
            });

            // Función para buscar cliente por DPI o NIT
            function buscarCliente(tipo, valor) {
                if (!valor) {
                    alert('Debe ingresar un ' + tipo.toUpperCase() + ' para buscar.');
                    return;
                }

                // Realizar petición AJAX para buscar cliente
                fetch(`/api/clientes/buscar-por-${tipo}/${valor}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la búsqueda');
                        }
                        return response.json();
                    })
                    .then(cliente => {
                        if (cliente) {
                            // Llenar formulario con datos del cliente
                            document.getElementById('nombre_cliente').value = cliente.nombre;
                            document.getElementById('documento_identidad').value = cliente.dpi;
                            document.getElementById('nit').value = cliente.nit;
                            document.getElementById('telefono').value = cliente.telefono;
                            alert('Cliente encontrado. Se han cargado sus datos.');
                        } else {
                            alert('No se encontró ningún cliente con ese ' + tipo.toUpperCase() + '.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al buscar el cliente.');
                    });
            }

            // Trigger inicial para establecer las fechas correctamente
            fechaEntrada.dispatchEvent(new Event('change'));
        });
    </script>
@stop
