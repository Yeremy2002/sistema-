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
                    <form action="{{ route('reservas.checkout', $reserva) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-info"
                            onclick="return confirm('¿Confirma que desea realizar el check-out?');">
                            <i class="fas fa-sign-out-alt"></i> Check-out
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
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
                                    <td>S/. {{ number_format($reserva->total, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Adelanto:</th>
                                    <td>S/. {{ number_format($reserva->adelanto, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Pendiente:</th>
                                    <td>S/. {{ number_format($reserva->pendiente, 2) }}</td>
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
