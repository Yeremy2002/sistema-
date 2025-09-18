@extends('adminlte::page')

@section('title', 'Nuevo Movimiento de Caja')

@section('content_header')
    <h1>Nuevo Movimiento de Caja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Caja</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Usuario</span>
                            <span class="info-box-number">{{ $caja->user->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Turno</span>
                            <span class="info-box-number">{{ ucfirst($caja->turno) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Actual</span>
                            <span class="info-box-number">${{ number_format($caja->saldo_actual, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('cajas.movimientos.store', $caja) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo">Tipo de Movimiento</label>
                            <select class="form-control @error('tipo') is-invalid @enderror" id="tipo" name="tipo"
                                required>
                                <option value="">Seleccione un tipo</option>
                                <option value="ingreso" {{ old('tipo') == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                                <option value="egreso" {{ old('tipo') == 'egreso' ? 'selected' : '' }}>Egreso</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto">Monto</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01"
                                    class="form-control @error('monto') is-invalid @enderror" id="monto" name="monto"
                                    value="{{ old('monto') }}" required>
                                @error('monto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="concepto">Concepto</label>
                    <input type="text" class="form-control @error('concepto') is-invalid @enderror" id="concepto"
                        name="concepto" value="{{ old('concepto') }}" required>
                    @error('concepto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                        rows="3">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Movimiento
                    </button>
                    <a href="{{ route('cajas.show', $caja) }}" class="btn btn-secondary">
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
            // Formatear el monto mientras se escribe
            $('#monto').on('input', function() {
                let value = $(this).val();
                if (value) {
                    value = parseFloat(value).toFixed(2);
                    $(this).val(value);
                }
            });
        });
    </script>
@stop
