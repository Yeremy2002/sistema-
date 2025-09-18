<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // Configuración de notificaciones
            $table->integer('notificacion_intervalo_segundos')->default(30)->comment('Intervalo en segundos para actualizar notificaciones');
            $table->boolean('notificacion_activa')->default(true)->comment('Activar/desactivar actualizaciones automáticas de notificaciones');
            $table->string('notificacion_badge_color')->default('danger')->comment('Color del badge de notificaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'notificacion_intervalo_segundos',
                'notificacion_activa',
                'notificacion_badge_color'
            ]);
        });
    }
};
