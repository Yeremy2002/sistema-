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
    Schema::create('hotels', function (Blueprint $table) {
      $table->id();
      $table->string('nombre')->nullable();
      $table->string('nit')->nullable();
      $table->string('nombre_fiscal')->nullable();
      $table->text('direccion')->nullable();
      $table->string('simbolo_moneda')->default('Q.');
      $table->string('logo')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hotels');
  }
};
