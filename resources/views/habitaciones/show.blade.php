@extends('adminlte::page')

@section('title', 'Detalle de Habitación')

@section('content_header')
    <h1>Detalle de Habitación</h1>
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
            <!-- Galería de imágenes -->
            @if ($habitacione->imagenes->count() > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <h4>Galería de Imágenes</h4>
                        <div id="carouselHabitacion" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                @foreach ($habitacione->imagenes as $index => $imagen)
                                    <li data-target="#carouselHabitacion" data-slide-to="{{ $index }}"
                                        {{ $index === 0 ? 'class="active"' : '' }}></li>
                                @endforeach
                            </ol>
                            <div class="carousel-inner">
                                @foreach ($habitacione->imagenes as $index => $imagen)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ Storage::url($imagen->ruta) }}" class="d-block w-100"
                                            alt="Imagen de habitación"
                                            style="height: 600px; object-fit: cover; object-position: center;">
                                        @if ($imagen->descripcion)
                                            <div
                                                class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 p-3 rounded">
                                                <p class="mb-0">{{ $imagen->descripcion }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#carouselHabitacion" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Anterior</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselHabitacion" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Siguiente</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

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
                        <p class="form-control-static">{{ $hotel->simbolo_moneda }}
                            {{ number_format($habitacione->precio, 2) }}</p>
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
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .carousel-item {
            background-color: #000;
        }

        .carousel-caption {
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            max-width: 80%;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar el carrusel
            $('.carousel').carousel({
                interval: 5000
            });
        });
    </script>
@stop
