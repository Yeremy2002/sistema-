@extends('adminlte::page')

@section('title', 'Cerrar Caja')

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <h1>Cerrar Caja</h1>
@stop

@section('content')
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('success') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: '{{ session('warning') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('info'))
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: '{{ session('info') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de Cierre</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('cajas.update', $caja) }}" method="POST" id="formCierreCaja">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="saldo_final">Saldo Final Real (Efectivo en Caja)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $hotel->simbolo_moneda }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="saldo_final"
                                    name="saldo_final" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Diferencia</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $hotel->simbolo_moneda }}</span>
                                </div>
                                <input type="text" class="form-control" id="diferencia" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones_cierre">Observaciones</label>
                            <textarea class="form-control" id="observaciones_cierre" name="observaciones_cierre" rows="3"></textarea>
                        </div>

                        @if (isset($esAdmin) && $esAdmin)
                            <div class="form-group">
                                <label for="justificacion_admin" class="text-danger">*Justificación Administrativa</label>
                                <textarea class="form-control border-warning" id="justificacion_admin" name="justificacion_admin" rows="3"
                                    placeholder="Como administrador, debe justificar el cierre de esta caja..." required></textarea>
                                <small class="form-text text-muted">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    Este campo es obligatorio para administradores.
                                </small>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">Cerrar Caja</button>
                        <button type="button" class="btn btn-info" id="btnImprimir">
                            <i class="fas fa-print"></i> Imprimir Ticket
                        </button>
                        <a href="{{ route('cajas.show', $caja) }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Vista Previa del Reporte</h3>
                </div>
                <div class="card-body">
                    <div id="ticket-preview"
                        style="font-family: 'Courier New', monospace; font-size: 12px; width: 300px; margin: 0 auto;">
                        <div style="text-align: center; margin-bottom: 10px;">
                            <h4>{{ $hotel->nombre }}</h4>
                            <p>{{ $hotel->direccion }}<br>
                                Tel: {{ $hotel->telefono }}</p>
                            <p>================================</p>
                            <h5>CIERRE DE CAJA</h5>
                            <p>================================</p>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <p>Fecha: {{ now()->format('d/m/Y') }}<br>
                                Hora: {{ now()->format('H:i:s') }}<br>
                                Cajero: {{ $caja->user->name }}<br>
                                Turno: {{ ucfirst($caja->turno) }}</p>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <p>================================</p>
                            <p>RESUMEN DE OPERACIONES</p>
                            <p>================================</p>
                            <table style="width: 100%;">
                                <tr>
                                    <td>Saldo Inicial:</td>
                                    <td style="text-align: right;">
                                        {{ $hotel->simbolo_moneda }}{{ number_format($caja->saldo_inicial, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>+ Ingresos:</td>
                                    <td style="text-align: right;">
                                        {{ $hotel->simbolo_moneda }}{{ number_format($caja->total_ingresos, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>- Egresos:</td>
                                    <td style="text-align: right;">
                                        {{ $hotel->simbolo_moneda }}{{ number_format($caja->total_egresos, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>= Saldo Final Esperado:</td>
                                    <td style="text-align: right;">
                                        {{ $hotel->simbolo_moneda }}{{ number_format($caja->saldo_actual, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Saldo Final Real:</td>
                                    <td style="text-align: right;" id="saldo-final-ticket">{{ $hotel->simbolo_moneda }}0.00
                                    </td>
                                </tr>
                                <tr>
                                    <td>Diferencia:</td>
                                    <td style="text-align: right;" id="diferencia-ticket">{{ $hotel->simbolo_moneda }}0.00
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <p>================================</p>
                            <p>DESGLOSE DE MOVIMIENTOS</p>
                            <p>================================</p>
                            <p>INGRESOS POR TIPO</p>
                            @php
                                $ingresosPorTipo = $caja
                                    ->movimientos()
                                    ->where('tipo', 'ingreso')
                                    ->selectRaw('concepto, SUM(monto) as total')
                                    ->groupBy('concepto')
                                    ->get();
                            @endphp
                            @foreach ($ingresosPorTipo as $ingreso)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <div class="concepto-multilinea">{{ $ingreso->concepto }}</div>
                                    <span>{{ $hotel->simbolo_moneda }}{{ number_format($ingreso->total, 2) }}</span>
                                </div>
                            @endforeach

                            <p>--------------------------------</p>
                            <p>EGRESOS POR TIPO</p>
                            @php
                                $egresosPorTipo = $caja
                                    ->movimientos()
                                    ->where('tipo', 'egreso')
                                    ->selectRaw('concepto, SUM(monto) as total')
                                    ->groupBy('concepto')
                                    ->get();
                            @endphp
                            @foreach ($egresosPorTipo as $egreso)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <div class="concepto-multilinea">{{ $egreso->concepto }}</div>
                                    <span>{{ $hotel->simbolo_moneda }}{{ number_format($egreso->total, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div style="text-align: center; margin-top: 20px;">
                            <p>================================</p>
                            <p>Firma del Cajero</p>
                            <br>
                            <p>_______________________</p>
                            <p>{{ $caja->user->name }}</p>
                            <br>
                            <p>Firma del Supervisor</p>
                            <br>
                            <p>_______________________</p>
                            <p>Nombre y Firma</p>
                            <p>================================</p>
                            <p>{{ $hotel->nombre }}<br>
                                Gracias por su trabajo</p>
                            <p>================================</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        #ticket-preview {
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .concepto-multilinea {
            word-wrap: break-word;
            white-space: pre-wrap;
            max-width: 200px;
            font-size: 11px;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #ticket-preview,
            #ticket-preview * {
                visibility: visible;
            }

            #ticket-preview {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                /* Ancho estándar para tickets */
                box-shadow: none;
            }

            .concepto-multilinea {
                font-size: 10px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            function calcularDiferencia() {
                let saldoEsperado = {{ $caja->saldo_actual }};
                let saldoReal = parseFloat($('#saldo_final').val()) || 0;
                let diferencia = saldoReal - saldoEsperado;

                $('#diferencia').val(diferencia.toFixed(2));

                // Actualizar el ticket
                $('#saldo-final-ticket').text('{{ $hotel->simbolo_moneda }}' + saldoReal.toFixed(2));
                $('#diferencia-ticket').text('{{ $hotel->simbolo_moneda }}' + diferencia.toFixed(2));

                if (diferencia > 0) {
                    $('#diferencia, #diferencia-ticket').addClass('text-success').removeClass('text-danger');
                } else if (diferencia < 0) {
                    $('#diferencia, #diferencia-ticket').addClass('text-danger').removeClass('text-success');
                } else {
                    $('#diferencia, #diferencia-ticket').removeClass('text-success text-danger');
                }
            }

            // Actualizar el cálculo en tiempo real al escribir
            $('#saldo_final').on('input', function() {
                calcularDiferencia();
            });

            // Formatear el número cuando el usuario termine de escribir
            $('#saldo_final').on('blur', function() {
                let value = $(this).val();
                if (value) {
                    const parsedValue = parseFloat(value);
                    if (!isNaN(parsedValue)) {
                        $(this).val(parsedValue.toFixed(2));
                    }
                }
            });

            // Función para imprimir el ticket (disponible globalmente)
            window.imprimirTicket = function() {
                return new Promise((resolve) => {
                    const printWindow = window.open('', '_blank');
                    const ticketContent = document.getElementById('ticket-preview').innerHTML;

                    printWindow.document.write(`
                        <html>
                            <head>
                                <style>
                                    body {
                                        font-family: 'Courier New', monospace;
                                        font-size: 12px;
                                        width: 80mm;
                                        margin: 0 auto;
                                    }
                                    .concepto-multilinea {
                                        word-wrap: break-word;
                                        white-space: pre-wrap;
                                        max-width: 200px;
                                        font-size: 10px;
                                    }
                                </style>
                            </head>
                            <body>
                                ${ticketContent}
                            </body>
                        </html>
                    `);

                    printWindow.document.close();
                    printWindow.focus();

                    printWindow.onafterprint = function() {
                        printWindow.close();
                        resolve();
                    };

                    printWindow.print();

                    // Fallback por si onafterprint no se dispara
                    setTimeout(() => {
                        if (!printWindow.closed) {
                            printWindow.close();
                        }
                        resolve();
                    }, 1000);
                });
            }

            // Configurar los manejadores de eventos
            function setupEventHandlers() {
                $('#formCierreCaja').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const form = this;

                    Swal.fire({
                        title: '¿Está seguro?',
                        text: "Va a cerrar la caja. Esta acción no se puede revertir.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, cerrar caja',
                        cancelButtonText: 'Cancelar'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                // Mostrar loading
                                Swal.fire({
                                    title: 'Procesando...',
                                    text: 'Cerrando caja, por favor espere',
                                    allowOutsideClick: false,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Imprimir ticket
                                await imprimirTicket();

                                // Obtener el token CSRF de múltiples fuentes para mayor seguridad
                                let csrfToken = document.querySelector(
                                    'meta[name="csrf-token"]')?.content;
                                if (!csrfToken) {
                                    csrfToken = document.querySelector('input[name="_token"]')
                                        ?.value;
                                }
                                if (!csrfToken) {
                                    csrfToken = '{{ csrf_token() }}';
                                }

                                console.log('Token CSRF obtenido:', csrfToken ?
                                    'Sí (longitud: ' + csrfToken.length + ')' : 'No');
                                console.log('Fuente del token:', document.querySelector(
                                        'meta[name="csrf-token"]') ? 'meta tag' :
                                    document.querySelector('input[name="_token"]') ?
                                    'input hidden' :
                                    'blade directive');

                                if (!csrfToken || csrfToken.length < 10) {
                                    throw new Error(
                                        'No se pudo obtener un token CSRF válido. Por favor, recargue la página.'
                                        );
                                }

                                // Enviar formulario por AJAX
                                const formData = new FormData(form);

                                // Asegurar que el token CSRF esté incluido
                                formData.set('_token', csrfToken);
                                formData.set('_method', 'PUT');

                                console.log('Enviando datos con token CSRF:', csrfToken
                                    .substring(0, 10) + '...');
                                console.log('URL de destino:', form.action);
                                console.log('Método HTTP:', 'POST con _method=PUT');

                                const response = await fetch(form.action, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': csrfToken
                                    }
                                });

                                console.log('Respuesta del servidor:', response.status, response
                                    .statusText);

                                console.log('Respuesta del servidor:', response.status, response
                                    .statusText);

                                if (response.ok) {
                                    // Éxito - parsear respuesta JSON
                                    const data = await response.json();
                                    console.log('Datos de respuesta:', data);
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Caja cerrada!',
                                        text: data.message ||
                                            'La caja se ha cerrado exitosamente',
                                        confirmButtonText: 'Continuar'
                                    }).then(() => {
                                        window.location.href = data.redirect ||
                                            '{{ route('cajas.index') }}';
                                    });
                                } else if (response.status === 419) {
                                    // Token CSRF expirado - intentar obtener uno nuevo
                                    console.log('Token CSRF expirado, intentando refrescar...');

                                    try {
                                        const tokenResponse = await fetch(window.location
                                        .href, {
                                            method: 'GET',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        });

                                        if (tokenResponse.ok) {
                                            const html = await tokenResponse.text();
                                            const parser = new DOMParser();
                                            const doc = parser.parseFromString(html,
                                                'text/html');
                                            const newToken = doc.querySelector(
                                                'meta[name="csrf-token"]')?.content;

                                            if (newToken) {
                                                console.log(
                                                    'Nuevo token CSRF obtenido, reintentando...'
                                                    );
                                                document.querySelector(
                                                        'meta[name="csrf-token"]').content =
                                                    newToken;
                                                formData.set('_token', newToken);

                                                // Reintentar la solicitud con el nuevo token
                                                const retryResponse = await fetch(form.action, {
                                                    method: 'POST',
                                                    body: formData,
                                                    headers: {
                                                        'X-Requested-With': 'XMLHttpRequest',
                                                        'X-CSRF-TOKEN': newToken
                                                    }
                                                });

                                                if (retryResponse.ok) {
                                                    const data = await retryResponse.json();
                                                    console.log('Éxito con token refrescado:',
                                                        data);
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: '¡Caja cerrada!',
                                                        text: data.message ||
                                                            'La caja se ha cerrado exitosamente',
                                                        confirmButtonText: 'Continuar'
                                                    }).then(() => {
                                                        window.location.href = data
                                                            .redirect ||
                                                            '{{ route('cajas.index') }}';
                                                    });
                                                    return; // Salir exitosamente
                                                }
                                            }
                                        }
                                    } catch (tokenError) {
                                        console.error('Error al refrescar token:', tokenError);
                                    }

                                    throw new Error(
                                        'Token de seguridad expirado. Por favor, recargue la página y vuelva a intentar.'
                                        );
                                } else {
                                    // Error del servidor - intentar parsear JSON de error
                                    let errorMessage =
                                        `Error ${response.status}: ${response.statusText}`;
                                    try {
                                        const errorData = await response.json();
                                        console.error('Error del servidor:', errorData);

                                        if (response.status === 422) {
                                            // Error de validación
                                            if (errorData.errors) {
                                                const errors = Object.values(errorData.errors)
                                                    .flat();
                                                errorMessage = 'Error de validación: ' + errors
                                                    .join(', ');
                                            } else {
                                                errorMessage = errorData.message ||
                                                    'Error de validación en los datos enviados.';
                                            }
                                        } else {
                                            errorMessage = errorData.message || errorMessage;
                                        }
                                    } catch (e) {
                                        console.error('Error parseando respuesta JSON:', e);
                                        // Si no es JSON, usar el mensaje de error estándar
                                    }
                                    throw new Error(errorMessage);
                                }
                            } catch (error) {
                                console.error('Error al cerrar caja:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Hubo un problema al cerrar la caja: ' + error
                                        .message,
                                    confirmButtonText: 'Entendido'
                                });
                            }
                        }
                    });
                });

                $('#btnImprimir').off('click').on('click', function() {
                    imprimirTicket();
                });
            }

            // Inicializar
            calcularDiferencia();
            setupEventHandlers();
        });
    </script>
@stop
