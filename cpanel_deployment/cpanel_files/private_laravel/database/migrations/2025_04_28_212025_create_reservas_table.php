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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitacions');
            $table->foreignId('user_id')->constrained('users');
            $table->string('nombre_cliente');
            $table->string('documento_cliente');
            $table->string('telefono_cliente')->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('fecha_entrada');
            $table->datetime('fecha_salida');
            $table->decimal('total', 10, 2);
            $table->decimal('adelanto', 10, 2)->default(0);
            $table->enum('estado', ['Pendiente de Confirmación', 'Pendiente', 'Check-in', 'Check-out', 'Cancelada'])->default('Pendiente de Confirmación');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
