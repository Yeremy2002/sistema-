@extends('adminlte::page')

@section('title', 'Editar Reserva')

@section('content_header')
    <h1>Editar Reserva</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('reservas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Información de resumen financiero -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <h5>Total</h5>
                                    <h3 class="text-primary">{{ $hotel->simbolo_moneda }}
                                        {{ number_format($reserva->total, 2) }}
                                    </h3>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h5>Adelanto</h5>
                                    <h3 class="text-success">{{ $hotel->simbolo_moneda }}
                                        {{ number_format($reserva->adelanto, 2) }}
                                    </h3>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h5>Pendiente</h5>
                                    <h3 class="text-danger">{{ $hotel->simbolo_moneda }}
                                        {{ number_format($reserva->pendiente, 2) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('reservas.update', $reserva) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Información de la Habitación -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title">Información de la Habitación</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="habitacion_id">Habitación</label>
                                    <select class="form-control" id="habitacion_id" name="habitacion_id" required>
                                        @foreach ($habitaciones as $habitacion)
                                            <option value="{{ $habitacion->id }}"
                                                {{ $reserva->habitacion_id == $habitacion->id ? 'selected' : '' }}
                                                {{ $habitacion->id != $reserva->habitacion_id && $habitacion->estado != 'Disponible' ? 'disabled' : '' }}>
                                                {{ $habitacion->numero }} - {{ $habitacion->categoria->nombre }}
                                                ({{ $habitacion->precio }} {{ $hotel->simbolo_moneda }})
                                                -
                                                {{ $habitacion->id != $reserva->habitacion_id && $habitacion->estado != 'Disponible' ? 'No disponible' : 'Disponible' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="fecha_entrada">Fecha de Entrada</label>
                                    <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada"
                                        value="{{ $reserva->fecha_entrada->format('Y-m-d') }}" required>
                                    <small class="text-muted">Hora de check-in: 14:00</small>
                                </div>

                                <div class="form-group">
                                    <label for="fecha_salida">Fecha de Salida</label>
                                    <input type="date" class="form-control" id="fecha_salida" name="fecha_salida"
                                        value="{{ $reserva->fecha_salida->format('Y-m-d') }}" required>
                                    <small class="text-muted">Hora de check-out: 12:30</small>
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado de la Reserva</label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="Pendiente de Confirmación"
                                            {{ $reserva->estado == 'Pendiente de Confirmación' ? 'selected' : '' }}>
                                            Pendiente de Confirmación</option>
                                        <option value="Pendiente" {{ $reserva->estado == 'Pendiente' ? 'selected' : '' }}>
                                            Pendiente</option>
                                        <option value="Check-in" {{ $reserva->estado == 'Check-in' ? 'selected' : '' }}>
                                            Check-in</option>
                                        <option value="Check-out" {{ $reserva->estado == 'Check-out' ? 'selected' : '' }}>
                                            Check-out</option>
                                        <option value="Cancelada" {{ $reserva->estado == 'Cancelada' ? 'selected' : '' }}>
                                            Cancelada</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ $reserva->observaciones }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="adelanto">Adelanto ({{ $hotel->simbolo_moneda }})</label>
                                    <input type="number" step="0.01" class="form-control" id="adelanto" name="adelanto"
                                        value="{{ $reserva->adelanto }}" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Cliente -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info">
                                <h3 class="card-title">Información del Cliente</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="cliente_id">Cliente</label>
                                    <select class="form-control" id="cliente_id" name="cliente_id" required>
                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->id }}"
                                                {{ $reserva->cliente_id == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->nombre }} - DPI: {{ $cliente->dpi }} - NIT:
                                                {{ $cliente->nit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="nombre_cliente">Nombre del Cliente</label>
                                    <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente"
                                        value="{{ $reserva->nombre_cliente }}" required>
                                    <small class="text-muted">Este nombre se guardará automáticamente en mayúsculas</small>
                                </div>

                                <div class="form-group">
                                    <label for="documento_cliente">Documento de Identidad (DPI)</label>
                                    <input type="text" class="form-control" id="documento_cliente"
                                        name="documento_cliente" value="{{ $reserva->documento_cliente }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="telefono_cliente">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono_cliente"
                                        name="telefono_cliente" value="{{ $reserva->telefono_cliente }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .card-header {
            color: white;
        }
    </style>
@stop

@section('js')
    <script>
        // Función para actualizar automáticamente los campos del cliente cuando se selecciona uno
        document.getElementById('cliente_id').addEventListener('change', function() {
            const clienteId = this.value;
            const clientes = @json($clientes);

            const clienteSeleccionado = clientes.find(cliente => cliente.id == clienteId);

            if (clienteSeleccionado) {
                document.getElementById('nombre_cliente').value = clienteSeleccionado.nombre;
                document.getElementById('documento_cliente').value = clienteSeleccionado.dpi;
                document.getElementById('telefono_cliente').value = clienteSeleccionado.telefono;
            }
        });

        // Convertir nombre a mayúsculas al perder el foco
        document.getElementById('nombre_cliente').addEventListener('blur', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
@stop
