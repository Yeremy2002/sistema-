@extends('adminlte::page')

@section('title', 'Registro de Reparaciones')

@section('content_header')
    <h1>Registro de Reparaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('mantenimiento.reparacion.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Reparación
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
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reparaciones as $reparacion)
                            <tr>
                                <td>{{ $reparacion->habitacion->numero }}</td>
                                <td>{{ $reparacion->user->name }}</td>
                                <td>{{ $reparacion->fecha->format('d/m/Y') }}</td>
                                <td>{{ $reparacion->hora->format('H:i') }}</td>
                                <td>{{ $reparacion->tipo_reparacion }}</td>
                                <td>
                                    @switch($reparacion->estado)
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
                                <td>${{ number_format($reparacion->costo, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                        data-target="#viewModal{{ $reparacion->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#updateModal{{ $reparacion->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de Vista -->
                            <div class="modal fade" id="viewModal{{ $reparacion->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="viewModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewModalLabel">Detalles de la Reparación</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <dl class="row">
                                                <dt class="col-sm-4">Habitación</dt>
                                                <dd class="col-sm-8">{{ $reparacion->habitacion->numero }}</dd>

                                                <dt class="col-sm-4">Tipo</dt>
                                                <dd class="col-sm-8">{{ $reparacion->tipo_reparacion }}</dd>

                                                <dt class="col-sm-4">Descripción</dt>
                                                <dd class="col-sm-8">{{ $reparacion->descripcion }}</dd>

                                                <dt class="col-sm-4">Observaciones</dt>
                                                <dd class="col-sm-8">
                                                    {{ $reparacion->observaciones ?: 'Sin observaciones' }}</dd>

                                                <dt class="col-sm-4">Costo</dt>
                                                <dd class="col-sm-8">${{ number_format($reparacion->costo, 2) }}</dd>
                                            </dl>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal de Actualización -->
                            <div class="modal fade" id="updateModal{{ $reparacion->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="updateModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('mantenimiento.reparacion.update', $reparacion) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="updateModalLabel">Actualizar Estado de
                                                    Reparación</h5>
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
                                                            {{ $reparacion->estado == 'pendiente' ? 'selected' : '' }}>
                                                            Pendiente</option>
                                                        <option value="en_proceso"
                                                            {{ $reparacion->estado == 'en_proceso' ? 'selected' : '' }}>En
                                                            Proceso</option>
                                                        <option value="completada"
                                                            {{ $reparacion->estado == 'completada' ? 'selected' : '' }}>
                                                            Completada</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="costo">Costo</label>
                                                    <input type="number" name="costo" id="costo" class="form-control"
                                                        step="0.01" min="0" value="{{ $reparacion->costo }}"
                                                        required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="observaciones">Observaciones</label>
                                                    <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ $reparacion->observaciones }}</textarea>
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
                                    <td colspan="8" class="text-center">No hay registros de reparaciones</td>
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
