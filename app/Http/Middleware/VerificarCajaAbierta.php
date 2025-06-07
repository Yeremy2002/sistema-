<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Caja;
use Illuminate\Support\Facades\Auth;

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
            // Verificar si el usuario tiene una caja abierta
            $cajaAbierta = Caja::where('user_id', Auth::id())
                ->where('estado', true)
                ->first();

            // Si estamos en una ruta relacionada con reservas o cajas y no tiene caja abierta
            $requireCaja = $this->requiresCaja($request);

            if ($requireCaja && !$cajaAbierta) {
                return redirect()->route('cajas.create')
                    ->with('error', 'Debe abrir una caja antes de realizar operaciones. Por favor, complete el formulario para aperturar su caja.');
            }
        }

        return $next($request);
    }

    /**
     * Determina si la ruta actual requiere una caja abierta
     */
    private function requiresCaja(Request $request): bool
    {
        // Rutas que requieren una caja abierta
        $routes = [
            'reservas.store',
            'reservas.update',
            'cajas.movimientos.store',
            'reservas.checkin',
            'reservas.checkout'
        ];

        $currentRoute = $request->route()->getName();

        // Verificar si la ruta actual está en la lista de rutas que requieren caja
        return in_array($currentRoute, $routes);
    }
}
