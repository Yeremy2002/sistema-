<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('cajas', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->decimal('saldo_inicial', 10, 2);
      $table->decimal('saldo_actual', 10, 2);
      $table->enum('turno', ['matutino', 'vespertino', 'nocturno']);
      $table->boolean('estado')->default(true);
      $table->timestamp('fecha_apertura')->useCurrent();
      $table->timestamp('fecha_cierre')->nullable();
      $table->text('observaciones')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down()
  {
    Schema::dropIfExists('cajas');
  }
};
