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
            $table->integer('reservas_vencidas_horas')->default(24)->comment('Horas después de fecha de entrada para marcar como vencida');
            $table->enum('scheduler_frecuencia', ['12h', '24h', '48h', '72h'])->default('24h')->comment('Frecuencia de ejecución del comando de reservas vencidas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['reservas_vencidas_horas', 'scheduler_frecuencia']);
        });
    }
};
