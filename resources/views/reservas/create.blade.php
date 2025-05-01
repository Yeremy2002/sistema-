@extends('adminlte::page')

@section('title', 'Nueva Reserva')

@section('content_header')
    <h1>Nueva Reserva</h1>
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
            <form action="{{ route('reservas.store') }}" method="POST">
                @csrf
                <div class="row mb-4" style="gap: 20px;">
                    <!-- Habitación -->
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label for="habitacion_id">Habitación</label>
                            <div class="select2-wrapper">
                                <select name="habitacion_id" id="habitacion_id" class="form-control select2" required>
                                    <option value="">Seleccione una habitación</option>
                                    @foreach ($habitaciones as $habitacion)
                                        <option value="{{ $habitacion->id }}"
                                            {{ $habitacionSeleccionada && $habitacionSeleccionada->id == $habitacion->id ? 'selected' : '' }}>
                                            Habitación {{ $habitacion->numero }} - {{ $habitacion->categoria->nombre }} -
                                            {{ $habitacion->nivel->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Cliente -->
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label for="cliente_id">Cliente</label>
                            <div class="select2-wrapper">
                                <div class="input-group">
                                    <select name="cliente_id" id="cliente_id" class="form-control select2-clientes" required
                                        style="width: calc(100% - 50px);">
                                        <option value="">Seleccione un cliente</option>
                                    </select>
                                    <div class="input-group-append" style="margin-left: 5px;">
                                        <button type="button" class="btn btn-success" style="height:38px; margin-top: 1px;"
                                            data-toggle="modal" data-target="#modalNuevoCliente">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_entrada">Fecha de Entrada</label>
                            <input type="datetime-local" name="fecha_entrada" id="fecha_entrada" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="dias">Días</label>
                            <input type="number" name="dias" id="dias" class="form-control" min="1"
                                value="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_salida">Fecha de Salida</label>
                            <input type="datetime-local" name="fecha_salida" id="fecha_salida" class="form-control" required
                                readonly>
                        </div>
                    </div>
                </div>

                <!-- Información adicional del cliente -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nombre_cliente">Nombre del Cliente</label>
                            <input type="text" name="nombre_cliente" id="nombre_cliente" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="documento_identidad">Documento de Identidad</label>
                            <input type="text" name="documento_identidad" id="documento_identidad" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Información de pago -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adelanto">Adelanto</label>
                            <div class="input-group">
                                <span class="input-group-text">Q</span>
                                <input type="number" name="adelanto" id="adelanto" class="form-control" step="0.01"
                                    min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('reservas.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para nuevo cliente -->
    <div class="modal fade" id="modalNuevoCliente" tabindex="-1" role="dialog"
        aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoClienteLabel">Nuevo Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-nuevo-cliente">
                        @csrf
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre</label>
                            <input type="text" class="form-control" id="nuevo_nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_nit">NIT</label>
                            <input type="text" class="form-control" id="nuevo_nit" name="nit" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_dpi">DPI</label>
                            <input type="text" class="form-control" id="nuevo_dpi" name="dpi" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_telefono">Teléfono</label>
                            <input type="text" class="form-control" id="nuevo_telefono" name="telefono" required>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                        <div id="cliente-error" class="text-danger mt-2" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <style>
        .select2-container {
            width: 100% !important;
        }

        .row {
            --bs-gutter-x: 2rem;
        }

        .input-group {
            gap: 10px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para habitaciones y clientes
            $('.select2').select2({
                containerCssClass: 'habitacion_id-container'
            });
            $('.select2-clientes').select2({
                placeholder: 'Buscar cliente por NIT, DPI o nombre',
                ajax: {
                    url: '/api/clientes/buscar',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(cliente) {
                                return {
                                    id: cliente.id,
                                    text: cliente.nombre + ' (' + cliente.nit + ' / ' + cliente
                                        .dpi + ')',
                                    nombre: cliente.nombre,
                                    dpi: cliente.dpi,
                                    telefono: cliente.telefono
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            }).on('select2:select', function(e) {
                var data = e.params.data;
                // Autocompletar los campos con la información del cliente
                $('#nombre_cliente').val(data.nombre);
                $('#documento_identidad').val(data.dpi);
                $('#telefono').val(data.telefono);
            });

            // Limpiar los campos cuando se deseleccione un cliente
            $('.select2-clientes').on('select2:unselect', function() {
                $('#nombre_cliente').val('');
                $('#documento_identidad').val('');
                $('#telefono').val('');
            });

            // Guardar nuevo cliente vía AJAX
            $('#form-nuevo-cliente').on('submit', function(e) {
                e.preventDefault();
                $('#cliente-error').hide().text('');
                $.ajax({
                    url: '{{ route('clientes.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(cliente) {
                        var newOption = new Option(cliente.nombre + ' (' + cliente.nit + ' / ' +
                            cliente.dpi + ')', cliente.id, true, true);
                        $('#cliente_id').append(newOption).trigger('change');
                        $('#modalNuevoCliente').modal('hide');
                        $('#form-nuevo-cliente')[0].reset();
                    },
                    error: function(xhr) {
                        let msg = 'Error al guardar el cliente';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                            .message;
                        $('#cliente-error').show().text(msg);
                    }
                });
            });

            // Configuración de zona horaria y hora de check-out
            const TIMEZONE_OFFSET = -6; // Guatemala UTC-6
            const CHECKOUT_HOUR = '12:30';

            // Función para obtener la fecha y hora actual 
            function getGuatemalaDateTime() {
                let now = new Date();
                let utc = now.getTime() + (now.getTimezoneOffset() * 60000);
                return new Date(utc + (3600000 * TIMEZONE_OFFSET));
            }

            // Función para formatear fecha y hora para input datetime-local
            function formatDateTime(date) {
                return date.getFullYear() + '-' +
                    String(date.getMonth() + 1).padStart(2, '0') + '-' +
                    String(date.getDate()).padStart(2, '0') + 'T' +
                    String(date.getHours()).padStart(2, '0') + ':' +
                    String(date.getMinutes()).padStart(2, '0');
            }

            // Establecer fecha y hora actual de Guatemala
            let now = getGuatemalaDateTime();
            $('#fecha_entrada').val(formatDateTime(now));

            // Función para calcular la fecha de salida
            function calcularFechaSalida() {
                let fechaEntrada = new Date($('#fecha_entrada').val());
                let dias = parseInt($('#dias').val()) || 1;

                // Ajustar a zona horaria de Guatemala
                let utc = fechaEntrada.getTime() + (fechaEntrada.getTimezoneOffset() * 60000);
                let fechaGuatemala = new Date(utc + (3600000 * TIMEZONE_OFFSET));

                let fechaSalida = new Date(fechaGuatemala);
                fechaSalida.setDate(fechaSalida.getDate() + dias);

                // Establecer siempre la hora de salida a las 12:30 PM
                let [hours, minutes] = CHECKOUT_HOUR.split(':');
                fechaSalida.setHours(parseInt(hours), parseInt(minutes), 0, 0);

                $('#fecha_salida').val(formatDateTime(fechaSalida));
            }

            // Actualizar fecha de salida cuando cambie la fecha de entrada o los días
            $('#fecha_entrada, #dias').on('change', function() {
                calcularFechaSalida();
            });

            // Trigger inicial para establecer la fecha de salida
            calcularFechaSalida();
        });
    </script>
@stop
