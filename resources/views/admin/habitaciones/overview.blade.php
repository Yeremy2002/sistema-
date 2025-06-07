@foreach ($habitaciones as $habitacion)
    <div class="col-md-3">
        <div class="card {{ $habitacion->estado_color }}">
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
            <div class="card-body">
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
                        <span class="info-box-number">{{ $habitacion->estado }}</span>
                    </div>
                </div>

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-tag"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Precio</span>
                        <span class="info-box-number">${{ number_format($habitacion->precio, 2) }}</span>
                    </div>
                </div>

                <div class="btn-group w-100">
                    @if ($habitacion->estado === 'Disponible')
                        <a href="{{ route('reservas.create', ['habitacion_id' => $habitacion->id]) }}"
                            class="btn btn-success">
                            <i class="fas fa-calendar-plus"></i> Reservar
                        </a>
                        <a href="{{ route('habitaciones.checkin', $habitacion->id) }}" class="btn btn-primary">
                            <i class="fas fa-door-open"></i> Check-in
                        </a>
                        <button type="button" class="btn btn-warning"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Limpieza')">
                            <i class="fas fa-broom"></i> Limpieza
                        </button>
                        <button type="button" class="btn btn-secondary"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Mantenimiento')">
                            <i class="fas fa-tools"></i> Mantenimiento
                        </button>
                    @elseif($habitacion->estado === 'Ocupada')
                        @if ($habitacion->reservaActiva)
                            <a href="{{ route('reservas.checkout', $habitacion->reservaActiva->id) }}"
                                class="btn btn-warning">
                                <i class="fas fa-door-closed"></i> Check-out
                            </a>
                        @endif
                        <a href="{{ route('habitaciones.show', $habitacion->id) }}" class="btn btn-info">
                            <i class="fas fa-info-circle"></i> Detalles
                        </a>
                    @elseif($habitacion->estado === 'Limpieza')
                        <button type="button" class="btn btn-success"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Disponible')">
                            <i class="fas fa-check"></i> Finalizar Limpieza
                        </button>
                        <button type="button" class="btn btn-secondary"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Mantenimiento')">
                            <i class="fas fa-tools"></i> Mantenimiento
                        </button>
                    @elseif($habitacion->estado === 'Mantenimiento')
                        <button type="button" class="btn btn-warning"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Limpieza')">
                            <i class="fas fa-broom"></i> Limpieza
                        </button>
                        <button type="button" class="btn btn-success"
                            onclick="cambiarEstado({{ $habitacion->id }}, 'Disponible')">
                            <i class="fas fa-check"></i> Finalizar Mantenimiento
                        </button>
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
    </script>
@endpush
