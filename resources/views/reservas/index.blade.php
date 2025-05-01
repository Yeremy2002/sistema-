@extends('adminlte::page')

@section('title', 'Reservas')

@section('content_header')
    <h1>Gestión de Reservas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('reservas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Reserva
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Habitación</th>
                            <th>Cliente</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservas as $reserva)
                            <tr>
                                <td>{{ $reserva->habitacion->numero }}</td>
                                <td>
                                    {{ $reserva->nombre_cliente }}<br>
                                    <small class="text-muted">{{ $reserva->telefono }}</small>
                                </td>
                                <td>{{ $reserva->fecha_entrada->format('d/m/Y H:i') }}</td>
                                <td>{{ $reserva->fecha_salida->format('d/m/Y H:i') }}</td>
                                <td>
                                    @switch($reserva->estado)
                                        @case('pendiente')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @break

                                        @case('activa')
                                            <span class="badge badge-success">Activa</span>
                                        @break

                                        @case('completada')
                                            <span class="badge badge-info">Completada</span>
                                        @break

                                        @case('cancelada')
                                            <span class="badge badge-danger">Cancelada</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>S/. {{ number_format($reserva->total, 2) }}</td>
                                <td>S/. {{ number_format($reserva->pendiente, 2) }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('reservas.show', $reserva) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('reservas.destroy', $reserva) }}" method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('¿Está seguro de eliminar esta reserva?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay reservas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reservas->links() }}
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
