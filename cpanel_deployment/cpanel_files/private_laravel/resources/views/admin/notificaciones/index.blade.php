@extends('adminlte::page')

@section('title', 'Notificaciones')

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <h1>Notificaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Todas las Notificaciones</h3>
            @if($notifications->count() > 0)
                <div class="card-tools">
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i> Marcar todas como leídas
                        </button>
                    </form>
                </div>
            @endif
        </div>
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="50px"></th>
                                <th>Tipo</th>
                                <th>Título</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th width="100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                @php
                                    $data = $notification->data;
                                    $tipo = $data['tipo'] ?? $data['type'] ?? 'general';
                                    $titulo = $data['habitacion'] ?? $data['title'] ?? 'N/A';
                                    $mensaje = $data['mensaje'] ?? $data['message'] ?? 'Notificación';
                                    $url = $data['url'] ?? $data['action_url'] ?? '#';
                                    $isUnread = is_null($notification->read_at);
                                @endphp
                                <tr class="{{ $isUnread ? 'table-warning' : '' }}">
                                    <td>
                                        @if ($tipo === 'limpieza')
                                            <i class="fas fa-broom text-warning"></i>
                                        @elseif ($tipo === 'mantenimiento')
                                            <i class="fas fa-tools text-info"></i>
                                        @elseif ($tipo === 'reserva_pendiente')
                                            <i class="fas fa-calendar-check text-primary"></i>
                                        @elseif ($tipo === 'recordatorio_cierre_caja')
                                            <i class="fas fa-cash-register text-danger"></i>
                                        @else
                                            <i class="fas fa-bell text-secondary"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $isUnread ? 'warning' : 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $tipo)) }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $titulo }}</strong></td>
                                    <td>{{ Str::limit($mensaje, 80) }}</td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($isUnread)
                                            <span class="badge badge-warning">No leída</span>
                                        @else
                                            <span class="badge badge-success">Leída</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($url !== '#')
                                            <a href="{{ $url }}" class="btn btn-sm btn-primary" 
                                               onclick="markAsRead('{{ $notification->id }}')">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        @if($isUnread)
                                            <form action="{{ route('notifications.read', $notification->id) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Marcar como leída">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-4">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay notificaciones</h5>
                    <p class="text-muted">Cuando recibas notificaciones aparecerán aquí.</p>
                </div>
            @endif
        </div>
        @if($notifications->count() > 0)
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@stop

@section('js')
    <script>
        function markAsRead(notificationId) {
            // Esta función se puede usar para marcar una notificación como leída vía AJAX
            // antes de redirigir, si se desea
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        }
    </script>
@stop
