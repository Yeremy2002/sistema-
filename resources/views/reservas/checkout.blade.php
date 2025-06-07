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
                        <p><strong>Total a Pagar:</strong> S/. {{ number_format($reserva->total, 2) }}</p>
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
                        <p><strong>Estancia:</strong> S/. {{ number_format($reserva->total, 2) }}</p>
                        <p><strong>Consumos Adicionales:</strong> S/. 0.00</p>
                        <p><strong>Descuentos:</strong> S/. 0.00</p>
                        <p><strong>Anticipo registrado:</strong> S/. {{ number_format($reserva->adelanto, 2) }}</p>
                        <p><strong>Total a Pagar:</strong> S/. {{ number_format($reserva->total - $reserva->adelanto, 2) }}
                        </p>
                        <p><strong>Estado de Pago:</strong> Pendiente</p>
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
                        <form action="{{ route('reservas.checkout.store', $reserva) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="metodo_pago">Método de Pago</label>
                                <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta (Crédito/Débito)</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>

                            <div class="form-group" id="campo_autorizacion" style="display: none;">
                                <label for="numero_autorizacion">Número de Autorización</label>
                                <input type="text" name="numero_autorizacion" id="numero_autorizacion"
                                    class="form-control">
                            </div>

                            <div class="form-group" id="campo_transferencia" style="display: none;">
                                <label for="nombre_banco">Nombre del Banco</label>
                                <input type="text" name="nombre_banco" id="nombre_banco" class="form-control">

                                <label for="numero_boleta" class="mt-2">Número de Boleta</label>
                                <input type="text" name="numero_boleta" id="numero_boleta" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="monto_total">Monto a Pagar</label>
                                <input type="number" step="0.01"
                                    class="form-control @error('monto_total') is-invalid @enderror" id="monto_total"
                                    name="monto_total" value="{{ old('monto_total', $reserva->total) }}" required>
                                @error('monto_total')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones"
                                    rows="3">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success">Registrar Check-out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('metodo_pago').addEventListener('change', function() {
            const campoAutorizacion = document.getElementById('campo_autorizacion');
            const campoTransferencia = document.getElementById('campo_transferencia');

            if (this.value === 'tarjeta') {
                campoAutorizacion.style.display = 'block';
                campoTransferencia.style.display = 'none';
            } else if (this.value === 'transferencia') {
                campoAutorizacion.style.display = 'none';
                campoTransferencia.style.display = 'block';
            } else {
                campoAutorizacion.style.display = 'none';
                campoTransferencia.style.display = 'none';
            }
        });
    </script>
@stop
