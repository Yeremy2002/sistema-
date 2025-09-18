<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gasto extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'concepto',
    'monto',
    'descripcion',
    'fecha',
    'comprobante',
    'estado'
  ];

  protected $casts = [
    'fecha' => 'date',
    'monto' => 'decimal:2'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function movimientos()
  {
    return $this->morphMany(MovimientoCaja::class, 'movimientable');
  }
}
