{{-- Estilos urgentes para iconos SVG --}}
<style>
svg.w-5, svg.h-5, .w-5.h-5 {
    width: 1.25rem !important;
    height: 1.25rem !important;
    max-width: 1.25rem !important;
    max-height: 1.25rem !important;
    display: inline-block !important;
    vertical-align: middle !important;
}
svg {
    max-width: 2rem !important;
    max-height: 2rem !important;
}
.btn svg, .table svg, a svg {
    width: 1rem !important;
    height: 1rem !important;
    max-width: 1rem !important;
    max-height: 1rem !important;
}
</style>

@if (Auth::user()->unreadNotifications->count() > 0)
    @foreach (Auth::user()->unreadNotifications as $notification)
        @php
            // Determinar el tipo de notificación
            $tipo = $notification->data['tipo'] ?? ($notification->data['type'] ?? 'general');
            $mensaje =
                $notification->data['mensaje'] ??
                ($notification->data['message'] ?? ($notification->data['title'] ?? 'Notificación'));
            $titulo = $notification->data['habitacion'] ?? ($notification->data['title'] ?? 'N/A');
            $url = $notification->data['url'] ?? ($notification->data['action_url'] ?? '#');
        @endphp
        <a href="javascript:void(0)" class="dropdown-item notification-item" data-notification-id="{{ $notification->id }}"
            data-url="{{ $url }}">
            <div class="d-flex align-items-center">
                @if ($tipo === 'limpieza')
                    <i class="fas fa-broom text-warning mr-2"></i>
                @elseif ($tipo === 'mantenimiento')
                    <i class="fas fa-tools text-info mr-2"></i>
                @elseif ($tipo === 'reserva_pendiente')
                    <i class="fas fa-calendar-check text-primary mr-2"></i>
                @elseif ($tipo === 'recordatorio_cierre_caja')
                    <i class="fas fa-cash-register text-danger mr-2"></i>
                @else
                    <i class="fas fa-bell text-secondary mr-2"></i>
                @endif
                <div>
                    <span class="font-weight-bold">{{ $titulo }}</span>
                    <p class="mb-0 small">{{ $mensaje }}</p>
                </div>
            </div>
        </a>
    @endforeach
@else
    <div class="dropdown-item text-center">
        <p class="mb-0">No hay notificaciones</p>
    </div>
@endif

<script>
(function() {
    // Usar event delegation para manejar clics en notificaciones
    // Esto funciona tanto para notificaciones cargadas inicialmente como dinámicamente
    document.addEventListener('click', function(e) {
        // Verificar si el elemento clickeado es una notificación
        const notificationItem = e.target.closest('.notification-item');
        if (!notificationItem) return;
        
        e.preventDefault();
        const notificationId = notificationItem.dataset.notificationId;
        const url = notificationItem.dataset.url;
        
        // Verificar que tenemos los datos necesarios
        if (!notificationId || !url) {
            console.warn('Notification missing required data:', { notificationId, url });
            return;
        }

        // Marcar como leída y navegar
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        }).then(() => {
            // Navegar a la URL después de marcar como leída
            if (url && url !== '#' && url !== 'javascript:void(0)') {
                window.location.href = url;
            }
        }).catch((error) => {
            console.error('Error marking notification as read:', error);
            // Navegar de todos modos si la URL es válida
            if (url && url !== '#' && url !== 'javascript:void(0)') {
                window.location.href = url;
            }
        });
    });
})();
</script>
