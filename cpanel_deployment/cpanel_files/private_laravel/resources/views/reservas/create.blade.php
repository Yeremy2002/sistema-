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
                            @if (isset($habitacionSeleccionada) && $habitacionSeleccionada)
                                <input type="hidden" name="habitacion_id" value="{{ $habitacionSeleccionada->id }}">
                                <input type="text" class="form-control"
                                    value="Habitación {{ $habitacionSeleccionada->numero }} - {{ $habitacionSeleccionada->categoria->nombre }} - {{ $habitacionSeleccionada->nivel->nombre }}"
                                    readonly>
                            @else
                                <div class="select2-wrapper">
                                    <select name="habitacion_id" id="habitacion_id" class="form-control select2" required>
                                        <option value="">Seleccione una habitación</option>
                                        @foreach ($habitaciones as $habitacion)
                                            <option value="{{ $habitacion->id }}"
                                                {{ $habitacionSeleccionada && $habitacionSeleccionada->id == $habitacion->id ? 'selected' : '' }}>
                                                Habitación {{ $habitacion->numero }} - {{ $habitacion->categoria->nombre }}
                                                -
                                                {{ $habitacion->nivel->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Cliente -->
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label for="buscarCliente">Buscar Cliente (NIT, DPI o Nombre)</label>
                            <div class="input-group">
                                <input type="text" id="buscarCliente" class="form-control"
                                    placeholder="Ingrese NIT, DPI o nombre...">
                                <div class="input-group-append" style="margin-left: 5px;">
                                    <button type="button" class="btn btn-success" style="height:38px; margin-top: 1px;"
                                        data-toggle="modal" data-target="#modalNuevoCliente">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="resultadosBusqueda" class="dropdown-menu" style="width: 100%;"></div>
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
                                <span class="input-group-text">{{ $hotel->simbolo_moneda }}</span>
                                <input type="number" value="{{ number_format($adelanto, 2, '.', '') }}" name="adelanto"
                                    id="adelanto" class="form-control" step="0.01" min="0" required>
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

                <div id="feedback_reserva" class="mt-2"></div>

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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para habitaciones y clientes
            $('.select2').select2({
                containerCssClass: 'habitacion_id-container'
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

        // Mayúsculas en nombre del cliente
        document.addEventListener('DOMContentLoaded', function() {
            const nombreCliente = document.getElementById('nombre_cliente');
            if (nombreCliente) {
                nombreCliente.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }
            // Modal nuevo cliente
            const nuevoNombre = document.getElementById('nuevo_nombre');
            if (nuevoNombre) {
                nuevoNombre.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }
        });

        const inputBusqueda = document.getElementById('buscarCliente');
        const resultadosBusqueda = document.getElementById('resultadosBusqueda');

        inputBusqueda.addEventListener('input', async (e) => {
            const query = e.target.value;
            resultadosBusqueda.classList.add('show');
            resultadosBusqueda.style.display = 'block';
            resultadosBusqueda.style.position = 'absolute';
            resultadosBusqueda.style.zIndex = 1000;
            resultadosBusqueda.style.left = inputBusqueda.offsetLeft + 'px';
            resultadosBusqueda.style.top = (inputBusqueda.offsetTop + inputBusqueda.offsetHeight) + 'px';
            resultadosBusqueda.style.width = inputBusqueda.offsetWidth + 'px';

            if (query.length < 3) {
                resultadosBusqueda.innerHTML = '';
                resultadosBusqueda.classList.remove('show');
                resultadosBusqueda.style.display = 'none';
                return;
            }
            resultadosBusqueda.innerHTML = '<div class="dropdown-item">Buscando...</div>';
            try {
                const response = await axios.get(`/api/clientes/buscar?q=${query}`);
                const clientes = response.data;
                mostrarResultados(clientes);
            } catch (error) {
                resultadosBusqueda.innerHTML = '<div class="dropdown-item text-danger">Error al buscar</div>';
            }
        });

        function mostrarResultados(clientes) {
            resultadosBusqueda.innerHTML = '';
            if (clientes.length === 0) {
                resultadosBusqueda.innerHTML = '<div class="dropdown-item">No se encontraron clientes</div>';
                return;
            }
            clientes.forEach(cliente => {
                const item = document.createElement('div');
                item.className = 'dropdown-item';
                item.innerHTML = `${cliente.nombre} (${cliente.nit})`;
                item.style.cursor = 'pointer';
                item.addEventListener('click', () => seleccionarCliente(cliente));
                resultadosBusqueda.appendChild(item);
            });
        }

        function seleccionarCliente(cliente) {
            document.getElementById('nombre_cliente').value = cliente.nombre;
            document.getElementById('documento_identidad').value = cliente.dpi;
            document.getElementById('telefono').value = cliente.telefono;
            resultadosBusqueda.innerHTML = '';
            resultadosBusqueda.classList.remove('show');
            resultadosBusqueda.style.display = 'none';
        }

        // Oculta el dropdown si haces click fuera
        document.addEventListener('click', function(event) {
            if (!inputBusqueda.contains(event.target) && !resultadosBusqueda.contains(event.target)) {
                resultadosBusqueda.innerHTML = '';
                resultadosBusqueda.classList.remove('show');
                resultadosBusqueda.style.display = 'none';
            }
        });

        function validarFechas() {
            const entrada = new Date(document.getElementById('fecha_entrada').value);
            const salida = new Date(document.getElementById('fecha_salida').value);
            const feedback = document.getElementById('feedback_reserva');
            let msg = '';
            if (entrada && salida) {
                // Solo mostrar advertencia si la fecha y hora de salida es igual o anterior a la de entrada
                if (salida.getTime() <= entrada.getTime()) {
                    msg =
                    '<div class="alert alert-danger p-2">La fecha de salida debe ser posterior a la de entrada.</div>';
                    document.getElementById('fecha_salida').classList.add('is-invalid');
                } else {
                    document.getElementById('fecha_salida').classList.remove('is-invalid');
                }
            }
            feedback.innerHTML = msg;
        }
        document.getElementById('fecha_entrada').addEventListener('input', validarFechas);
        document.getElementById('fecha_salida').addEventListener('input', validarFechas);

        // Validación con SweetAlert2 al enviar el formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const entrada = new Date(document.getElementById('fecha_entrada').value);
            const salida = new Date(document.getElementById('fecha_salida').value);
            let errorMsg = '';
            if (entrada && salida && salida.getTime() <= entrada.getTime()) {
                errorMsg = 'La fecha de salida debe ser posterior a la de entrada.';
            }
            if (errorMsg) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la reserva',
                    text: errorMsg
                });
            }
        });

        // --- NUEVO: AJAX para crear cliente desde el modal ---
        document.addEventListener('DOMContentLoaded', function() {
            const formNuevoCliente = document.getElementById('form-nuevo-cliente');
            if (formNuevoCliente) {
                formNuevoCliente.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const nombre = document.getElementById('nuevo_nombre').value.trim();
                    const nit = document.getElementById('nuevo_nit').value.trim();
                    const dpi = document.getElementById('nuevo_dpi').value.trim();
                    const telefono = document.getElementById('nuevo_telefono').value.trim();
                    const errorDiv = document.getElementById('cliente-error');
                    errorDiv.style.display = 'none';
                    errorDiv.innerText = '';

                    if (!nombre || !nit || !dpi || !telefono) {
                        errorDiv.innerText = 'Todos los campos son obligatorios.';
                        errorDiv.style.display = 'block';
                        return;
                    }
                    try {
                        const response = await axios.post('/clientes', {
                            nombre: nombre,
                            nit: nit,
                            dpi: dpi,
                            telefono: telefono
                        });
                        const cliente = response.data;
                        // Rellenar los campos del formulario de reserva
                        document.getElementById('nombre_cliente').value = cliente.nombre;
                        document.getElementById('documento_identidad').value = cliente.dpi;
                        document.getElementById('telefono').value = cliente.telefono;
                        // Cerrar el modal
                        $('#modalNuevoCliente').modal('hide');
                        // Limpiar el formulario del modal
                        formNuevoCliente.reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Cliente creado',
                            text: 'El cliente fue registrado exitosamente.'
                        });
                    } catch (error) {
                        let msg = 'Error al crear el cliente.';
                        if (error.response && error.response.data && error.response.data.errors) {
                            const errors = error.response.data.errors;
                            msg = Object.values(errors).map(arr => arr.join(' ')).join(' ');
                        } else if (error.response && error.response.data && error.response.data
                            .message) {
                            msg = error.response.data.message;
                        }
                        errorDiv.innerText = msg;
                        errorDiv.style.display = 'block';
                    }
                });
            }
        });
    </script>
@stop
