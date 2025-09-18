<?php
/**
 * Script de post-deployment para cPanel
 * Ejecutar después de subir archivos para finalizar configuración
 */

echo "🚀 Iniciando configuración post-deployment...\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('artisan')) {
    echo "❌ Error: Ejecutar desde el directorio Laravel (private_laravel)\n";
    exit(1);
}

// Verificar archivo .env
if (!file_exists('.env')) {
    echo "⚠️  Archivo .env no encontrado, copiando desde .env.example\n";
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ Archivo .env creado\n";
    } else {
        echo "❌ Error: .env.example no encontrado\n";
        exit(1);
    }
}

// Leer configuración actual
$env = file_get_contents('.env');

// Verificar APP_KEY
if (strpos($env, 'APP_KEY=base64:') === false || strpos($env, 'APP_KEY=') === strlen($env) - 8) {
    echo "🔑 Generando APP_KEY...\n";
    $appKey = generateAppKey();
    $env = preg_replace('/APP_KEY=.*/', "APP_KEY=base64:$appKey", $env);
    file_put_contents('.env', $env);
    echo "✅ APP_KEY generada\n";
}

// Crear directorios necesarios si no existen
$directories = [
    'storage/logs',
    'storage/app/public',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "📁 Directorio creado: $dir\n";
    }
}

// Crear archivos .gitkeep
$gitkeepFiles = [
    'storage/logs/.gitkeep',
    'storage/app/.gitkeep',
    'storage/app/public/.gitkeep',
    'storage/framework/cache/data/.gitkeep',
    'storage/framework/sessions/.gitkeep',
    'storage/framework/views/.gitkeep'
];

foreach ($gitkeepFiles as $file) {
    if (!file_exists($file)) {
        touch($file);
        echo "📄 Archivo creado: $file\n";
    }
}

// Establecer permisos
echo "🔧 Configurando permisos...\n";
chmod('artisan', 0755);
chmodRecursive('storage', 0755, 0644);
chmodRecursive('bootstrap/cache', 0755, 0644);

// Crear symlink para storage si no existe
$publicStoragePath = '../public_html/storage';
$targetPath = realpath('storage/app/public');

if (!file_exists($publicStoragePath) && $targetPath) {
    if (symlink($targetPath, $publicStoragePath)) {
        echo "🔗 Symlink de storage creado\n";
    } else {
        echo "⚠️  No se pudo crear symlink de storage\n";
    }
}

// Verificar vendor
if (!is_dir('vendor')) {
    echo "⚠️  Directorio vendor no encontrado\n";
    echo "   📦 Subir vendor/ desde desarrollo local o ejecutar: composer install\n";
} else {
    echo "✅ Dependencias encontradas\n";
}

// Crear archivo de verificación del sistema
createSystemCheckFile();

// Limpiar caches si es posible
echo "🧹 Limpiando caches...\n";
if (is_dir('bootstrap/cache')) {
    array_map('unlink', array_filter((array) glob('bootstrap/cache/*.php')));
}

echo "✅ Configuración post-deployment completada\n\n";

echo "📋 Siguientes pasos:\n";
echo "1. Configurar base de datos en .env\n";
echo "2. Ejecutar migraciones: php artisan migrate\n";
echo "3. Configurar cronjobs en cPanel\n";
echo "4. Probar el sitio web\n\n";

echo "🔗 URLs de prueba:\n";
$url = getUrlFromEnv();
echo "- Sitio principal: $url\n";
echo "- API de prueba: $url/api/test-cors\n";
echo "- Verificación del sistema: $url/system-check.php\n";

function generateAppKey() {
    return base64_encode(random_bytes(32));
}

function chmodRecursive($path, $dirPerm, $filePerm) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            chmod($item->getPathname(), $dirPerm);
        } else {
            chmod($item->getPathname(), $filePerm);
        }
    }
}

function createSystemCheckFile() {
    $checkScript = '<?php
/**
 * Script de verificación del sistema
 * Accesible desde: tudominio.com/system-check.php
 */

// Solo permitir en desarrollo o con token
$allowedIPs = ["127.0.0.1", "::1"];
$token = $_GET["token"] ?? "";
$validToken = "hotel_check_2024";

if (!in_array($_SERVER["REMOTE_ADDR"], $allowedIPs) && $token !== $validToken) {
    http_response_code(403);
    die("Acceso denegado");
}

echo "<h1>Verificación del Sistema - Hotel Management</h1>";
echo "<p>Fecha: " . date("Y-m-d H:i:s") . "</p>";

// Verificar PHP
echo "<h2>Configuración PHP</h2>";
echo "<p>Versión: " . phpversion() . "</p>";
echo "<p>Memoria límite: " . ini_get("memory_limit") . "</p>";
echo "<p>Tiempo ejecución: " . ini_get("max_execution_time") . "s</p>";

// Verificar extensiones
echo "<h2>Extensiones PHP</h2>";
$required = ["pdo", "pdo_mysql", "mbstring", "openssl", "bcmath", "ctype", "fileinfo", "json", "tokenizer", "xml"];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "<p>$status $ext</p>";
}

// Verificar Laravel
echo "<h2>Configuración Laravel</h2>";
$laravelPath = __DIR__ . "/../private_laravel";

if (file_exists("$laravelPath/.env")) {
    echo "<p>✅ Archivo .env encontrado</p>";
    $env = file_get_contents("$laravelPath/.env");
    echo "<p>" . (strpos($env, "APP_KEY=base64:") !== false ? "✅" : "❌") . " APP_KEY configurada</p>";
    echo "<p>" . (strpos($env, "DB_DATABASE=") !== false ? "✅" : "❌") . " Base de datos configurada</p>";
} else {
    echo "<p>❌ Archivo .env no encontrado</p>";
}

// Verificar permisos
echo "<h2>Permisos de Archivos</h2>";
$paths = [
    "$laravelPath/storage" => "Storage",
    "$laravelPath/bootstrap/cache" => "Cache",
    "$laravelPath/artisan" => "Artisan"
];

foreach ($paths as $path => $name) {
    if (file_exists($path)) {
        $perms = substr(sprintf("%o", fileperms($path)), -3);
        $writable = is_writable($path) ? "✅" : "❌";
        echo "<p>$writable $name ($perms)</p>";
    } else {
        echo "<p>❌ $name (no encontrado)</p>";
    }
}

// Verificar conectividad de base de datos
echo "<h2>Conectividad</h2>";
try {
    if (file_exists("$laravelPath/.env")) {
        $env = parse_ini_string(file_get_contents("$laravelPath/.env"));
        if (isset($env["DB_HOST"]) && isset($env["DB_DATABASE"])) {
            $pdo = new PDO(
                "mysql:host={$env["DB_HOST"]};dbname={$env["DB_DATABASE"]}",
                $env["DB_USERNAME"],
                $env["DB_PASSWORD"]
            );
            echo "<p>✅ Conexión a base de datos exitosa</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><small>Para ocultar esta página, eliminar system-check.php</small></p>";
?>';

    file_put_contents('../public_html/system-check.php', $checkScript);
    echo "🔍 Archivo de verificación creado: /system-check.php\n";
}

function getUrlFromEnv() {
    $env = file_get_contents('.env');
    if (preg_match('/APP_URL=(.+)/', $env, $matches)) {
        return trim($matches[1]);
    }
    return 'https://tudominio.com';
}

echo "🎉 ¡Deployment preparado para cPanel!\n";