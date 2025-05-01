@extends('adminlte::page')

@section('title', 'Detalles de Caja')

@section('content_header')
    <h1>Detalles de Caja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Caja</h3>
            <div class="card-tools">
                @if ($caja->estado)
                    <a href="{{ route('cajas.edit', $caja) }}" class="btn btn-warning">
                        <i class="fas fa-lock"></i> Cerrar Caja
                    </a>
                @endif
                <a href="{{ route('cajas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Usuario</span>
                            <span class="info-box-number">{{ $caja->user->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Turno</span>
                            <span class="info-box-number">{{ ucfirst($caja->turno) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Inicial</span>
                            <span class="info-box-number">${{ number_format($caja->saldo_inicial, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-plus-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Ingresos</span>
                            <span class="info-box-number">${{ number_format($caja->total_ingresos, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-minus-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Egresos</span>
                            <span class="info-box-number">${{ number_format($caja->total_egresos, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Actual</span>
                            <span class="info-box-number">${{ number_format($caja->saldo_actual, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha Apertura</span>
                            <span class="info-box-number">{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha Cierre</span>
                            <span
                                class="info-box-number">{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'Caja Abierta' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @if ($caja->observaciones_apertura)
                <div class="row">
                    <div class="col-md-12">
                        <div class="callout callout-info">
                            <h5>Observaciones de Apertura</h5>
                            <p>{{ $caja->observaciones_apertura }}</p>
                        </div>
                    </div>
                </div>
            @endif
            @if ($caja->observaciones_cierre)
                <div class="row">
                    <div class="col-md-12">
                        <div class="callout callout-warning">
                            <h5>Observaciones de Cierre</h5>
                            <p>{{ $caja->observaciones_cierre }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Movimientos de Caja</h3>
            @if ($caja->estado)
                <div class="card-tools">
                    <a href="{{ route('cajas.movimientos.create', $caja) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Movimiento
                    </a>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $movimiento)
                            <tr>
                                <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $movimiento->tipo == 'ingreso' ? 'success' : 'danger' }}">
                                        {{ ucfirst($movimiento->tipo) }}
                                    </span>
                                </td>
                                <td>{{ $movimiento->concepto }}</td>
                                <td>${{ number_format($movimiento->monto, 2) }}</td>
                                <td>{{ $movimiento->user->name }}</td>
                                <td>{{ $movimiento->descripcion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $movimientos->links() }}
        </div>
    </div>
@stop
