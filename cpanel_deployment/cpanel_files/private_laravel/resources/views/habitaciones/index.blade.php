@extends('adminlte::page')

@section('title', 'Gestión de Habitaciones')

@section('content_header')
    <h1>Gestión de Habitaciones</h1>
@stop

@section('content')
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('success') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: '{{ session('warning') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('info'))
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: '{{ session('info') }}',
                timer: 3500,
                showConfirmButton: false
            });
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Habitaciones</h3>
            <div class="card-tools">
                <a href="{{ route('habitaciones.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nueva Habitación
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Nivel</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($habitaciones as $habitacione)
                            <tr>
                                <td>{{ $habitacione->numero }}</td>
                                <td>{{ $habitacione->descripcion }}</td>
                                <td>{{ $habitacione->categoria->nombre }}</td>
                                <td>{{ $habitacione->nivel->nombre }}</td>
                                <td>
                                    @switch($habitacione->estado)
                                        @case('Disponible')
                                            <span class="badge badge-success">Disponible</span>
                                        @break

                                        @case('Ocupada')
                                            <span class="badge badge-warning">Ocupada</span>
                                        @break

                                        @case('Mantenimiento')
                                            <span class="badge badge-danger">Mantenimiento</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>{{ $hotel->simbolo_moneda ?? 'Q.' }}{{ number_format($habitacione->precio, 2) }}</td>
                                <td>
                                    <a href="{{ route('habitaciones.show', ['habitacione' => $habitacione->id]) }}"
                                        class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('habitaciones.edit', ['habitacione' => $habitacione->id]) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('habitaciones.destroy', ['habitacione' => $habitacione->id]) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="event.preventDefault();
                                                Swal.fire({
                                                    title: '¿Está seguro de eliminar esta habitación?',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Sí, eliminar',
                                                    cancelButtonText: 'Cancelar',
                                                    reverseButtons: true
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        this.closest('form').submit();
                                                    }
                                                });
                                            return false;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
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
        $(document).ready(function() {
            $('.table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop
