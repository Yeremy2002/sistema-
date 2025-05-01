<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('cajas', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->decimal('saldo_inicial', 10, 2);
      $table->decimal('saldo_actual', 10, 2);
      $table->decimal('total_ingresos', 10, 2)->default(0);
      $table->decimal('total_egresos', 10, 2)->default(0);
      $table->datetime('fecha_apertura');
      $table->datetime('fecha_cierre')->nullable();
      $table->text('observaciones_apertura')->nullable();
      $table->text('observaciones_cierre')->nullable();
      $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('cajas');
  }
};
