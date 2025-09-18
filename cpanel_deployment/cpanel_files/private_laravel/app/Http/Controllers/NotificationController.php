<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Obtener el conteo de notificaciones para AJAX
     */
    public function getCount()
    {
        if (!Auth::check()) {
            return response()->json([
                'label' => 0,
                'label_color' => 'secondary',
                'icon_color' => 'muted',
                'dropdown' => ''
            ]);
        }

        $user = Auth::user();
        $hotel = Hotel::getInfo();
        $unreadCount = $user->unreadNotifications->count();
        
        // Renderizar el dropdown con las notificaciones
        $dropdownHtml = view('vendor.adminlte.partials.navbar.notifications', [
            'notifications' => $user->unreadNotifications->take(10) // Últimas 10
        ])->render();

        return response()->json([
            'label' => $unreadCount,
            'label_color' => $unreadCount > 0 ? ($hotel->notificacion_badge_color ?? 'danger') : 'secondary',
            'icon_color' => $unreadCount > 0 ? 'warning' : 'muted',
            'dropdown' => $dropdownHtml
        ]);
    }

    /**
     * Mostrar todas las notificaciones
     */
    public function show()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        
        return view('admin.notificaciones.index', compact('notifications'));
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }
}
