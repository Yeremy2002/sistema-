<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservaApiController extends Controller
{
    public function calendario()
    {
        $reservas = Reserva::with(['habitacion', 'cliente'])
            ->whereIn('estado', [
                'Check-in',
                'Pendiente',
                'Pendiente de Confirmación',
                'Reservada-Pendiente',
                'Reservada-Confirmada'
            ])
            ->get()
            ->map(function ($reserva) {
                $color = match ($reserva->estado) {
                    'Check-in' => '#dc3545', // rojo
                    'Pendiente' => '#ffc107', // amarillo
                    'Pendiente de Confirmación' => '#007bff', // azul
                    'Reservada-Pendiente' => '#17a2b8', // celeste
                    'Reservada-Confirmada' => '#6610f2', // morado
                    default => '#6c757d'
                };
                $title = 'Hab. ' . $reserva->habitacion->numero . ' - ' . $reserva->estado;
                return [
                    'id' => $reserva->id,
                    'title' => $title,
                    'start' => $reserva->fecha_entrada->format('Y-m-d\TH:i:s'),
                    'end' => $reserva->fecha_salida->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'estado' => $reserva->estado,
                        'habitacion_id' => $reserva->habitacion_id,
                        'cliente_id' => $reserva->cliente_id,
                        'habitacion_numero' => $reserva->habitacion->numero,
                        'cliente_nombre' => $reserva->cliente->nombre,
                        'precio' => $reserva->habitacion->precio,
                        'adelanto' => $reserva->adelanto,
                        'total' => $reserva->total
                    ]
                ];
            });

        return response()->json($reservas);
    }

    /**
     * Consultar disponibilidad de habitaciones
     */
    public function disponibilidad(Request $request)
    {
        \Log::info('API disponibilidad called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);
        $validator = Validator::make($request->all(), [
            'fecha_entrada' => 'required|date|after_or_equal:today',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'categoria_id' => 'nullable|exists:categorias,id',
            'nivel_id' => 'nullable|exists:nivels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $fechaEntrada = Carbon::parse($request->fecha_entrada);
        $fechaSalida = Carbon::parse($request->fecha_salida);

        // Obtener habitaciones base
        $query = Habitacion::with(['categoria', 'nivel', 'imagenes'])
            ->whereIn('estado', ['Disponible', 'Activa']); // Aceptar ambos estados

        if ($request->categoria_id) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->nivel_id) {
            $query->where('nivel_id', $request->nivel_id);
        }

        $habitaciones = $query->get();

        // Filtrar habitaciones disponibles
        $habitacionesDisponibles = $habitaciones->filter(function ($habitacion) use ($fechaEntrada, $fechaSalida) {
            // Verificar si la habitación tiene reservas que se solapen con las fechas solicitadas
            $reservasConflicto = $habitacion->reservas()
                ->whereIn('estado', [
                    'Check-in',
                    'Pendiente',
                    'Pendiente de Confirmación',
                    'Reservada-Pendiente',
                    'Reservada-Confirmada'
                ])
                ->where(function ($query) use ($fechaEntrada, $fechaSalida) {
                    $query->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                        ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                        ->orWhere(function ($subQuery) use ($fechaEntrada, $fechaSalida) {
                            $subQuery->where('fecha_entrada', '<=', $fechaEntrada)
                                ->where('fecha_salida', '>=', $fechaSalida);
                        });
                })
                ->exists();

            return !$reservasConflicto;
        });

        // Obtener información del hotel incluyendo la moneda
        $hotel = \App\Models\Hotel::first();
        $moneda = $hotel ? $hotel->simbolo_moneda : 'Q.'; // Default a Quetzales si no hay hotel configurado
        
        $response = [
            'success' => true,
            'data' => [
                'habitaciones_disponibles' => $habitacionesDisponibles->values(),
                'total_disponibles' => $habitacionesDisponibles->count(),
                'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                'fecha_salida' => $fechaSalida->format('Y-m-d'),
                'moneda' => $moneda // Incluir el símbolo de moneda
            ]
        ];
        
        \Log::info('API disponibilidad response', [
            'total_rooms' => $habitacionesDisponibles->count(),
            'response_data' => $response
        ]);
        
        return response()->json($response);
    }

    /**
     * Crear una nueva reserva desde la landing page
     */
    public function crearReserva(Request $request)
    {
        \Log::info('API crear reserva called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);
        
        $validator = Validator::make($request->all(), [
            'habitacion_id' => 'required|exists:habitacions,id',
            'fecha_entrada' => 'required|date|after_or_equal:today',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'adelanto' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',

            // Datos básicos del cliente (requeridos para landing page)
            'cliente_nombre' => 'required|string|max:255',
            'cliente_telefono' => 'required|string|max:20',
            'cliente_email' => 'nullable|email|max:255',

            // Datos opcionales del cliente
            'cliente_documento' => 'nullable|string|max:20',
            'cliente_direccion' => 'nullable|string|max:500',
            'cliente_nit' => 'nullable|string|max:20',
            'cliente_dpi' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $fechaEntrada = Carbon::parse($request->fecha_entrada);
        $fechaSalida = Carbon::parse($request->fecha_salida);

        // Verificar disponibilidad de la habitación
        $habitacion = Habitacion::find($request->habitacion_id);

        $reservaConflicto = Reserva::where('habitacion_id', $request->habitacion_id)
            ->whereIn('estado', [
                'Check-in',
                'Pendiente',
                'Pendiente de Confirmación',
                'Reservada-Pendiente',
                'Reservada-Confirmada'
            ])
            ->where(function ($query) use ($fechaEntrada, $fechaSalida) {
                $query->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                    ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                    ->orWhere(function ($subQuery) use ($fechaEntrada, $fechaSalida) {
                        $subQuery->where('fecha_entrada', '<=', $fechaEntrada)
                            ->where('fecha_salida', '>=', $fechaSalida);
                    });
            })
            ->exists();

        if ($reservaConflicto) {
            \Log::warning('API crear reserva - Conflicto de disponibilidad', [
                'habitacion_id' => $request->habitacion_id,
                'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                'fecha_salida' => $fechaSalida->format('Y-m-d'),
                'cliente_nombre' => $request->cliente_nombre
            ]);
            return response()->json([
                'success' => false,
                'message' => 'La habitación no está disponible en las fechas seleccionadas. Es posible que otra persona la haya reservado recientemente.'
            ], 409);
        }

        // Crear cliente (normalmente es un cliente nuevo en landing page)
        // Solo buscar cliente existente si proporciona DPI o NIT específicos
        $cliente = null;

        if ($request->cliente_dpi && strlen($request->cliente_dpi) >= 8) {
            $cliente = Cliente::where('dpi', $request->cliente_dpi)->first();
        } elseif ($request->cliente_nit && strlen($request->cliente_nit) >= 5) {
            $cliente = Cliente::where('nit', $request->cliente_nit)->first();
        }

        // Si no existe el cliente, crear uno nuevo (caso más común en landing page)
        if (!$cliente) {
            $cliente = Cliente::create([
                'nombre' => $request->cliente_nombre,
                'documento' => $request->cliente_documento ?? 'N/A',
                'telefono' => $request->cliente_telefono,
                'email' => $request->cliente_email,
                'direccion' => $request->cliente_direccion,
                'nit' => $request->cliente_nit,
                'dpi' => $request->cliente_dpi,
                'origen' => 'landing' // Marcar como cliente de landing page
            ]);
        }

        // Calcular total y noches
        $noches = $fechaEntrada->diffInDays($fechaSalida);
        $total = $habitacion->precio * $noches;

        try {
            // Crear la reserva con expiración de 24 horas para confirmación
            $reserva = Reserva::create([
                'habitacion_id' => $request->habitacion_id,
                'cliente_id' => $cliente->id,
                'nombre_cliente' => $request->cliente_nombre,
                'documento_cliente' => $request->cliente_documento ?? $cliente->documento ?? 'N/A',
                'telefono_cliente' => $request->cliente_telefono,
                'fecha_entrada' => $fechaEntrada,
                'fecha_salida' => $fechaSalida,
                'adelanto' => $request->adelanto,
                'total' => $total,
                'estado' => 'Pendiente de Confirmación',
                'observaciones' => $request->observaciones,
                'user_id' => 1, // Usuario por defecto para reservas desde landing page
                'expires_at' => now()->addHours(24) // Expira en 24 horas (más tiempo para confirmar)
            ]);
            
            \Log::info('API crear reserva - Reserva creada exitosamente', [
                'reserva_id' => $reserva->id,
                'habitacion_numero' => $habitacion->numero,
                'cliente_nombre' => $request->cliente_nombre,
                'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                'fecha_salida' => $fechaSalida->format('Y-m-d'),
                'total' => $total,
                'expires_at' => $reserva->expires_at->format('Y-m-d H:i:s')
            ]);

            // FIXED: Enviar notificaciones a recepcionistas sobre nueva reserva pendiente
            // Esta funcionalidad estaba faltando y causaba que las notificaciones no aparecieran
            $recepcionistas = \App\Models\User::role('Recepcionista')->active()->get();
            foreach ($recepcionistas as $recepcionista) {
                $recepcionista->notify(new \App\Notifications\ReservaPendienteNotification($reserva));
            }

            \Log::info('API crear reserva - Notificaciones enviadas', [
                'reserva_id' => $reserva->id,
                'recepcionistas_notificados' => $recepcionistas->count()
            ]);

            $response = [
                'success' => true,
                'message' => 'Reserva creada exitosamente. Debe ser confirmada dentro de 24 horas.',
                'data' => [
                    'reserva_id' => $reserva->id,
                    'numero_reserva' => $reserva->id,
                    'estado' => $reserva->estado,
                    'total' => $total,
                    'adelanto' => $request->adelanto,
                    'pendiente' => $total - $request->adelanto,
                    'expires_at' => $reserva->expires_at->format('Y-m-d H:i:s'),
                    'habitacion' => [
                        'numero' => $habitacion->numero,
                        'categoria' => $habitacion->categoria->nombre ?? null,
                        'nivel' => $habitacion->nivel->nombre ?? null,
                        'precio' => $habitacion->precio
                    ],
                    'cliente' => [
                        'id' => $cliente->id,
                        'nombre' => $cliente->nombre
                    ]
                ]
            ];
            
            \Log::info('API crear reserva response', $response);
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('API crear reserva - Error al crear reserva', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'habitacion_id' => $request->habitacion_id,
                'cliente_nombre' => $request->cliente_nombre
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al crear la reserva. Por favor, inténtalo nuevamente o contacta al hotel directamente.'
            ], 500);
        }
    }
}
