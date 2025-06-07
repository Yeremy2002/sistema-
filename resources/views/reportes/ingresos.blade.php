@extends('adminlte::page')

@section('title', 'Reporte de Ingresos')

@section('content_header')
    <h1>Reporte de Ingresos</h1>
@stop

@section('content')
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-plus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Ingresos</span>
                    <span class="info-box-number">{{ $hotel->simbolo_moneda }}{{ number_format($totalIngresos, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-minus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Egresos</span>
                    <span class="info-box-number">{{ $hotel->simbolo_moneda }}{{ number_format($totalEgresos, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Últimos Movimientos de Caja</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Caja</th>
                            <th>Concepto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimientos as $mov)
                            <tr>
                                <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $mov->tipo == 'ingreso' ? 'success' : 'danger' }}">
                                        {{ ucfirst($mov->tipo) }}
                                    </span>
                                </td>
                                <td>${{ number_format($mov->monto, 2) }}</td>
                                <td>{{ $mov->user->name ?? '-' }}</td>
                                <td>#{{ $mov->caja_id }}</td>
                                <td>{{ $mov->concepto }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay movimientos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
