<?php
/**
 * Optimizaciones específicas para shared hosting
 * Ejecutar después del deployment inicial
 */

echo "⚡ Aplicando optimizaciones para shared hosting...\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('artisan')) {
    echo "❌ Error: Ejecutar desde el directorio Laravel (private_laravel)\n";
    exit(1);
}

// 1. Configurar optimizaciones de cache
echo "🗄️ Configurando optimizaciones de cache...\n";
optimizeCache();

// 2. Configurar optimizaciones de sesión
echo "🔐 Configurando optimizaciones de sesión...\n";
optimizeSession();

// 3. Configurar optimizaciones de queue
echo "📋 Configurando optimizaciones de queue...\n";
optimizeQueue();

// 4. Configurar optimizaciones de email
echo "📧 Configurando optimizaciones de email...\n";
optimizeEmail();

// 5. Crear configuración de PHP personalizada
echo "🔧 Creando configuración PHP optimizada...\n";
createOptimizedPhpConfig();

// 6. Optimizar autoload
echo "📦 Optimizando autoload...\n";
optimizeAutoload();

// 7. Crear script de mantenimiento
echo "🧹 Creando script de mantenimiento...\n";
createMaintenanceScript();

echo "✅ Optimizaciones aplicadas para shared hosting\n\n";

echo "📋 Configuraciones aplicadas:\n";
echo "- Cache: File-based con limpieza automática\n";
echo "- Sesiones: File-based optimizadas\n";
echo "- Queue: Sync con fallback\n";
echo "- Email: SMTP con retry\n";
echo "- PHP: Configuración de memoria y timeout\n";
echo "- Autoload: Optimizado para producción\n";

function optimizeCache() {
    $cacheConfig = [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/data'),
            ],
        ],
        'prefix' => 'hotel_cache'
    ];

    updateConfigFile('cache.php', $cacheConfig);
    echo "  ✅ Configuración de cache optimizada\n";
}

function optimizeSession() {
    $sessionConfig = [
        'driver' => 'file',
        'lifetime' => 480, // 8 horas
        'expire_on_close' => false,
        'encrypt' => true,
        'files' => storage_path('framework/sessions'),
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100], // Limpiar sesiones expiradas
        'cookie' => 'hotel_session',
        'path' => '/',
        'domain' => null,
        'secure' => true,
        'http_only' => true,
        'same_site' => 'lax',
    ];

    updateConfigFile('session.php', $sessionConfig);
    echo "  ✅ Configuración de sesión optimizada\n";
}

function optimizeQueue() {
    $queueConfig = [
        'default' => 'sync',
        'connections' => [
            'sync' => [
                'driver' => 'sync',
            ],
            'database' => [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'retry_after' => 90,
            ],
        ],
        'failed' => [
            'driver' => 'database',
            'database' => 'mysql',
            'table' => 'failed_jobs',
        ],
    ];

    updateConfigFile('queue.php', $queueConfig);
    echo "  ✅ Configuración de queue optimizada\n";
}

function optimizeEmail() {
    // Crear configuración de email con retry y fallback
    $mailConfig = <<<'PHP'
<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => 60,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];
PHP;

    file_put_contents('config/mail.php', $mailConfig);
    echo "  ✅ Configuración de email optimizada\n";
}

function createOptimizedPhpConfig() {
    $phpIni = <<<'INI'
; Configuración PHP optimizada para shared hosting
; Hotel Management System

; Memory and execution limits
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000
post_max_size = 100M
upload_max_filesize = 100M

; Error reporting (production)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = error_log

; Date and timezone
date.timezone = "America/Guatemala"

; Session settings
session.gc_maxlifetime = 28800
session.cookie_lifetime = 0
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = "Lax"
session.gc_probability = 1
session.gc_divisor = 100

; OPcache (if available)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1

; Realpath cache
realpath_cache_size = 4096K
realpath_cache_ttl = 600

; File uploads
file_uploads = On
upload_tmp_dir = /tmp

; Output compression
zlib.output_compression = On
zlib.output_compression_level = 6

; Security
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

; Performance
enable_dl = Off
mail.add_x_header = Off
INI;

    file_put_contents('../php.ini', $phpIni);
    file_put_contents('.htaccess', "php_value auto_prepend_file ../php.ini\n", FILE_APPEND);
    echo "  ✅ Configuración PHP optimizada creada\n";
}

function optimizeAutoload() {
    if (file_exists('vendor/autoload.php')) {
        // Crear autoload optimizado
        exec('composer dump-autoload --optimize --no-dev 2>&1', $output, $return);
        if ($return === 0) {
            echo "  ✅ Autoload optimizado\n";
        } else {
            echo "  ⚠️  No se pudo optimizar autoload: " . implode("\n", $output) . "\n";
        }
    } else {
        echo "  ⚠️  Vendor no encontrado, omitir optimización de autoload\n";
    }
}

function createMaintenanceScript() {
    $maintenanceScript = <<<'PHP'
<?php
/**
 * Script de mantenimiento para shared hosting
 * Ejecutar diariamente via cron
 */

echo "🧹 Iniciando mantenimiento del sistema...\n";

// Limpiar logs antiguos
cleanOldLogs();

// Limpiar cache de sesiones
cleanOldSessions();

// Limpiar cache de vistas
cleanViewCache();

// Limpiar archivos temporales
cleanTempFiles();

// Optimizar base de datos
optimizeDatabase();

echo "✅ Mantenimiento completado\n";

function cleanOldLogs() {
    $logPath = __DIR__ . '/storage/logs';
    $files = glob("$logPath/*.log");

    foreach ($files as $file) {
        if (filemtime($file) < time() - (7 * 24 * 60 * 60)) { // 7 días
            unlink($file);
            echo "  🗑️  Log eliminado: " . basename($file) . "\n";
        }
    }
}

function cleanOldSessions() {
    $sessionPath = __DIR__ . '/storage/framework/sessions';
    $files = glob("$sessionPath/sess_*");

    foreach ($files as $file) {
        if (filemtime($file) < time() - (24 * 60 * 60)) { // 24 horas
            unlink($file);
        }
    }
    echo "  🗑️  Sesiones antiguas limpiadas\n";
}

function cleanViewCache() {
    $viewPath = __DIR__ . '/storage/framework/views';
    $files = glob("$viewPath/*.php");

    foreach ($files as $file) {
        unlink($file);
    }
    echo "  🗑️  Cache de vistas limpiado\n";
}

function cleanTempFiles() {
    $tempPaths = [
        __DIR__ . '/storage/framework/cache/data',
        '/tmp'
    ];

    foreach ($tempPaths as $path) {
        if (is_dir($path)) {
            $files = glob("$path/laravel_*");
            foreach ($files as $file) {
                if (filemtime($file) < time() - (6 * 60 * 60)) { // 6 horas
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }
    echo "  🗑️  Archivos temporales limpiados\n";
}

function optimizeDatabase() {
    try {
        require_once __DIR__ . '/vendor/autoload.php';

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $pdo = new PDO(
            'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );

        // Optimizar tablas principales
        $tables = ['reservas', 'habitacions', 'clientes', 'users'];
        foreach ($tables as $table) {
            $pdo->exec("OPTIMIZE TABLE $table");
        }

        echo "  🔧 Base de datos optimizada\n";

    } catch (Exception $e) {
        echo "  ⚠️  Error optimizando BD: " . $e->getMessage() . "\n";
    }
}
PHP;

    file_put_contents('maintenance.php', $maintenanceScript);
    echo "  ✅ Script de mantenimiento creado\n";
}

function updateConfigFile($filename, $config) {
    $configPath = "config/$filename";
    $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
    file_put_contents($configPath, $content);
}

echo "\n📋 Comandos de cron recomendados para shared hosting:\n";
echo "# Mantenimiento diario\n";
echo "0 2 * * * cd /home/username/private_laravel && php maintenance.php\n\n";

echo "# Scheduler (reducir frecuencia si hay límites)\n";
echo "*/5 * * * * cd /home/username/private_laravel && php artisan schedule:run\n\n";

echo "🔧 Configuraciones adicionales recomendadas:\n";
echo "- Configurar cache de opciones en .htaccess\n";
echo "- Habilitar compresión gzip\n";
echo "- Configurar headers de cache para assets\n";
echo "- Optimizar imágenes antes de subir\n";