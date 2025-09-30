<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'habitacion_id',
        'nombre_cliente',
        'documento_cliente',
        'telefono_cliente',
        'fecha_entrada',
        'fecha_salida',
        'adelanto',
        'estado',
        'observaciones',
        'total',
        'user_id',
        'cliente_id',
        'expires_at'
    ];

    protected $casts = [
        'fecha_entrada' => 'datetime',
        'fecha_salida' => 'datetime',
        'adelanto' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function movimientos()
    {
        return $this->morphMany(MovimientoCaja::class, 'movimientable');
    }

    public function getDiasEstanciaAttribute()
    {
        $dias = $this->fecha_entrada->diffInDays($this->fecha_salida);
        // Para estadías del mismo día (0 días), considerar como 1 día
        return $dias === 0 ? 1 : $dias;
    }

    public function getTotalAttribute($value)
    {
        // Si ya hay un total guardado en la base de datos, usarlo
        // Esto previene recalcular cuando ya se guardó un total específico
        if ($value !== null && $value > 0) {
            return $value;
        }
        
        // Si no hay total guardado, calcular usando días de estancia
        return $this->diasEstancia * $this->habitacion->precio;
    }

    public function getPendienteAttribute()
    {
        return $this->total - $this->adelanto;
    }

    /**
     * Verifica si la reserva ha expirado
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verifica si la reserva está pendiente de confirmación y no ha expirado
     */
    public function isPendingConfirmation()
    {
        return $this->estado === 'Pendiente de Confirmación' && !$this->isExpired();
    }

    /**
     * Confirma la reserva (cambia de 'Pendiente de Confirmación' o 'Reservada-Pendiente' a 'Pendiente')
     */
    public function confirmar()
    {
        if (in_array($this->estado, ['Pendiente de Confirmación', 'Reservada-Pendiente']) && !$this->isExpired()) {
            $this->estado = 'Pendiente';
            $this->expires_at = null; // Limpiar la fecha de expiración
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Cancela la reserva por expiración
     */
    public function cancelarPorExpiracion()
    {
        if ($this->estado === 'Pendiente de Confirmación' && $this->isExpired()) {
            $this->estado = 'Cancelada';
            $this->save();

            // Liberar la habitación si estaba reservada en cualquier estado de reserva
            if ($this->habitacion && in_array($this->habitacion->estado, ['Reservada', 'Reservada-Pendiente', 'Reservada-Confirmada'])) {
                $this->habitacion->estado = 'Disponible';
                $this->habitacion->save();
            }

            // Enviar notificaciones a recepcionistas y administradores
            $this->enviarNotificacionExpiracion();

            return true;
        }
        return false;
    }

    /**
     * Envía notificaciones cuando una reserva expira
     */
    private function enviarNotificacionExpiracion()
    {
        // Obtener usuarios activos con roles de recepcionista y administrador
        $usuariosANotificar = \App\Models\User::where('active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Recepcionista', 'Administrador', 'Super Admin']);
            })
            ->get();

        // Enviar notificación a cada usuario
        foreach ($usuariosANotificar as $usuario) {
            $usuario->notify(new \App\Notifications\ReservaExpiradaNotification($this));
        }
    }

    /**
     * Establece la fecha de expiración basada en la configuración del hotel
     */
    public function setExpirationTime()
    {
        $hotel = \App\Models\Hotel::first();
        $tiempoExpiracionHoras = $hotel ? $hotel->reservas_vencidas_horas : 24; // 24 horas por defecto

        $this->expires_at = now()->addHours($tiempoExpiracionHoras);
        $this->save();
    }

    public function registrarPago($monto, $caja, $concepto = 'Pago de reserva')
    {
        $this->adelanto += $monto;
        $this->save();

        return $caja->registrarMovimiento(
            'ingreso',
            $monto,
            $concepto,
            "Pago de reserva #" . $this->id,
            $this
        );
    }
}
