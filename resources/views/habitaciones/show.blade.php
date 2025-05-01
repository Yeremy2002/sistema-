@extends('adminlte::page')

@section('title', 'Detalles de Habitación')

@section('content_header')
    <h1>Detalles de Habitación</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Habitación</h3>
            <div class="card-tools">
                <a href="{{ route('habitaciones.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Número de Habitación:</label>
                        <p class="form-control-static">{{ $habitacione->numero }}</p>
                    </div>
                    <div class="form-group">
                        <label>Categoría:</label>
                        <p class="form-control-static">{{ $habitacione->categoria->nombre }}</p>
                    </div>
                    <div class="form-group">
                        <label>Nivel:</label>
                        <p class="form-control-static">{{ $habitacione->nivel->nombre }}</p>
                    </div>
                    <div class="form-group">
                        <label>Estado:</label>
                        <p class="form-control-static">
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
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio:</label>
                        <p class="form-control-static">S/ {{ number_format($habitacione->precio, 2) }}</p>
                    </div>
                    <div class="form-group">
                        <label>Descripción:</label>
                        <p class="form-control-static">{{ $habitacione->descripcion ?: 'No especificada' }}</p>
                    </div>
                    <div class="form-group">
                        <label>Características:</label>
                        <p class="form-control-static">{{ $habitacione->caracteristicas ?: 'No especificadas' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="btn-group">
                        <a href="{{ route('habitaciones.edit', ['habitacione' => $habitacione->id]) }}"
                            class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('habitaciones.destroy', ['habitacione' => $habitacione->id]) }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('¿Está seguro de eliminar esta habitación?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .form-control-static {
            padding-top: 7px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .badge {
            font-size: 14px;
            padding: 8px 12px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-hide alert after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
@stop
