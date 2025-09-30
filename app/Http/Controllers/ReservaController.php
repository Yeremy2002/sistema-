<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Hotel;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver reservas')->only(['index', 'show']);
        $this->middleware('permission:crear reservas')->only(['create', 'store', 'checkin']);
        $this->middleware('permission:editar reservas')->only(['edit', 'update']);
        $this->middleware('permission:eliminar reservas')->only('destroy');
        $this->middleware('permission:cancelar reservas')->only('checkout');
    }

    public function index()
    {
        $reservas = Reserva::with(['habitacion', 'cliente'])->latest()->paginate(10);
        $hotel = Hotel::getInfo(); // Obtener información del hotel
        return view('reservas.index', compact('reservas', 'hotel'));
    }

    public function create(Request $request)
    {
        $habitaciones = Habitacion::where('estado', 'Disponible')->get();

        $habitacionSeleccionada = null;
        // Permitir recibir habitacion_id o habitacion como parámetro
        $habitacionId = $request->get('habitacion_id') ?? $request->get('habitacion');
        if ($habitacionId) {
            $habitacionSeleccionada = Habitacion::findOrFail($habitacionId);
        }

        $clientes = Cliente::all();
        $adelanto = 0; // Inicializar la variable adelanto
        $hotel = Hotel::getInfo(); // Obtener información del hotel
        return view('reservas.create', compact('habitaciones', 'habitacionSeleccionada', 'clientes', 'adelanto', 'hotel'));
    }

    public function store(Request $request)
    {
        // Obtener configuración del hotel para validación
        $hotel = Hotel::getInfo();
        
        // Reglas de validación dinámicas según configuración de estadías por horas
        $validationRules = [
            'habitacion_id' => 'required|exists:habitacions,id',
            'nombre_cliente' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'fecha_entrada' => 'required|date',
            'observaciones' => 'nullable|string',
            'adelanto' => 'nullable|numeric|min:0',
            'nit' => 'nullable|string|max:255'
        ];
        
        // Si NO se permiten estadías por horas, usar validación tradicional
        if (!$hotel->permitir_estancias_horas) {
            $validationRules['fecha_salida'] = 'required|date|after:fecha_entrada';
        } else {
            // Si se permiten estadías por horas, validación más flexible
            $validationRules['fecha_salida'] = 'required|date|after_or_equal:fecha_entrada';
        }
        
        $request->validate($validationRules);
        
        // ===================== VALIDACIÓN DE ESTADÍAS POR HORAS =====================
        if ($hotel->permitir_estancias_horas) {
            $fechaEntrada = \Carbon\Carbon::parse($request->fecha_entrada);
            $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida);
            
            // Si es el mismo día, validar que cumpla con las reglas de estadías por horas
            if ($fechaEntrada->toDateString() === $fechaSalida->toDateString()) {
                // Verificar que se cumpla el mínimo de horas
                $horasDiferencia = $fechaSalida->diffInHours($fechaEntrada);
                $minimoHoras = $hotel->minimo_horas_estancia ?? 2;
                
                if ($horasDiferencia < $minimoHoras) {
                    return back()->with('error', 'Para estadías del mismo día, se requiere un mínimo de ' . $minimoHoras . ' horas.')->withInput();
                }
                
                // Verificar que el checkout sea antes de la hora límite del mismo día
                $horaLimite = $hotel->checkout_mismo_dia_limite ? $hotel->checkout_mismo_dia_limite->format('H:i') : '20:00';
                list($limitHour, $limitMin) = explode(':', $horaLimite);
                $fechaLimite = \Carbon\Carbon::parse($request->fecha_salida)->setTime($limitHour, $limitMin, 0);
                
                if ($fechaSalida->gt($fechaLimite)) {
                    return back()->with('error', 'Para estadías del mismo día, el check-out debe ser antes de las ' . $horaLimite . '.')->withInput();
                }
            }
        }
        // =================== FIN VALIDACIÓN DE ESTADÍAS POR HORAS =================

        // ===================== VALIDACIÓN DE DISPONIBILIDAD =====================
        // Antes de crear la reserva, validamos que NO existan reservas (Pendiente de Confirmación, Pendiente o Check-in)
        // que se solapen con las fechas seleccionadas para la misma habitación.
        // Esto previene dobles reservas y asegura que la habitación esté realmente disponible.
        // -----------------------------------------------------------------------
        // Algoritmo:
        // 1. Buscar reservas de la misma habitación con estado Pendiente de Confirmación, Pendiente o Check-in
        // 2. Verificar si alguna de esas reservas se solapa con el rango solicitado
        // 3. Si hay solapamiento, rechazar la reserva y mostrar mensaje de error
        // -----------------------------------------------------------------------
        $reservasSolapadas = \App\Models\Reserva::where('habitacion_id', $request->habitacion_id)
            ->whereIn('estado', ['Pendiente de Confirmación', 'Pendiente', 'Check-in'])
            ->where(function ($query) use ($request) {
                $entrada = $request->fecha_entrada;
                $salida = $request->fecha_salida;
                $query->whereBetween('fecha_entrada', [$entrada, $salida])
                    ->orWhereBetween('fecha_salida', [$entrada, $salida])
                    ->orWhere(function ($q) use ($entrada, $salida) {
                        $q->where('fecha_entrada', '<=', $entrada)
                            ->where('fecha_salida', '>=', $salida);
                    });
            })
            ->exists();
        if ($reservasSolapadas) {
            return back()->with('error', 'No se puede realizar la reserva. La habitación ya está reservada u ocupada en el rango de fechas seleccionado.')->withInput();
        }
        // =================== FIN VALIDACIÓN DE DISPONIBILIDAD ===================

        // Verificar estado de la habitación
        $habitacion = Habitacion::findOrFail($request->habitacion_id);
        if ($habitacion->estado !== 'Disponible') {
            return back()->with('error', 'No se puede realizar la reserva. La habitación está ' . $habitacion->estado);
        }        // Crear o actualizar el cliente en la base de datos
        // Usar el NIT proporcionado, si no existe usar el DPI como NIT
        $nit = $request->nit ?: $request->documento_identidad;

        // Convertir el nombre a mayúsculas
        $nombreCliente = strtoupper($request->nombre_cliente);

        // Primero buscamos si ya existe el cliente por DPI o NIT
        $clientePorDPI = Cliente::where('dpi', $request->documento_identidad)->first();
        $clientePorNIT = null;

        if ($request->nit) {
            $clientePorNIT = Cliente::where('nit', $request->nit)->first();
        }

        // Decidir qué cliente usar o crear uno nuevo
        if ($clientePorDPI) {
            // Si encontramos un cliente con el mismo DPI, lo actualizamos
            $cliente = $clientePorDPI;
            $cliente->nombre = $nombreCliente;
            $cliente->telefono = $request->telefono;
            // Solo actualizamos el NIT si se proporcionó uno nuevo
            if ($request->nit) {
                $cliente->nit = $request->nit;
            }
            $cliente->save();
        } elseif ($clientePorNIT) {
            // Si encontramos un cliente con el mismo NIT pero distinto DPI
            $cliente = $clientePorNIT;
            $cliente->nombre = $nombreCliente;
            $cliente->telefono = $request->telefono;
            $cliente->dpi = $request->documento_identidad;
            $cliente->save();
        } else {
            // Si no existe el cliente, lo creamos
            try {
                $cliente = Cliente::create([
                    'nombre' => $nombreCliente,
                    'dpi' => $request->documento_identidad,
                    'nit' => $nit,
                    'telefono' => $request->telefono
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Si hay un error de duplicidad de NIT u otro error
                if ($e->errorInfo[1] == 1062) {
                    return back()
                        ->with('error', 'No se puede crear el cliente. El NIT o DPI ya está registrado con otro cliente.')
                        ->withInput();
                } else {
                    // Si es otro tipo de error, lo propagamos
                    throw $e;
                }
            }
        }

        $reserva = new Reserva();
        $reserva->habitacion_id = $request->habitacion_id;
        $reserva->user_id = \Auth::id();
        $reserva->cliente_id = $cliente->id; // Guardar la relación con el cliente
        $reserva->nombre_cliente = $nombreCliente;
        $reserva->documento_cliente = $request->documento_identidad; // Mapear documento_identidad a documento_cliente
        $reserva->telefono_cliente = $request->telefono;           // Mapear telefono a telefono_cliente

        // Configurar la fecha de entrada con la hora específica de check-in
        $horaCheckinEstandar = $hotel->checkin_hora_inicio ? $hotel->checkin_hora_inicio->format('H:i') : '14:00';
        list($checkinHour, $checkinMin) = explode(':', $horaCheckinEstandar);
        $fechaEntrada = \Carbon\Carbon::parse($request->fecha_entrada)->setTime($checkinHour, $checkinMin, 0);
        $reserva->fecha_entrada = $fechaEntrada;

        // Configurar la fecha de salida
        $fechaEntradaObj = \Carbon\Carbon::parse($request->fecha_entrada);
        $fechaSalidaObj = \Carbon\Carbon::parse($request->fecha_salida);
        
        if ($hotel->permitir_estancias_horas && $fechaEntradaObj->toDateString() === $fechaSalidaObj->toDateString()) {
            // Para estadías del mismo día, usar la hora límite configurada
            $horaLimite = $hotel->checkout_mismo_dia_limite ? $hotel->checkout_mismo_dia_limite->format('H:i') : '20:00';
            list($limitHour, $limitMin) = explode(':', $horaLimite);
            $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida)->setTime($limitHour, $limitMin, 0);
        } else {
            // Para estadías tradicionales, usar la hora estándar de checkout
            $horaCheckoutEstandar = $hotel->checkout_hora_inicio ? $hotel->checkout_hora_inicio->format('H:i') : '12:30';
            list($checkoutHour, $checkoutMin) = explode(':', $horaCheckoutEstandar);
            $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida)->setTime($checkoutHour, $checkoutMin, 0);
        }
        $reserva->fecha_salida = $fechaSalida;

        $reserva->observaciones = $request->observaciones;
        $reserva->adelanto = $request->adelanto ?? 0;
        $reserva->estado = 'Pendiente de Confirmación';

        // Calcular el total basado en los días de estancia (ya se configuraron las horas correctas)
        $diasEstancia = $fechaEntrada->diffInDays($fechaSalida);
        if ($diasEstancia == 0) $diasEstancia = 1; // Mínimo un día
        $reserva->total = $diasEstancia * $habitacion->precio;

        // Validar que el anticipo no exceda el total de la reserva
        if ($reserva->adelanto > $reserva->total) {
            return back()
                ->with('error', 'El anticipo (Q' . number_format($reserva->adelanto, 2) . ') no puede ser mayor al total de la reserva (Q' . number_format($reserva->total, 2) . ').')
                ->withInput();
        }

        $reserva->save();

        // Establecer la fecha de expiración
        $reserva->setExpirationTime();

        // Actualizar el estado de la habitación a Reservada-Pendiente
        $habitacion->estado = 'Reservada-Pendiente';
        $habitacion->save();

        // Notificar a los recepcionistas sobre la nueva reserva pendiente de confirmación
        $recepcionistas = \App\Models\User::role('Recepcionista')->active()->get();
        foreach ($recepcionistas as $recepcionista) {
            $recepcionista->notify(new \App\Notifications\ReservaPendienteNotification($reserva));
        }

        // Registrar el anticipo en la caja si es mayor a cero
        if ($reserva->adelanto > 0) {
            // Buscar la caja activa del usuario
            $caja = \App\Models\Caja::where('user_id', \Auth::id())
                ->where('estado', true)
                ->first();

            if ($caja) {
                try {
                    // Registrar el anticipo como ingreso en la caja
                    $caja->registrarMovimiento(
                        'ingreso',
                        $reserva->adelanto,
                        'Anticipo de reserva #' . $reserva->id,
                        'Pago de anticipo por reserva de la habitación ' . $habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente,
                        $reserva
                    );
                } catch (\Exception $e) {
                    return redirect()->route('reservas.index')
                        ->with('warning', 'Reserva creada exitosamente, pero no se pudo registrar el anticipo en caja: ' . $e->getMessage());
                }
            } else {
                return redirect()->route('reservas.index')
                    ->with('warning', 'Reserva creada exitosamente, pero no se pudo registrar el anticipo en caja porque no hay una caja abierta para el usuario actual.');
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Reserva creada exitosamente. Pendiente de confirmación por el recepcionista.');
    }

    public function show(Reserva $reserva)
    {
        // Verificar si la reserva está vencida y en estado 'Pendiente de Confirmación'
        if ($reserva->estado === 'Pendiente de Confirmación' && $reserva->isExpired()) {
            // Cancelar automáticamente la reserva vencida
            $reserva->cancelarPorExpiracion();
            
            return redirect()->route('reservas.index')
                ->with('error', 'Esta reserva ha expirado y ha sido cancelada automáticamente. El tiempo máximo para confirmar era de ' . 
                       ($reserva->habitacion->hotel->reservas_vencidas_horas ?? 24) . ' horas.');
        }
        
        // Verificar si la reserva puede ser confirmada (no expirada y en estado pendiente)
        $puedeConfirmar = $reserva->isPendingConfirmation();
        $tiempoRestante = null;
        
        if ($reserva->estado === 'Pendiente de Confirmación' && $reserva->expires_at) {
            $tiempoRestante = $reserva->expires_at->diffForHumans(null, true);
        }
        
        $hotel = Hotel::getInfo(); // Obtener información del hotel
        return view('reservas.show', compact('reserva', 'hotel', 'puedeConfirmar', 'tiempoRestante'));
    }

    public function edit(Reserva $reserva)
    {
        $habitaciones = Habitacion::all();
        $clientes = Cliente::all();
        $hotel = Hotel::getInfo(); // Obtener información del hotel
        return view('reservas.edit', compact('reserva', 'habitaciones', 'clientes', 'hotel'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitacions,id',
            'cliente_id' => 'required|exists:clientes,id',
            'nombre_cliente' => 'required|string|max:255',
            'documento_cliente' => 'required|string|max:255',
            'telefono_cliente' => 'required|string|max:20',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'estado' => 'required|in:Pendiente,Check-in,Check-out,Cancelada',
            'observaciones' => 'nullable|string',
            'adelanto' => 'nullable|numeric|min:0'
        ]);

        // Si cambia la habitación
        if ($reserva->habitacion_id != $request->habitacion_id) {
            // Verificar estado de la nueva habitación
            $nuevaHabitacion = Habitacion::findOrFail($request->habitacion_id);
            if ($nuevaHabitacion->estado !== 'Disponible') {
                return back()->with('error', 'No se puede cambiar la reserva. La habitación está ' . $nuevaHabitacion->estado);
            }

            // Liberar la habitación anterior
            $habitacionAnterior = $reserva->habitacion;
            $habitacionAnterior->estado = 'Disponible';
            $habitacionAnterior->save();

            // Ocupar la nueva habitación
            $nuevaHabitacion->estado = 'Ocupada';
            $nuevaHabitacion->save();
        }

        // Si cambia el estado a Cancelada, liberar la habitación
        if ($request->estado === 'Cancelada' && $reserva->estado !== 'Cancelada') {
            $habitacion = Habitacion::findOrFail($request->habitacion_id);
            $habitacion->estado = 'Disponible';
            $habitacion->save();
        }

        // Si cambia el estado de Cancelada a Check-in, ocupar la habitación
        if ($reserva->estado === 'Cancelada' && $request->estado === 'Check-in') {
            $habitacion = Habitacion::findOrFail($request->habitacion_id);
            $habitacion->estado = 'Ocupada';
            $habitacion->save();
        }

        // Actualizar el cliente relacionado con la información actualizada
        if ($request->cliente_id) {
            $cliente = Cliente::findOrFail($request->cliente_id);

            // Convertir el nombre a mayúsculas
            $nombreCliente = strtoupper($request->nombre_cliente);

            // Actualizar datos del cliente si es necesario
            if (
                $cliente->nombre != $nombreCliente ||
                $cliente->dpi != $request->documento_cliente ||
                $cliente->telefono != $request->telefono_cliente
            ) {

                $cliente->nombre = $nombreCliente;
                $cliente->dpi = $request->documento_cliente;
                $cliente->telefono = $request->telefono_cliente;
                $cliente->save();
            }
        }

        // Preparar los datos para la actualización de la reserva
        $updateData = $request->except(['fecha_entrada', 'fecha_salida', 'nombre_cliente']);

        // Si hay un nombre de cliente, convertirlo a mayúsculas
        if ($request->has('nombre_cliente')) {
            $updateData['nombre_cliente'] = strtoupper($request->nombre_cliente);
        }

        // Configurar las fechas con las horas específicas de check-in y check-out
        if ($request->has('fecha_entrada')) {
            $updateData['fecha_entrada'] = \Carbon\Carbon::parse($request->fecha_entrada)->setTime(14, 0, 0);
        }

        if ($request->has('fecha_salida')) {
            $updateData['fecha_salida'] = \Carbon\Carbon::parse($request->fecha_salida)->setTime(12, 30, 0);
        }

        // Verificar si ha cambiado el anticipo
        $adelantoAnterior = $reserva->adelanto;
        $adelantoNuevo = $request->adelanto ?? 0;
        $diferenciaAdelanto = $adelantoNuevo - $adelantoAnterior;

        // Validar que el nuevo anticipo no exceda el total de la reserva
        if ($adelantoNuevo > 0) {
            // Recalcular el total con las nuevas fechas si han cambiado
            $fechaEntrada = $request->has('fecha_entrada') ? 
                \Carbon\Carbon::parse($request->fecha_entrada) : $reserva->fecha_entrada;
            $fechaSalida = $request->has('fecha_salida') ? 
                \Carbon\Carbon::parse($request->fecha_salida) : $reserva->fecha_salida;
            
            $diasEstancia = $fechaEntrada->diffInDays($fechaSalida);
            $habitacion = $reserva->habitacion;
            $totalCalculado = $diasEstancia * $habitacion->precio;
            
            if ($adelantoNuevo > $totalCalculado) {
                return redirect()->back()
                    ->with('error', 'El anticipo (' . number_format($adelantoNuevo, 2) . ') no puede ser mayor al total de la reserva (' . number_format($totalCalculado, 2) . ').')
                    ->withInput();
            }
        }

        $reserva->update($updateData);

        // Si el adelanto aumentó, registrarlo como ingreso en caja
        if ($diferenciaAdelanto > 0) {
            // Buscar la caja activa del usuario
            $caja = \App\Models\Caja::where('user_id', \Auth::id())
                ->where('estado', true)
                ->first();

            if ($caja) {
                try {
                    // Registrar la diferencia como ingreso en la caja
                    $caja->registrarMovimiento(
                        'ingreso',
                        $diferenciaAdelanto,
                        'Aumento en anticipo de reserva #' . $reserva->id,
                        'Actualización de anticipo para habitación ' . $reserva->habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente,
                        $reserva
                    );
                } catch (\Exception $e) {
                    return redirect()->route('reservas.index')
                        ->with('warning', 'Reserva actualizada exitosamente, pero no se pudo registrar el anticipo adicional en caja: ' . $e->getMessage());
                }
            } else {
                return redirect()->route('reservas.index')
                    ->with('warning', 'Reserva actualizada exitosamente, pero no se pudo registrar el anticipo adicional en caja porque no hay una caja abierta para el usuario actual.');
            }
        }

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva actualizada exitosamente.');
    }

    public function destroy(Reserva $reserva)
    {
        // Liberar la habitación si la reserva estaba activa
        if ($reserva->estado !== 'Cancelada' && $reserva->estado !== 'Check-out') {
            $habitacion = $reserva->habitacion;
            $habitacion->estado = 'Disponible';
            $habitacion->save();
        }

        // Actualizar el estado de la reserva a "Limpieza realizada" solo si aplica
        if ($reserva->estado === 'Limpieza') {
            $reserva->estado = 'Limpieza realizada';
            $reserva->save();
        }

        $reserva->delete();

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva eliminada exitosamente y habitación actualizada.');
    }

    public function checkin(Habitacion $habitacione)
    {
        if ($habitacione->estado !== 'Disponible') {
            return redirect()->route('habitaciones.index')
                ->with('error', 'La habitación no está disponible para check-in.');
        }

        $clientes = Cliente::all();
        $fechaActual = \Carbon\Carbon::now()->format('Y-m-d');
        $hotel = Hotel::getInfo(); // Obtener información del hotel

        // Si la petición es POST, procesar el check-in
        if (request()->isMethod('post')) {
            // Reglas de validación dinámicas según configuración de estadías por horas
            $validationRules = [
                'nombre_cliente' => 'required|string|max:255',
                'documento_identidad' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'fecha_entrada' => 'required|date',
                'adelanto' => 'nullable|numeric|min:0',
                'nit' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
            ];
            
            // Si NO se permiten estadías por horas, usar validación tradicional
            if (!$hotel->permitir_estancias_horas) {
                $validationRules['fecha_salida'] = 'required|date|after:fecha_entrada';
            } else {
                // Si se permiten estadías por horas, validación más flexible
                $validationRules['fecha_salida'] = 'required|date|after_or_equal:fecha_entrada';
            }
            
            $data = request()->validate($validationRules);
            
            // ===================== VALIDACIÓN DE ESTADÍAS POR HORAS =====================
            if ($hotel->permitir_estancias_horas) {
                $fechaEntrada = \Carbon\Carbon::parse($data['fecha_entrada']);
                $fechaSalida = \Carbon\Carbon::parse($data['fecha_salida']);
                
                // Si es el mismo día, validar que cumpla con las reglas de estadías por horas
                if ($fechaEntrada->toDateString() === $fechaSalida->toDateString()) {
                    // Verificar que se cumpla el mínimo de horas
                    $horasDiferencia = $fechaSalida->diffInHours($fechaEntrada);
                    $minimoHoras = $hotel->minimo_horas_estancia ?? 2;
                    
                    if ($horasDiferencia < $minimoHoras) {
                        return back()->with('error', 'Para estadías del mismo día, se requiere un mínimo de ' . $minimoHoras . ' horas.')->withInput();
                    }
                    
                    // Verificar que el checkout sea antes de la hora límite del mismo día
                    $horaLimite = $hotel->checkout_mismo_dia_limite ? $hotel->checkout_mismo_dia_limite->format('H:i') : '20:00';
                    list($limitHour, $limitMin) = explode(':', $horaLimite);
                    $fechaLimite = \Carbon\Carbon::parse($data['fecha_salida'])->setTime($limitHour, $limitMin, 0);
                    
                    if ($fechaSalida->gt($fechaLimite)) {
                        return back()->with('error', 'Para estadías del mismo día, el check-out debe ser antes de las ' . $horaLimite . '.')->withInput();
                    }
                }
            }
            // =================== FIN VALIDACIÓN DE ESTADÍAS POR HORAS =================

            $nit = $data['nit'] ?: $data['documento_identidad'];
            $nombreCliente = strtoupper($data['nombre_cliente']);
            $cliente = Cliente::firstOrCreate(
                ['dpi' => $data['documento_identidad']],
                ['nombre' => $nombreCliente, 'nit' => $nit, 'telefono' => $data['telefono']]
            );

            // Buscar reserva pendiente para la habitación y fechas
            $reservaPendiente = \App\Models\Reserva::where('habitacion_id', $habitacione->id)
                ->whereIn('estado', ['Pendiente', 'Pendiente de Confirmación', 'Reservada', 'Confirmada'])
                ->where(function ($query) use ($data) {
                    $entrada = $data['fecha_entrada'];
                    $salida = $data['fecha_salida'];
                    $query->whereBetween('fecha_entrada', [$entrada, $salida])
                        ->orWhereBetween('fecha_salida', [$entrada, $salida])
                        ->orWhere(function ($q) use ($entrada, $salida) {
                            $q->where('fecha_entrada', '<=', $entrada)
                                ->where('fecha_salida', '>=', $salida);
                        });
                })
                ->first();

            if ($reservaPendiente) {
                // Actualizar la reserva pendiente a Check-in
                $reservaPendiente->cliente_id = $cliente->id;
                $reservaPendiente->nombre_cliente = $nombreCliente;
                $reservaPendiente->documento_cliente = $data['documento_identidad'];
                $reservaPendiente->telefono_cliente = $data['telefono'];
                $reservaPendiente->fecha_entrada = \Carbon\Carbon::parse($data['fecha_entrada'])->setTime(14, 0, 0);
                $reservaPendiente->fecha_salida = \Carbon\Carbon::parse($data['fecha_salida'])->setTime(12, 30, 0);
                $reservaPendiente->observaciones = $data['observaciones'] ?? null;
                $reservaPendiente->adelanto = $data['adelanto'] ?? 0;
                $reservaPendiente->estado = 'Check-in';
                $diasEstancia = $reservaPendiente->fecha_entrada->diffInDays($reservaPendiente->fecha_salida);
                if ($diasEstancia == 0) $diasEstancia = 1;
                $reservaPendiente->total = $diasEstancia * $habitacione->precio;
                $reservaPendiente->save();
                $reserva = $reservaPendiente;
            } else {
                // ===================== VALIDACIÓN DE DISPONIBILIDAD =====================
                $reservasSolapadas = \App\Models\Reserva::where('habitacion_id', $habitacione->id)
                    ->whereIn('estado', ['Pendiente de Confirmación', 'Pendiente', 'Check-in'])
                    ->where(function ($query) use ($data) {
                        $entrada = $data['fecha_entrada'];
                        $salida = $data['fecha_salida'];
                        $query->whereBetween('fecha_entrada', [$entrada, $salida])
                            ->orWhereBetween('fecha_salida', [$entrada, $salida])
                            ->orWhere(function ($q) use ($entrada, $salida) {
                                $q->where('fecha_entrada', '<=', $entrada)
                                    ->where('fecha_salida', '>=', $salida);
                            });
                    })
                    ->exists();
                if ($reservasSolapadas) {
                    return redirect()->route('habitaciones.index')
                        ->with('error', 'No se puede realizar el check-in. La habitación ya está reservada u ocupada en el rango de fechas seleccionado.');
                }
                // =================== FIN VALIDACIÓN DE DISPONIBILIDAD ===================

                $reserva = new \App\Models\Reserva();
                $reserva->habitacion_id = $habitacione->id;
                $reserva->user_id = \Auth::id();
                $reserva->cliente_id = $cliente->id;
                $reserva->nombre_cliente = $nombreCliente;
                $reserva->documento_cliente = $data['documento_identidad'];
                $reserva->telefono_cliente = $data['telefono'];
                $reserva->fecha_entrada = \Carbon\Carbon::parse($data['fecha_entrada'])->setTime(14, 0, 0);
                $reserva->fecha_salida = \Carbon\Carbon::parse($data['fecha_salida'])->setTime(12, 30, 0);
                $reserva->observaciones = $data['observaciones'] ?? null;
                $reserva->adelanto = $data['adelanto'] ?? 0;
                $reserva->estado = 'Check-in';
                $diasEstancia = $reserva->fecha_entrada->diffInDays($reserva->fecha_salida);
                if ($diasEstancia == 0) $diasEstancia = 1;
                $reserva->total = $diasEstancia * $habitacione->precio;
                
                // Validar que el anticipo no exceda el total de la reserva
                if ($reserva->adelanto > $reserva->total) {
                    return back()
                        ->with('error', 'El anticipo (Q' . number_format($reserva->adelanto, 2) . ') no puede ser mayor al total de la reserva (Q' . number_format($reserva->total, 2) . ').')
                        ->withInput();
                }
                
                $reserva->save();
            }

            $habitacione->estado = 'Ocupada';
            $habitacione->save();

            // Registrar el anticipo en la caja si es mayor a cero
            if ($reserva->adelanto > 0) {
                $caja = \App\Models\Caja::where('user_id', \Auth::id())
                    ->where('estado', true)
                    ->first();
                if ($caja) {
                    try {
                        $caja->registrarMovimiento(
                            'ingreso',
                            $reserva->adelanto,
                            'Anticipo de reserva #' . $reserva->id,
                            'Pago de anticipo por reserva de la habitación ' . $habitacione->numero . ' a nombre de ' . $reserva->nombre_cliente,
                            $reserva
                        );
                    } catch (\Exception $e) {
                        return redirect()->route('reservas.index')
                            ->with('warning', 'Check-in realizado, pero no se pudo registrar el anticipo en caja: ' . $e->getMessage());
                    }
                } else {
                    return redirect()->route('reservas.index')
                        ->with('warning', 'Check-in realizado, pero no se pudo registrar el anticipo en caja porque no hay una caja abierta para el usuario actual.');
                }
            }
            return redirect()->route('reservas.index')->with('success', 'Check-in realizado exitosamente.');
        }

        return view('reservas.checkin', compact('habitacione', 'clientes', 'fechaActual', 'hotel'));
    }

    public function checkout(Reserva $reserva)
    {
        if ($reserva->estado !== 'Check-in') {
            return redirect()->route('reservas.index')
                ->with('error', 'La reserva no está activa para realizar el Check-out.');
        }

        $esAdmin = \Auth::user()->hasRole(['Administrador', 'Super Admin']);
        $hotel = Hotel::getInfo();
        $ahora = \Carbon\Carbon::now();
        $hoy = $ahora->toDateString();
        
        // Obtener horarios de checkout del hotel
        $horaCheckoutInicio = $hotel->checkout_hora_inicio ? $hotel->checkout_hora_inicio->format('H:i') : '12:30';
        $horaCheckoutFin = $hotel->checkout_hora_fin ? $hotel->checkout_hora_fin->format('H:i') : '13:00';
        
        $fueraDeHorario = false;
        $checkoutMuyRetrasado = false;
        $horasVencidas = 0;
        $requiereAutorizacionAdmin = false;
        
        // Verificar si es checkout del día actual
        $esCheckoutHoy = $reserva->fecha_salida->toDateString() === $hoy;
        
        if ($esCheckoutHoy) {
            $horaLimiteCheckout = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $hoy . ' ' . $horaCheckoutFin);
            
            if ($ahora->gt($horaLimiteCheckout)) {
                $fueraDeHorario = true;
                $horasVencidas = $ahora->diffInHours($horaLimiteCheckout);
                
                // Si han pasado más de 2 horas, requiere autorización administrativa
                if ($horasVencidas >= 2) {
                    $checkoutMuyRetrasado = true;
                    $requiereAutorizacionAdmin = true;
                }
            }
        }
        
        // Validar permisos según el estado del checkout
        if (!$esAdmin) {
            if ($requiereAutorizacionAdmin) {
                return redirect()->route('reservas.index')
                    ->with('error', 'Este checkout está muy retrasado (' . $horasVencidas . ' horas). Requiere autorización de un administrador.');
            }
            
            if ($fueraDeHorario && !$checkoutMuyRetrasado) {
                return redirect()->route('reservas.index')
                    ->with('warning', 'El check-out está fuera del horario establecido (' . $horaCheckoutInicio . ' - ' . $horaCheckoutFin . '). Contacte a un administrador.');
            }
        }

        // Calcular saldo pendiente para la vista
        $saldoPendiente = $reserva->total - $reserva->adelanto;
        
        return view('reservas.checkout', compact(
            'reserva', 
            'hotel', 
            'esAdmin', 
            'fueraDeHorario', 
            'checkoutMuyRetrasado', 
            'horasVencidas',
            'requiereAutorizacionAdmin',
            'saldoPendiente'
        ));
    }

    public function storeCheckout(Request $request, Reserva $reserva)
    {
        // Log inicial del checkout
        \Log::info('Iniciando checkout para reserva ID: ' . $reserva->id, [
            'request_data' => $request->all()
        ]);
        
        $esAdmin = \Auth::user()->hasRole(['Administrador', 'Super Admin']);
        $hotel = Hotel::getInfo();
        $ahora = \Carbon\Carbon::now();
        $hoy = $ahora->toDateString();
        
        // Verificar si hay saldo negativo
        $saldoPendiente = $reserva->total - $reserva->adelanto;
        $checkoutForzado = $request->has('checkout_forzado') && $request->checkout_forzado == '1';
        
        \Log::info('Datos del checkout', [
            'reserva_id' => $reserva->id,
            'saldo_pendiente' => $saldoPendiente,
            'checkout_forzado' => $checkoutForzado,
            'es_admin' => $esAdmin,
            'usuario' => \Auth::user()->name
        ]);
        
        // Validar checkout forzado para saldo negativo
        if ($saldoPendiente < 0) {
            \Log::info('Saldo negativo detectado', [
                'reserva_id' => $reserva->id,
                'saldo' => $saldoPendiente,
                'es_admin' => $esAdmin,
                'checkout_forzado' => $checkoutForzado
            ]);
            
            if (!$esAdmin) {
                \Log::warning('Usuario no administrador intentó checkout con saldo negativo', [
                    'reserva_id' => $reserva->id,
                    'usuario' => \Auth::user()->name
                ]);
                return redirect()->route('reservas.index')
                    ->with('error', 'Solo un administrador puede procesar un checkout con saldo negativo.');
            }
            
            if (!$checkoutForzado) {
                \Log::warning('Checkout con saldo negativo sin autorización forzada', [
                    'reserva_id' => $reserva->id
                ]);
                return redirect()->back()
                    ->with('error', 'Este checkout tiene saldo negativo y requiere autorización administrativa.')
                    ->withInput();
            }
        }
        
        // Verificar si el checkout está muy retrasado
        $horaCheckoutFin = $hotel->checkout_hora_fin ? $hotel->checkout_hora_fin->format('H:i') : '13:00';
        $esCheckoutHoy = $reserva->fecha_salida->toDateString() === $hoy;
        $checkoutMuyRetrasado = false;
        $horasVencidas = 0;
        
        if ($esCheckoutHoy) {
            $horaLimiteCheckout = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $hoy . ' ' . $horaCheckoutFin);
            if ($ahora->gt($horaLimiteCheckout)) {
                $horasVencidas = $ahora->diffInHours($horaLimiteCheckout);
                if ($horasVencidas >= 2) {
                    $checkoutMuyRetrasado = true;
                }
            }
        }
        
        // Validar autorización para checkouts muy retrasados
        if ($checkoutMuyRetrasado && !$esAdmin) {
            return redirect()->route('reservas.index')
                ->with('error', 'Este checkout está muy retrasado (' . $horasVencidas . ' horas). Solo un administrador puede procesarlo.');
        }
        
        // Validaciones específicas según el tipo de checkout
        if ($checkoutForzado && $saldoPendiente < 0) {
            \Log::info('Validando checkout forzado con saldo negativo', [
                'reserva_id' => $reserva->id,
                'justificacion' => $request->justificacion_checkout_forzado,
                'autorizacion' => $request->autorizacion_checkout_forzado
            ]);
            
            // Checkout forzado por saldo negativo requiere justificación obligatoria
            try {
                $request->validate([
                    'monto_total' => 'required|numeric|min:0',
                    'descuento_adicional' => 'nullable|numeric|min:0',
                    'pago_efectivo' => 'nullable|numeric|min:0',
                    'pago_tarjeta' => 'nullable|numeric|min:0',
                    'pago_transferencia' => 'nullable|numeric|min:0',
                    'observaciones' => 'nullable|string',
                    'justificacion_checkout_forzado' => 'required|string|max:500',
                    'autorizacion_checkout_forzado' => 'required|accepted'
                ], [
                    'justificacion_checkout_forzado.required' => 'La justificación es obligatoria para checkouts forzados con saldo negativo.',
                    'autorizacion_checkout_forzado.required' => 'Debe autorizar explícitamente este checkout forzado.',
                    'autorizacion_checkout_forzado.accepted' => 'Debe confirmar la autorización del checkout forzado.'
                ]);
                
                \Log::info('Validación de checkout forzado exitosa', [
                    'reserva_id' => $reserva->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Falló la validación de checkout forzado', [
                    'reserva_id' => $reserva->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        } elseif ($checkoutMuyRetrasado && $esAdmin) {
            // Checkout muy retrasado requiere justificación obligatoria
            $request->validate([
                'monto_total' => 'required|numeric|min:0',
                'descuento_adicional' => 'nullable|numeric|min:0',
                'pago_efectivo' => 'nullable|numeric|min:0',
                'pago_tarjeta' => 'nullable|numeric|min:0',
                'pago_transferencia' => 'nullable|numeric|min:0',
                'observaciones' => 'nullable|string',
                'justificacion_admin' => 'required|string|max:500',
                'autorizacion_checkout_retrasado' => 'required|accepted'
            ], [
                'justificacion_admin.required' => 'La justificación es obligatoria para checkouts muy retrasados.',
                'autorizacion_checkout_retrasado.required' => 'Debe autorizar explícitamente este checkout retrasado.',
                'autorizacion_checkout_retrasado.accepted' => 'Debe confirmar la autorización del checkout retrasado.'
            ]);
        } elseif ($esAdmin && $request->has('justificacion_admin')) {
            // Checkout administrativo normal
            $request->validate([
                'monto_total' => 'required|numeric|min:0',
                'descuento_adicional' => 'nullable|numeric|min:0',
                'pago_efectivo' => 'nullable|numeric|min:0',
                'pago_tarjeta' => 'nullable|numeric|min:0',
                'pago_transferencia' => 'nullable|numeric|min:0',
                'observaciones' => 'nullable|string',
                'justificacion_admin' => 'required|string|max:500'
            ]);
        } else {
            // Checkout normal
            $request->validate([
                'monto_total' => 'required|numeric|min:0',
                'descuento_adicional' => 'nullable|numeric|min:0',
                'pago_efectivo' => 'nullable|numeric|min:0',
                'pago_tarjeta' => 'nullable|numeric|min:0',
                'pago_transferencia' => 'nullable|numeric|min:0',
                'observaciones' => 'nullable|string',
            ]);
        }

        $descuento = $request->descuento_adicional ?? 0;
        $totalEsperado = $reserva->total - $reserva->adelanto - $descuento;
        $totalEsperado = round($totalEsperado, 2);
        $montoTotal = round($request->monto_total, 2);
        $pagoEfectivo = round($request->pago_efectivo ?? 0, 2);
        $pagoTarjeta = round($request->pago_tarjeta ?? 0, 2);
        $pagoTransferencia = round($request->pago_transferencia ?? 0, 2);
        $sumaPagos = $pagoEfectivo + $pagoTarjeta + $pagoTransferencia;

        if (abs($montoTotal - $totalEsperado) > 0.01) {
            return back()->with('error', 'El monto a pagar no coincide con el total menos el adelanto y descuento.')->withInput();
        }
        if (abs($sumaPagos - $totalEsperado) > 0.01) {
            return back()->with('error', 'La suma de los pagos no coincide con el total a pagar.')->withInput();
        }

        // Combinar observaciones regulares con justificación administrativa si aplica
        $observaciones = $request->observaciones;
        
        if ($checkoutForzado && $saldoPendiente < 0) {
            // Checkout forzado por saldo negativo
            $observaciones .= ($observaciones ? "\n\n" : '') . 
                "[CHECK-OUT FORZADO AUTORIZADO POR: " . \Auth::user()->name . "]\n" .
                "Motivo: Saldo negativo (Anticipo: " . $hotel->simbolo_moneda . number_format($reserva->adelanto, 2) . 
                " > Total: " . $hotel->simbolo_moneda . number_format($reserva->total, 2) . ")\n" .
                "Fecha/Hora de autorización: " . $ahora->format('d/m/Y H:i') . "\n" .
                "Justificación: " . $request->justificacion_checkout_forzado . "\n" .
                "NOTA: Este checkout requirió autorización administrativa por saldo negativo.";
        } elseif ($checkoutMuyRetrasado && $esAdmin) {
            // Checkout muy retrasado con autorización administrativa
            $observaciones .= ($observaciones ? "\n\n" : '') . 
                "[CHECK-OUT RETRASADO AUTORIZADO POR: " . \Auth::user()->name . "]\n" .
                "Retraso: " . $horasVencidas . " horas después del límite (" . $horaCheckoutFin . ")\n" .
                "Fecha/Hora de autorización: " . $ahora->format('d/m/Y H:i') . "\n" .
                "Justificación: " . $request->justificacion_admin . "\n" .
                "NOTA: Este checkout afecta el cierre de caja del día.";
        } elseif ($esAdmin && $request->justificacion_admin) {
            // Checkout administrativo normal
            $observaciones .= ($observaciones ? "\n\n" : '') . 
                "[CHECK-OUT ADMINISTRATIVO POR: " . \Auth::user()->name . "]\n" .
                "Justificación: " . $request->justificacion_admin;
        }
        
        $reserva->estado = 'Check-out';
        $reserva->observaciones = $observaciones;
        $reserva->descuento_adicional = $descuento;
        $reserva->save();

        // Liberar la habitación
        $habitacion = $reserva->habitacion;
        $habitacion->estado = 'Disponible'; // Se actualizará según la acción seleccionada
        $habitacion->save();

        $acciones = explode(',', $request->input('post_checkout_accion', 'limpieza'));
        $estadoFinal = 'Disponible';
        $notificados = collect();
        if (in_array('limpieza', $acciones)) {
            $limpieza = new \App\Models\Limpieza();
            $limpieza->habitacion_id = $habitacion->id;
            $limpieza->user_id = \Auth::id();
            $limpieza->fecha = now()->toDateString();
            $limpieza->hora = now()->toTimeString();
            $limpieza->estado = 'pendiente';
            $limpieza->observaciones = 'Limpieza generada automáticamente tras check-out de la reserva #' . $reserva->id;
            $limpieza->save();
            $estadoFinal = 'Limpieza';
            $usuariosLimpieza = \App\Models\User::role('Limpieza')->active()->get();
            $notificados = $notificados->merge($usuariosLimpieza);
            foreach ($usuariosLimpieza as $usuario) {
                $usuario->notify(new \App\Notifications\LimpiezaMantenimientoNotification(
                    'limpieza',
                    $habitacion->numero,
                    'Nueva limpieza pendiente en habitación ' . $habitacion->numero . ' tras check-out.',
                    $limpieza->id,
                    'limpieza'
                ));
            }
        }
        if (in_array('mantenimiento', $acciones)) {
            $reparacion = new \App\Models\Reparacion();
            $reparacion->habitacion_id = $habitacion->id;
            $reparacion->user_id = \Auth::id();
            $reparacion->fecha = now()->toDateString();
            $reparacion->hora = now()->toTimeString();
            $reparacion->estado = 'pendiente';
            $reparacion->tipo_reparacion = 'General';
            $reparacion->costo = 0;
            $reparacion->descripcion = 'Mantenimiento generado automáticamente tras check-out de la reserva #' . $reserva->id;
            $reparacion->observaciones = null;
            $reparacion->save();
            $estadoFinal = 'Mantenimiento';
            $usuariosMantenimiento = \App\Models\User::role('Mantenimiento')->active()->get();
            $notificados = $notificados->merge($usuariosMantenimiento);
            foreach ($usuariosMantenimiento as $usuario) {
                $usuario->notify(new \App\Notifications\LimpiezaMantenimientoNotification(
                    'mantenimiento',
                    $habitacion->numero,
                    'Nuevo mantenimiento pendiente en habitación ' . $habitacion->numero . ' tras check-out.',
                    $reparacion->id,
                    'mantenimiento'
                ));
            }
        }
        // Notificar a roles adicionales (Administrador, Super Admin, Cajero)
        $rolesExtra = ['Administrador', 'Super Admin', 'Cajero'];
        foreach ($rolesExtra as $rol) {
            $usuarios = \App\Models\User::role($rol)->active()->get();
            foreach ($usuarios as $usuario) {
                if (!$notificados->contains('id', $usuario->id)) {
                    $usuario->notify(new \App\Notifications\LimpiezaMantenimientoNotification(
                        implode(' y ', $acciones),
                        $habitacion->numero,
                        'Nueva acción post check-out en habitación ' . $habitacion->numero . ': ' . implode(' y ', $acciones),
                        isset($limpieza) && in_array('limpieza', $acciones) ? $limpieza->id : (isset($reparacion) ? $reparacion->id : null),
                        isset($limpieza) && in_array('limpieza', $acciones) ? 'limpieza' : (isset($reparacion) ? 'mantenimiento' : null)
                    ));
                }
            }
        }
        // Actualizar estado final de la habitación
        $habitacion->estado = $estadoFinal;
        $habitacion->save();

        // Registrar los pagos en la caja activa
        $caja = \App\Models\Caja::where('user_id', \Auth::id())
            ->where('estado', true)
            ->first();

        if ($caja) {
            try {
                $concepto = 'Pago de Check-out para la habitación ' . $habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente;
                if ($pagoEfectivo > 0) {
                    $caja->registrarMovimiento('ingreso', $pagoEfectivo, $concepto, 'Pago en efectivo', $reserva);
                }
                if ($pagoTarjeta > 0) {
                    $caja->registrarMovimiento('ingreso', $pagoTarjeta, $concepto, 'Pago con tarjeta', $reserva);
                }
                if ($pagoTransferencia > 0) {
                    $caja->registrarMovimiento('ingreso', $pagoTransferencia, $concepto, 'Pago por transferencia', $reserva);
                }
            } catch (\Exception $e) {
                return redirect()->route('reservas.index')
                    ->with('warning', 'Check-out realizado, pero no se pudo registrar el pago en caja: ' . $e->getMessage());
            }
        } else {
            return redirect()->route('reservas.index')
                ->with('warning', 'Check-out realizado, pero no se pudo registrar el pago en caja porque no hay una caja abierta para el usuario actual.');
        }

        \Log::info('Checkout completado exitosamente', [
            'reserva_id' => $reserva->id,
            'habitacion' => $habitacion->numero,
            'checkout_forzado' => $checkoutForzado,
            'saldo_pendiente' => $saldoPendiente,
            'usuario' => \Auth::user()->name
        ]);
        
        return redirect()->route('reservas.index')
            ->with('success', 'Check-out realizado exitosamente.');
    }

    public function confirmar(Reserva $reserva)
    {
        // Verificar que la reserva esté en estado 'Pendiente de Confirmación' o 'Reservada-Pendiente' y no haya expirado
        if (!in_array($reserva->estado, ['Pendiente de Confirmación', 'Reservada-Pendiente'])) {
            return back()->with('error', 'Solo se pueden confirmar reservas en estado PENDIENTE DE CONFIRMACIÓN o RESERVADA-PENDIENTE.');
        }

        if ($reserva->isExpired()) {
            return back()->with('error', 'No se puede confirmar la reserva porque ya ha expirado.');
        }

        // Confirmar la reserva usando el método del modelo
        if ($reserva->confirmar()) {
            // Cambiar el estado de la habitación a Reservada-Confirmada
            $habitacion = $reserva->habitacion;
            $habitacion->estado = 'Reservada-Confirmada';
            $habitacion->save();
            return back()->with('success', 'Reserva confirmada correctamente. Ahora puede proceder con el check-in.');
        } else {
            return back()->with('error', 'No se pudo confirmar la reserva.');
        }
    }

    public function checkinFromReserva(Reserva $reserva)
    {
        // Verificar que la reserva esté en un estado válido para check-in
        if (!in_array($reserva->estado, ['Pendiente', 'Pendiente de Confirmación', 'Reservada', 'Confirmada', 'Reservada-Pendiente', 'Reservada-Confirmada'])) {
            return redirect()->route('reservas.index')
                ->with('error', 'La reserva no está en un estado válido para realizar check-in.');
        }

        // Verificar que la habitación esté disponible o reservada-confirmada
        if (!in_array($reserva->habitacion->estado, ['Disponible', 'Reservada-Confirmada', 'Reservada-Pendiente'])) {
            return redirect()->route('reservas.index')
                ->with('error', 'La habitación no está disponible para check-in.');
        }

        $clientes = Cliente::all();
        $fechaActual = \Carbon\Carbon::now()->format('Y-m-d');
        $hotel = Hotel::getInfo();

        // Si la petición es POST, procesar el check-in
        if (request()->isMethod('post')) {
            $data = request()->validate([
                'nombre_cliente' => 'required|string|max:255',
                'documento_identidad' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'fecha_entrada' => 'required|date',
                'fecha_salida' => 'required|date|after:fecha_entrada',
                'adelanto' => 'nullable|numeric|min:0',
                'nit' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
            ]);

            // Validar horario de check-in
            $horaActual = \Carbon\Carbon::now()->format('H:i');
            $horaCheckinEstandar = $hotel->checkin_hora_inicio ? $hotel->checkin_hora_inicio->format('H:i') : '14:00';
            $horaCheckinAnticipado = $hotel->checkin_hora_anticipado ? $hotel->checkin_hora_anticipado->format('H:i') : '12:00';
            
            if ($horaActual >= $horaCheckinEstandar) {
                // Check-in normal
                $horaCheckin = $horaCheckinEstandar;
            } elseif ($hotel->permitir_checkin_anticipado && $horaActual >= $horaCheckinAnticipado) {
                // Check-in anticipado permitido
                $horaCheckin = $horaCheckinAnticipado;
            } else {
                return back()->with('error', 'No se puede realizar el check-in antes de las ' . $horaCheckinEstandar . '. Para check-in anticipado, debe ser después de las ' . $horaCheckinAnticipado . '.');
            }

            $nit = $data['nit'] ?: $data['documento_identidad'];
            $nombreCliente = strtoupper($data['nombre_cliente']);
            $cliente = Cliente::firstOrCreate(
                ['dpi' => $data['documento_identidad']],
                ['nombre' => $nombreCliente, 'nit' => $nit, 'telefono' => $data['telefono']]
            );

            // Actualizar la reserva existente a Check-in
            $reserva->cliente_id = $cliente->id;
            $reserva->nombre_cliente = $nombreCliente;
            $reserva->documento_cliente = $data['documento_identidad'];
            $reserva->telefono_cliente = $data['telefono'];
            $reserva->fecha_entrada = \Carbon\Carbon::parse($data['fecha_entrada'])->setTime(14, 0, 0);
            $reserva->fecha_salida = \Carbon\Carbon::parse($data['fecha_salida'])->setTime(12, 30, 0);
            $reserva->observaciones = $data['observaciones'] ?? null;
            $reserva->adelanto = $data['adelanto'] ?? 0;
            $reserva->estado = 'Check-in';
            $diasEstancia = $reserva->fecha_entrada->diffInDays($reserva->fecha_salida);
            if ($diasEstancia == 0) $diasEstancia = 1;
            $reserva->total = $diasEstancia * $reserva->habitacion->precio;
            
            // Validar que el anticipo no exceda el total de la reserva
            if ($reserva->adelanto > $reserva->total) {
                return back()
                    ->with('error', 'El anticipo (Q' . number_format($reserva->adelanto, 2) . ') no puede ser mayor al total de la reserva (Q' . number_format($reserva->total, 2) . ').')
                    ->withInput();
            }
            
            $reserva->save();

            // Actualizar estado de la habitación
            $reserva->habitacion->estado = 'Ocupada';
            $reserva->habitacion->save();

            // Registrar el anticipo en la caja si es mayor a cero
            if ($reserva->adelanto > 0) {
                $caja = \App\Models\Caja::where('user_id', \Auth::id())
                    ->where('estado', true)
                    ->first();
                if ($caja) {
                    try {
                        $caja->registrarMovimiento(
                            'ingreso',
                            $reserva->adelanto,
                            'Anticipo de reserva #' . $reserva->id,
                            'Pago de anticipo por reserva de la habitación ' . $reserva->habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente,
                            $reserva
                        );
                    } catch (\Exception $e) {
                        return redirect()->route('reservas.index')
                            ->with('warning', 'Check-in realizado, pero no se pudo registrar el anticipo en caja: ' . $e->getMessage());
                    }
                } else {
                    return redirect()->route('reservas.index')
                        ->with('warning', 'Check-in realizado, pero no se pudo registrar el anticipo en caja porque no hay una caja abierta para el usuario actual.');
                }
            }

            return redirect()->route('reservas.index')->with('success', 'Check-in realizado exitosamente.');
        }

        return view('reservas.checkin', compact('reserva', 'clientes', 'fechaActual', 'hotel'));
    }
}
