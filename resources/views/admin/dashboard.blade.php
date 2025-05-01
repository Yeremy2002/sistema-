@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard - Estado de Habitaciones</h1>
@stop

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-box bg-success">
                <span class="info-box-icon">
                    <i class="fas fa-bed"></i>
                </span>
                <div class="info-box-content">
                    <h6 class="info-box-text">Total de Habitaciones</h6>
                    <h2 class="info-box-number">{{ $habitaciones->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Vista General de Habitaciones</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($habitaciones as $habitacion)
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div
                            class="card h-100 
                            @switch($habitacion->estado)
                                @case('Disponible')
                                    border-success bg-success-light
                                    @break
                                @case('Ocupada')
                                    border-danger bg-danger-light
                                    @break
                                @case('Mantenimiento')
                                    border-warning bg-warning-light
                                    @break
                                @case('Limpieza')
                                    border-info bg-info-light
                                    @break
                            @endswitch
                        ">
                            <div class="card-header border-0 bg-transparent">
                                <h5 class="card-title">Habitación {{ $habitacion->numero }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Categoría:</strong> {{ $habitacion->categoria->nombre }}
                                </div>
                                <div class="mb-2">
                                    <strong>Nivel:</strong> {{ $habitacion->nivel->nombre }}
                                </div>
                                <div class="mb-2">
                                    <strong>Estado:</strong>
                                    @switch($habitacion->estado)
                                        @case('Disponible')
                                            <span class="badge badge-success">Disponible</span>
                                        @break

                                        @case('Ocupada')
                                            <span class="badge badge-danger">Ocupada</span>
                                        @break

                                        @case('Mantenimiento')
                                            <span class="badge badge-warning">Mantenimiento</span>
                                        @break

                                        @case('Limpieza')
                                            <span class="badge badge-info">Limpieza</span>
                                        @break
                                    @endswitch
                                </div>
                                <div class="mb-2">
                                    <strong>Precio:</strong> Q. {{ number_format($habitacion->precio, 2) }}
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                @if ($habitacion->estado == 'Disponible')
                                    <a href="{{ route('reservas.create', ['habitacion' => $habitacion->id]) }}"
                                        class="btn btn-success btn-sm btn-block mb-2">
                                        <i class="fas fa-sign-in-alt"></i> Check-in
                                    </a>
                                @endif
                                <a href="{{ route('habitaciones.show', $habitacion) }}"
                                    class="btn btn-info btn-sm btn-block">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
        }

        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-info-light {
            background-color: rgba(23, 162, 184, 0.1) !important;
        }

        .border-success {
            border-width: 2px !important;
        }

        .border-danger {
            border-width: 2px !important;
        }

        .border-warning {
            border-width: 2px !important;
        }

        .border-info {
            border-width: 2px !important;
        }

        .card-header {
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-size: 85%;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .info-box {
            min-height: 100px;
            background: #28a745;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
            display: flex;
            padding: 0.5rem;
        }

        .info-box-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #fff;
        }

        .info-box-content {
            padding: 5px 10px;
            flex: 1;
            color: #fff;
        }

        .info-box-text {
            margin: 0;
            font-size: 1rem;
        }

        .info-box-number {
            margin: 0;
            font-weight: 700;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar tooltips si los usas
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop
