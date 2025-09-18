<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Reserva;
use App\Models\Cliente;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            //
        });

        // Para cada reserva sin cliente_id, buscar un cliente que coincida con el documento_cliente
        // Si se encuentra, asignar ese cliente_id a la reserva
        $reservas = Reserva::whereNull('cliente_id')->get();

        foreach ($reservas as $reserva) {
            // Intentar encontrar un cliente por documento_cliente (DPI)
            $cliente = Cliente::where('dpi', $reserva->documento_cliente)->first();

            if (!$cliente) {
                // Si no se encuentra por DPI, intentar buscar por nombre
                // Esto es menos preciso pero puede ser útil como respaldo
                $cliente = Cliente::where('nombre', $reserva->nombre_cliente)->first();
            }

            if ($cliente) {
                $reserva->cliente_id = $cliente->id;
                $reserva->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     * No es necesario revertir esta migración ya que solo actualizó datos
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            //
        });

        // No hay acción de reversión necesaria
    }
};
