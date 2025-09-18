<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNotificationUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:fix-urls {--dry-run : Solo mostrar las notificaciones que se actualizarían}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige las URLs incorrectas en las notificaciones existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Buscando notificaciones con URLs incorrectas...');

        // Buscar notificaciones con URLs que no incluyen el puerto correcto
        $notifications = DB::table('notifications')
            ->whereRaw("JSON_EXTRACT(data, '$.action_url') LIKE 'http://localhost/%'")
            ->whereRaw("JSON_EXTRACT(data, '$.action_url') NOT LIKE 'http://localhost:8001/%'")
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No se encontraron notificaciones con URLs incorrectas.');
            return;
        }

        $this->info("Se encontraron {$notifications->count()} notificaciones con URLs incorrectas.");

        if ($dryRun) {
            $this->info('Modo dry-run activado. Mostrando notificaciones que se actualizarían:');

            foreach ($notifications as $notification) {
                $data = json_decode($notification->data, true);
                $this->line("ID: {$notification->id}");
                $this->line("URL actual: {$data['action_url']}");
                $newUrl = str_replace('http://localhost/', 'http://localhost:8001/', $data['action_url']);
                $this->line("URL nueva: {$newUrl}");
                $this->line('---');
            }

            return;
        }

        $updated = 0;
        $bar = $this->output->createProgressBar($notifications->count());

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);

            // Actualizar la URL
            $data['action_url'] = str_replace('http://localhost/', 'http://localhost:8001/', $data['action_url']);

            // Actualizar en la base de datos
            DB::table('notifications')
                ->where('id', $notification->id)
                ->update(['data' => json_encode($data)]);

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Se actualizaron {$updated} notificaciones exitosamente.");
    }
}
