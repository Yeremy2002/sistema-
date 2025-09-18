@extends('adminlte::page')

@section('title', 'Editar Cliente')

@section('content_header')
    <h1>Editar Cliente</h1>
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

            <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                value="{{ old('nombre', $cliente->nombre) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nit">NIT</label>
                            <input type="text" class="form-control" id="nit" name="nit"
                                value="{{ old('nit', $cliente->nit) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dpi">DPI</label>
                            <input type="text" class="form-control" id="dpi" name="dpi"
                                value="{{ old('dpi', $cliente->dpi) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono"
                                value="{{ old('telefono', $cliente->telefono) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="feedback_cliente" class="mt-2"></div>
@stop

@section('css')
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
@stop

@section('js')
    <script>
        // Feedback en tiempo real para NIT y DPI únicos (simulado, para ejemplo; en producción usar AJAX)
        document.getElementById('nit').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('feedback_cliente').innerHTML = '';
        });
        document.getElementById('dpi').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('feedback_cliente').innerHTML = '';
        });
        // Validación con SweetAlert2 al enviar el formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            let errorMsg = '';
            const nombre = document.getElementById('nombre').value.trim();
            const nit = document.getElementById('nit').value.trim();
            const dpi = document.getElementById('dpi').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            if (!nombre || !nit || !dpi || !telefono) {
                errorMsg = 'Todos los campos obligatorios deben estar completos.';
            }
            // Aquí podrías agregar una validación AJAX para NIT/DPI únicos
            if (errorMsg) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la edición',
                    text: errorMsg
                });
            }
        });
    </script>
@stop
