<?php
/**
 * Script de Diagnóstico para Producción - Hotel Casa Vieja
 * 
 * Este archivo debe subirse a la raíz del sitio web para identificar
 * problemas de configuración que causan el error 500.
 */

echo "<html><head><title>Diagnóstico - Hotel Casa Vieja</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h1>🔍 Diagnóstico del Sistema - Hotel Casa Vieja</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Información PHP básica
echo "<h2>📋 Información del Sistema</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No disponible') . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

// Verificar extensiones PHP requeridas
echo "<h2>🔧 Extensiones PHP Requeridas</h2>";
$required_extensions = [
    'pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 
    'curl', 'fileinfo', 'tokenizer', 'xml', 'zip'
];

foreach($required_extensions as $ext) {
    $status = extension_loaded($ext);
    $icon = $status ? "✅" : "❌";
    $class = $status ? "ok" : "error";
    echo "<p class='$class'>$icon $ext: " . ($status ? "Cargada" : "NO ENCONTRADA") . "</p>";
}

// Verificar versión PHP
echo "<h2>⚙️ Compatibilidad PHP</h2>";
$php_version = PHP_VERSION;
$min_required = '8.2.0';
$is_compatible = version_compare($php_version, $min_required, '>=');
$icon = $is_compatible ? "✅" : "❌";
$class = $is_compatible ? "ok" : "error";
echo "<p class='$class'>$icon PHP $php_version " . ($is_compatible ? "es compatible" : "NO es compatible (se requiere $min_required+)") . "</p>";

// Verificar archivos y directorios esenciales
echo "<h2>📁 Archivos y Directorios</h2>";
$essential_files = [
    '.env' => 'Archivo de configuración',
    'artisan' => 'Laravel CLI',
    'composer.json' => 'Dependencias Composer',
    'vendor/autoload.php' => 'Autoloader de Composer',
    'bootstrap/app.php' => 'Bootstrap de Laravel',
    'public/index.php' => 'Entry point público',
];

foreach($essential_files as $file => $description) {
    $exists = file_exists($file);
    $icon = $exists ? "✅" : "❌";
    $class = $exists ? "ok" : "error";
    echo "<p class='$class'>$icon $file: $description " . ($exists ? "- Encontrado" : "- NO ENCONTRADO") . "</p>";
}

// Verificar permisos de directorios
echo "<h2>🔒 Permisos de Directorios</h2>";
$directories = [
    'storage' => 'Almacenamiento general',
    'storage/logs' => 'Logs del sistema',
    'storage/app' => 'Archivos de aplicación',
    'storage/framework' => 'Framework cache',
    'bootstrap/cache' => 'Cache de bootstrap',
];

foreach($directories as $dir => $description) {
    if(is_dir($dir)) {
        $writable = is_writable($dir);
        $readable = is_readable($dir);
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        
        $icon = ($writable && $readable) ? "✅" : "❌";
        $class = ($writable && $readable) ? "ok" : "error";
        echo "<p class='$class'>$icon $dir ($perms): $description - " . 
             ($writable ? "Escribible" : "NO escribible") . " / " . 
             ($readable ? "Legible" : "NO legible") . "</p>";
    } else {
        echo "<p class='error'>❌ $dir: $description - DIRECTORIO NO EXISTE</p>";
    }
}

// Verificar archivo .env y variables importantes
echo "<h2>🔐 Configuración (.env)</h2>";
if(file_exists('.env')) {
    $env_content = file_get_contents('.env');
    $env_readable = is_readable('.env');
    $env_perms = substr(sprintf('%o', fileperms('.env')), -4);
    
    echo "<p class='ok'>✅ Archivo .env encontrado (permisos: $env_perms)</p>";
    
    // Verificar variables críticas (sin mostrar valores sensibles)
    $critical_vars = [
        'APP_NAME' => 'Nombre de la aplicación',
        'APP_ENV' => 'Entorno',
        'APP_KEY' => 'Clave de aplicación',
        'DB_CONNECTION' => 'Driver de base de datos',
        'DB_HOST' => 'Host de base de datos',
        'DB_DATABASE' => 'Nombre de base de datos',
        'DB_USERNAME' => 'Usuario de base de datos'
    ];
    
    foreach($critical_vars as $var => $desc) {
        $has_var = strpos($env_content, $var . '=') !== false;
        $icon = $has_var ? "✅" : "❌";
        $class = $has_var ? "ok" : "error";
        echo "<p class='$class'>$icon $var: $desc " . ($has_var ? "- Configurado" : "- NO configurado") . "</p>";
    }
} else {
    echo "<p class='error'>❌ Archivo .env NO encontrado - Este es probablemente el problema principal</p>";
}

// Prueba de conexión a base de datos
echo "<h2>🗄️ Conexión a Base de Datos</h2>";
try {
    if(file_exists('.env')) {
        // Leer configuración del .env
        $env_lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env_vars = [];
        
        foreach($env_lines as $line) {
            if(strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                list($key, $value) = explode('=', $line, 2);
                $env_vars[trim($key)] = trim($value);
            }
        }
        
        $host = $env_vars['DB_HOST'] ?? 'localhost';
        $dbname = $env_vars['DB_DATABASE'] ?? '';
        $username = $env_vars['DB_USERNAME'] ?? '';
        $password = $env_vars['DB_PASSWORD'] ?? '';
        
        if(empty($dbname) || empty($username)) {
            echo "<p class='error'>❌ Configuración de base de datos incompleta en .env</p>";
        } else {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Probar una consulta simple
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            echo "<p class='ok'>✅ Conexión a base de datos exitosa</p>";
            echo "<p class='ok'>✅ Base de datos: $dbname en $host</p>";
            echo "<p class='ok'>✅ Usuario: $username</p>";
        }
    } else {
        echo "<p class='error'>❌ No se puede probar la conexión sin archivo .env</p>";
    }
} catch(Exception $e) {
    echo "<p class='error'>❌ Error de conexión a base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Verificar Laravel
echo "<h2>🎭 Laravel Framework</h2>";
try {
    if(file_exists('vendor/autoload.php')) {
        echo "<p class='ok'>✅ Autoloader de Composer encontrado</p>";
        
        // Intentar cargar Laravel
        if(file_exists('bootstrap/app.php')) {
            echo "<p class='ok'>✅ Bootstrap de Laravel encontrado</p>";
            
            // Verificar si podemos instanciar la aplicación
            try {
                require_once 'vendor/autoload.php';
                $app = require_once 'bootstrap/app.php';
                echo "<p class='ok'>✅ Aplicación Laravel se puede instanciar</p>";
            } catch(Exception $e) {
                echo "<p class='error'>❌ Error al instanciar Laravel: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Bootstrap de Laravel no encontrado</p>";
        }
    } else {
        echo "<p class='error'>❌ Autoloader de Composer no encontrado - ejecutar 'composer install'</p>";
    }
} catch(Exception $e) {
    echo "<p class='error'>❌ Error verificando Laravel: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Información del servidor web
echo "<h2>🌐 Configuración del Servidor Web</h2>";
echo "<p><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'No disponible') . "</p>";
echo "<p><strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No disponible') . "</p>";
echo "<p><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'No disponible') . "</p>";

// Verificar .htaccess
if(file_exists('public/.htaccess')) {
    echo "<p class='ok'>✅ Archivo .htaccess encontrado en public/</p>";
} else {
    echo "<p class='error'>❌ Archivo .htaccess NO encontrado en public/ - esto puede causar problemas de routing</p>";
}

// Verificar mod_rewrite
if(function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    echo "<p class='ok'>✅ mod_rewrite está habilitado</p>";
} else {
    echo "<p class='warning'>⚠️ No se puede verificar mod_rewrite (puede estar habilitado)</p>";
}

// Logs recientes si existen
echo "<h2>📝 Logs Recientes</h2>";
$log_file = 'storage/logs/laravel.log';
if(file_exists($log_file) && is_readable($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = explode("\n", $log_content);
    $recent_lines = array_slice($log_lines, -10); // Últimas 10 líneas
    
    echo "<p class='ok'>✅ Log de Laravel encontrado. Últimas entradas:</p>";
    echo "<pre style='background:#f5f5f5; padding:10px; overflow-x:auto;'>";
    echo htmlspecialchars(implode("\n", $recent_lines));
    echo "</pre>";
} else {
    echo "<p class='warning'>⚠️ Log de Laravel no encontrado o no legible</p>";
}

// Resumen final
echo "<h2>📊 Resumen de Diagnóstico</h2>";
echo "<p><strong>Tiempo de ejecución:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Status:</strong> Diagnóstico completado</p>";

echo "<hr>";
echo "<p><small>Script de diagnóstico para Hotel Casa Vieja Management System</small></p>";
echo "</body></html>";
?>