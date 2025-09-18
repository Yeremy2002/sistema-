@foreach ($habitaciones as $habitacion)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 d-flex align-items-stretch">
        <div class="card w-100 {{ $habitacion->estado_color }}" style="min-width: 0;">
            <div class="card-header">
                <h3 class="card-title">
                    Habitación {{ $habitacion->numero }}
                    <small class="ml-2">{{ $habitacion->categoria->nombre }}</small>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    @if ($habitacion->imagenes->isNotEmpty())
                        <img src="{{ asset('storage/' . $habitacion->imagenes->first()->ruta) }}"
                            alt="Habitación {{ $habitacion->numero }}" class="img-fluid rounded"
                            style="max-height: 150px; width: 100%; object-fit: cover;">
                    @else
                        <div class="bg-light rounded"
                            style="height: 150px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-bed fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-{{ $habitacion->estado_color }}">
                        <i class="fas fa-{{ $habitacion->estado_icono }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number">
                            @switch($habitacion->estado)
                                @case('Reservada-Pendiente')
                                    <span class="badge badge-warning">Reservada-Pendiente</span>
                                @break

                                @case('Reservada-Confirmada')
                                    <span class="badge badge-info">Reservada-Confirmada</span>
                                @break

                                @default
                                    {{ $habitacion->estado }}
                            @endswitch
                        </span>
                    </div>
                </div>

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-tag"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Precio</span>
                        <span class="info-box-number">{{ $hotel->simbolo_moneda ?? 'Q.' }}{{ number_format($habitacion->precio, 2) }}</span>
                    </div>
                </div>

                <div class="btn-group w-100 flex-wrap">
                    @if ($habitacion->estado === 'Disponible')
                        <a href="{{ route('reservas.create', ['habitacion_id' => $habitacion->id]) }}"
                            class="btn btn-success mb-1 w-100">
                            <i class="fas fa-calendar-plus"></i> Reservar
                        </a>
                        <a href="{{ route('habitaciones.checkin', $habitacion->id) }}"
                            class="btn btn-primary mb-1 w-100">
                            <i class="fas fa-door-open"></i> Check-in
                        </a>
                        <button type="button" class="btn btn-warning mb-1 w-100"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Limpieza')">
                            <i class="fas fa-broom"></i> Limpieza
                        </button>
                        <button type="button" class="btn btn-secondary mb-1 w-100"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Mantenimiento')">
                            <i class="fas fa-tools"></i> Mantenimiento
                        </button>
                    @elseif($habitacion->estado === 'Limpieza')
                        <button type="button" class="btn btn-success mb-1 w-100"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Disponible')">
                            <i class="fas fa-check"></i> Finalizar Limpieza
                        </button>
                        <button type="button" class="btn btn-secondary mb-1 w-100"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Mantenimiento')">
                            <i class="fas fa-tools"></i> Mantenimiento
                        </button>
                    @elseif($habitacion->estado === 'Mantenimiento')
                        <button type="button" class="btn btn-warning mb-1 w-100"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Limpieza')">
                            <i class="fas fa-broom"></i> Limpieza
                        </button>
                        <button type="button" class="btn btn-success mb-1 w-100"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Disponible')">
                            <i class="fas fa-check"></i> Finalizar Mantenimiento
                        </button>
                    @elseif($habitacion->estado === 'Reservada-Confirmada')
                        @php
                            $reservaConfirmada = $habitacion->reservas()->whereIn('estado', ['Reservada-Confirmada', 'Pendiente'])->first();
                        @endphp
                        @if($reservaConfirmada)
                            <a href="{{ route('reservas.checkin', $reservaConfirmada->id) }}"
                                class="btn btn-primary mb-1 w-100">
                                <i class="fas fa-door-open"></i> Check-in
                            </a>
                        @else
                            <a href="{{ route('habitaciones.checkin', $habitacion->id) }}"
                                class="btn btn-primary mb-1 w-100">
                                <i class="fas fa-door-open"></i> Check-in
                            </a>
                        @endif
                    @elseif($habitacion->estado === 'Reservada-Pendiente')
                        @php
                            $reservaPendiente = $habitacion->reservas()->whereIn('estado', ['Reservada-Pendiente', 'Pendiente de Confirmación'])->first();
                        @endphp
                        @if($reservaPendiente)
                            <form action="{{ route('reservas.confirmar', $reservaPendiente->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success mb-1 w-100">
                                    <i class="fas fa-check"></i> Confirmar
                                </button>
                            </form>
                            <a href="{{ route('reservas.checkin', $reservaPendiente->id) }}"
                                class="btn btn-primary mb-1 w-100">
                                <i class="fas fa-door-open"></i> Check-in
                            </a>
                        @else
                            <a href="{{ route('habitaciones.checkin', $habitacion->id) }}"
                                class="btn btn-primary mb-1 w-100">
                                <i class="fas fa-door-open"></i> Check-in
                            </a>
                        @endif
                    @elseif($habitacion->estado === 'Ocupada')
                        @if($habitacion->reservaActiva && $habitacion->reservaActiva->id)
                            <a href="{{ route('reservas.checkout', $habitacion->reservaActiva->id) }}"
                                class="btn btn-danger mb-1 w-100">
                                <i class="fas fa-door-closed"></i> Check-out
                            </a>
                        @else
                            <!-- Estado inconsistente: habitación ocupada sin reserva activa -->
                            <button type="button" class="btn btn-warning mb-1 w-100"
                                onclick="corregirEstadoHabitacion({{ $habitacion->id }})">
                                <i class="fas fa-exclamation-triangle"></i> Corregir Estado
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
    <script>
        function cambiarEstado(habitacionId, nuevoEstado) {
            if (confirm('¿Está seguro de cambiar el estado de la habitación a ' + nuevoEstado + '?')) {
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
                            if (window.calendar) {
                                window.calendar.refetchEvents();
                            }
                            // Pequeño delay para que el calendario se actualice antes de recargar la lista
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            alert('Error al cambiar el estado: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cambiar el estado de la habitación');
                    });
            }
        }
        
        function corregirEstadoHabitacion(habitacionId) {
            if (confirm('Esta habitación está marcada como "Ocupada" pero no tiene una reserva activa. ¿Desea cambiar el estado a "Disponible"?')) {
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
                            alert('Estado corregido exitosamente.');
                            if (window.calendar) {
                                window.calendar.refetchEvents();
                            }
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            alert('Error al corregir el estado: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al corregir el estado de la habitación');
                    });
            }
        }
    </script>
@endpush

@push('css')
    <style>
        @media (max-width: 576px) {

            .card .info-box,
            .card .btn-group {
                flex-direction: column !important;
            }

            .card .btn {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }

            .card .info-box {
                margin-bottom: 0.5rem !important;
            }
        }
    </style>
@endpush
