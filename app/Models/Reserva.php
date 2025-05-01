<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'habitacion_id',
        'nombre_cliente',
        'documento_identidad',
        'telefono',
        'fecha_entrada',
        'fecha_salida',
        'adelanto',
        'estado',
        'observaciones',
        'total',
        'user_id'
    ];

    protected $casts = [
        'fecha_entrada' => 'datetime',
        'fecha_salida' => 'datetime',
        'adelanto' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->morphMany(MovimientoCaja::class, 'movimientable');
    }

    public function getDiasEstanciaAttribute()
    {
        return $this->fecha_entrada->diffInDays($this->fecha_salida);
    }

    public function getTotalAttribute()
    {
        return $this->diasEstancia * $this->habitacion->precio;
    }

    public function getPendienteAttribute()
    {
        return $this->total - $this->adelanto;
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
