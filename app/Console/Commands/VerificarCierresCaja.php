<?php

namespace App\Console\Commands;

use App\Models\Caja;
use App\Models\User;
use App\Notifications\RecordatorioCierreCaja;
use Illuminate\Console\Command;
use Carbon\Carbon;

class VerificarCierresCaja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cajas:verificar-cierres {--force : Forzar envío de notificaciones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica cajas abiertas y envía recordatorios de cierre';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando cajas pendientes de cierre...');
        
        $now = Carbon::now();
        $horaActual = $now->hour;
        
        // Definir horarios críticos para recordatorios
        $esFinTurnoMatutino = ($horaActual >= 13 && $horaActual < 15); // 1-3 PM
        $esFinTurnoNocturno = ($horaActual >= 5 && $horaActual < 7);   // 5-7 AM
        $esCambioTurno = $esFinTurnoMatutino || $esFinTurnoNocturno;
        
        // Buscar cajas abiertas
        $cajasAbiertas = Caja::with('user')
            ->where('estado', true)
            ->get();
            
        if ($cajasAbiertas->isEmpty()) {
            $this->info('✅ No hay cajas abiertas pendientes de cierre.');
            return;
        }
        
        $recordatoriosEnviados = 0;
        $cajasProblematicas = collect();
        
        foreach ($cajasAbiertas as $caja) {
            $horasAbierta = $now->diffInHours($caja->fecha_apertura);
            $esCajaAntigua = $caja->fecha_apertura->startOfDay()->lt($now->startOfDay());
            
            // Determinar si necesita recordatorio
            $necesitaRecordatorio = false;
            $tipoRecordatorio = 'normal';
            $razon = '';
            
            if ($esCajaAntigua) {
                $necesitaRecordatorio = true;
                $tipoRecordatorio = 'urgente';
                $razon = 'Caja abierta desde día anterior';
                $cajasProblematicas->push($caja);
            } elseif ($horasAbierta > 12) {
                $necesitaRecordatorio = true;
                $tipoRecordatorio = 'advertencia';
                $razon = sprintf('Caja abierta por %.1f horas', $horasAbierta);
            } elseif ($esCambioTurno) {
                $turnoActual = $this->detectarTurno($horaActual);
                if ($caja->turno !== $turnoActual) {
                    $necesitaRecordatorio = true;
                    $tipoRecordatorio = 'cambio_turno';
                    $razon = "Hora de cambio de turno ({$caja->turno} -> {$turnoActual})";
                }
            } elseif ($this->option('force')) {
                $necesitaRecordatorio = true;
                $tipoRecordatorio = 'forzado';
                $razon = 'Verificación forzada';
            }
            
            if ($necesitaRecordatorio) {
                try {
                    $caja->user->notify(new RecordatorioCierreCaja($caja, $tipoRecordatorio, $razon));
                    $recordatoriosEnviados++;
                    
                    $this->line("📧 Recordatorio enviado a {$caja->user->name} (Caja #{$caja->id}): {$razon}");
                    
                    // También notificar a supervisores si es urgente
                    if ($tipoRecordatorio === 'urgente') {
                        $this->notificarSupervisores($caja, $razon);
                    }
                    
                } catch (\Exception $e) {
                    $this->error("❌ Error enviando recordatorio a {$caja->user->name}: {$e->getMessage()}");
                }
            }
        }
        
        // Resumen
        $this->info("\n📊 Resumen:");
        $this->info("   • Cajas abiertas encontradas: {$cajasAbiertas->count()}");
        $this->info("   • Recordatorios enviados: {$recordatoriosEnviados}");
        
        if ($cajasProblematicas->isNotEmpty()) {
            $this->warn("   • Cajas problemáticas (día anterior): {$cajasProblematicas->count()}");
            foreach ($cajasProblematicas as $caja) {
                $this->warn("     - Caja #{$caja->id} ({$caja->user->name}) - Abierta desde {$caja->fecha_apertura->format('d/m/Y H:i')}");
            }
        }
        
        $this->info("\n✅ Verificación completada.");
    }
    
    /**
     * Detecta el turno actual basado en la hora
     */
    private function detectarTurno($hora)
    {
        return ($hora >= 6 && $hora < 18) ? 'matutino' : 'nocturno';
    }
    
    /**
     * Notifica a supervisores sobre cajas problemáticas
     */
    private function notificarSupervisores(Caja $caja, string $razon)
    {
        try {
            $supervisores = User::role(['Administrador', 'Super Admin'])
                ->where('active', true)
                ->get();
                
            foreach ($supervisores as $supervisor) {
                $supervisor->notify(new RecordatorioCierreCaja($caja, 'supervisor', $razon));
            }
            
            $this->line("   👥 Supervisores notificados sobre caja problemática");
        } catch (\Exception $e) {
            $this->error("   ❌ Error notificando supervisores: {$e->getMessage()}");
        }
    }
}
