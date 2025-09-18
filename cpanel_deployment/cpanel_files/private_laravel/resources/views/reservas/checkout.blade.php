@extends('adminlte::page')

@section('title', 'Check-out')

@section('content_header')
    <h1>Check-out de Reserva</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Datos de la Reserva/Habitación -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Datos de la Reserva/Habitación</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Número:</strong> {{ $reserva->habitacion->numero }}</p>
                        <p><strong>Tipo:</strong> {{ $reserva->habitacion->tipo }}</p>
                        <p><strong>Estado:</strong> {{ $reserva->habitacion->estado }}</p>
                        <p><strong>Fecha de Entrada:</strong> {{ $reserva->fecha_entrada->format('d, M Y H:i') }}</p>
                        <p><strong>Fecha de Salida:</strong> {{ $reserva->fecha_salida->format('d, M Y H:i') }}</p>
                        <p><strong>Total a Pagar:</strong>
                            {{ $hotel->simbolo_moneda }}{{ number_format($reserva->total, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Datos del Huésped -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Datos del Huésped</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> {{ $reserva->cliente->nombre }}</p>
                        <p><strong>Documento:</strong> {{ $reserva->cliente->dpi }}</p>
                        <p><strong>Teléfono:</strong> {{ $reserva->cliente->telefono }}</p>
                        <p><strong>Observaciones:</strong> {{ $reserva->observaciones }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Resumen de la Cuenta -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>Resumen de la Cuenta</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Estancia:</strong> {{ $hotel->simbolo_moneda }} {{ number_format($reserva->total, 2) }}
                        </p>
                        <p><strong>Consumos Adicionales:</strong> {{ $hotel->simbolo_moneda }}{{ number_format(0, 2) }}
                        </p>
                        <p><strong>Descuentos:</strong> {{ $hotel->simbolo_moneda }}{{ number_format(0, 2) }}</p>
                        <p><strong>Anticipo registrado:</strong>
                            {{ $hotel->simbolo_moneda }}{{ number_format($reserva->adelanto, 2) }}</p>

                        <p><strong>Total a Pagar:</strong> {{ $hotel->simbolo_moneda }}
                            <span class="{{ $saldoPendiente < 0 ? 'text-danger font-weight-bold' : '' }}">
                                {{ number_format($saldoPendiente, 2) }}
                            </span>
                        </p>
                        @if($saldoPendiente < 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>¡SALDO NEGATIVO!</strong><br>
                                El anticipo ({{ $hotel->simbolo_moneda }}{{ number_format($reserva->adelanto, 2) }}) 
                                excede el total de la reserva ({{ $hotel->simbolo_moneda }}{{ number_format($reserva->total, 2) }}).
                                <br><strong>Se requiere checkout forzado por administrador.</strong>
                            </div>
                        @endif
                        <p><strong>Estado de Pago:</strong> 
                            @if($saldoPendiente < 0)
                                <span class="text-danger">Saldo Negativo</span>
                            @elseif($saldoPendiente == 0)
                                <span class="text-success">Pagado</span>
                            @else
                                Pendiente
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Opciones de Pago -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5>Opciones de Pago</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkout-form" action="{{ route('reservas.checkout.store', $reserva) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="descuento_adicional">Descuento Adicional</label>
                                <input type="number" step="0.01" class="form-control" id="descuento_adicional"
                                    name="descuento_adicional" value="0" min="0">
                            </div>
                            <div class="form-group">
                                <label>Métodos de Pago</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="pago_efectivo">Efectivo</label>
                                        <input type="number" step="0.01" class="form-control" id="pago_efectivo"
                                            name="pago_efectivo" value="0" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="pago_tarjeta">Tarjeta</label>
                                        <input type="number" step="0.01" class="form-control" id="pago_tarjeta"
                                            name="pago_tarjeta" value="0" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="pago_transferencia">Transferencia</label>
                                        <input type="number" step="0.01" class="form-control" id="pago_transferencia"
                                            name="pago_transferencia" value="0" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <label for="monto_total">Total a Pagar</label>
                                <input type="number" step="0.01" class="form-control" id="monto_total"
                                    name="monto_total"
                                    value="{{ old('monto_total', $reserva->total - $reserva->adelanto) }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones"
                                    rows="3">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($saldoPendiente < 0)
                                @if($esAdmin)
                                    <div class="alert alert-warning">
                                        <i class="fas fa-shield-alt"></i>
                                        <strong>Checkout Forzado - Solo Administrador</strong><br>
                                        Este checkout tiene saldo negativo y requiere autorización administrativa.
                                    </div>
                                    <div class="form-group">
                                        <label for="justificacion_checkout_forzado">Justificación del Checkout Forzado *</label>
                                        <textarea class="form-control @error('justificacion_checkout_forzado') is-invalid @enderror" 
                                                  id="justificacion_checkout_forzado" name="justificacion_checkout_forzado" 
                                                  rows="3" required placeholder="Explique por qué se autoriza este checkout con saldo negativo...">{{ old('justificacion_checkout_forzado') }}</textarea>
                                        @error('justificacion_checkout_forzado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input @error('autorizacion_checkout_forzado') is-invalid @enderror" 
                                               type="checkbox" id="autorizacion_checkout_forzado" name="autorizacion_checkout_forzado" 
                                               value="1" {{ old('autorizacion_checkout_forzado') ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="autorizacion_checkout_forzado">
                                            <strong>Autorizo explícitamente este checkout forzado con saldo negativo</strong>
                                        </label>
                                        @error('autorizacion_checkout_forzado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <input type="hidden" name="checkout_forzado" value="1">
                                @else
                                    <div class="alert alert-danger">
                                        <i class="fas fa-lock"></i>
                                        <strong>Acceso Denegado</strong><br>
                                        Solo un administrador puede procesar un checkout con saldo negativo.
                                        Contacte a un administrador para continuar.
                                    </div>
                                @endif
                            @endif
                            
                            <div id="feedback_pago" class="mt-2"></div>
                            <input type="hidden" name="post_checkout_accion" id="post_checkout_accion" value="limpieza">
                            
                            @if($saldoPendiente < 0 && !$esAdmin)
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="fas fa-lock"></i> Checkout Bloqueado
                                </button>
                            @else
                                <button type="submit" class="btn {{ $saldoPendiente < 0 ? 'btn-warning' : 'btn-success' }}">
                                    @if($saldoPendiente < 0)
                                        <i class="fas fa-exclamation-triangle"></i> Checkout Forzado
                                    @else
                                        Registrar Check-out
                                    @endif
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function actualizarTotal() {
            const totalReserva = {{ $reserva->total }};
            const adelanto = {{ $reserva->adelanto }};
            const descuento = parseFloat(document.getElementById('descuento_adicional').value) || 0;
            const efectivo = parseFloat(document.getElementById('pago_efectivo').value) || 0;
            const tarjeta = parseFloat(document.getElementById('pago_tarjeta').value) || 0;
            const transferencia = parseFloat(document.getElementById('pago_transferencia').value) || 0;
            const totalPagar = totalReserva - adelanto - descuento;
            document.getElementById('monto_total').value = totalPagar.toFixed(2);
            
            // Verificar si es checkout forzado
            const saldoNegativo = {{ $saldoPendiente }} < 0;
            const esCheckoutForzado = document.querySelector('input[name="checkout_forzado"]');
            
            // Solo aplicar validaciones si NO es checkout forzado
            if (!saldoNegativo || !esCheckoutForzado) {
                // Validación visual: suma de pagos debe ser igual al total a pagar
                const sumaPagos = efectivo + tarjeta + transferencia;
                const feedback = document.getElementById('feedback_pago');
                let msg = '';
                if (descuento > (totalReserva - adelanto)) {
                    msg =
                        '<div class="alert alert-danger p-2">El descuento no puede ser mayor al total menos el adelanto.</div>';
                    document.getElementById('descuento_adicional').classList.add('is-invalid');
                } else {
                    document.getElementById('descuento_adicional').classList.remove('is-invalid');
                }
                if (Math.abs(sumaPagos - totalPagar) > 0.01) {
                    msg += '<div class="alert alert-warning p-2">La suma de los pagos (' + sumaPagos.toFixed(2) +
                        ') no coincide con el total a pagar (' + totalPagar.toFixed(2) + ').</div>';
                    document.getElementById('monto_total').style.backgroundColor = '#f8d7da';
                } else if (!msg) {
                    document.getElementById('monto_total').style.backgroundColor = '#d4edda';
                } else {
                    document.getElementById('monto_total').style.backgroundColor = '';
                }
                feedback.innerHTML = msg;
            } else {
                // Para checkout forzado, limpiar validaciones visuales
                const feedback = document.getElementById('feedback_pago');
                feedback.innerHTML = '';
                document.getElementById('descuento_adicional').classList.remove('is-invalid');
                document.getElementById('monto_total').style.backgroundColor = '';
            }
        }
            document.getElementById('descuento_adicional').addEventListener('input', actualizarTotal);
            document.getElementById('pago_efectivo').addEventListener('input', actualizarTotal);
            document.getElementById('pago_tarjeta').addEventListener('input', actualizarTotal);
            document.getElementById('pago_transferencia').addEventListener('input', actualizarTotal);
            actualizarTotal();

            // Función para verificar si SweetAlert2 está disponible
            function waitForSwal() {
                return new Promise((resolve) => {
                    if (typeof Swal !== 'undefined') {
                        resolve();
                    } else {
                        setTimeout(() => waitForSwal().then(resolve), 100);
                    }
                });
            }

            // Validación simplificada al enviar el formulario
            document.getElementById('checkout-form').addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            
            // Verificar si es checkout forzado
            const checkoutForzadoInput = document.querySelector('input[name="checkout_forzado"]');
            const esCheckoutForzado = checkoutForzadoInput && checkoutForzadoInput.value === '1';
            const saldoNegativo = {{ $saldoPendiente }} < 0;
            
            console.log('Debug checkout:');
            console.log('checkoutForzadoInput:', checkoutForzadoInput);
            console.log('esCheckoutForzado:', esCheckoutForzado);
            console.log('saldoNegativo:', saldoNegativo);
            console.log('saldoPendiente:', {{ $saldoPendiente }});
            
            // Para checkout forzado, validaciones básicas
            if (saldoNegativo && esCheckoutForzado) {
                const justificacion = document.getElementById('justificacion_checkout_forzado');
                const autorizacion = document.getElementById('autorizacion_checkout_forzado');
                
                if (!justificacion || !justificacion.value.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Justificación requerida',
                        text: 'Debe proporcionar una justificación para el checkout forzado.',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                } else if (!autorizacion || !autorizacion.checked) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Autorización requerida',
                        text: 'Debe autorizar explícitamente el checkout forzado.',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                }
                
                // Confirmar checkout forzado
                e.preventDefault();
                Swal.fire({
                    title: '¿Confirmar checkout forzado?',
                    text: '¿Está seguro de realizar un checkout con saldo negativo?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, proceder',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Checkout forzado confirmado, enviando formulario...');
                        // Para checkout forzado, establecer acción por defecto
                        document.getElementById('post_checkout_accion').value = 'limpieza';
                        
                        // Crear un nuevo formulario con todos los datos necesarios
                        const originalForm = document.getElementById('checkout-form');
                        const newForm = document.createElement('form');
                        newForm.method = 'POST';
                        newForm.action = originalForm.action;
                        
                        // Agregar token CSRF
                        const csrfToken = document.querySelector('input[name="_token"]').value;
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        newForm.appendChild(csrfInput);
                        
                        // Agregar todos los campos del formulario original
                        const inputs = originalForm.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            if (input.name && input.name !== '_token') {
                                const newInput = document.createElement('input');
                                newInput.type = 'hidden';
                                newInput.name = input.name;
                                if (input.type === 'checkbox') {
                                    newInput.value = input.checked ? input.value : '';
                                } else {
                                    newInput.value = input.value;
                                }
                                newForm.appendChild(newInput);
                                console.log('Campo agregado: ' + input.name + ' = ' + newInput.value);
                            }
                        });
                        
                        document.body.appendChild(newForm);
                        console.log('Enviando formulario...');
                        newForm.submit();
                    }
                });
                return;
            }
            
            // Para checkout normal, validaciones básicas de pago
            const descuento = parseFloat(document.getElementById('descuento_adicional').value) || 0;
            const efectivo = parseFloat(document.getElementById('pago_efectivo').value) || 0;
            const tarjeta = parseFloat(document.getElementById('pago_tarjeta').value) || 0;
            const transferencia = parseFloat(document.getElementById('pago_transferencia').value) || 0;
            const totalReserva = {{ $reserva->total }};
            const adelanto = {{ $reserva->adelanto }};
            const totalPagar = totalReserva - adelanto - descuento;
            const sumaPagos = efectivo + tarjeta + transferencia;
            
            if (Math.abs(sumaPagos - totalPagar) > 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en los pagos',
                    text: 'La suma de los pagos (' + sumaPagos.toFixed(2) + ') no coincide con el total a pagar (' + totalPagar.toFixed(2) + ').',
                    confirmButtonColor: '#3085d6'
                });
                e.preventDefault();
                return;
            }
            
            // Para checkout normal, establecer acción por defecto
            document.getElementById('post_checkout_accion').value = 'limpieza';
            // Permitir que el formulario se envíe normalmente
        });
    });
    </script>
@stop
