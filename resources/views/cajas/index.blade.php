@extends('adminlte::page')

@section('title', 'Gestión de Cajas')

@section('content_header')
    <h1>Gestión de Cajas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Cajas</h3>
            @can('abrir caja')
                <div class="card-tools">
                    <a href="{{ route('cajas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Abrir Caja
                    </a>
                </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Saldo Inicial</th>
                            <th>Saldo Actual</th>
                            <th>Total Ingresos</th>
                            <th>Total Egresos</th>
                            <th>Turno</th>
                            <th>Estado</th>
                            <th>Fecha Apertura</th>
                            <th>Fecha Cierre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cajas as $caja)
                            <tr>
                                <td>{{ $caja->id }}</td>
                                <td>{{ $caja->user->name }}</td>
                                <td>{{ number_format($caja->saldo_inicial, 2) }}</td>
                                <td>{{ number_format($caja->saldo_actual, 2) }}</td>
                                <td>{{ number_format($caja->total_ingresos, 2) }}</td>
                                <td>{{ number_format($caja->total_egresos, 2) }}</td>
                                <td>{{ ucfirst($caja->turno) }}</td>
                                <td>
                                    <span class="badge badge-{{ $caja->estado ? 'success' : 'danger' }}">
                                        {{ $caja->estado ? 'Abierta' : 'Cerrada' }}
                                    </span>
                                </td>
                                <td>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</td>
                                <td>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '-' }}</td>
                                <td>
                                    <a href="{{ route('cajas.show', $caja) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($caja->estado)
                                        <a href="{{ route('cajas.edit', $caja) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-lock"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $cajas->links() }}
        </div>
    </div>
@stop
