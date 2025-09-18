<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HabitacionImagen extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'habitacion_imagenes';

  protected $fillable = [
    'habitacion_id',
    'ruta',
    'descripcion',
    'es_principal'
  ];

  protected $casts = [
    'es_principal' => 'boolean'
  ];

  public function habitacion()
  {
    return $this->belongsTo(Habitacion::class);
  }
}
