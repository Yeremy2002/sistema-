@extends('adminlte::page')

@section('title', 'Registro de Limpieza')

@section('content_header')
    <h1>Registro de Limpieza</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('mantenimiento.limpieza.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Limpieza
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Habitación</th>
                            <th>Responsable</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($limpiezas as $limpieza)
                            <tr>
                                <td>{{ $limpieza->habitacion->numero }}</td>
                                <td>{{ $limpieza->user->name }}</td>
                                <td>{{ $limpieza->fecha->format('d/m/Y') }}</td>
                                <td>{{ $limpieza->hora->format('H:i') }}</td>
                                <td>
                                    @switch($limpieza->estado)
                                        @case('pendiente')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @break

                                        @case('en_proceso')
                                            <span class="badge badge-info">En Proceso</span>
                                        @break

                                        @case('completada')
                                            <span class="badge badge-success">Completada</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>{{ $limpieza->observaciones }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#updateModal{{ $limpieza->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de Actualización -->
                            <div class="modal fade" id="updateModal{{ $limpieza->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="updateModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('mantenimiento.limpieza.update', $limpieza) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="updateModalLabel">Actualizar Estado de Limpieza
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="estado">Estado</label>
                                                    <select name="estado" id="estado" class="form-control" required>
                                                        <option value="pendiente"
                                                            {{ $limpieza->estado == 'pendiente' ? 'selected' : '' }}>
                                                            Pendiente</option>
                                                        <option value="en_proceso"
                                                            {{ $limpieza->estado == 'en_proceso' ? 'selected' : '' }}>En
                                                            Proceso</option>
                                                        <option value="completada"
                                                            {{ $limpieza->estado == 'completada' ? 'selected' : '' }}>
                                                            Completada</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="observaciones">Observaciones</label>
                                                    <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ $limpieza->observaciones }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay registros de limpieza</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
