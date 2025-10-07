<?php
/**
 * Laravel 12 Setup Script for cPanel (No SSH Required)
 *
 * Upload this file to /home/casaviejagt/public_html/setup.php
 * Then visit: https://casaviejagt.com/setup.php in your browser
 *
 * ⚠️ DELETE THIS FILE after setup is complete!
 */

// Security: Only allow execution once
$setupCompleteFile = __DIR__ . '/.setup_complete';
if (file_exists($setupCompleteFile)) {
    die('Setup already completed. Delete .setup_complete file to run again.');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Setup - Hotel Casa Vieja</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        .step {
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
            background: #ecf0f1;
        }
        .success {
            border-left-color: #27ae60;
            background: #d5f4e6;
        }
        .error {
            border-left-color: #e74c3c;
            background: #fadbd8;
        }
        .warning {
            border-left-color: #f39c12;
            background: #fef5e7;
        }
        button {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        button:hover {
            background: #2980b9;
        }
        .danger {
            background: #e74c3c;
        }
        .danger:hover {
            background: #c0392b;
        }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        code {
            background: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 Hotel Casa Vieja - Setup Laravel 12</h1>
        <p class="subtitle">Configuración automática para cPanel (Sin SSH)</p>

        <?php
        // Check if setup has been requested
        if (!isset($_POST['action'])) {
            ?>
            <div class="step warning">
                <strong>⚠️ IMPORTANTE:</strong> Este script realizará la configuración inicial de Laravel.
                Asegúrate de:
                <ul>
                    <li>Haber extraído el archivo .tar.gz</li>
                    <li>Haber subido el archivo .env</li>
                    <li>Tener la base de datos MySQL configurada</li>
                </ul>
            </div>

            <h3>📋 Tareas a realizar:</h3>
            <div class="step">
                <strong>1. Generar APP_KEY</strong> - Clave de encriptación única
            </div>
            <div class="step">
                <strong>2. Establecer permisos</strong> - storage/ y bootstrap/cache/
            </div>
            <div class="step">
                <strong>3. Ejecutar migraciones</strong> - Crear tablas en MySQL
            </div>
            <div class="step">
                <strong>4. Poblar datos esenciales</strong> - Admin, configuración, etc.
            </div>
            <div class="step">
                <strong>5. Cachear configuración</strong> - Optimizar para producción
            </div>
            <div class="step">
                <strong>6. Crear storage link</strong> - Para archivos públicos
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="setup">
                <button type="submit">🚀 Iniciar Setup</button>
            </form>
            <?php
        } elseif ($_POST['action'] === 'setup') {
            echo '<h3>🔧 Ejecutando Setup...</h3>';

            // Initialize Laravel
            require __DIR__.'/vendor/autoload.php';
            $app = require_once __DIR__.'/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

            // Step 1: Generate APP_KEY
            echo '<div class="step">';
            echo '<strong>1️⃣ Generando APP_KEY...</strong><br>';
            try {
                $exitCode = $kernel->call('key:generate', ['--force' => true]);
                if ($exitCode === 0) {
                    echo '<span style="color: #27ae60;">✓ APP_KEY generada exitosamente</span>';
                } else {
                    echo '<span style="color: #e74c3c;">✗ Error al generar APP_KEY</span>';
                }
            } catch (Exception $e) {
                echo '<span style="color: #e74c3c;">✗ Error: ' . $e->getMessage() . '</span>';
            }
            echo '</div>';

            // Step 2: Set permissions
            echo '<div class="step">';
            echo '<strong>2️⃣ Estableciendo permisos...</strong><br>';
            try {
                chmod(__DIR__ . '/storage', 0775);
                chmod(__DIR__ . '/bootstrap/cache', 0775);

                // Set recursive permissions
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(__DIR__ . '/storage'),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    chmod($item, 0775);
                }

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(__DIR__ . '/bootstrap/cache'),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    chmod($item, 0775);
                }

                echo '<span style="color: #27ae60;">✓ Permisos establecidos correctamente</span>';
            } catch (Exception $e) {
                echo '<span style="color: #f39c12;">⚠ Permisos: ' . $e->getMessage() . '</span><br>';
                echo '<small>Puede que necesites establecer permisos manualmente via File Manager</small>';
            }
            echo '</div>';

            // Step 3: Run migrations
            echo '<div class="step">';
            echo '<strong>3️⃣ Ejecutando migraciones...</strong><br>';
            try {
                $exitCode = $kernel->call('migrate', ['--force' => true]);
                if ($exitCode === 0) {
                    echo '<span style="color: #27ae60;">✓ Migraciones ejecutadas exitosamente</span>';
                } else {
                    echo '<span style="color: #e74c3c;">✗ Error en migraciones</span>';
                }
            } catch (Exception $e) {
                echo '<span style="color: #e74c3c;">✗ Error: ' . $e->getMessage() . '</span>';
            }
            echo '</div>';

            // Step 4: Seed essential data
            echo '<div class="step">';
            echo '<strong>4️⃣ Poblando datos esenciales...</strong><br>';
            try {
                $exitCode = $kernel->call('db:seed', [
                    '--class' => 'Database\\Seeders\\EssentialDataSeeder',
                    '--force' => true
                ]);
                if ($exitCode === 0) {
                    echo '<span style="color: #27ae60;">✓ Datos esenciales poblados</span><br>';
                    echo '<small>Admin user, hotel config, roles, permissions creados</small>';
                } else {
                    echo '<span style="color: #e74c3c;">✗ Error al poblar datos</span>';
                }
            } catch (Exception $e) {
                echo '<span style="color: #e74c3c;">✗ Error: ' . $e->getMessage() . '</span>';
            }
            echo '</div>';

            // Step 5: Cache configuration
            echo '<div class="step">';
            echo '<strong>5️⃣ Cacheando configuración...</strong><br>';
            try {
                $kernel->call('config:cache');
                echo '<span style="color: #27ae60;">✓ Config cache creado</span><br>';

                $kernel->call('route:cache');
                echo '<span style="color: #27ae60;">✓ Route cache creado</span><br>';

                $kernel->call('view:cache');
                echo '<span style="color: #27ae60;">✓ View cache creado</span><br>';
            } catch (Exception $e) {
                echo '<span style="color: #e74c3c;">✗ Error: ' . $e->getMessage() . '</span>';
            }
            echo '</div>';

            // Step 6: Storage link
            echo '<div class="step">';
            echo '<strong>6️⃣ Creando storage link...</strong><br>';
            try {
                $exitCode = $kernel->call('storage:link');
                echo '<span style="color: #27ae60;">✓ Storage link creado</span>';
            } catch (Exception $e) {
                echo '<span style="color: #f39c12;">⚠ Storage link: ' . $e->getMessage() . '</span>';
            }
            echo '</div>';

            // Mark setup as complete
            file_put_contents($setupCompleteFile, date('Y-m-d H:i:s'));

            echo '<div class="step success">';
            echo '<h3>✅ Setup Completado!</h3>';
            echo '<p>Tu aplicación Laravel 12 está lista para usar.</p>';
            echo '<ul>';
            echo '<li><strong>URL:</strong> <a href="https://casaviejagt.com">https://casaviejagt.com</a></li>';
            echo '<li><strong>Login:</strong> <a href="https://casaviejagt.com/login">https://casaviejagt.com/login</a></li>';
            echo '<li><strong>Admin Email:</strong> admin@casaviejagt.com</li>';
            echo '<li><strong>Password:</strong> Revisar EssentialDataSeeder.php</li>';
            echo '</ul>';
            echo '</div>';

            echo '<div class="step error">';
            echo '<h3>⚠️ IMPORTANTE: Eliminar este archivo</h3>';
            echo '<p>Por seguridad, <strong>DEBES ELIMINAR</strong> este archivo ahora:</p>';
            echo '<pre>rm /home/casaviejagt/public_html/setup.php</pre>';
            echo '<p>O elimínalo vía File Manager</p>';
            echo '</div>';

            echo '<h3>📋 Próximos pasos:</h3>';
            echo '<div class="step">';
            echo '<ol>';
            echo '<li>Eliminar este archivo (setup.php)</li>';
            echo '<li>Configurar cron jobs en cPanel</li>';
            echo '<li>Instalar certificado SSL</li>';
            echo '<li>Probar la aplicación</li>';
            echo '<li>Cambiar password del admin</li>';
            echo '</ol>';
            echo '</div>';

            echo '<form method="POST" action="/">';
            echo '<button type="submit">🏠 Ir a la aplicación</button>';
            echo '</form>';
        }
        ?>

        <hr>
        <p style="text-align: center; color: #7f8c8d; font-size: 12px;">
            Hotel Casa Vieja Management System - Laravel 12<br>
            Setup Script v1.0
        </p>
    </div>
</body>
</html>
