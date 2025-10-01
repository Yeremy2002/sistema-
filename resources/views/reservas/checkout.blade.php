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
                            
                            <!-- Total a Pagar - Destacado -->
                            <div class="alert alert-info text-center mb-4">
                                <h5 class="mb-2">Total a Pagar</h5>
                                <h3 class="mb-0">
                                    <strong>{{ $hotel->simbolo_moneda }}{{ number_format($reserva->total, 2) }}</strong>
                                </h3>
                            </div>

                            <!-- Método de Pago -->
                            <div class="form-group">
                                <label><strong>Forma de Pago</strong></label>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo_pago_principal" 
                                               id="metodo_efectivo" value="efectivo" checked>
                                        <label class="form-check-label" for="metodo_efectivo">
                                            <i class="fas fa-money-bill-wave text-success"></i> <strong>Efectivo</strong>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo_pago_principal" 
                                               id="metodo_tarjeta" value="tarjeta">
                                        <label class="form-check-label" for="metodo_tarjeta">
                                            <i class="fas fa-credit-card text-primary"></i> <strong>Tarjeta</strong> <span class="badge badge-warning">+5% recargo</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo_pago_principal" 
                                               id="metodo_transferencia" value="transferencia">
                                        <label class="form-check-label" for="metodo_transferencia">
                                            <i class="fas fa-exchange-alt text-info"></i> <strong>Transferencia</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de Pago en Efectivo -->
                            <div id="pago_efectivo_section" class="payment-section">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title text-success"><i class="fas fa-money-bill-wave"></i> Pago en Efectivo</h6>
                                        <div class="form-group">
                                            <label for="pago_efectivo">Monto Recibido</label>
                                            <input type="number" step="0.01" class="form-control" id="pago_efectivo"
                                                name="pago_efectivo" value="{{ number_format($reserva->total, 2, '.', '') }}" min="0">
                                            <small class="form-text text-muted">Puede ingresar un monto mayor para calcular el vuelto</small>
                                        </div>
                                        <div id="cambio_section" class="alert alert-success" style="display: none;">
                                            <i class="fas fa-hand-holding-usd"></i>
                                            <strong>Vuelto/Cambio a entregar: </strong>
                                            <span id="cambio_amount" class="h5">{{ $hotel->simbolo_moneda }}0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de Pago con Tarjeta -->
                            <div id="pago_tarjeta_section" class="payment-section" style="display: none;">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary"><i class="fas fa-credit-card"></i> Pago con Tarjeta</h6>
                                        <div class="form-group">
                                            <label for="pago_tarjeta">Monto Recibido</label>
                                            <input type="number" step="0.01" class="form-control" id="pago_tarjeta"
                                                name="pago_tarjeta" value="{{ number_format($reserva->total, 2, '.', '') }}" min="0">
                                            <small class="form-text text-muted">Monto base sin incluir el recargo</small>
                                        </div>
                                        
                                        <!-- Desglose del recargo -->
                                        <div class="alert alert-warning">
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
                                        
                                        <div class="form-group">
                                            <label for="tarjeta_voucher">Número de Boleta/Voucher</label>
                                            <input type="text" class="form-control" id="tarjeta_voucher"
                                                name="tarjeta_voucher" placeholder="Ingrese el número de voucher">
                                            <small class="form-text text-muted">Opcional - Para verificación en cierre de caja</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de Pago por Transferencia -->
                            <div id="pago_transferencia_section" class="payment-section" style="display: none;">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-title text-info"><i class="fas fa-exchange-alt"></i> Pago por Transferencia</h6>
                                        <div class="form-group">
                                            <label for="pago_transferencia">Monto Recibido</label>
                                            <input type="number" step="0.01" class="form-control" id="pago_transferencia"
                                                name="pago_transferencia" value="{{ number_format($reserva->total, 2, '.', '') }}" min="0">
                                            <small class="form-text text-muted">Monto transferido que debe coincidir con el total</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="transferencia_banco_nombre">Nombre del Banco</label>
                                            <input type="text" class="form-control" id="transferencia_banco_nombre"
                                                name="transferencia_banco_nombre" placeholder="Ej: Banco Industrial">
                                            <small class="form-text text-muted">Opcional - Para verificación en cierre de caja</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="transferencia_numero">Número de Transferencia</label>
                                            <input type="text" class="form-control" id="transferencia_numero" 
                                                   name="transferencia_numero" placeholder="Ej: 123456789">
                                            <small class="form-text text-muted">Opcional - Para verificación en cierre de caja</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="monto_total" id="monto_total" value="{{ $reserva->total }}">
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
                
                // Mostrar sección correspondiente
                if (metodo === 'efectivo') {
                    document.getElementById('pago_efectivo_section').style.display = 'block';
                    calcularVuelto();
                } else if (metodo === 'tarjeta') {
                    document.getElementById('pago_tarjeta_section').style.display = 'block';
                    const montoTarjeta = parseFloat(document.getElementById('pago_tarjeta').value) || totalReserva;
                    calcularRecargoTarjeta(montoTarjeta);
                } else if (metodo === 'transferencia') {
                    document.getElementById('pago_transferencia_section').style.display = 'block';
                }
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
                
                const montoRecibido = parseFloat(document.getElementById('pago_efectivo').value) || 0;
                const vuelto = montoRecibido - totalReserva;
                
                if (vuelto > 0) {
                    document.getElementById('cambio_amount').textContent = simboloMoneda + vuelto.toFixed(2);
                    document.getElementById('cambio_section').style.display = 'block';
                } else {
                    document.getElementById('cambio_section').style.display = 'none';
                }
            }
            
            // Event listeners para cambios de método de pago
            document.querySelectorAll('input[name="metodo_pago_principal"]').forEach(radio => {
                radio.addEventListener('change', cambiarMetodoPago);
            });
            
            // Event listeners para actualización de cálculos
            document.getElementById('pago_efectivo').addEventListener('input', calcularVuelto);
            document.getElementById('pago_tarjeta').addEventListener('input', function() {
                const montoTarjeta = parseFloat(this.value) || 0;
                calcularRecargoTarjeta(montoTarjeta);
            });
            
            // Inicializar
            cambiarMetodoPago();

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
                const metodoPago = document.querySelector('input[name="metodo_pago_principal"]:checked').value;
                
                // Validaciones básicas por método de pago
                if (metodoPago === 'efectivo') {
                    const efectivo = parseFloat(document.getElementById('pago_efectivo').value) || 0;
                    if (efectivo < totalReserva) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Monto insuficiente',
                            text: 'El monto recibido en efectivo (' + simboloMoneda + efectivo.toFixed(2) + ') es menor al total a pagar (' + simboloMoneda + totalReserva.toFixed(2) + ').',
                            confirmButtonColor: '#3085d6'
                        });
                        e.preventDefault();
                        return;
                    }
                } else if (metodoPago === 'tarjeta') {
                    const tarjeta = parseFloat(document.getElementById('pago_tarjeta').value) || 0;
                    if (tarjeta <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Monto requerido',
                            text: 'Debe ingresar un monto válido para el pago con tarjeta.',
                            confirmButtonColor: '#3085d6'
                        });
                        e.preventDefault();
                        return;
                    }
                } else if (metodoPago === 'transferencia') {
                    const transferencia = parseFloat(document.getElementById('pago_transferencia').value) || 0;
                    if (transferencia <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Monto requerido',
                            text: 'Debe ingresar un monto válido para la transferencia.',
                            confirmButtonColor: '#3085d6'
                        });
                        e.preventDefault();
                        return;
                    }
                }
                
                // Establecer acción por defecto
                document.getElementById('post_checkout_accion').value = 'limpieza';
            });
    });
    </script>
@stop
