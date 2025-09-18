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
            // Agregar el campo saldo_final después del campo saldo_actual
            $table->decimal('saldo_final', 10, 2)->nullable()->after('saldo_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            // Eliminar el campo saldo_final en caso de rollback
            $table->dropColumn('saldo_final');
        });
    }
};
