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
            $table->time('checkin_hora_inicio')->default('14:00:00')->comment('Hora de inicio de check-in');
            $table->time('checkin_hora_anticipado')->default('12:00:00')->comment('Hora mínima para check-in anticipado');
            $table->time('checkout_hora_inicio')->default('12:30:00')->comment('Hora de inicio de check-out');
            $table->time('checkout_hora_fin')->default('13:00:00')->comment('Hora límite de check-out');
            $table->boolean('permitir_checkin_anticipado')->default(true)->comment('Permitir check-in anticipado con confirmación');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_hora_inicio',
                'checkin_hora_anticipado',
                'checkout_hora_inicio',
                'checkout_hora_fin',
                'permitir_checkin_anticipado'
            ]);
        });
    }
};
