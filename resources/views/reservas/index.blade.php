@extends('adminlte::page')

@section('title', 'Reservas')

@section('content_header')
    <h1>Gestión de Reservas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                @can('crear reservas')
                    <a href="{{ route('reservas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Reserva
                    </a>
                @endcan
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
                                    {{ $reserva->cliente ? $reserva->cliente->nombre : $reserva->nombre_cliente }}<br>
                                    <small
                                        class="text-muted">{{ $reserva->cliente ? $reserva->cliente->telefono : $reserva->telefono_cliente }}</small>
                                    @if ($reserva->cliente)
                                        <br><small class="text-muted">NIT: {{ $reserva->cliente->nit }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $reserva->fecha_entrada->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">Check-in: 14:00</small>
                                </td>
                                <td>
                                    {{ $reserva->fecha_salida->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">Check-out: 12:30</small>
                                </td>
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
                                <td>S/. {{ number_format($reserva->total, 2) }}</td>
                                <td>S/. {{ number_format($reserva->pendiente, 2) }}</td>
                                <td>
                                    <div class="btn-group">
                                        @can('ver reservas')
                                            <a href="{{ route('reservas.show', $reserva) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('editar reservas')
                                            <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('eliminar reservas')
                                            <form action="{{ route('reservas.destroy', $reserva) }}" method="POST"
                                                style="display: inline;"
                                                onsubmit="event.preventDefault();
                                                    Swal.fire({
                                                        title: '¿Está seguro de eliminar esta reserva?',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonText: 'Sí, eliminar',
                                                        cancelButtonText: 'Cancelar',
                                                        reverseButtons: true
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            this.submit();
                                                        }
                                                    });
                                                return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @if ($reserva->estado === 'Check-in')
                                            <a href="{{ route('reservas.checkout', $reserva) }}"
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-sign-out-alt"></i> Check-out
                                            </a>
                                        @endif
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
