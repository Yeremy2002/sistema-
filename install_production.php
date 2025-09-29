<?php
/**
 * Script de Instalación Automática - Hotel Casa Vieja
 * 
 * Este script automatiza la instalación completa del sistema en producción
 */

echo "<html><head><title>Instalación - Hotel Casa Vieja</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h1>🏨 Instalación Automática - Hotel Casa Vieja</h1>";
echo "<p>Iniciando proceso de instalación...</p>";
echo "<hr>";

function logMessage($message, $type = 'info') {
    $icons = [
        'info' => '🔵',
        'ok' => '✅',
        'warning' => '⚠️',
        'error' => '❌'
    ];
    
    $classes = [
        'info' => 'info',
        'ok' => 'ok', 
        'warning' => 'warning',
        'error' => 'error'
    ];
    
    $icon = $icons[$type] ?? '•';
    $class = $classes[$type] ?? 'info';
    
    echo "<p class='$class'>$icon $message</p>";
    flush();
    
    // También guardamos en log
    $logFile = __DIR__ . '/installation.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " [$type] $message\n", FILE_APPEND);
}

function executeCommand($command, $description) {
    logMessage("Ejecutando: $description", 'info');
    logMessage("Comando: $command", 'info');
    
    $output = [];
    $returnCode = 0;
    exec("cd " . __DIR__ . " && $command 2>&1", $output, $returnCode);
    
    if($returnCode === 0) {
        logMessage("✓ $description completado exitosamente", 'ok');
        return true;
    } else {
        logMessage("✗ Error en: $description", 'error');
        foreach($output as $line) {
            logMessage("  " . $line, 'error');
        }
        return false;
    }
}

function testDatabaseConnection() {
    logMessage("Probando conexión a la base de datos...", 'info');
    
    try {
        // Leer configuración del .env
        if(!file_exists('.env')) {
            logMessage("Archivo .env no encontrado", 'error');
            return false;
        }
        
        $env_lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env_vars = [];
        
        foreach($env_lines as $line) {
            if(strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                list($key, $value) = explode('=', $line, 2);
                $env_vars[trim($key)] = trim($value, '"\'');
            }
        }
        
        $host = $env_vars['DB_HOST'] ?? 'localhost';
        $dbname = $env_vars['DB_DATABASE'] ?? '';
        $username = $env_vars['DB_USERNAME'] ?? '';
        $password = $env_vars['DB_PASSWORD'] ?? '';
        
        if(empty($dbname) || empty($username)) {
            logMessage("Configuración de base de datos incompleta en .env", 'error');
            return false;
        }
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        logMessage("Conexión a base de datos exitosa: $dbname@$host con usuario $username", 'ok');
        return true;
        
    } catch(Exception $e) {
        logMessage("Error de conexión a base de datos: " . $e->getMessage(), 'error');
        return false;
    }
}

// Paso 1: Verificar requisitos básicos
logMessage("=== PASO 1: Verificación de Requisitos ===", 'info');

// Verificar PHP
$phpVersion = PHP_VERSION;
$minRequired = '8.2.0';
if(version_compare($phpVersion, $minRequired, '>=')) {
    logMessage("PHP $phpVersion es compatible (requiere $minRequired+)", 'ok');
} else {
    logMessage("PHP $phpVersion NO es compatible (requiere $minRequired+)", 'error');
    die("Instalación abortada por versión de PHP incompatible");
}

// Verificar extensiones
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'fileinfo', 'tokenizer', 'xml', 'zip'];
$missingExtensions = [];

foreach($requiredExtensions as $ext) {
    if(extension_loaded($ext)) {
        logMessage("Extensión $ext: Cargada", 'ok');
    } else {
        logMessage("Extensión $ext: NO encontrada", 'error');
        $missingExtensions[] = $ext;
    }
}

if(!empty($missingExtensions)) {
    logMessage("Faltan extensiones requeridas: " . implode(', ', $missingExtensions), 'error');
    die("Instalación abortada por extensiones faltantes");
}

// Paso 2: Verificar archivos esenciales
logMessage("=== PASO 2: Verificación de Archivos ===", 'info');

$essentialFiles = [
    'artisan' => 'Laravel CLI',
    'composer.json' => 'Archivo Composer',
    'vendor/autoload.php' => 'Autoloader'
];

foreach($essentialFiles as $file => $desc) {
    if(file_exists($file)) {
        logMessage("$desc: Encontrado", 'ok');
    } else {
        logMessage("$desc: NO encontrado", 'error');
        die("Instalación abortada: archivo esencial faltante: $file");
    }
}

// Paso 3: Configurar .env
logMessage("=== PASO 3: Configuración de Variables de Entorno ===", 'info');

if(!file_exists('.env')) {
    if(file_exists('.env.production')) {
        logMessage("Copiando .env.production a .env", 'info');
        copy('.env.production', '.env');
        logMessage("Archivo .env creado desde .env.production", 'ok');
    } else {
        logMessage("No se encuentra .env ni .env.production", 'error');
        die("Instalación abortada: sin configuración de entorno");
    }
} else {
    logMessage("Archivo .env ya existe", 'ok');
}

// Paso 4: Verificar conexión a base de datos
logMessage("=== PASO 4: Conexión a Base de Datos ===", 'info');
if(!testDatabaseConnection()) {
    die("Instalación abortada: no se pudo conectar a la base de datos");
}

// Paso 5: Limpiar cachés antiguos
logMessage("=== PASO 5: Limpieza de Cachés ===", 'info');
$cleanCommands = [
    'php artisan config:clear' => 'Limpiar caché de configuración',
    'php artisan route:clear' => 'Limpiar caché de rutas',
    'php artisan view:clear' => 'Limpiar caché de vistas',
    'php artisan cache:clear' => 'Limpiar caché de aplicación'
];

foreach($cleanCommands as $cmd => $desc) {
    executeCommand($cmd, $desc);
}

// Paso 6: Ejecutar migraciones
logMessage("=== PASO 6: Migraciones de Base de Datos ===", 'info');
executeCommand('php artisan migrate --force', 'Ejecutar migraciones');

// Paso 7: Poblar datos esenciales
logMessage("=== PASO 7: Datos Iniciales ===", 'info');
executeCommand('php artisan db:seed --class=EssentialDataSeeder --force', 'Poblar datos esenciales');

// Paso 8: Optimizar para producción
logMessage("=== PASO 8: Optimización para Producción ===", 'info');
$optimizeCommands = [
    'php artisan config:cache' => 'Generar caché de configuración',
    'php artisan route:cache' => 'Generar caché de rutas',
    'php artisan view:cache' => 'Generar caché de vistas',
    'php artisan event:cache' => 'Generar caché de eventos'
];

foreach($optimizeCommands as $cmd => $desc) {
    executeCommand($cmd, $desc);
}

// Paso 9: Crear enlace de storage
logMessage("=== PASO 9: Configuración de Storage ===", 'info');
executeCommand('php artisan storage:link', 'Crear enlace de almacenamiento público');

// Paso 10: Verificar permisos
logMessage("=== PASO 10: Verificación de Permisos ===", 'info');
$directories = ['storage', 'bootstrap/cache'];

foreach($directories as $dir) {
    if(is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        if(is_writable($dir)) {
            logMessage("Directorio $dir ($perms): Permisos correctos", 'ok');
        } else {
            logMessage("Directorio $dir ($perms): Sin permisos de escritura", 'warning');
            logMessage("Intentando cambiar permisos de $dir", 'info');
            chmod($dir, 0775);
        }
    } else {
        logMessage("Directorio $dir: NO existe", 'error');
    }
}

// Verificación final
logMessage("=== VERIFICACIÓN FINAL ===", 'info');

try {
    // Probar que Laravel puede arrancar
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    logMessage("Laravel puede instanciarse correctamente", 'ok');
    
    // Probar conexión final a BD
    if(testDatabaseConnection()) {
        logMessage("Conexión final a base de datos: OK", 'ok');
    }
    
} catch(Exception $e) {
    logMessage("Error en verificación final: " . $e->getMessage(), 'error');
}

// Información final
echo "<hr>";
echo "<h2>🎉 ¡Instalación Completada!</h2>";
echo "<div class='ok'>";
echo "<h3>Sistema Hotel Casa Vieja instalado exitosamente</h3>";
echo "<p><strong>URL del sistema:</strong> <a href='https://casaviejagt.com'>https://casaviejagt.com</a></p>";
echo "<p><strong>Panel de administración:</strong> <a href='https://casaviejagt.com/admin'>https://casaviejagt.com/admin</a></p>";
echo "<p><strong>Credenciales por defecto:</strong></p>";
echo "<ul>";
echo "<li><strong>Email:</strong> admin@hotel.com</li>";
echo "<li><strong>Password:</strong> password</li>";
echo "</ul>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Cambia la contraseña inmediatamente después del primer login</p>";
echo "</div>";

echo "<h3>📋 Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Configurar Cron Job en cPanel: <code>* * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1</code></li>";
echo "<li>Cambiar contraseña del administrador</li>";
echo "<li>Configurar información del hotel</li>";
echo "<li>Agregar habitaciones y categorías</li>";
echo "<li>Probar el sistema de reservas</li>";
echo "</ol>";

echo "<hr>";
echo "<p><small>Instalación completada el: " . date('Y-m-d H:i:s') . "</small></p>";
echo "<p><small>Log guardado en: installation.log</small></p>";
echo "</body></html>";
?>