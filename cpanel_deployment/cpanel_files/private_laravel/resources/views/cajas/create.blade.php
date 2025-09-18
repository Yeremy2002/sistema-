@extends('adminlte::page')

@section('title', 'Abrir Caja')

@section('content_header')
    <h1>Abrir Caja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (isset($turnoSugerido))
                <div class="alert alert-info">
                    <strong>Turno sugerido:</strong>
                    {{ $turnoSugerido == 'matutino' ? 'Matutino (mañana)' : 'Nocturno (noche)' }} basado en la hora actual.
                </div>
            @endif
            <form action="{{ route('cajas.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="saldo_inicial">Saldo Inicial</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $hotel->simbolo_moneda }}</span>
                                </div>
                                <input type="number" step="0.01" min="0"
                                    class="form-control @error('saldo_inicial') is-invalid @enderror" id="saldo_inicial"
                                    name="saldo_inicial" value="{{ old('saldo_inicial') }}" required>
                                @error('saldo_inicial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="turno">Turno</label>
                            <select class="form-control @error('turno') is-invalid @enderror" id="turno" name="turno"
                                required>
                                <option value="">Seleccione un turno</option>
                                <option value="matutino" {{ old('turno') == 'matutino' ? 'selected' : '' }}>Matutino (7:00
                                    AM - 5:00 PM)</option>
                                <option value="nocturno" {{ old('turno') == 'nocturno' ? 'selected' : '' }}>Nocturno (5:00
                                    PM - 7:00 AM)</option>
                            </select>
                            @error('turno')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="observaciones_apertura">Observaciones</label>
                    <textarea class="form-control @error('observaciones_apertura') is-invalid @enderror" id="observaciones_apertura"
                        name="observaciones_apertura" rows="3">{{ old('observaciones_apertura') }}</textarea>
                    @error('observaciones_apertura')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Abrir Caja
                    </button>
                    <a href="{{ route('cajas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Mostrar alerta si el usuario intentó hacer una operación sin caja abierta
            @if(session('alerta_caja_requerida'))
                Swal.fire({
                    title: '¡DEBE ABRIR SU CAJA PRIMERO!',
                    html: '<div style="font-size: 1.5em; line-height: 1.5;">'
                        + '<p><strong>⚠️ ATENCIÓN ⚠️</strong></p>'
                        + '<p>No puede registrar <strong>INGRESOS</strong> o <strong>GASTOS</strong> sin tener una caja abierta.</p>'
                        + '<p style="color: #dc3545;">Por favor, complete el formulario para <strong>ABRIR SU CAJA</strong> ahora.</p>'
                        + '</div>',
                    icon: 'warning',
                    confirmButtonText: 'ENTENDIDO, ABRIRÉ MI CAJA',
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'swal2-large',
                        title: 'swal2-title-large',
                        htmlContainer: 'swal2-html-large',
                        confirmButton: 'btn btn-lg btn-success'
                    },
                    didOpen: () => {
                        // Aplicar estilos personalizados
                        const popup = Swal.getPopup();
                        popup.style.minWidth = '600px';
                        const title = Swal.getTitle();
                        title.style.fontSize = '2.5em';
                        title.style.fontWeight = 'bold';
                        title.style.color = '#dc3545';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enfocar el campo de saldo inicial
                        $('#saldo_inicial').focus();
                        
                        // Sugerir el turno si está disponible
                        @if(isset($turnoSugerido))
                            $('#turno').val('{{ $turnoSugerido }}');
                        @endif
                    }
                });
            @endif
            
            // Formatear el saldo inicial al perder el foco
            $('#saldo_inicial').on('blur', function() {
                let value = $(this).val();
                if (value) {
                    value = parseFloat(value).toFixed(2);
                    $(this).val(value);
                }
            });
            
            // Agregar validación adicional al formulario
            $('form').on('submit', function(e) {
                const saldoInicial = $('#saldo_inicial').val();
                const turno = $('#turno').val();
                
                if (!saldoInicial || parseFloat(saldoInicial) < 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor ingrese un saldo inicial válido',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return false;
                }
                
                if (!turno) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor seleccione un turno',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return false;
                }
            });
        });
    </script>
    <style>
        /* Estilos adicionales para hacer la alerta más prominente */
        .swal2-large {
            font-size: 1.2em !important;
        }
        .swal2-title-large {
            margin-bottom: 1.5em !important;
        }
        .swal2-html-large {
            font-size: 1.1em !important;
        }
    </style>
@stop
