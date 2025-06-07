<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Cliente;
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
        return view('reservas.index', compact('reservas'));
    }

    public function create(Request $request)
    {
        $habitaciones = Habitacion::where('estado', 'Disponible')->get();

        $habitacionSeleccionada = null;
        if ($request->has('habitacion')) {
            $habitacionSeleccionada = Habitacion::findOrFail($request->habitacion);
        }

        $clientes = Cliente::all();
        return view('reservas.create', compact('habitaciones', 'habitacionSeleccionada', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitacions,id',
            'nombre_cliente' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'observaciones' => 'nullable|string',
            'adelanto' => 'nullable|numeric|min:0',
            'nit' => 'nullable|string|max:255'
        ]);

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

        // Configurar la fecha de entrada con la hora específica de check-in (14:00)
        $fechaEntrada = \Carbon\Carbon::parse($request->fecha_entrada)->setTime(14, 0, 0);
        $reserva->fecha_entrada = $fechaEntrada;

        // Configurar la fecha de salida con la hora específica de check-out (12:30)
        $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida)->setTime(12, 30, 0);
        $reserva->fecha_salida = $fechaSalida;

        $reserva->observaciones = $request->observaciones;
        $reserva->adelanto = $request->adelanto ?? 0;
        $reserva->estado = 'Check-in';

        // Calcular el total basado en los días de estancia (ya se configuraron las horas correctas)
        $diasEstancia = $fechaEntrada->diffInDays($fechaSalida);
        if ($diasEstancia == 0) $diasEstancia = 1; // Mínimo un día
        $reserva->total = $diasEstancia * $habitacion->precio;

        $reserva->save();

        // Actualizar el estado de la habitación a Ocupada
        $habitacion->estado = 'Ocupada';
        $habitacion->save();

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

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva creada exitosamente.');
    }

    public function show(Reserva $reserva)
    {
        return view('reservas.show', compact('reserva'));
    }

    public function edit(Reserva $reserva)
    {
        $habitaciones = Habitacion::all();
        $clientes = Cliente::all();
        return view('reservas.edit', compact('reserva', 'habitaciones', 'clientes'));
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

            $nit = $data['nit'] ?: $data['documento_identidad'];
            $nombreCliente = strtoupper($data['nombre_cliente']);
            $cliente = Cliente::firstOrCreate(
                ['dpi' => $data['documento_identidad']],
                ['nombre' => $nombreCliente, 'nit' => $nit, 'telefono' => $data['telefono']]
            );

            $reserva = new Reserva();
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
            $reserva->save();

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

        return view('reservas.checkin', compact('habitacione', 'clientes', 'fechaActual'));
    }

    public function checkout(Reserva $reserva)
    {
        if ($reserva->estado !== 'Check-in') {
            return redirect()->route('reservas.index')
                ->with('error', 'La reserva no está activa para realizar el Check-out.');
        }

        return view('reservas.checkout', compact('reserva'));
    }

    public function storeCheckout(Request $request, Reserva $reserva)
    {
        $request->validate([
            'monto_total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,transferencia',
            'numero_autorizacion' => 'nullable|string',
            'nombre_banco' => 'nullable|string',
            'numero_boleta' => 'nullable|string',
        ]);

        $reserva->estado = 'Check-out';
        $reserva->observaciones = $request->observaciones;
        $reserva->save();

        // Liberar la habitación
        $habitacion = $reserva->habitacion;
        $habitacion->estado = 'Limpieza';
        $habitacion->save();

        // Registrar el pago en la caja activa
        $caja = \App\Models\Caja::where('user_id', \Auth::id())
            ->where('estado', true)
            ->first();

        if ($caja) {
            try {
                $concepto = 'Pago de Check-out para la habitación ' . $habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente;
                $observaciones = 'Método de pago: ' . $request->metodo_pago;

                if ($request->metodo_pago === 'tarjeta' || $request->metodo_pago === 'transferencia') {
                    $observaciones .= ', Número de autorización: ' . $request->numero_autorizacion;
                }

                if ($request->metodo_pago === 'transferencia') {
                    $observaciones .= ', Banco: ' . $request->nombre_banco . ', Número de boleta: ' . $request->numero_boleta;
                }

                $caja->registrarMovimiento(
                    'ingreso',
                    $request->monto_total,
                    $concepto,
                    $observaciones,
                    $reserva
                );
            } catch (\Exception $e) {
                return redirect()->route('reservas.index')
                    ->with('warning', 'Check-out realizado, pero no se pudo registrar el pago en caja: ' . $e->getMessage());
            }
        } else {
            return redirect()->route('reservas.index')
                ->with('warning', 'Check-out realizado, pero no se pudo registrar el pago en caja porque no hay una caja abierta para el usuario actual.');
        }

        return redirect()->route('reservas.index')
            ->with('success', 'Check-out realizado exitosamente.');
    }
}
