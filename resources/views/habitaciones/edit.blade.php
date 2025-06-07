@extends('adminlte::page')

@section('title', 'Editar Habitación')

@section('content_header')
    <h1>Editar Habitación</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('habitaciones.update', ['habitacione' => $habitacione->id]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numero">Número de Habitación</label>
                            <input type="text" class="form-control @error('numero') is-invalid @enderror" id="numero"
                                name="numero" value="{{ old('numero', $habitacione->numero) }}" required>
                            @error('numero')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categoria_id">Categoría</label>
                            <select class="form-control @error('categoria_id') is-invalid @enderror" id="categoria_id"
                                name="categoria_id" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}"
                                        {{ old('categoria_id', $habitacione->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nivel_id">Nivel</label>
                            <select class="form-control @error('nivel_id') is-invalid @enderror" id="nivel_id"
                                name="nivel_id" required>
                                <option value="">Seleccione un nivel</option>
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel->id }}"
                                        {{ old('nivel_id', $habitacione->nivel_id) == $nivel->id ? 'selected' : '' }}>
                                        {{ $nivel->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nivel_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" step="0.01" class="form-control @error('precio') is-invalid @enderror"
                                id="precio" name="precio" value="{{ old('precio', $habitacione->precio) }}" required>
                            @error('precio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado"
                                required>
                                <option value="Disponible"
                                    {{ old('estado', $habitacione->estado) == 'Disponible' ? 'selected' : '' }}>
                                    Disponible
                                </option>
                                <option value="Ocupada"
                                    {{ old('estado', $habitacione->estado) == 'Ocupada' ? 'selected' : '' }}>
                                    Ocupada
                                </option>
                                <option value="Mantenimiento"
                                    {{ old('estado', $habitacione->estado) == 'Mantenimiento' ? 'selected' : '' }}>
                                    Mantenimiento
                                </option>
                            </select>
                            @error('estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                        rows="3">{{ old('descripcion', $habitacione->descripcion) }}</textarea>
                    @error('descripcion')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="caracteristicas">Características</label>
                    <textarea class="form-control @error('caracteristicas') is-invalid @enderror" id="caracteristicas"
                        name="caracteristicas" rows="3">{{ old('caracteristicas', $habitacione->caracteristicas) }}</textarea>
                    @error('caracteristicas')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Sección de imágenes existentes -->
                @if ($habitacione->imagenes->count() > 0)
                    <div class="form-group">
                        <label>Imágenes Actuales</label>
                        <div class="row">
                            @foreach ($habitacione->imagenes as $imagen)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="{{ Storage::url($imagen->ruta) }}" class="card-img-top"
                                            alt="Imagen de habitación">
                                        <div class="card-body">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input"
                                                    id="imagen_principal_{{ $imagen->id }}" name="imagen_principal"
                                                    value="{{ $imagen->id }}"
                                                    {{ $imagen->es_principal ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="imagen_principal_{{ $imagen->id }}">
                                                    Imagen Principal
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox mt-2">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="eliminar_imagen_{{ $imagen->id }}" name="eliminar_imagenes[]"
                                                    value="{{ $imagen->id }}">
                                                <label class="custom-control-label text-danger"
                                                    for="eliminar_imagen_{{ $imagen->id }}">
                                                    Eliminar
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Sección para agregar nuevas imágenes -->
                <div class="form-group">
                    <label for="imagenes">Agregar Nuevas Imágenes</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('imagenes.*') is-invalid @enderror"
                            id="imagenes" name="imagenes[]" multiple accept="image/*">
                        <label class="custom-file-label" for="imagenes">Seleccionar imágenes</label>
                    </div>
                    <small class="form-text text-muted">Puede seleccionar múltiples imágenes. Seleccione una imagen
                        principal en la sección de arriba.</small>
                    @error('imagenes.*')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        // Actualizar el nombre del archivo seleccionado
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files.length > 1 ?
                e.target.files.length + ' archivos seleccionados' :
                e.target.files[0].name;
            e.target.nextElementSibling.innerHTML = fileName;
        });
    </script>
@stop
