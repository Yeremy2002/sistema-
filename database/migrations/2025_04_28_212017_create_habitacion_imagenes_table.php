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
    Schema::create('habitacion_imagenes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('habitacion_id')->constrained('habitacions')->onDelete('cascade');
      $table->string('ruta');
      $table->string('descripcion')->nullable();
      $table->boolean('es_principal')->default(false);
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('habitacion_imagenes');
  }
};
