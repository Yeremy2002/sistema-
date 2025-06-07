<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Habitacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'habitacions';

    protected $fillable = [
        'numero',
        'categoria_id',
        'nivel_id',
        'descripcion',
        'caracteristicas',
        'precio',
        'estado'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'estado' => 'string'
    ];

    // Relación con la categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con el nivel
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    // Relación con las reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    // Relación con las imágenes
    public function imagenes()
    {
        return $this->hasMany(HabitacionImagen::class);
    }

    // Obtiene la imagen principal
    public function imagenPrincipal()
    {
        return $this->hasOne(HabitacionImagen::class)->where('es_principal', true);
    }

    // Obtiene la reserva activa (en estado Check-in) de la habitación
    public function reservaActiva()
    {
        return $this->hasOne(Reserva::class)->where('estado', 'Check-in');
    }

    public function getEstadoColorAttribute()
    {
        return match ($this->estado) {
            'Disponible' => 'success',
            'Ocupada' => 'danger',
            'Limpieza' => 'warning',
            'Mantenimiento' => 'secondary',
            default => 'info'
        };
    }

    public function getEstadoIconoAttribute()
    {
        return match ($this->estado) {
            'Disponible' => 'check-circle',
            'Ocupada' => 'bed',
            'Limpieza' => 'broom',
            'Mantenimiento' => 'tools',
            default => 'question-circle'
        };
    }
}
