@extends('adminlte::page')

@section('title', 'Información del Hotel')

@section('content_header')
    <h1>Información del Hotel</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('configuracion.hotel.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre del Hotel</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre"
                                name="nombre" value="{{ old('nombre', $hotel->nombre) }}" required
                                placeholder="Ingrese el nombre del hotel">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Este nombre aparecerá en todo el sistema.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nit">NIT</label>
                            <input type="text" class="form-control @error('nit') is-invalid @enderror" id="nit"
                                name="nit" value="{{ old('nit', $hotel->nit) }}" required placeholder="Ingrese el NIT">
                            @error('nit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nombre_fiscal">Nombre Fiscal</label>
                    <input type="text" class="form-control @error('nombre_fiscal') is-invalid @enderror"
                        id="nombre_fiscal" name="nombre_fiscal" value="{{ old('nombre_fiscal', $hotel->nombre_fiscal) }}"
                        required placeholder="Ingrese el nombre fiscal">
                    @error('nombre_fiscal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" rows="3"
                        required placeholder="Ingrese la dirección completa">{{ old('direccion', $hotel->direccion) }}</textarea>
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="simbolo_moneda">Símbolo de Moneda</label>
                            <input type="text" class="form-control @error('simbolo_moneda') is-invalid @enderror"
                                id="simbolo_moneda" name="simbolo_moneda"
                                value="{{ old('simbolo_moneda', $hotel->simbolo_moneda) }}" required placeholder="Ej: Q.">
                            @error('simbolo_moneda')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Este símbolo se usará en todos los montos del
                                sistema.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('logo') is-invalid @enderror"
                                    id="logo" name="logo" accept="image/*">
                                <label class="custom-file-label" for="logo">Seleccionar archivo</label>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo:
                                2MB</small>
                        </div>
                    </div>
                </div>

                @if ($hotel->logo)
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Logo Actual</h3>
                                </div>
                                <div class="card-body">
                                    <img src="{{ Storage::url($hotel->logo) }}" alt="Logo del Hotel" class="img-fluid"
                                        style="max-height: 100px;">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label for="session_lifetime">Duración de la sesión (minutos)</label>
                    <input type="number" class="form-control @error('session_lifetime') is-invalid @enderror"
                        id="session_lifetime" name="session_lifetime"
                        value="{{ old('session_lifetime', $hotel->session_lifetime ?? 60) }}" min="1" required>
                    @error('session_lifetime')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Tiempo de inactividad antes de cerrar sesión
                        automáticamente.</small>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Mostrar el nombre del archivo seleccionado en el input file
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@stop
