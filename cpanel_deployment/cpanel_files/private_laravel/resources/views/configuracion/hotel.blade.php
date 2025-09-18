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

                <h4 class="mt-4 mb-3">Horarios de Check-in y Check-out</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="checkin_hora_inicio">Hora de Check-in</label>
                            <input type="time" class="form-control @error('checkin_hora_inicio') is-invalid @enderror"
                                id="checkin_hora_inicio" name="checkin_hora_inicio"
                                value="{{ old('checkin_hora_inicio', $hotel->checkin_hora_inicio ? $hotel->checkin_hora_inicio->format('H:i') : '14:00') }}" required>
                            @error('checkin_hora_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Hora estándar para hacer check-in.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="checkin_hora_anticipado">Hora Check-in Anticipado</label>
                            <input type="time" class="form-control @error('checkin_hora_anticipado') is-invalid @enderror"
                                id="checkin_hora_anticipado" name="checkin_hora_anticipado"
                                value="{{ old('checkin_hora_anticipado', $hotel->checkin_hora_anticipado ? $hotel->checkin_hora_anticipado->format('H:i') : '12:00') }}" required>
                            @error('checkin_hora_anticipado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Hora mínima para check-in anticipado con confirmación.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="checkout_hora_inicio">Hora de Check-out</label>
                            <input type="time" class="form-control @error('checkout_hora_inicio') is-invalid @enderror"
                                id="checkout_hora_inicio" name="checkout_hora_inicio"
                                value="{{ old('checkout_hora_inicio', $hotel->checkout_hora_inicio ? $hotel->checkout_hora_inicio->format('H:i') : '12:30') }}" required>
                            @error('checkout_hora_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Hora de inicio del check-out.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="checkout_hora_fin">Hora Límite Check-out</label>
                            <input type="time" class="form-control @error('checkout_hora_fin') is-invalid @enderror"
                                id="checkout_hora_fin" name="checkout_hora_fin"
                                value="{{ old('checkout_hora_fin', $hotel->checkout_hora_fin ? $hotel->checkout_hora_fin->format('H:i') : '13:00') }}" required>
                            @error('checkout_hora_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Hora límite para hacer check-out.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="permitir_checkin_anticipado" 
                               name="permitir_checkin_anticipado" value="1"
                               {{ old('permitir_checkin_anticipado', $hotel->permitir_checkin_anticipado ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="permitir_checkin_anticipado">
                            Permitir check-in anticipado con confirmación especial
                        </label>
                    </div>
                    <small class="form-text text-muted">Si está habilitado, permite hacer check-in antes de la hora estándar con confirmación del recepcionista.</small>
                </div>

                <h4 class="mt-4 mb-3">Configuración de Reservas Vencidas</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reservas_vencidas_horas">Horas para marcar como vencida</label>
                            <input type="number" class="form-control @error('reservas_vencidas_horas') is-invalid @enderror"
                                id="reservas_vencidas_horas" name="reservas_vencidas_horas" min="1" max="168"
                                value="{{ old('reservas_vencidas_horas', $hotel->reservas_vencidas_horas ?? 24) }}" required>
                            @error('reservas_vencidas_horas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Horas después de la fecha de entrada para marcar una reserva como vencida.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="scheduler_frecuencia">Frecuencia de verificación</label>
                            <select class="form-control @error('scheduler_frecuencia') is-invalid @enderror"
                                id="scheduler_frecuencia" name="scheduler_frecuencia" required>
                                <option value="12h" {{ old('scheduler_frecuencia', $hotel->scheduler_frecuencia ?? '24h') == '12h' ? 'selected' : '' }}>Cada 12 horas</option>
                                <option value="24h" {{ old('scheduler_frecuencia', $hotel->scheduler_frecuencia ?? '24h') == '24h' ? 'selected' : '' }}>Cada 24 horas</option>
                                <option value="48h" {{ old('scheduler_frecuencia', $hotel->scheduler_frecuencia ?? '24h') == '48h' ? 'selected' : '' }}>Cada 48 horas</option>
                                <option value="72h" {{ old('scheduler_frecuencia', $hotel->scheduler_frecuencia ?? '24h') == '72h' ? 'selected' : '' }}>Cada 72 horas</option>
                            </select>
                            @error('scheduler_frecuencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Frecuencia con la que se ejecuta automáticamente la verificación de reservas vencidas.</small>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Configuración de Notificaciones</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notificacion_intervalo_segundos">Intervalo de actualización (segundos)</label>
                            <input type="number" class="form-control @error('notificacion_intervalo_segundos') is-invalid @enderror"
                                id="notificacion_intervalo_segundos" name="notificacion_intervalo_segundos" min="10" max="300"
                                value="{{ old('notificacion_intervalo_segundos', $hotel->notificacion_intervalo_segundos ?? 30) }}" required>
                            @error('notificacion_intervalo_segundos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Frecuencia con la que se actualizan las notificaciones automáticamente (10-300 segundos).</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notificacion_badge_color">Color del badge de notificaciones</label>
                            <select class="form-control @error('notificacion_badge_color') is-invalid @enderror"
                                id="notificacion_badge_color" name="notificacion_badge_color" required>
                                <option value="primary" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'primary' ? 'selected' : '' }}>Azul (Primary)</option>
                                <option value="secondary" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'secondary' ? 'selected' : '' }}>Gris (Secondary)</option>
                                <option value="success" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'success' ? 'selected' : '' }}>Verde (Success)</option>
                                <option value="danger" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'danger' ? 'selected' : '' }}>Rojo (Danger)</option>
                                <option value="warning" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'warning' ? 'selected' : '' }}>Amarillo (Warning)</option>
                                <option value="info" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'info' ? 'selected' : '' }}>Celeste (Info)</option>
                                <option value="light" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'light' ? 'selected' : '' }}>Claro (Light)</option>
                                <option value="dark" {{ old('notificacion_badge_color', $hotel->notificacion_badge_color ?? 'danger') == 'dark' ? 'selected' : '' }}>Oscuro (Dark)</option>
                            </select>
                            @error('notificacion_badge_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Color del badge que muestra el número de notificaciones pendientes.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notificacion_activa" 
                               name="notificacion_activa" value="1"
                               {{ old('notificacion_activa', $hotel->notificacion_activa ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notificacion_activa">
                            Activar notificaciones automáticas
                        </label>
                    </div>
                    <small class="form-text text-muted">Si está habilitado, las notificaciones se actualizarán automáticamente en el intervalo configurado.</small>
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
