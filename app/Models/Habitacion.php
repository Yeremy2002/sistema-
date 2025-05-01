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
}
