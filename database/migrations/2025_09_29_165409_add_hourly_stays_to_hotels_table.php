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
            $table->boolean('permitir_estancias_horas')->default(false)->comment('Permitir estadías por horas (check-in y check-out el mismo día)');
            $table->integer('minimo_horas_estancia')->default(2)->comment('Mínimo de horas para estadías del mismo día');
            $table->time('checkout_mismo_dia_limite')->default('20:00:00')->comment('Hora límite para check-out en estadías del mismo día');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'permitir_estancias_horas',
                'minimo_horas_estancia',
                'checkout_mismo_dia_limite'
            ]);
        });
    }
};
