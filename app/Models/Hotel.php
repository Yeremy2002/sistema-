<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
  protected $fillable = [
    'nombre',
    'nit',
    'nombre_fiscal',
    'direccion',
    'simbolo_moneda',
    'logo',
    'checkin_hora_inicio',
    'checkin_hora_anticipado',
    'checkout_hora_inicio',
    'checkout_hora_fin',
    'permitir_checkin_anticipado',
    'permitir_estancias_horas',
    'minimo_horas_estancia',
    'checkout_mismo_dia_limite',
    'reservas_vencidas_horas',
    'scheduler_frecuencia',
    'notificacion_intervalo_segundos',
    'notificacion_activa',
    'notificacion_badge_color',
  ];

  protected $casts = [
    'checkin_hora_inicio' => 'datetime:H:i',
    'checkin_hora_anticipado' => 'datetime:H:i',
    'checkout_hora_inicio' => 'datetime:H:i',
    'checkout_hora_fin' => 'datetime:H:i',
    'checkout_mismo_dia_limite' => 'datetime:H:i',
    'permitir_checkin_anticipado' => 'boolean',
    'permitir_estancias_horas' => 'boolean',
    'notificacion_activa' => 'boolean',
  ];

  // Solo habrá un registro en la tabla
  public static function getInfo()
  {
    return static::firstOrCreate([]);
  }
}
