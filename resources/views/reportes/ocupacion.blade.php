@extends('adminlte::page')

@section('title', 'Reporte de Ocupación')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i> Reporte de Ocupación
    </h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filtros de Fecha</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('reportes.ocupacion') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_inicio">Fecha Inicio</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                               value="{{ $fechaInicio }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_fin">Fecha Fin</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                               value="{{ $fechaFin }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $estadisticas['ocupadas'] }}</h3>
                        <p>Habitaciones Ocupadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bed"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $estadisticas['disponibles'] }}</h3>
                        <p>Habitaciones Disponibles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $estadisticas['limpieza'] }}</h3>
                        <p>En Limpieza</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-broom"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $estadisticas['mantenimiento'] }}</h3>
                        <p>En Mantenimiento</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Porcentaje de Ocupación -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Porcentaje de Ocupación</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $estadisticas['porcentaje_ocupacion'] }}%"
                                 aria-valuenow="{{ $estadisticas['porcentaje_ocupacion'] }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ $estadisticas['porcentaje_ocupacion'] }}%
                            </div>
                        </div>
                        <p class="text-center">
                            <strong>{{ $estadisticas['ocupadas'] }}</strong> de <strong>{{ $estadisticas['total'] }}</strong> habitaciones ocupadas
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen del Período</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Período:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Reservas:</strong></td>
                                <td>{{ $reservas->count() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Reservas Activas:</strong></td>
                                <td>{{ $reservas->where('estado', '!=', 'cancelada')->count() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Habitaciones:</strong></td>
                                <td>{{ $totalHabitaciones }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de Habitaciones -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Estado Actual de Habitaciones</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Categoría</th>
                                        <th>Nivel</th>
                                        <th>Estado</th>
                                        <th>Precio</th>
                                        <th>Huésped Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($habitaciones as $habitacion)
                                    <tr>
                                        <td>{{ $habitacion->numero }}</td>
                                        <td>{{ $habitacion->categoria->nombre ?? 'N/A' }}</td>
                                        <td>{{ $habitacion->nivel->nombre ?? 'N/A' }}</td>
                                        <td>
                                            @switch($habitacion->estado)
                                                @case('disponible')
                                                    <span class="badge badge-success">{{ ucfirst($habitacion->estado) }}</span>
                                                    @break
                                                @case('ocupada')
                                                    <span class="badge badge-info">{{ ucfirst($habitacion->estado) }}</span>
                                                    @break
                                                @case('limpieza')
                                                    <span class="badge badge-warning">{{ ucfirst($habitacion->estado) }}</span>
                                                    @break
                                                @case('mantenimiento')
                                                    <span class="badge badge-danger">{{ ucfirst($habitacion->estado) }}</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($habitacion->estado) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $habitacion->categoria->simbolo_moneda ?? '' }}{{ number_format($habitacion->categoria->precio ?? 0, 2) }}</td>
                                        <td>
                                            @php
                                                $reservaActual = $reservas->where('habitacion_id', $habitacion->id)
                                                                        ->where('estado', '!=', 'cancelada')
                                                                        ->where('fecha_entrada', '<=', now())
                                                                        ->where('fecha_salida', '>=', now())
                                                                        ->first();
                                            @endphp
                                            @if($reservaActual)
                                                {{ $reservaActual->cliente->nombre ?? 'N/A' }}
                                                <br><small class="text-muted">
                                                    {{ $reservaActual->fecha_entrada->format('d/m/Y H:i') }} - 
                                                    {{ $reservaActual->fecha_salida->format('d/m/Y H:i') }}
                                                </small>
                                            @else
                                                <span class="text-muted">Sin huésped</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas del Período -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Reservas en el Período Seleccionado</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Habitación</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reservas as $reserva)
                                    <tr>
                                        <td>{{ $reserva->id }}</td>
                                        <td>{{ $reserva->cliente->nombre ?? 'N/A' }}</td>
                                        <td>
                                            {{ $reserva->habitacion->numero ?? 'N/A' }}
                                            <br><small class="text-muted">{{ $reserva->habitacion->categoria->nombre ?? '' }}</small>
                                        </td>
                                        <td>{{ $reserva->fecha_entrada->format('d/m/Y H:i') }}</td>
                                        <td>{{ $reserva->fecha_salida->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @switch($reserva->estado)
                                                @case('pendiente')
                                                    <span class="badge badge-warning">{{ ucfirst($reserva->estado) }}</span>
                                                    @break
                                                @case('confirmada')
                                                    <span class="badge badge-success">{{ ucfirst($reserva->estado) }}</span>
                                                    @break
                                                @case('ocupada')
                                                    <span class="badge badge-info">{{ ucfirst($reserva->estado) }}</span>
                                                    @break
                                                @case('completada')
                                                    <span class="badge badge-primary">{{ ucfirst($reserva->estado) }}</span>
                                                    @break
                                                @case('cancelada')
                                                    <span class="badge badge-danger">{{ ucfirst($reserva->estado) }}</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($reserva->estado) }}</span>
                                            @endswitch
                                        </td>
                                        <td>Q{{ number_format($reserva->total, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay reservas en el período seleccionado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .inner h3 {
            font-size: 2.2rem;
        }
        .progress {
            height: 2rem;
        }
        .progress-bar {
            line-height: 2rem;
            font-size: 1.1rem;
            font-weight: bold;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar DataTables para las tablas si se desea
            $('.table').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "pageLength": 25,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop