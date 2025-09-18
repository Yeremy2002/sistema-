@extends('adminlte::page')

@section('title', 'Detalles de Reserva')

@section('content_header')
    <h1>Detalles de la Reserva</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('reservas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @if ($reserva->estado === 'Check-in')
                    <a href="{{ route('reservas.checkout', $reserva) }}" class="btn btn-info">
                        <i class="fas fa-sign-out-alt"></i> Check-out
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">Información de la Reserva</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Habitación:</th>
                                    <td>{{ $reserva->habitacion->numero }} - {{ $reserva->habitacion->categoria->nombre }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        @switch($reserva->estado)
                                            @case('Pendiente de Confirmación')
                                                <span class="badge badge-secondary">Pendiente de Confirmación</span>
                                                @if (isset($tiempoRestante))
                                                    <br><small class="text-danger"><i class="fas fa-clock"></i> 
                                                        Tiempo restante: {{ $tiempoRestante }}</small>
                                                @endif
                                                @if ($reserva->expires_at)
                                                    <br><small class="text-muted">Expira:
                                                        {{ $reserva->expires_at->format('d/m/Y H:i') }}</small>
                                                @endif
                                            @break

                                            @case('Pendiente')
                                                <span class="badge badge-warning">Pendiente</span>
                                            @break

                                            @case('Check-in')
                                                <span class="badge badge-success">Check-in</span>
                                            @break

                                            @case('Check-out')
                                                <span class="badge badge-info">Check-out</span>
                                            @break

                                            @case('Cancelada')
                                                <span class="badge badge-danger">Cancelada</span>
                                            @break

                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha de Entrada:</th>
                                    <td>
                                        {{ $reserva->fecha_entrada->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha de Salida:</th>
                                    <td>
                                        {{ $reserva->fecha_salida->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Días de Estancia:</th>
                                    <td>{{ $reserva->diasEstancia }} día(s)</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td>{{ $hotel->simbolo_moneda }} {{ number_format($reserva->total, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Adelanto:</th>
                                    <td>{{ $hotel->simbolo_moneda }} {{ number_format($reserva->adelanto, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Pendiente:</th>
                                    <td>{{ $hotel->simbolo_moneda }} {{ number_format($reserva->pendiente, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Observaciones:</th>
                                    <td>{{ $reserva->observaciones ?: 'Sin observaciones' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">Información del Cliente</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Nombre:</th>
                                    <td>{{ $reserva->cliente ? $reserva->cliente->nombre : $reserva->nombre_cliente }}</td>
                                </tr>
                                @if ($reserva->cliente)
                                    <tr>
                                        <th>DPI:</th>
                                        <td>{{ $reserva->cliente->dpi }}</td>
                                    </tr>
                                    <tr>
                                        <th>NIT:</th>
                                        <td>{{ $reserva->cliente->nit }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <th>Documento:</th>
                                        <td>{{ $reserva->documento_cliente }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $reserva->cliente ? $reserva->cliente->telefono : $reserva->telefono_cliente }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if (isset($puedeConfirmar) && $puedeConfirmar && $reserva->estado === 'Pendiente de Confirmación')
                        <div class="card mt-4">
                            <div class="card-header bg-warning">
                                <h3 class="card-title">Confirmar Reserva</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Esta reserva requiere confirmación antes de que expire el plazo.
                                </p>
                                @if (isset($tiempoRestante))
                                    <p class="text-danger">
                                        <i class="fas fa-clock"></i>
                                        <strong>Tiempo restante: {{ $tiempoRestante }}</strong>
                                    </p>
                                @endif
                                <form action="{{ route('reservas.confirmar', $reserva) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('¿Está seguro de confirmar esta reserva?')">
                                        <i class="fas fa-check"></i> Confirmar Reserva
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if ($reserva->estado === 'Check-in')
                        <div class="card mt-4">
                            <div class="card-header bg-success">
                                <h3 class="card-title">Registrar Pago</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('cajas.registrarPago', $reserva) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="monto">Monto</label>
                                        <input type="number" step="0.01" class="form-control" id="monto"
                                            name="monto" required value="{{ $reserva->pendiente }}"
                                            max="{{ $reserva->pendiente }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="concepto">Concepto</label>
                                        <input type="text" class="form-control" id="concepto" name="concepto"
                                            value="Pago de hospedaje">
                                    </div>
                                    <button type="submit" class="btn btn-success">Registrar Pago</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        console.log('Hi!');
    </script>
@stop
