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
                        mensaje = `Habitación: ${info.event.extendedProps.habitacion_numero}\n` +
                            `Cliente: ${info.event.extendedProps.cliente_nombre}\n` +
                            `Estado: ${info.event.extendedProps.estado}\n` +
                            `Precio: $${info.event.extendedProps.precio}\n` +
                            `Total: $${info.event.extendedProps.total}`;
                    } else {
                        mensaje = `Habitación: ${info.event.extendedProps.habitacion_numero}\n` +
                            `Estado: ${info.event.extendedProps.estado}`;
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
    </script>
@stop
