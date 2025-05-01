@extends('adminlte::page')

@section('title', 'Abrir Caja')

@section('content_header')
    <h1>Abrir Caja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('cajas.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="saldo_inicial">Saldo Inicial</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01"
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
            // Formatear el saldo inicial mientras se escribe
            $('#saldo_inicial').on('input', function() {
                let value = $(this).val();
                if (value) {
                    value = parseFloat(value).toFixed(2);
                    $(this).val(value);
                }
            });
        });
    </script>
@stop
