@extends('adminlte::page')

@section('title', 'Crear Cliente')

@section('content_header')
    <h1>Crear Nuevo Cliente</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre"
                        name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nit">NIT</label>
                    <input type="text" class="form-control @error('nit') is-invalid @enderror" id="nit"
                        name="nit" value="{{ old('nit') }}" required>
                    @error('nit')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="dpi">DPI</label>
                    <input type="text" class="form-control @error('dpi') is-invalid @enderror" id="dpi"
                        name="dpi" value="{{ old('dpi') }}" required>
                    @error('dpi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono"
                        name="telefono" value="{{ old('telefono') }}" required>
                    @error('telefono')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}">
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion">{{ old('direccion') }}</textarea>
                    @error('direccion')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div id="feedback_cliente" class="mt-2"></div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
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
                    title: 'Error en el registro',
                    text: errorMsg
                });
            }
        });
    </script>
@stop
