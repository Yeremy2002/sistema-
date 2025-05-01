<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('movimientos_caja', function (Blueprint $table) {
      $table->id();
      $table->foreignId('caja_id')->constrained()->onDelete('cascade');
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->morphs('movimientable'); // Para reservas, gastos u otros tipos de movimientos
      $table->enum('tipo', ['ingreso', 'egreso']);
      $table->decimal('monto', 10, 2);
      $table->string('concepto');
      $table->text('descripcion')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('movimientos_caja');
  }
};
