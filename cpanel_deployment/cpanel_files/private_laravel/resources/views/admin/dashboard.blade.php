@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Calendario de Ocupación</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" id="prevBtn">
                                    <i class="fas fa-chevron-left"></i> Anterior
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn">
                                    Siguiente <i class="fas fa-chevron-right"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="todayBtn">
                                    Hoy
                                </button>
                            </div>
                            <div class="btn-group ml-2">
                                <button type="button" class="btn btn-info" id="monthBtn">Mes</button>
                                <button type="button" class="btn btn-info" id="weekBtn">Semana</button>
                                <button type="button" class="btn btn-info" id="dayBtn">Día</button>
                            </div>
                        </div>
                    </div>
                    <div id="calendar" style="min-height: 350px; max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Habitaciones</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @include('admin.habitaciones.overview', ['habitaciones' => $habitaciones])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($estadoCajas['mostrar_widget'])
    <!-- Widget de Estado de Cajas -->
    <div class="card card-{{ $estadoCajas['hay_problemas'] ? 'danger' : 'info' }} card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cash-register"></i> Estado de Cajas
                @if($estadoCajas['hay_problemas'])
                    <span class="badge badge-danger ml-2">{{ $estadoCajas['cajas_problematicas']->count() }} Problemas</span>
                @endif
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <a href="{{ route('cajas.index') }}" class="btn btn-tool" title="Ver todas las cajas">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-cash-register"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cajas Abiertas</span>
                            <span class="info-box-number">{{ $estadoCajas['total_abiertas'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $estadoCajas['hay_problemas'] ? 'danger' : 'success' }}">
                            <i class="fas fa-{{ $estadoCajas['hay_problemas'] ? 'exclamation-triangle' : 'check-circle' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Problemáticas</span>
                            <span class="info-box-number">{{ $estadoCajas['cajas_problematicas']->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-thumbs-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Normales</span>
                            <span class="info-box-number">{{ $estadoCajas['cajas_normales']->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Última Verificación</span>
                            <span class="info-box-number" style="font-size: 0.8rem;">{{ now()->format('H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($estadoCajas['cajas_problematicas']->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h5><i class="fas fa-exclamation-triangle text-danger"></i> Cajas que Requieren Atención:</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Caja</th>
                                    <th>Usuario</th>
                                    <th>Turno</th>
                                    <th>Estado</th>
                                    <th>Tiempo Abierta</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadoCajas['cajas_problematicas'] as $estado)
                                <tr>
                                    <td>#{{ $estado['caja']->id }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $estado['caja']->user->name }}</span>
                                    </td>
                                    <td>{{ ucfirst($estado['caja']->turno) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $estado['color'] }}">
                                            <i class="{{ $estado['icono'] }}"></i> {{ $estado['mensaje'] }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($estado['horas_abierta'], 1) }}h</td>
                                    <td>
                                        <a href="{{ route('cajas.edit', $estado['caja']) }}" 
                                           class="btn btn-sm btn-{{ $estado['color'] }}" 
                                           title="Cerrar caja">
                                            <i class="fas fa-lock"></i>
                                        </a>
                                        <a href="{{ route('cajas.show', $estado['caja']) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    @endif
    
    {{-- Widget de Notificaciones de Caja --}}
    @if(auth()->user()->notifications()->whereJsonContains('data->type', 'recordatorio_cierre_caja')->whereNull('read_at')->exists())
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i> Notificaciones de Caja
                        <span class="badge badge-warning ml-2">
                            {{ auth()->user()->notifications()->whereJsonContains('data->type', 'recordatorio_cierre_caja')->whereNull('read_at')->count() }}
                        </span>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="timeline" style="max-height: 300px; overflow-y: auto;">
                        @foreach(auth()->user()->notifications()->whereJsonContains('data->type', 'recordatorio_cierre_caja')->whereNull('read_at')->limit(10)->get() as $notification)
                            @php
                                $data = $notification->data; // Laravel auto-deserializa JSON
                                $severity = $data['severity'] ?? 'low';
                                $color = match($severity) {
                                    'high' => 'danger',
                                    'medium' => 'warning', 
                                    default => 'info'
                                };
                            @endphp
                            <div class="timeline-item" data-notification="{{ $notification->id }}">
                                <span class="time"><i class="fas fa-clock"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                <h3 class="timeline-header">
                                    <i class="{{ $data['icon'] ?? 'fas fa-cash-register' }} text-{{ $color }}"></i>
                                    {{ $data['title'] ?? 'Recordatorio de Caja' }}
                                </h3>
                                <div class="timeline-body">
                                    <p class="mb-1"><strong>{{ $data['message'] ?? '' }}</strong></p>
                                    @if(!empty($data['reason']))
                                        <small class="text-muted">Razón: {{ $data['reason'] }}</small>
                                    @endif
                                </div>
                                <div class="timeline-footer">
                                    @if(!empty($data['action_url']))
                                        <a href="{{ $data['action_url'] }}" class="btn btn-{{ $color }} btn-sm">
                                            <i class="fas fa-external-link-alt"></i> {{ $data['action_text'] ?? 'Ver' }}
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="markAsRead('{{ $notification->id }}')">Marcar como leída</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if(auth()->user()->notifications()->whereJsonContains('data->type', 'recordatorio_cierre_caja')->whereNull('read_at')->count() > 10)
                        <div class="text-center mt-2">
                            <small class="text-muted">Y {{ auth()->user()->notifications()->whereJsonContains('data->type', 'recordatorio_cierre_caja')->whereNull('read_at')->count() - 10 }} notificaciones más...</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if($estadoCajas['cajas_normales']->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#cajasNormales" aria-expanded="false">
                        <i class="fas fa-eye"></i> Ver Cajas Normales ({{ $estadoCajas['cajas_normales']->count() }})
                    </button>
                    <div class="collapse mt-2" id="cajasNormales">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <tbody>
                                    @foreach($estadoCajas['cajas_normales'] as $estado)
                                    <tr>
                                        <td>Caja #{{ $estado['caja']->id }}</td>
                                        <td>{{ $estado['caja']->user->name }}</td>
                                        <td>{{ ucfirst($estado['caja']->turno) }}</td>
                                        <td>{{ $estado['mensaje'] }}</td>
                                        <td>
                                            <a href="{{ route('cajas.show', $estado['caja']) }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Leyenda de Estados -->
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">Leyenda de Estados</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-bed"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ocupada</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reservada</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-broom"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Limpieza</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-tools"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Mantenimiento</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        .fc {
            font-size: 0.95rem;
        }

        #calendar {
            min-height: 350px;
            max-height: 400px;
            overflow-y: auto;
        }

        .fc-toolbar-title {
            font-size: 1.1rem;
        }

        .fc-event {
            cursor: pointer;
        }

        .fc-event-title {
            font-weight: bold;
        }

        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-info-light {
            background-color: rgba(23, 162, 184, 0.1);
        }
    </style>
@stop

@section('js')
    <script>
        window.simboloMoneda = @json($hotel->simbolo_moneda);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'es',
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                events: '{{ route('api.reservas.calendario') }}',
                eventClick: function(info) {
                    var mensaje = '';
                    if (info.event.extendedProps.tipo === 'reserva') {
                        mensaje = 'Habitación: ' + info.event.extendedProps.habitacion_numero + '\n' +
                            'Cliente: ' + info.event.extendedProps.cliente_nombre + '\n' +
                            'Estado: ' + info.event.extendedProps.estado + '\n' +
                            'Precio: ' + window.simboloMoneda + info.event.extendedProps.precio + '\n' +
                            'Total: ' + window.simboloMoneda + info.event.extendedProps.total;
                    } else {
                        mensaje = 'Habitación: ' + info.event.extendedProps.habitacion_numero + '\n' +
                            'Estado: ' + info.event.extendedProps.estado;
                    }
                    alert(mensaje);
                },
                eventDidMount: function(info) {
                    // Personalizar el estilo de los eventos según su tipo
                    if (info.event.extendedProps.tipo === 'estado') {
                        info.el.style.backgroundColor = info.event.backgroundColor;
                        info.el.style.borderColor = info.event.borderColor;
                    }
                }
            });
            calendar.render();

            // Botones de navegación
            document.getElementById('prevBtn').addEventListener('click', function() {
                calendar.prev();
            });
            document.getElementById('nextBtn').addEventListener('click', function() {
                calendar.next();
            });
            document.getElementById('todayBtn').addEventListener('click', function() {
                calendar.today();
            });

            // Botones de vista
            document.getElementById('monthBtn').addEventListener('click', function() {
                calendar.changeView('dayGridMonth');
            });
            document.getElementById('weekBtn').addEventListener('click', function() {
                calendar.changeView('timeGridWeek');
            });
            document.getElementById('dayBtn').addEventListener('click', function() {
                calendar.changeView('timeGridDay');
            });
        });
        
        // Función para marcar notificaciones como leídas
        function markAsRead(notificationId) {
            fetch('/notifications/' + notificationId + '/read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover la notificación del DOM
                    const notificationElement = document.querySelector(`[data-notification="${notificationId}"]`);
                    if (notificationElement) {
                        notificationElement.remove();
                    }
                    // Actualizar contador si existe
                    const badge = document.querySelector('.badge-warning');
                    if (badge) {
                        const currentCount = parseInt(badge.textContent);
                        if (currentCount > 1) {
                            badge.textContent = currentCount - 1;
                        } else {
                            // Ocultar todo el widget si no hay más notificaciones
                            const notificationCard = document.querySelector('.card-warning');
                            if (notificationCard) {
                                notificationCard.style.display = 'none';
                            }
                        }
                    }
                    // Mostrar mensaje de éxito
                    toastr.success('Notificación marcada como leída');
                } else {
                    toastr.error('Error al marcar la notificación como leída');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al marcar la notificación como leída');
            });
        }
    </script>
@stop
