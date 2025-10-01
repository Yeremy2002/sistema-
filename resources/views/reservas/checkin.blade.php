@extends('adminlte::page')

@section('title', 'Check-in')

@section('content_header')
    <h1>Check-in para Habitación {{ isset($reserva) ? $reserva->habitacion->numero : $habitacione->numero }}</h1>
@stop

@section('content')
    {{-- Mensajes de sesión --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('alerta_caja_requerida'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-cash-register"></i> ATENCIÓN: CAJA NO ABIERTA</h5>
            <p class="mb-2"><strong>Debe abrir una caja antes de realizar operaciones de check-in.</strong></p>
            <p class="mb-0">
                <a href="{{ route('cajas.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle"></i> Abrir Caja Ahora
                </a>
            </p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form
                action="{{ isset($reserva) ? route('reservas.checkin', $reserva) : route('habitaciones.checkin', $habitacione) }}"
                method="POST">
                @csrf
                @if (isset($reserva))
                    <input type="hidden" name="habitacion_id" value="{{ $reserva->habitacion->id }}">
                @else
                    <input type="hidden" name="habitacion_id" value="{{ $habitacione->id }}">
                @endif

                <div class="row mb-4" style="gap: 20px;">
                    <!-- Cliente -->
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_cliente">Nombre del Cliente</label>
                            <input type="text" name="nombre_cliente" id="nombre_cliente"
                                class="form-control @error('nombre_cliente') is-invalid @enderror"
                                value="{{ old('nombre_cliente', isset($reserva) ? $reserva->nombre_cliente : '') }}"
                                required>
                            @error('nombre_cliente')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="documento_identidad">Documento de Identidad (DPI)</label>
                            <input type="text" name="documento_identidad" id="documento_identidad"
                                class="form-control @error('documento_identidad') is-invalid @enderror"
                                value="{{ old('documento_identidad', isset($reserva) ? $reserva->documento_cliente : '') }}"
                                required>
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
                            <input type="text" name="nit" id="nit"
                                class="form-control @error('nit') is-invalid @enderror"
                                value="{{ old('nit', isset($reserva) && $reserva->cliente ? $reserva->cliente->nit : '') }}">
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
                                class="form-control @error('telefono') is-invalid @enderror"
                                value="{{ old('telefono', isset($reserva) ? $reserva->telefono_cliente : '') }}" required>
                            @error('telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="adelanto">Adelanto</label>
                            <input type="number" name="adelanto" id="adelanto"
                                class="form-control @error('adelanto') is-invalid @enderror"
                                value="{{ old('adelanto', isset($reserva) ? $reserva->adelanto : '') }}" required
                                min="0" step="0.01">
                            @error('adelanto')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_entrada">Fecha de Entrada
                                @if($hotel->permitir_estancias_horas)
                                    <span class="text-muted">(con hora)</span>
                                @else
                                    <span class="text-muted">(Check-in 14:00)</span>
                                @endif
                            </label>
                            @if($hotel->permitir_estancias_horas)
                                <input type="datetime-local" name="fecha_entrada" id="fecha_entrada"
                                    class="form-control @error('fecha_entrada') is-invalid @enderror"
                                    value="{{ old('fecha_entrada', isset($reserva) ? $reserva->fecha_entrada->format('Y-m-d\TH:i') : date('Y-m-d\T14:00')) }}"
                                    required>
                                <small class="form-text text-info">
                                    <i class="fas fa-info-circle"></i> Ingrese fecha y hora exacta para estadías por horas (mismo día)
                                </small>
                            @else
                                <input type="date" name="fecha_entrada" id="fecha_entrada"
                                    class="form-control @error('fecha_entrada') is-invalid @enderror"
                                    value="{{ old('fecha_entrada', isset($reserva) ? $reserva->fecha_entrada->format('Y-m-d') : $fechaActual ?? date('Y-m-d')) }}"
                                    required>
                                <small class="form-text text-info">
                                    <i class="fas fa-info-circle"></i> El check-in siempre se realiza a las 14:00 horas
                                </small>
                            @endif
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
                            <label for="fecha_salida">Fecha de Salida
                                @if($hotel->permitir_estancias_horas)
                                    <span class="text-muted">(con hora)</span>
                                @else
                                    <span class="text-muted">(Check-out 12:30)</span>
                                @endif
                            </label>
                            @if($hotel->permitir_estancias_horas)
                                <input type="datetime-local" name="fecha_salida" id="fecha_salida"
                                    class="form-control @error('fecha_salida') is-invalid @enderror"
                                    value="{{ old('fecha_salida', isset($reserva) ? $reserva->fecha_salida->format('Y-m-d\TH:i') : '') }}"
                                    required>
                                <small class="form-text text-info">
                                    <i class="fas fa-info-circle"></i> Ingrese fecha y hora exacta (máximo hasta las {{ $hotel->checkout_mismo_dia_limite ? $hotel->checkout_mismo_dia_limite->format('H:i') : '23:59' }} para mismo día)
                                </small>
                            @else
                                <input type="date" name="fecha_salida" id="fecha_salida"
                                    class="form-control @error('fecha_salida') is-invalid @enderror"
                                    value="{{ old('fecha_salida', isset($reserva) ? $reserva->fecha_salida->format('Y-m-d') : '') }}"
                                    required>
                                <small class="form-text text-info">
                                    <i class="fas fa-info-circle"></i> El check-out debe realizarse a las 12:30 horas
                                </small>
                            @endif
                            @error('fecha_salida')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror"
                        rows="3">{{ old('observaciones', isset($reserva) ? $reserva->observaciones : '') }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="card bg-light mt-3">
                    <div class="card-body">
                        <h5>Detalles de la Habitación</h5>
                        @if (isset($reserva))
                            <p><strong>Número:</strong> {{ $reserva->habitacion->numero }}</p>
                            <p><strong>Categoría:</strong> {{ $reserva->habitacion->categoria->nombre }}</p>
                            <p><strong>Nivel:</strong> {{ $reserva->habitacion->nivel->nombre }}</p>
                            <p><strong>Precio por día:</strong> {{ $hotel->simbolo_moneda }}
                                {{ number_format($reserva->habitacion->precio, 2) }}</p>
                            @if ($reserva->estado !== 'Check-in')
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Reserva existente:</strong> Esta es una reserva en estado
                                    "{{ $reserva->estado }}" que será actualizada a "Check-in".
                                </div>
                            @endif
                        @else
                            <p><strong>Número:</strong> {{ $habitacione->numero }}</p>
                            <p><strong>Categoría:</strong> {{ $habitacione->categoria->nombre }}</p>
                            <p><strong>Nivel:</strong> {{ $habitacione->nivel->nombre }}</p>
                            <p><strong>Precio por día:</strong> {{ $hotel->simbolo_moneda }}
                                {{ number_format($habitacione->precio, 2) }}</p>
                        @endif
                    </div>
                </div>

                <div id="feedback_checkin" class="mt-2"></div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        {{ isset($reserva) ? 'Confirmar Check-in' : 'Realizar Check-in' }}
                    </button>
                    <a href="{{ route('reservas.index') }}" class="btn btn-secondary">Cancelar</a>
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
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configurar axios con token CSRF
        const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

        console.log('JS de checkin.blade.php inyectado y ejecutándose');
        document.addEventListener('DOMContentLoaded', function() {
            // Constantes para las horas de check-in y check-out
            const CHECKIN_HOUR = '14:00';
            const CHECKOUT_HOUR = '12:30';

            // Configuración de estadías por horas desde el hotel
            const PERMITIR_ESTANCIAS_HORAS = {{ $hotel->permitir_estancias_horas ? 'true' : 'false' }};
            const MINIMO_HORAS_ESTANCIA = {{ $hotel->minimo_horas_estancia ?? 2 }};
            const CHECKOUT_MISMO_DIA_LIMITE = '{{ $hotel->checkout_mismo_dia_limite ?? '20:00' }}';

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

                let minSalida;
                if (PERMITIR_ESTANCIAS_HORAS) {
                    // Si se permiten estadías por horas, la fecha de salida puede ser la misma
                    minSalida = new Date(entrada);
                    // Pero debe ser al menos X horas después del check-in
                    minSalida.setHours(minSalida.getHours() + MINIMO_HORAS_ESTANCIA);

                    // Si la fecha límite del mismo día se pasa, debe ser al día siguiente
                    const [limitHour, limitMin] = CHECKOUT_MISMO_DIA_LIMITE.split(':');
                    const limitTime = new Date(entrada);
                    limitTime.setHours(parseInt(limitHour), parseInt(limitMin), 0, 0);

                    if (minSalida > limitTime) {
                        // Si el tiempo mínimo excede el límite del mismo día, pasar al siguiente día
                        minSalida = new Date(entrada);
                        minSalida.setDate(minSalida.getDate() + 1);
                        setCheckOutTime(minSalida);
                    }
                } else {
                    // Comportamiento tradicional: la fecha de salida debe ser al menos un día después
                    minSalida = new Date(entrada);
                    minSalida.setDate(minSalida.getDate() + 1);
                    setCheckOutTime(minSalida);
                }

                // Convertir a formato YYYY-MM-DD sin dependencia de toISOString
                const salida_año = minSalida.getFullYear();
                const salida_mes = String(minSalida.getMonth() + 1).padStart(2, '0');
                const salida_dia = String(minSalida.getDate()).padStart(2, '0');
                const fechaSalidaStr = `${salida_año}-${salida_mes}-${salida_dia}`;

                console.log('Fecha salida calculada:', fechaSalidaStr, 'Estadías por horas:', PERMITIR_ESTANCIAS_HORAS);

                fechaSalida.min = PERMITIR_ESTANCIAS_HORAS ? entrada.toISOString().split('T')[0] : fechaSalidaStr;
                if (!fechaSalida.value || new Date(fechaSalida.value) < entrada) {
                    fechaSalida.value = fechaSalidaStr;
                }
            });

            // Asegurar que la fecha de salida tenga la hora de check-out correcta
            fechaSalida.addEventListener('change', function() {
                const salida = new Date(this.value);
                setCheckOutTime(salida);
            });

            // Buscar cliente por DPI o NIT
            const btnBuscarPorDpi = document.getElementById('buscar-por-dpi');
            if (btnBuscarPorDpi) {
                btnBuscarPorDpi.addEventListener('click', function() {
                    buscarCliente('dpi', document.getElementById('documento_identidad').value);
                });
            }
            const btnBuscarPorNit = document.getElementById('buscar-por-nit');
            if (btnBuscarPorNit) {
                btnBuscarPorNit.addEventListener('click', function() {
                    buscarCliente('nit', document.getElementById('nit').value);
                });
            }

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

            // Feedback y validación de fechas en tiempo real
            function validarFechasCheckin() {
                const entrada = new Date(document.getElementById('fecha_entrada').value);
                const salida = new Date(document.getElementById('fecha_salida').value);
                const feedback = document.getElementById('feedback_checkin');
                let msg = '';

                if (entrada && salida) {
                    if (PERMITIR_ESTANCIAS_HORAS) {
                        // Para estadías por horas, validar que si es el mismo día, cumpla con los requisitos
                        const esMismoDia = entrada.toDateString() === salida.toDateString();

                        if (esMismoDia) {
                            // Verificar que la hora de salida esté dentro del límite permitido
                            const [limitHour, limitMin] = CHECKOUT_MISMO_DIA_LIMITE.split(':');
                            const limitTime = new Date(entrada);
                            limitTime.setHours(parseInt(limitHour), parseInt(limitMin), 0, 0);

                            // Para validación, usar la hora límite de checkout del mismo día
                            const salidaConHora = new Date(salida);
                            salidaConHora.setHours(parseInt(limitHour), parseInt(limitMin), 0, 0);

                            if (salidaConHora > limitTime) {
                                msg = '<div class="alert alert-warning p-2">Para estadías del mismo día, el check-out debe ser antes de las ' + CHECKOUT_MISMO_DIA_LIMITE + '.</div>';
                            } else {
                                msg = '<div class="alert alert-info p-2"><i class="fas fa-clock"></i> <strong>Estadía por horas:</strong> Check-out el mismo día hasta las ' + CHECKOUT_MISMO_DIA_LIMITE + '.</div>';
                            }
                        } else if (salida < entrada) {
                            msg = '<div class="alert alert-danger p-2">La fecha de salida no puede ser anterior a la de entrada.</div>';
                            document.getElementById('fecha_salida').classList.add('is-invalid');
                        }
                    } else {
                        // Validación tradicional
                        if (salida <= entrada) {
                            msg = '<div class="alert alert-danger p-2">La fecha de salida debe ser posterior a la de entrada.</div>';
                            document.getElementById('fecha_salida').classList.add('is-invalid');
                        } else {
                            document.getElementById('fecha_salida').classList.remove('is-invalid');
                        }
                    }

                    // Remover clase de error si no hay error
                    if (!msg.includes('alert-danger')) {
                        document.getElementById('fecha_salida').classList.remove('is-invalid');
                    }
                }

                feedback.innerHTML = msg;
            }
            if (fechaEntrada) {
                fechaEntrada.addEventListener('input', validarFechasCheckin);
            }
            if (fechaSalida) {
                fechaSalida.addEventListener('input', validarFechasCheckin);
            }

            // Validación con SweetAlert2 al enviar el formulario
            document.querySelector('form').addEventListener('submit', function(e) {
                const entrada = new Date(document.getElementById('fecha_entrada').value);
                const salida = new Date(document.getElementById('fecha_salida').value);
                let errorMsg = '';

                if (entrada && salida) {
                    if (PERMITIR_ESTANCIAS_HORAS) {
                        const esMismoDia = entrada.toDateString() === salida.toDateString();

                        if (salida < entrada) {
                            errorMsg = 'La fecha de salida no puede ser anterior a la de entrada.';
                        } else if (esMismoDia) {
                            // Verificar que si es mismo día, esté dentro del horario permitido
                            const [limitHour, limitMin] = CHECKOUT_MISMO_DIA_LIMITE.split(':');
                            const limitTime = new Date(entrada);
                            limitTime.setHours(parseInt(limitHour), parseInt(limitMin), 0, 0);

                            const salidaConHora = new Date(salida);
                            salidaConHora.setHours(parseInt(limitHour), parseInt(limitMin), 0, 0);

                            if (salidaConHora > limitTime) {
                                errorMsg = 'Para estadías del mismo día, el check-out debe realizarse antes de las ' + CHECKOUT_MISMO_DIA_LIMITE + '.';
                            }
                        }
                    } else {
                        // Validación tradicional
                        if (salida <= entrada) {
                            errorMsg = 'La fecha de salida debe ser posterior a la de entrada.';
                        }
                    }
                }

                if (errorMsg) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en el check-in',
                        text: errorMsg
                    });
                }
            });

            // Mayúsculas en nombre del cliente principal
            const nombreCliente = document.getElementById('nombre_cliente');
            if (nombreCliente) {
                nombreCliente.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }
            // Mayúsculas en nombre del cliente del modal
            const nuevoNombre = document.getElementById('nuevo_nombre');
            if (nuevoNombre) {
                nuevoNombre.addEventListener('input', function(e) {
                    if (e.target && e.target.id === 'nuevo_nombre') {
                        console.log('Detectado input en #nuevo_nombre:', e.target.value);
                        e.target.value = e.target.value.toUpperCase();
                    }
                });
            }

            const inputBusqueda = document.getElementById('buscarCliente');
            const resultadosBusqueda = document.getElementById('resultadosBusqueda');

            inputBusqueda.addEventListener('input', async (e) => {
                const query = e.target.value;
                resultadosBusqueda.classList.add('show');
                resultadosBusqueda.style.display = 'block';
                resultadosBusqueda.style.position = 'absolute';
                resultadosBusqueda.style.zIndex = 1000;
                resultadosBusqueda.style.left = inputBusqueda.offsetLeft + 'px';
                resultadosBusqueda.style.top = (inputBusqueda.offsetTop + inputBusqueda.offsetHeight) +
                    'px';
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
                    resultadosBusqueda.innerHTML =
                        '<div class="dropdown-item text-danger">Error al buscar</div>';
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
                document.getElementById('nit').value = cliente.nit;
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

            // --- NUEVO: AJAX para crear cliente desde el modal ---
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOMContentLoaded ejecutado - buscando form-nuevo-cliente');
                const formNuevoCliente = document.getElementById('form-nuevo-cliente');
                console.log('Form encontrado:', formNuevoCliente);

                if (formNuevoCliente) {
                    console.log('Agregando event listener al form');
                    formNuevoCliente.addEventListener('submit', function(e) {
                        console.log('Submit del form detectado');
                        e.preventDefault();

                        const formData = new FormData(formNuevoCliente);
                        const data = {
                            nombre: formData.get('nombre'),
                            nit: formData.get('nit'),
                            dpi: formData.get('dpi'),
                            telefono: formData.get('telefono'),
                            _token: '{{ csrf_token() }}'
                        };

                        console.log('Datos del cliente:', data);

                        if (!data.nombre || !data.nit || !data.dpi || !data.telefono) {
                            alert('Todos los campos son obligatorios.');
                            return;
                        }

                        // Usar fetch en lugar de axios para simplificar
                        fetch('/api/clientes', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(data)
                            })
                            .then(response => response.json())
                            .then(cliente => {
                                console.log('Cliente creado:', cliente);
                                if (cliente.id) {
                                    // Llenar los campos automáticamente
                                    document.getElementById('nombre_cliente').value = cliente
                                        .nombre;
                                    document.getElementById('documento_identidad').value =
                                        cliente.dpi;
                                    document.getElementById('telefono').value = cliente
                                        .telefono;
                                    document.getElementById('nit').value = cliente.nit;

                                    // Cerrar modal
                                    $('#modalNuevoCliente').modal('hide');

                                    // Limpiar formulario
                                    formNuevoCliente.reset();

                                    alert('Cliente creado exitosamente');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error al crear el cliente');
                            });
                    });
                } else {
                    console.error('No se encontró el form-nuevo-cliente');
                }
            });

            $('#modalNuevoCliente').on('shown.bs.modal', function() {
                const nuevoNombre = document.getElementById('nuevo_nombre');
                if (nuevoNombre) {
                    nuevoNombre.oninput = function() {
                        this.value = this.value.toUpperCase();
                    };
                }
            });

            document.addEventListener('input', function(e) {
                if (e.target && e.target.id === 'nuevo_nombre') {
                    console.log('Detectado input en #nuevo_nombre:', e.target.value);
                    e.target.value = e.target.value.toUpperCase();
                }
            });
        }); // <-- Cierre de document.addEventListener('DOMContentLoaded', ...)
    </script>
@stop
