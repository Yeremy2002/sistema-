<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reparacion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'habitacion_id',
        'user_id',
        'fecha',
        'fecha_fin',
        'hora',
        'hora_fin',
        'estado',
        'tipo_reparacion',
        'costo',
        'descripcion',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_fin' => 'date',
        'hora' => 'datetime',
        'hora_fin' => 'datetime',
        'costo' => 'decimal:2'
    ];

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
