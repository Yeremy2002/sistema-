<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Caja;
use App\Models\User;
use App\Notifications\RecordatorioCierreCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarCajaAbierta
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (Auth::check()) {
            $user = Auth::user();
            $ahora = Carbon::now('America/Guatemala');
            
            // Solo aplicar la lógica de caja a Recepcionistas y Administradores
            if ($user->hasRole(['Recepcionista', 'Administrador'])) {
                // Determinar el turno actual
                $turnoActual = $this->determinarTurno($ahora);
                
                // Verificar si el usuario tiene una caja abierta
                $cajaAbierta = Caja::where('user_id', $user->id)
                    ->where('estado', true)
                    ->first();

                // Verificar si hay una caja sin cerrar del día anterior
                $cajaAnterior = Caja::where('user_id', $user->id)
                    ->where('estado', true)
                    ->where('fecha_apertura', '<', $ahora->startOfDay())
                    ->first();

                if ($cajaAnterior) {
                    // Enviar notificaciones si es la primera vez que se detecta
                    if (!session()->has('caja_anterior_notificada_' . $cajaAnterior->id)) {
                        $this->notificarCajaPendiente($user, $cajaAnterior, 'dia_anterior');
                        $this->notificarAdministradores($user, $cajaAnterior, 'dia_anterior');
                        session()->put('caja_anterior_notificada_' . $cajaAnterior->id, true);
                    }
                    
                    return redirect()->route('cajas.edit', $cajaAnterior)
                        ->with('error', '⚠️ ATENCIÓN: Tiene una caja abierta del día ' . $cajaAnterior->fecha_apertura->format('d/m/Y') . '. Debe cerrarla inmediatamente o contactar al administrador.');
                }
                
                // Verificar si la caja abierta es del turno incorrecto
                if ($cajaAbierta && $cajaAbierta->turno !== $turnoActual && 
                    $cajaAbierta->fecha_apertura->format('Y-m-d') == $ahora->format('Y-m-d')) {
                    
                    // Enviar notificaciones si es la primera vez que se detecta
                    if (!session()->has('caja_turno_incorrecto_' . $cajaAbierta->id)) {
                        $this->notificarCajaPendiente($user, $cajaAbierta, 'turno_incorrecto');
                        $this->notificarAdministradores($user, $cajaAbierta, 'turno_incorrecto');
                        session()->put('caja_turno_incorrecto_' . $cajaAbierta->id, true);
                    }
                    
                    // Mensaje de advertencia pero no bloquear
                    session()->flash('warning', "⚠️ IMPORTANTE: Su caja fue abierta en el turno {$cajaAbierta->turno} pero ahora es turno {$turnoActual}. Considere cerrarla y abrir una nueva caja para el turno actual.");
                }

                // Si estamos en una ruta relacionada con reservas o cajas y no tiene caja abierta
                $requireCaja = $this->requiresCaja($request);

                if ($requireCaja && !$cajaAbierta) {
                    // Para rutas AJAX, devolver una respuesta JSON con el mensaje de alerta
                    if ($request->ajax()) {
                        return response()->json([
                            'error' => true,
                            'require_caja' => true,
                            'message' => '¡DEBE ABRIR SU CAJA PRIMERO!',
                            'redirect' => route('cajas.create')
                        ], 403);
                    }
                    
                    // Para peticiones normales, guardar la URL de retorno y mostrar alerta
                    session()->put('url.intended', $request->url());
                    session()->put('alerta_caja_requerida', true);
                    
                    // Si no hay caja abierta hoy, redirigir a crear una nueva
                    return redirect()->route('cajas.create')
                        ->with('alerta_caja_requerida', true)
                        ->with('warning', '⚠️ ATENCIÓN: Debe abrir una caja antes de realizar operaciones financieras.');
                }

                // Si hay una caja abierta, verificar que sea del día actual
                if ($cajaAbierta && $cajaAbierta->fecha_apertura->startOfDay()->lt(now()->startOfDay())) {
                    return redirect()->route('cajas.edit', $cajaAbierta)
                        ->with('warning', 'Su caja actual fue abierta en una fecha anterior. Por favor, realice el cierre antes de continuar.');
                }
            }
        }
        return $next($request);
    }

    /**
     * Determina si la ruta actual requiere una caja abierta
     */
    private function requiresCaja(Request $request): bool
    {
        // Rutas que requieren una caja abierta para movimientos financieros
        $routes = [
            // Reservas con anticipos o pagos
            'reservas.store',
            'reservas.update',
            'reservas.confirmar',
            'reservas.storeCheckout',
            
            // Check-in y Check-out (pueden involucrar pagos)
            'habitaciones.checkin',
            'habitaciones.checkin.store',
            'reservas.checkin',
            'reservas.checkout',
            
            // Movimientos directos de caja
            'cajas.movimientos.store',
            'cajas.storeMovimiento',
            
            // Gastos de mantenimiento
            'mantenimiento.reparacion.store',
            'mantenimiento.reparacion.update',
            
            // Otras rutas que involucren gastos o ingresos
            'gastos.store',
            'gastos.update',
            'ingresos.store',
            'ingresos.update'
        ];

        $currentRoute = $request->route() ? $request->route()->getName() : null;
        
        if (!$currentRoute) {
            return false;
        }

        // Verificar si la ruta actual está en la lista de rutas que requieren caja
        // También verificar si es una petición POST/PUT/PATCH con parámetros financieros
        $requiresByRoute = in_array($currentRoute, $routes);
        
        // Detectar si hay movimientos financieros en los parámetros
        $hasFinancialParams = false;
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            // Verificar si hay parámetros que indiquen transacciones financieras
            $financialParams = ['adelanto', 'anticipo', 'pago', 'monto', 'pago_efectivo', 'pago_tarjeta', 'pago_transferencia', 'total_pagar'];
            foreach ($financialParams as $param) {
                if ($request->has($param) && $request->get($param) > 0) {
                    $hasFinancialParams = true;
                    break;
                }
            }
        }
        
        return $requiresByRoute || $hasFinancialParams;
    }
    
    /**
     * Determinar el turno actual basado en la hora
     */
    private function determinarTurno(Carbon $fecha): string
    {
        $hora = $fecha->hour;
        // Turno diurno: 6:00 AM - 6:00 PM
        // Turno nocturno: 6:00 PM - 6:00 AM
        return ($hora >= 6 && $hora < 18) ? 'diurno' : 'nocturno';
    }
    
    /**
     * Enviar notificación al usuario sobre caja pendiente
     */
    private function notificarCajaPendiente(User $user, Caja $caja, string $tipo)
    {
        try {
            $razon = $tipo === 'dia_anterior' 
                ? 'Caja abierta desde el día ' . $caja->fecha_apertura->format('d/m/Y')
                : 'Caja del turno ' . $caja->turno . ' detectada en turno diferente';
                
            $user->notify(new RecordatorioCierreCaja($caja, $tipo === 'dia_anterior' ? 'urgente' : 'cambio_turno', $razon));
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de caja pendiente: ' . $e->getMessage());
        }
    }
    
    /**
     * Notificar a todos los administradores sobre caja pendiente
     */
    private function notificarAdministradores(User $usuario, Caja $caja, string $tipo)
    {
        try {
            $administradores = User::role(['Administrador', 'Super Admin'])->get();
            
            $razon = $tipo === 'dia_anterior' 
                ? "Usuario {$usuario->name} tiene caja pendiente del día " . $caja->fecha_apertura->format('d/m/Y')
                : "Usuario {$usuario->name} tiene caja del turno {$caja->turno} en turno incorrecto";
            
            foreach ($administradores as $admin) {
                $admin->notify(new RecordatorioCierreCaja($caja, 'supervisor', $razon));
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar administradores: ' . $e->getMessage());
        }
    }
}
