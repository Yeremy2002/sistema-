<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'turno',
    'saldo_inicial',
    'saldo_actual',
    'fecha_apertura',
    'fecha_cierre',
    'observaciones_apertura',
    'observaciones_cierre',
    'estado'
  ];

  protected $casts = [
    'saldo_inicial' => 'decimal:2',
    'saldo_actual' => 'decimal:2',
    'fecha_apertura' => 'datetime',
    'fecha_cierre' => 'datetime',
    'estado' => 'boolean'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function movimientos()
  {
    return $this->hasMany(MovimientoCaja::class);
  }

  public function registrarMovimiento($tipo, $monto, $concepto, $observaciones = null, $movimientable = null)
  {
    // Validar tipo de movimiento
    if (!in_array($tipo, ['ingreso', 'egreso'])) {
      throw new \InvalidArgumentException('Tipo de movimiento inválido');
    }

    // Crear el movimiento
    $movimiento = new MovimientoCaja([
      'user_id' => auth()->id(),
      'tipo' => $tipo,
      'monto' => $monto,
      'concepto' => $concepto,
      'observaciones' => $observaciones
    ]);

    // Si hay un modelo relacionado (por ejemplo, una reserva)
    if ($movimientable) {
      $movimiento->movimientable()->associate($movimientable);
    }

    // Guardar el movimiento
    $this->movimientos()->save($movimiento);

    // Actualizar el saldo de la caja
    $this->saldo_actual = $tipo === 'ingreso'
      ? $this->saldo_actual + $monto
      : $this->saldo_actual - $monto;
    $this->save();

    return $movimiento;
  }
}
