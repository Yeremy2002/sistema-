<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Habitacion;
use App\Models\Reserva;

echo "=== DIAGNÓSTICO DE ESTADOS DE HABITACIONES ===\n\n";

// Obtener todas las habitaciones
$habitaciones = Habitacion::with(['reservaActiva', 'reservas'])->get();

$inconsistentes = [];
$correctas = [];

foreach ($habitaciones as $habitacion) {
    echo "Habitación #{$habitacion->numero} - Estado: {$habitacion->estado}\n";
    
    if ($habitacion->estado === 'Ocupada') {
        $reservaActiva = $habitacion->reservas()->where('estado', 'Check-in')->first();
        
        if (!$reservaActiva) {
            echo "  ❌ INCONSISTENTE: Marcada como 'Ocupada' pero sin reserva activa\n";
            $inconsistentes[] = $habitacion;
        } else {
            echo "  ✅ CORRECTA: Tiene reserva activa ID #{$reservaActiva->id}\n";
            $correctas[] = $habitacion;
        }
    } else {
        echo "  ℹ️  No aplica verificación (no está ocupada)\n";
    }
    
    echo "\n";
}

echo "\n=== RESUMEN ===\n";
echo "Total de habitaciones: " . $habitaciones->count() . "\n";
echo "Habitaciones ocupadas correctamente: " . count($correctas) . "\n";
echo "Habitaciones con estado inconsistente: " . count($inconsistentes) . "\n\n";

if (!empty($inconsistentes)) {
    echo "HABITACIONES QUE NECESITAN CORRECCIÓN:\n";
    foreach ($inconsistentes as $habitacion) {
        echo "- Habitación #{$habitacion->numero} (ID: {$habitacion->id})\n";
    }
    
    echo "\nPara corregir estas habitaciones, puedes:\n";
    echo "1. Usar el dashboard (botón 'Corregir Estado' que aparecerá)\n";
    echo "2. Ejecutar este comando: php artisan tinker\n";
    echo "   Y luego: App\\Models\\Habitacion::whereIn('id', [" . implode(',', array_column($inconsistentes, 'id')) . "])->update(['estado' => 'Disponible']);\n";
}

echo "\n=== DIAGNÓSTICO COMPLETO ===\n";
