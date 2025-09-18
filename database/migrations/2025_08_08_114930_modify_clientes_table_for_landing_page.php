<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Hacer NIT y DPI opcionales para clientes de landing page
            $table->string('nit')->nullable()->change();
            $table->string('dpi')->nullable()->change();

            // Agregar campos adicionales para mejorar la experiencia
            if (!Schema::hasColumn('clientes', 'email')) {
                $table->string('email')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('clientes', 'direccion')) {
                $table->text('direccion')->nullable()->after('email');
            }
            if (!Schema::hasColumn('clientes', 'documento')) {
                $table->string('documento')->nullable()->after('direccion');
            }

            // Campo para indicar el origen del cliente
            if (!Schema::hasColumn('clientes', 'origen')) {
                $table->enum('origen', ['landing', 'backend'])->default('backend')->after('documento');
            }
        });

        // Eliminar restricciones unique existentes si existen
        try {
try {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement('ALTER TABLE clientes DROP INDEX clientes_nit_unique');
                    DB::statement('ALTER TABLE clientes DROP INDEX clientes_dpi_unique');
                } else {
if (DB::getDriverName() === 'mysql') {
                    DB::statement('ALTER TABLE clientes DROP INDEX IF EXISTS clientes_nit_unique');
                    DB::statement('ALTER TABLE clientes DROP INDEX IF EXISTS clientes_dpi_unique');
                } else {
                    DB::statement('DROP INDEX IF EXISTS clientes_nit_unique');
                    DB::statement('DROP INDEX IF EXISTS clientes_dpi_unique');
                }
                }
            } catch (Exception $e) {
                // Continuar si no existen
            }
        } catch (Exception $e) {
            // Las restricciones pueden no existir, continuar
        }

// Crear índices únicos, evitando duplicados de forma compatible
        if (DB::getDriverName() === 'mysql') {
            $dbName = DB::getDatabaseName();
            // Verificar existencia del índice NIT
            $nitExists = DB::table('information_schema.statistics')
                ->where('table_schema', $dbName)
                ->where('table_name', 'clientes')
                ->where('index_name', 'clientes_nit_unique')
                ->exists();
            if (!$nitExists) {
                DB::statement('ALTER TABLE clientes ADD UNIQUE INDEX clientes_nit_unique (nit)');
            }
            // Verificar existencia del índice DPI
            $dpiExists = DB::table('information_schema.statistics')
                ->where('table_schema', $dbName)
                ->where('table_name', 'clientes')
                ->where('index_name', 'clientes_dpi_unique')
                ->exists();
            if (!$dpiExists) {
                DB::statement('ALTER TABLE clientes ADD UNIQUE INDEX clientes_dpi_unique (dpi)');
            }
        } else {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS clientes_nit_unique ON clientes (nit) WHERE nit IS NOT NULL');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS clientes_dpi_unique ON clientes (dpi) WHERE dpi IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminar índices condicionales
            try {
                DB::statement('DROP INDEX IF EXISTS clientes_nit_unique');
                DB::statement('DROP INDEX IF EXISTS clientes_dpi_unique');
            } catch (Exception $e) {
                // Continuar si no existen
            }

            // Remover campos nuevos si existen
            if (Schema::hasColumn('clientes', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('clientes', 'direccion')) {
                $table->dropColumn('direccion');
            }
            if (Schema::hasColumn('clientes', 'documento')) {
                $table->dropColumn('documento');
            }
            if (Schema::hasColumn('clientes', 'origen')) {
                $table->dropColumn('origen');
            }
        });
    }
};
