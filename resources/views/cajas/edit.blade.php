@extends('adminlte::page')

@section('title', 'Cerrar Caja')

@section('content_header')
    <h1>Cerrar Caja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen de Caja</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Inicial</span>
                            <span class="info-box-number">${{ number_format($caja->saldo_inicial, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-plus-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Ingresos</span>
                            <span class="info-box-number">${{ number_format($caja->total_ingresos, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-minus-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Egresos</span>
                            <span class="info-box-number">${{ number_format($caja->total_egresos, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Final Esperado</span>
                            <span
                                class="info-box-number">${{ number_format($caja->saldo_inicial + $caja->total_ingresos - $caja->total_egresos, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('cajas.update', $caja) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="saldo_final">Saldo Final Real</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01"
                                    class="form-control @error('saldo_final') is-invalid @enderror" id="saldo_final"
                                    name="saldo_final" value="{{ old('saldo_final') }}" required>
                                @error('saldo_final')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="diferencia">Diferencia</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" id="diferencia" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="observaciones_cierre">Observaciones</label>
                    <textarea class="form-control @error('observaciones_cierre') is-invalid @enderror" id="observaciones_cierre"
                        name="observaciones_cierre" rows="3">{{ old('observaciones_cierre') }}</textarea>
                    @error('observaciones_cierre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock"></i> Cerrar Caja
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
            // Calcular la diferencia entre el saldo final real y el esperado
            function calcularDiferencia() {
                let saldoEsperado = {{ $caja->saldo_inicial + $caja->total_ingresos - $caja->total_egresos }};
                let saldoReal = parseFloat($('#saldo_final').val()) || 0;
                let diferencia = saldoReal - saldoEsperado;

                $('#diferencia').val(diferencia.toFixed(2));

                // Cambiar el color según si hay sobrante o faltante
                if (diferencia > 0) {
                    $('#diferencia').addClass('text-success').removeClass('text-danger');
                } else if (diferencia < 0) {
                    $('#diferencia').addClass('text-danger').removeClass('text-success');
                } else {
                    $('#diferencia').removeClass('text-success text-danger');
                }
            }

            $('#saldo_final').on('input', function() {
                let value = $(this).val();
                if (value) {
                    value = parseFloat(value).toFixed(2);
                    $(this).val(value);
                }
                calcularDiferencia();
            });

            // Calcular diferencia inicial
            calcularDiferencia();
        });
    </script>
@stop
