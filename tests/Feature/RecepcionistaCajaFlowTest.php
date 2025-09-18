<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Caja;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecepcionistaCajaFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valida el flujo completo Recepcionista + Caja
     *
     * 1. Usuario sin caja -> es redirigido a apertura
     * 2. Abre caja correctamente
     * 3. Accede a ruta operativa sin redirección
     * 4. Cierra caja -> vuelve a requerir apertura
     */
    public function test_recepcionista_caja_workflow(): void
    {
        // Preparar datos básicos (Hotel, roles, permisos).
        // Ejecuta los seeders globales para roles / permisos / hotel
        $this->seed();

        // Crear hotel mínimo si el seeder no lo hizo
        if (!Hotel::first()) {
            Hotel::factory()->create();
        }

        // Crear usuario Recepcionista
        $user = User::factory()->create([
            'email' => 'david.ortiz@gmail.com',
            'password' => Hash::make('password'),
        ]);
$user->assignRole('Recepcionista');
// Asegurar que exista el permiso y asignarlo
$permCrearReservas = 'crear reservas';
        \Spatie\Permission\Models\Permission::findOrCreate($permCrearReservas);
$user->givePermissionTo(['abrir caja','cerrar caja','ver cajas','ver reservas',$permCrearReservas]);

        // 1) Intentar acceder a ruta que requiere caja (reservas.store)
        $response = $this->actingAs($user)
            ->post(route('reservas.store'), []); // datos vacíos solo para gatillar middleware

        $response->assertRedirect(route('cajas.create'));
        $this->assertEquals('Debe abrir una caja antes de realizar operaciones. Por favor, complete el formulario para aperturar su caja.', session('warning'));

        // 2) Abrir caja
        $createResponse = $this->actingAs($user)
            ->post(route('cajas.store'), [
                'saldo_inicial' => 0,
                'turno' => $this->horaEsMatutina() ? 'matutino' : 'nocturno',
            ]);
$caja = Caja::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($caja, 'La caja debe haberse creado.');
        $createResponse->assertRedirect(route('cajas.show', $caja));
        $this->assertTrue($caja->estado, 'La caja debe estar abierta');

        // 3) Acceder a ruta operativa ahora debe funcionar (GET reservas.create)
        $operResponse = $this->actingAs($user)
            ->get(route('reservas.create'));
        $operResponse->assertStatus(200);

        // 4) Cerrar caja
        $closeResponse = $this->actingAs($user)
            ->patch(route('cajas.update', $caja), [
                'saldo_final' => 0,
            ]);
        $caja->refresh();
        $this->assertFalse($caja->estado, 'La caja debe quedar cerrada');
        $closeResponse->assertSessionHas('success');

        // 5) Al acceder de nuevo a ruta operativa se vuelve a requerir caja
        $again = $this->actingAs($user)
            ->post(route('reservas.store'), []);
        $again->assertRedirect(route('cajas.create'));
    }

    private function horaEsMatutina(): bool
    {
        $h = now()->hour;
        return $h >= 6 && $h < 18;
    }
}

