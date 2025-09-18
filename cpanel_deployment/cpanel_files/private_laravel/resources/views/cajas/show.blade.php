@extends('adminlte::page')

@section('title', 'Detalles de Caja #' . $caja->id)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-cash-register"></i> Detalles de Caja #{{ $caja->id }}
                @if (!$caja->estado)
                    <span class="badge badge-secondary">CERRADA</span>
                @else
                    <span class="badge badge-success">ABIERTA</span>
                @endif
            </h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                @if ($caja->estado)
                    @if (Auth::user()->hasRole(['Administrador', 'Super Admin']) && $caja->user_id != Auth::id())
                        <button class="btn btn-danger" onclick="mostrarCierreAdministrativo()">
                            <i class="fas fa-user-lock"></i> Cierre Administrativo
                        </button>
                    @endif
                    <a href="{{ route('cajas.edit', $caja) }}" class="btn btn-warning">
                        <i class="fas fa-lock"></i> Cerrar Caja
                    </a>
                @endif
                <a href="{{ route('cajas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Alertas de sesión --}}
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Primera fila: Información básica --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-{{ $caja->estado ? 'primary' : 'secondary' }} card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información General
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Usuario y Turno --}}
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Usuario</span>
                                    <span class="info-box-number">{{ $caja->user->name }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-{{ $caja->turno == 'diurno' ? 'warning' : 'dark' }}">
                                <span class="info-box-icon">
                                    <i class="fas fa-{{ $caja->turno == 'diurno' ? 'sun' : 'moon' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Turno</span>
                                    <span class="info-box-number">{{ ucfirst($caja->turno) }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Fechas --}}
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-calendar-plus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Apertura</span>
                                    <span class="info-box-number">{{ $caja->fecha_apertura->format('d/m/Y') }}</span>
                                    <span class="progress-description">
                                        {{ $caja->fecha_apertura->format('H:i:s') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-{{ $caja->estado ? 'warning' : 'secondary' }}">
                                <span class="info-box-icon"><i class="fas fa-calendar-{{ $caja->estado ? 'clock' : 'check' }}"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cierre</span>
                                    @if($caja->fecha_cierre)
                                        <span class="info-box-number">{{ $caja->fecha_cierre->format('d/m/Y') }}</span>
                                        <span class="progress-description">
                                            {{ $caja->fecha_cierre->format('H:i:s') }}
                                        </span>
                                    @else
                                        <span class="info-box-number">Pendiente</span>
                                        <span class="progress-description">
                                            Caja aún abierta
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Segunda fila: Resumen Financiero --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Resumen Financiero
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Saldo Inicial --}}
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{$hotel->simbolo_moneda}}{{ number_format($caja->saldo_inicial, 2) }}</h3>
                                    <p>Saldo Inicial</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Total Ingresos --}}
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{$hotel->simbolo_moneda}}{{ number_format($caja->total_ingresos, 2) }}</h3>
                                    <p>Total Ingresos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Total Egresos --}}
                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{$hotel->simbolo_moneda}}{{ number_format($caja->total_egresos, 2) }}</h3>
                                    <p>Total Egresos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Saldo Actual --}}
                        <div class="col-md-3">
                            <div class="small-box bg-{{ $caja->saldo_actual >= 0 ? 'primary' : 'warning' }}">
                                <div class="inner">
                                    <h3>{{$hotel->simbolo_moneda}}{{ number_format($caja->saldo_actual, 2) }}</h3>
                                    <p>Saldo Actual</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    @if($caja->saldo_final !== null)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card bg-gradient-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-check-double"></i> Resultado del Cierre
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php $diferencia = $caja->saldo_final - $caja->saldo_actual; @endphp
                                        
                                        <div class="col-md-4">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-dark">
                                                    <i class="fas fa-money-check-alt"></i>
                                                </span>
                                                <h5 class="description-header">{{$hotel->simbolo_moneda}}{{ number_format($caja->saldo_final, 2) }}</h5>
                                                <span class="description-text">SALDO FINAL REAL</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-{{ abs($diferencia) > 0.01 ? ($diferencia > 0 ? 'success' : 'danger') : 'dark' }}">
                                                    <i class="fas fa-{{ abs($diferencia) > 0.01 ? ($diferencia > 0 ? 'caret-up' : 'caret-down') : 'equals' }}"></i>
                                                    {{ $diferencia > 0 ? '+' : '' }}{{$hotel->simbolo_moneda}}{{ number_format($diferencia, 2) }}
                                                </span>
                                                <h5 class="description-header">Diferencia</h5>
                                                <span class="description-text">{{ abs($diferencia) > 0.01 ? ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE') : 'EXACTO' }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="description-block">
                                                <span class="description-percentage text-info">
                                                    <i class="fas fa-percentage"></i>
                                                </span>
                                                <h5 class="description-header">{{ $caja->saldo_actual > 0 ? number_format((1 - abs($diferencia) / $caja->saldo_actual) * 100, 1) : '100.0' }}%</h5>
                                                <span class="description-text">PRECISIÓN</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha Apertura</span>
                            <span class="info-box-number">{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha Cierre</span>
                            <span
                                class="info-box-number">{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'Caja Abierta' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @if ($caja->observaciones_apertura)
                <div class="row">
                    <div class="col-md-12">
                        <div class="callout callout-info">
                            <h5>Observaciones de Apertura</h5>
                            <p>{{ $caja->observaciones_apertura }}</p>
                        </div>
                    </div>
                </div>
            @endif
            @if ($caja->observaciones_cierre)
                <div class="row">
                    <div class="col-md-12">
                        <div class="callout callout-warning">
                            <h5>Observaciones de Cierre</h5>
                            <p>{{ $caja->observaciones_cierre }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Movimientos de Caja</h3>
            @if ($caja->estado)
                <div class="card-tools">
                    <a href="{{ route('cajas.movimientos.create', $caja) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Movimiento
                    </a>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $movimiento)
                            <tr>
                                <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $movimiento->tipo == 'ingreso' ? 'success' : 'danger' }}">
                                        {{ ucfirst($movimiento->tipo) }}
                                    </span>
                                </td>
                                <td>{{ $movimiento->concepto }}</td>
                                <td>{{$hotel->simbolo_moneda}}{{ number_format($movimiento->monto, 2) }}</td>
                                <td>{{ $movimiento->user->name }}</td>
                                <td>{{ $movimiento->descripcion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $movimientos->links() }}
        </div>
    </div>
    
    {{-- Modal de Cierre Administrativo --}}
    @if (Auth::user()->hasRole(['Administrador', 'Super Admin']) && $caja->estado)
    <div class="modal fade" id="modalCierreAdministrativo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">
                        <i class="fas fa-user-lock"></i> Cierre Administrativo de Caja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('cajas.update', $caja) }}" method="POST" id="formCierreAdmin">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Atención:</strong> Está a punto de cerrar administrativamente la caja de <strong>{{ $caja->user->name }}</strong>.
                            Este proceso debe justificarse adecuadamente.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Usuario de la Caja:</label>
                                    <input type="text" class="form-control" value="{{ $caja->user->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Turno:</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($caja->turno) }}" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Saldo Esperado:</label>
                                    <input type="text" class="form-control" value="{{$hotel->simbolo_moneda}}{{ number_format($caja->saldo_actual, 2) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="saldo_final_admin">Saldo Final Real: <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{$hotel->simbolo_moneda}}</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" id="saldo_final_admin" 
                                               name="saldo_final" required value="{{ $caja->saldo_actual }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="justificacion_admin">Justificación del Cierre Administrativo: <span class="text-danger">*</span></label>
                            <textarea class="form-control border-danger" id="justificacion_admin" name="justificacion_admin" 
                                      rows="4" required placeholder="Explique detalladamente la razón por la cual está cerrando esta caja administrativamente..."></textarea>
                            <small class="text-muted">
                                Ejemplos: "Caja del día anterior no cerrada", "Usuario no disponible para cerrar", "Cambio de turno sin cierre", etc.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="observaciones_cierre">Observaciones Adicionales:</label>
                            <textarea class="form-control" id="observaciones_cierre" name="observaciones_cierre" 
                                      rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-lock"></i> Confirmar Cierre Administrativo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@stop

@section('js')
<script>
    function mostrarCierreAdministrativo() {
        $('#modalCierreAdministrativo').modal('show');
    }
    
    // Validación del formulario de cierre administrativo
    $('#formCierreAdmin').on('submit', function(e) {
        e.preventDefault();
        
        const justificacion = $('#justificacion_admin').val();
        if (justificacion.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La justificación debe tener al menos 10 caracteres'
            });
            return false;
        }
        
        // Guardar referencia al formulario
        const form = this;
        
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Está a punto de cerrar administrativamente esta caja. Esta acción quedará registrada.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Cerrando caja administrativamente',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar el formulario usando la referencia guardada
                form.submit();
            }
        });
    });
</script>
@endsection
