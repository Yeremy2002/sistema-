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
        Schema::table('cajas', function (Blueprint $table) {
            // Primero renombramos el campo observaciones a observaciones_apertura
            $table->renameColumn('observaciones', 'observaciones_apertura');

            // Luego añadimos el campo observaciones_cierre
            $table->text('observaciones_cierre')->nullable()->after('observaciones_apertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->renameColumn('observaciones_apertura', 'observaciones');
            $table->dropColumn('observaciones_cierre');
        });
    }
};
