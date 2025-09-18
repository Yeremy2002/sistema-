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
        if (!Schema::hasTable('clientes')) {
            Schema::create('clientes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('nit')->unique();
                $table->string('dpi')->unique();
                $table->string('telefono');
                $table->timestamps();
            });
        } else {
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasColumn('clientes', 'nombre')) {
                    $table->string('nombre');
                }
                if (!Schema::hasColumn('clientes', 'nit')) {
                    $table->string('nit')->unique();
                }
                if (!Schema::hasColumn('clientes', 'dpi')) {
                    $table->string('dpi')->unique();
                }
                if (!Schema::hasColumn('clientes', 'telefono')) {
                    $table->string('telefono');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
