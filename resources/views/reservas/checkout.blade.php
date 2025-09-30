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

                        <p><strong>Total a Pagar:</strong> {{ $hotel->simbolo_moneda }}
                            <span class="h5 text-primary">
                                {{ number_format($reserva->total, 2) }}
                            </span>
                        </p>
                        <p><strong>Estado de Pago:</strong> 
                            <span class="text-warning">Pendiente de Pago</span>
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
                                <label>Método de Pago Principal</label>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="metodo_pago_principal" 
                                               id="metodo_efectivo" value="efectivo" checked>
                                        <label class="form-check-label" for="metodo_efectivo">
                                            <i class="fas fa-money-bill-wave text-success"></i> Efectivo
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="metodo_pago_principal" 
                                               id="metodo_tarjeta" value="tarjeta">
                                        <label class="form-check-label" for="metodo_tarjeta">
                                            <i class="fas fa-credit-card text-primary"></i> Tarjeta (+5% recargo)
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="metodo_pago_principal" 
                                               id="metodo_transferencia" value="transferencia">
                                        <label class="form-check-label" for="metodo_transferencia">
                                            <i class="fas fa-exchange-alt text-info"></i> Transferencia
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos específicos por método de pago -->
                            <div id="pago_efectivo_section" class="payment-section">
                                <div class="form-group">
                                    <label for="pago_efectivo">Monto Recibido en Efectivo</label>
                                    <input type="number" step="0.01" class="form-control" id="pago_efectivo"
                                        name="pago_efectivo" value="0" min="0">
                                    <small class="form-text text-muted">Puede ingresar un monto mayor para calcular el vuelto</small>
                                </div>
                                <div id="cambio_section" class="alert alert-success" style="display: none;">
                                    <i class="fas fa-hand-holding-usd"></i>
                                    <strong>Vuelto/Cambio a entregar: </strong>
                                    <span id="cambio_amount" class="h5">{{ $hotel->simbolo_moneda }}0.00</span>
                                </div>
                            </div>

                            <div id="pago_tarjeta_section" class="payment-section" style="display: none;">
                                <div class="form-group">
                                    <label for="pago_tarjeta">Monto Base (sin recargo)</label>
                                    <input type="number" step="0.01" class="form-control" id="pago_tarjeta"
                                        name="pago_tarjeta" value="0" min="0" readonly>
                                </div>
                                <div class="card border-warning">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-warning"><i class="fas fa-credit-card"></i> Desglose Pago con Tarjeta</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Monto base:</small><br>
                                                <span id="tarjeta_base">{{ $hotel->simbolo_moneda }}0.00</span>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Recargo (5%):</small><br>
                                                <span id="tarjeta_recargo" class="text-warning">{{ $hotel->simbolo_moneda }}0.00</span>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="text-center">
                                            <strong>Total con recargo: <span id="tarjeta_total" class="h6 text-warning">{{ $hotel->simbolo_moneda }}0.00</span></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="pago_transferencia_section" class="payment-section" style="display: none;">
                                <div class="form-group">
                                    <label for="pago_transferencia">Monto Transferido</label>
                                    <input type="number" step="0.01" class="form-control" id="pago_transferencia"
                                        name="pago_transferencia" value="0" min="0" readonly>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="transferencia_banco">Banco</label>
                                            <select class="form-control" id="transferencia_banco" name="transferencia_banco">
                                                <option value="">Seleccionar banco...</option>
                                                <option value="BAM">BAM</option>
                                                <option value="BANRURAL">Banrural</option>
                                                <option value="BANCO_INDUSTRIAL">Banco Industrial</option>
                                                <option value="BANTRAB">Bantrab</option>
                                                <option value="AGROMERCANTIL">Banco Agromercantil</option>
                                                <option value="OTRO">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="transferencia_referencia">Número de Referencia/Hilera</label>
                                            <input type="text" class="form-control" id="transferencia_referencia" 
                                                   name="transferencia_referencia" placeholder="Ej: 123456789">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <label for="monto_total">Total a Pagar</label>
                                <input type="number" step="0.01" class="form-control" id="monto_total"
                                    name="monto_total"
                                    value="{{ old('monto_total', $reserva->total) }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones"
                                    rows="3">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            
                            <div id="feedback_pago" class="mt-2"></div>
                            <input type="hidden" name="post_checkout_accion" id="post_checkout_accion" value="limpieza">
                            
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-out-alt"></i> Registrar Check-out
                            </button>
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
            // Variables globales
            const totalReserva = {{ $reserva->total }};
            const simboloMoneda = '{{ $hotel->simbolo_moneda }}';
            
            // Manejar cambios en método de pago
            function cambiarMetodoPago() {
                const metodo = document.querySelector('input[name="metodo_pago_principal"]:checked').value;
                
                // Ocultar todas las secciones
                document.querySelectorAll('.payment-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Resetear valores
                document.getElementById('pago_efectivo').value = '0';
                document.getElementById('pago_tarjeta').value = '0';
                document.getElementById('pago_transferencia').value = '0';
                
                // Mostrar sección correspondiente y asignar valor
                const descuento = parseFloat(document.getElementById('descuento_adicional').value) || 0;
                const totalPagar = totalReserva - descuento;
                
                if (metodo === 'efectivo') {
                    document.getElementById('pago_efectivo_section').style.display = 'block';
                    document.getElementById('pago_efectivo').value = totalPagar.toFixed(2);
                } else if (metodo === 'tarjeta') {
                    document.getElementById('pago_tarjeta_section').style.display = 'block';
                    document.getElementById('pago_tarjeta').value = totalPagar.toFixed(2);
                    calcularRecargoTarjeta(totalPagar);
                } else if (metodo === 'transferencia') {
                    document.getElementById('pago_transferencia_section').style.display = 'block';
                    document.getElementById('pago_transferencia').value = totalPagar.toFixed(2);
                }
                
                actualizarTotal();
            }
            
            // Calcular recargo de tarjeta
            function calcularRecargoTarjeta(montoBase) {
                const recargo = montoBase * 0.05;
                const totalConRecargo = montoBase + recargo;
                
                document.getElementById('tarjeta_base').textContent = simboloMoneda + montoBase.toFixed(2);
                document.getElementById('tarjeta_recargo').textContent = simboloMoneda + recargo.toFixed(2);
                document.getElementById('tarjeta_total').textContent = simboloMoneda + totalConRecargo.toFixed(2);
            }
            
            // Calcular vuelto en efectivo
            function calcularVuelto() {
                const metodo = document.querySelector('input[name="metodo_pago_principal"]:checked').value;
                if (metodo !== 'efectivo') return;
                
                const descuento = parseFloat(document.getElementById('descuento_adicional').value) || 0;
                const totalPagar = totalReserva - descuento;
                const montoRecibido = parseFloat(document.getElementById('pago_efectivo').value) || 0;
                const vuelto = montoRecibido - totalPagar;
                
                if (vuelto > 0) {
                    document.getElementById('cambio_amount').textContent = simboloMoneda + vuelto.toFixed(2);
                    document.getElementById('cambio_section').style.display = 'block';
                } else {
                    document.getElementById('cambio_section').style.display = 'none';
                }
            }
            
            function actualizarTotal() {
            const descuento = parseFloat(document.getElementById('descuento_adicional').value) || 0;
            const efectivo = parseFloat(document.getElementById('pago_efectivo').value) || 0;
            const tarjeta = parseFloat(document.getElementById('pago_tarjeta').value) || 0;
            const transferencia = parseFloat(document.getElementById('pago_transferencia').value) || 0;
            const totalPagar = totalReserva - adelanto - descuento;
            document.getElementById('monto_total').value = totalPagar.toFixed(2);
            
            // Calcular vuelto si es efectivo
            calcularVuelto();
            
            // Aplicar validaciones normales
                // Validación visual: suma de pagos debe ser igual al total a pagar
                const sumaPagos = efectivo + tarjeta + transferencia;
                const feedback = document.getElementById('feedback_pago');
                let msg = '';
                if (descuento > totalReserva) {
                    msg =
                        '<div class="alert alert-danger p-2">El descuento no puede ser mayor al total de la reserva.</div>';
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
        }
            // Event listeners para cambios de método de pago
            document.querySelectorAll('input[name="metodo_pago_principal"]').forEach(radio => {
                radio.addEventListener('change', cambiarMetodoPago);
            });
            
            // Event listeners para actualización de totales
            document.getElementById('descuento_adicional').addEventListener('input', function() {
                cambiarMetodoPago(); // Recalcular con nuevo descuento
            });
            document.getElementById('pago_efectivo').addEventListener('input', actualizarTotal);
            document.getElementById('pago_tarjeta').addEventListener('input', actualizarTotal);
            document.getElementById('pago_transferencia').addEventListener('input', actualizarTotal);
            
            // Inicializar
            cambiarMetodoPago();
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

            // Validación al enviar el formulario
            document.getElementById('checkout-form').addEventListener('submit', function(e) {
            
            // Para checkout normal, validaciones básicas de pago
            const descuento = parseFloat(document.getElementById('descuento_adicional').value) || 0;
            const efectivo = parseFloat(document.getElementById('pago_efectivo').value) || 0;
            const tarjeta = parseFloat(document.getElementById('pago_tarjeta').value) || 0;
            const transferencia = parseFloat(document.getElementById('pago_transferencia').value) || 0;
            const totalPagar = totalReserva - descuento;
            const metodoPago = document.querySelector('input[name="metodo_pago_principal"]:checked').value;
            let montoAPagar = 0;
            
            // Validaciones específicas por método de pago
            if (metodoPago === 'efectivo') {
                montoAPagar = efectivo;
                if (efectivo < totalPagar) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Monto insuficiente',
                        text: 'El monto recibido en efectivo (' + simboloMoneda + efectivo.toFixed(2) + ') es menor al total a pagar (' + simboloMoneda + totalPagar.toFixed(2) + ').',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                }
            } else if (metodoPago === 'tarjeta') {
                const recargoTarjeta = tarjeta * 0.05;
                montoAPagar = tarjeta + recargoTarjeta;
                if (Math.abs(tarjeta - totalPagar) > 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en pago con tarjeta',
                        text: 'El monto base de la tarjeta debe ser igual al total a pagar.',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                }
            } else if (metodoPago === 'transferencia') {
                montoAPagar = transferencia;
                
                // Validar que se haya seleccionado un banco
                const banco = document.getElementById('transferencia_banco').value;
                if (!banco) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Banco requerido',
                        text: 'Debe seleccionar el banco de la transferencia.',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                }
                
                // Validar referencia
                const referencia = document.getElementById('transferencia_referencia').value.trim();
                if (!referencia) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Referencia requerida',
                        text: 'Debe ingresar el número de referencia o hilera de la transferencia.',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                }
                
                if (Math.abs(transferencia - totalPagar) > 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en transferencia',
                        text: 'El monto transferido debe ser igual al total a pagar.',
                        confirmButtonColor: '#3085d6'
                    });
                    e.preventDefault();
                    return;
                }
            }
            
            // Establecer acción por defecto
            document.getElementById('post_checkout_accion').value = 'limpieza';
            // Permitir que el formulario se envíe normalmente
        });
    });
    </script>
@stop
