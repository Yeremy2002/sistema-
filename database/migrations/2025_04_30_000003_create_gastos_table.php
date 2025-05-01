<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('gastos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('concepto');
      $table->decimal('monto', 10, 2);
      $table->text('descripcion')->nullable();
      $table->date('fecha');
      $table->string('comprobante')->nullable();
      $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('gastos');
  }
};
