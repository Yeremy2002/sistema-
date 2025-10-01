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

        /* Estilos para modal de reserva SweetAlert2 */
        .swal-wide {
            width: 600px !important;
            max-width: 90vw !important;
        }

        .reservation-details .row,
        .room-status .row {
            margin-bottom: 10px;
        }

        .reservation-details strong,
        .room-status strong {
            color: #495057;
            display: inline-block;
            min-width: 120px;
        }

        .swal-actions {
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
        }

        .swal-actions .btn {
            margin: 5px;
            min-width: 140px;
        }

        .badge-lg {
            padding: 8px 12px;
            font-size: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .swal-wide {
                width: 95vw !important;
            }

            .swal-actions .btn {
                width: 100%;
                margin: 5px 0;
                min-width: auto;
            }

            .reservation-details .col-md-6,
            .room-status .col-md-6 {
                margin-bottom: 10px;
            }
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
                    showReservationModal(info.event);
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

        // Función para mostrar modal de reserva con SweetAlert2
        function showReservationModal(event) {
            let html = '';
            let title = '';
            let actions = [];

            if (event.extendedProps.tipo === 'reserva') {
                // Es una reserva
                title = `Reserva - Habitación ${event.extendedProps.habitacion_numero}`;
                html = `
                    <div class="reservation-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>🏠 Habitación:</strong> ${event.extendedProps.habitacion_numero}
                            </div>
                            <div class="col-md-6">
                                <strong>👤 Cliente:</strong> ${event.extendedProps.cliente_nombre || 'N/A'}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>📅 Entrada:</strong> ${event.start ? event.start.toLocaleDateString() : 'N/A'}
                            </div>
                            <div class="col-md-6">
                                <strong>📅 Salida:</strong> ${event.end ? event.end.toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>📊 Estado:</strong>
                                <span class="badge badge-${getStatusBadgeClass(event.extendedProps.estado)}">${event.extendedProps.estado}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>💰 Total:</strong> ${window.simboloMoneda || 'Q.'}${event.extendedProps.total || '0'}
                            </div>
                        </div>
                        ${event.extendedProps.cliente_telefono ? `
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>📞 Teléfono:</strong> ${event.extendedProps.cliente_telefono}
                            </div>
                        </div>` : ''}
                    </div>
                `;

                // Definir acciones según el estado
                const estado = event.extendedProps.estado;
                const reservaId = event.extendedProps.reserva_id;

                if (estado === 'Pendiente de Confirmación') {
                    actions.push({
                        text: '✅ Confirmar Reserva',
                        value: 'confirmar',
                        className: 'btn btn-success'
                    });
                    actions.push({
                        text: '📞 Llamar Cliente',
                        value: 'llamar',
                        className: 'btn btn-info'
                    });
                } else if (estado === 'Pendiente') {
                    actions.push({
                        text: '🔑 Check-in',
                        value: 'checkin',
                        className: 'btn btn-primary'
                    });
                } else if (estado === 'Check-in') {
                    actions.push({
                        text: '🚪 Check-out',
                        value: 'checkout',
                        className: 'btn btn-danger'
                    });
                }

                // Agregar acción común de ver detalles
                actions.push({
                    text: '👁️ Ver Detalles',
                    value: 'detalles',
                    className: 'btn btn-secondary'
                });

            } else {
                // Es un estado de habitación
                title = `Estado Habitación ${event.extendedProps.habitacion_numero}`;
                html = `
                    <div class="room-status">
                        <div class="text-center mb-3">
                            <h4>🏠 Habitación ${event.extendedProps.habitacion_numero}</h4>
                            <span class="badge badge-lg badge-${getStatusBadgeClass(event.extendedProps.estado)}">${event.extendedProps.estado}</span>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <strong>📅 Fecha:</strong> ${event.start ? event.start.toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                    </div>
                `;

                actions.push({
                    text: '🏠 Gestionar Habitación',
                    value: 'gestionar',
                    className: 'btn btn-primary'
                });
            }

            // Mostrar SweetAlert2 con botones dinámicos
            Swal.fire({
                title: title,
                html: html,
                icon: 'info',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-wide',
                    htmlContainer: 'text-left'
                },
                didOpen: () => {
                    // Agregar botones de acción dinámicamente
                    const actionsHtml = actions.map(action =>
                        `<button type="button" class="${action.className} mr-2 mb-2" onclick="handleReservationAction('${action.value}', '${event.extendedProps.reserva_id || event.extendedProps.habitacion_numero}')">${action.text}</button>`
                    ).join('');

                    if (actionsHtml) {
                        const actionsDiv = document.createElement('div');
                        actionsDiv.className = 'swal-actions mt-3 text-center';
                        actionsDiv.innerHTML = actionsHtml;
                        document.querySelector('.swal2-html-container').appendChild(actionsDiv);
                    }
                }
            });
        }

        // Función para obtener la clase CSS del badge según el estado
        function getStatusBadgeClass(estado) {
            switch(estado) {
                case 'Pendiente de Confirmación':
                    return 'warning';
                case 'Pendiente':
                    return 'info';
                case 'Check-in':
                    return 'success';
                case 'Check-out':
                    return 'secondary';
                case 'Cancelada':
                    return 'danger';
                case 'Disponible':
                    return 'success';
                case 'Limpieza':
                    return 'warning';
                case 'Mantenimiento':
                    return 'danger';
                case 'Ocupada':
                    return 'primary';
                default:
                    return 'secondary';
            }
        }

        // Función para manejar las acciones de la reserva
        function handleReservationAction(action, id) {
            switch(action) {
                case 'confirmar':
                    if (confirm('¿Confirmar esta reserva?')) {
                        window.location.href = `/reservas/${id}/confirmar`;
                    }
                    break;
                case 'llamar':
                    Swal.fire({
                        title: 'Llamar al Cliente',
                        text: 'Recuerda confirmar los detalles de la reserva y el método de pago.',
                        icon: 'info',
                        confirmButtonText: 'Entendido'
                    });
                    break;
                case 'checkin':
                    window.location.href = `/reservas/${id}/checkin`;
                    break;
                case 'checkout':
                    window.location.href = `/reservas/${id}/checkout`;
                    break;
                case 'detalles':
                    window.location.href = `/reservas/${id}`;
                    break;
                case 'gestionar':
                    window.location.href = `/habitaciones`;
                    break;
                default:
                    console.log('Acción no reconocida:', action);
            }
            Swal.close();
        }
        
        // Función para cambiar el estado de una habitación
        function cambiarEstado(habitacionId, nuevoEstado) {
            Swal.fire({
                title: 'Cambiar Estado',
                text: `¿Está seguro de cambiar el estado de la habitación a ${nuevoEstado}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Cambiando estado de la habitación',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`/habitaciones/${habitacionId}/cambiar-estado`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                estado: nuevoEstado
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: 'Estado cambiado correctamente',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    if (window.calendar && typeof window.calendar.refetchEvents === 'function') {
                                        window.calendar.refetchEvents();
                                    }
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error al cambiar el estado: ' + data.message,
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cambiar el estado de la habitación',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                }
            });
        }
        
        // Función para corregir el estado de una habitación inconsistente
        function corregirEstadoHabitacion(habitacionId) {
            Swal.fire({
                title: 'Estado Inconsistente',
                text: 'Esta habitación está marcada como "Ocupada" pero no tiene una reserva activa. ¿Desea cambiar el estado a "Disponible"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, corregir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Corrigiendo estado de la habitación',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`/habitaciones/${habitacionId}/corregir-estado`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: 'Estado corregido exitosamente',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    if (window.calendar && typeof window.calendar.refetchEvents === 'function') {
                                        window.calendar.refetchEvents();
                                    }
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error al corregir el estado: ' + data.message,
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al corregir el estado de la habitación',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                }
            });
        }
    </script>
@stop
