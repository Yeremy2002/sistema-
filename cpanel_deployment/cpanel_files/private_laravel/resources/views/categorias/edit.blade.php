@extends('adminlte::page')

@section('title', 'Editar Categoría')

@section('content_header')
    <h1>Editar Categoría de Habitación</h1>
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

            <form action="{{ route('categorias.update', $categoria) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                value="{{ old('nombre', $categoria->nombre) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" class="form-control" required>
                                <option value="1" {{ old('estado', $categoria->estado) ? 'selected' : '' }}>Activo
                                </option>
                                <option value="0" {{ old('estado', $categoria->estado) ? '' : 'selected' }}>Inactivo
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $categoria->descripcion) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
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
        $(document).ready(function() {
            // Aquí puedes agregar cualquier JavaScript necesario
        });
    </script>
@stop
