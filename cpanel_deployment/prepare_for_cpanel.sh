#!/bin/bash

# Script para preparar archivos Laravel para deployment en cPanel
# Uso: ./prepare_for_cpanel.sh

set -e

echo "🚀 Preparando archivos Laravel para deployment en cPanel..."

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: Este script debe ejecutarse desde la raíz del proyecto Laravel"
    exit 1
fi

# Crear directorios de deployment
DEPLOYMENT_DIR="cpanel_deployment"
PUBLIC_DIR="$DEPLOYMENT_DIR/cpanel_files/public_html"
PRIVATE_DIR="$DEPLOYMENT_DIR/cpanel_files/private_laravel"

echo "📁 Creando estructura de directorios..."
mkdir -p "$PUBLIC_DIR"
mkdir -p "$PRIVATE_DIR"
mkdir -p "$DEPLOYMENT_DIR/database"
mkdir -p "$DEPLOYMENT_DIR/config"
mkdir -p "$DEPLOYMENT_DIR/docs"
mkdir -p "$DEPLOYMENT_DIR/scripts"

# Limpiar directorios existentes
rm -rf "$PUBLIC_DIR"/*
rm -rf "$PRIVATE_DIR"/*

echo "📦 Copiando archivos de la aplicación Laravel..."

# Copiar toda la aplicación Laravel (excepto public)
rsync -av --exclude='public' \
          --exclude='node_modules' \
          --exclude='.git' \
          --exclude='cpanel_deployment' \
          --exclude='storage/logs/*' \
          --exclude='storage/framework/cache/*' \
          --exclude='storage/framework/sessions/*' \
          --exclude='storage/framework/views/*' \
          --exclude='database/database.sqlite' \
          . "$PRIVATE_DIR/"

echo "🌐 Preparando contenido público..."

# Copiar contenido público
cp public/index.php "$PUBLIC_DIR/"
cp public/.htaccess "$PUBLIC_DIR/" 2>/dev/null || echo "⚠️  .htaccess no encontrado en public/, se creará uno personalizado"

# Copiar assets compilados si existen
if [ -d "public/build" ]; then
    cp -r public/build "$PUBLIC_DIR/"
    echo "✅ Assets compilados copiados"
fi

# Copiar otros archivos públicos
if [ -d "public/css" ]; then
    cp -r public/css "$PUBLIC_DIR/"
fi

if [ -d "public/js" ]; then
    cp -r public/js "$PUBLIC_DIR/"
fi

if [ -d "public/images" ]; then
    cp -r public/images "$PUBLIC_DIR/"
fi

if [ -d "public/assets" ]; then
    cp -r public/assets "$PUBLIC_DIR/"
fi

if [ -d "public/landing" ]; then
    cp -r public/landing "$PUBLIC_DIR/"
fi

# Copiar archivos sueltos del public
find public -maxdepth 1 -type f \( -name "*.png" -o -name "*.jpg" -o -name "*.ico" -o -name "*.svg" -o -name "*.txt" -o -name "*.xml" \) -exec cp {} "$PUBLIC_DIR/" \;

echo "🔧 Configurando archivos para cPanel..."

# Modificar index.php para cPanel
cat > "$PUBLIC_DIR/index.php" << 'EOF'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../private_laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../private_laravel/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../private_laravel/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
EOF

# Crear .htaccess optimizado para cPanel
cat > "$PUBLIC_DIR/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Angular & Vue History API fallback
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !^/api/
    RewriteRule ^.*$ index.php [L]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Deny access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

<Files "package.json">
    Order allow,deny
    Deny from all
</Files>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
EOF

echo "🗄️ Creando symlink para storage..."
# Crear symlink para storage
if [ ! -L "$PUBLIC_DIR/storage" ]; then
    ln -sf "../../private_laravel/storage/app/public" "$PUBLIC_DIR/storage"
fi

echo "📝 Creando archivo .env para cPanel..."
# Crear .env específico para cPanel
cat > "$DEPLOYMENT_DIR/config/.env.cpanel" << 'EOF'
# Application Configuration - cPanel Version
APP_NAME="Hotel Management System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tudominio.com

# Localization
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES
APP_TIMEZONE=America/Guatemala

# Database Configuration - UPDATE THESE VALUES
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_hotel_management
DB_USERNAME=username_hoteluser
DB_PASSWORD=tu_password_aqui

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.tudominio.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Cache Configuration (file-based for shared hosting)
CACHE_STORE=file
CACHE_PREFIX=hotel_cache

# Queue Configuration (sync for shared hosting)
QUEUE_CONNECTION=sync
QUEUE_FAILED_DRIVER=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAYS=7

# Filesystem
FILESYSTEM_DISK=local

# CORS Configuration
CORS_ALLOWED_ORIGINS="https://tudominio.com,https://www.tudominio.com"

# Hotel Management System Specific
HOTEL_DEFAULT_CURRENCY=Q
HOTEL_DEFAULT_TIMEZONE=America/Guatemala
HOTEL_CHECKIN_TIME=14:00
HOTEL_CHECKOUT_TIME=12:00
RESERVA_EXPIRATION_MINUTES=240

# Security
BCRYPT_ROUNDS=12

# Performance (adjust for shared hosting limits)
QUEUE_FAILED_DRIVER=database
TELESCOPE_ENABLED=false
DEBUGBAR_ENABLED=false
EOF

echo "🔄 Optimizando aplicación..."

# Limpiar y optimizar aplicación
cd "$PRIVATE_DIR"

# Limpiar caches
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf storage/logs/*

# Crear archivos .gitkeep necesarios
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep

# Establecer permisos correctos
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;

cd - > /dev/null

echo "📊 Exportando base de datos..."

# Exportar estructura de base de datos si existe
if [ -f "database/database.sqlite" ]; then
    echo "🔄 Convirtiendo SQLite a MySQL..."

    # Crear script para exportar estructura MySQL
    cat > "$DEPLOYMENT_DIR/scripts/sqlite_to_mysql.php" << 'EOF'
<?php
// Script para convertir SQLite a MySQL
require_once __DIR__ . '/../../private_laravel/vendor/autoload.php';

$sqliteFile = __DIR__ . '/../../database/database.sqlite';
$mysqlFile = __DIR__ . '/../database/hotel_management.sql';

if (!file_exists($sqliteFile)) {
    echo "❌ Archivo SQLite no encontrado\n";
    exit(1);
}

try {
    $sqlite = new PDO("sqlite:$sqliteFile");
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $output = "-- MySQL dump para Hotel Management System\n";
    $output .= "-- Generado: " . date('Y-m-d H:i:s') . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET AUTOCOMMIT = 0;\n";
    $output .= "START TRANSACTION;\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";

    // Obtener todas las tablas
    $tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "Procesando tabla: $table\n";

        // Obtener estructura de la tabla
        $schema = $sqlite->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();

        // Convertir SQLite CREATE TABLE a MySQL
        $mysqlSchema = convertSqliteToMysql($schema, $table);
        $output .= $mysqlSchema . "\n\n";

        // Obtener datos
        $data = $sqlite->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($data)) {
            $columns = array_keys($data[0]);
            $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";

            $values = [];
            foreach ($data as $row) {
                $escapedValues = array_map(function($value) use ($sqlite) {
                    return $value === null ? 'NULL' : $sqlite->quote($value);
                }, array_values($row));
                $values[] = "(" . implode(", ", $escapedValues) . ")";
            }

            $output .= implode(",\n", $values) . ";\n\n";
        }
    }

    $output .= "COMMIT;\n";

    file_put_contents($mysqlFile, $output);
    echo "✅ Archivo MySQL generado: $mysqlFile\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function convertSqliteToMysql($sqliteSchema, $tableName) {
    // Conversión básica de SQLite a MySQL
    $mysql = $sqliteSchema;

    // Reemplazar tipos de datos
    $mysql = preg_replace('/INTEGER PRIMARY KEY AUTOINCREMENT/i', 'INT AUTO_INCREMENT PRIMARY KEY', $mysql);
    $mysql = preg_replace('/INTEGER/i', 'INT', $mysql);
    $mysql = preg_replace('/TEXT/i', 'TEXT', $mysql);
    $mysql = preg_replace('/REAL/i', 'DECIMAL(10,2)', $mysql);
    $mysql = preg_replace('/BLOB/i', 'LONGBLOB', $mysql);

    // Agregar ENGINE y CHARSET
    $mysql = rtrim($mysql, ';') . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

    return $mysql;
}
EOF

    # Ejecutar conversión si es posible
    if command -v php >/dev/null 2>&1; then
        php "$DEPLOYMENT_DIR/scripts/sqlite_to_mysql.php"
    else
        echo "⚠️  PHP no disponible para conversión automática. Usar phpMyAdmin para importar después."
    fi
else
    echo "⚠️  Base de datos SQLite no encontrada. Crear estructura manualmente."
fi

echo "📋 Creando archivos de configuración adicionales..."

# Crear archivo de configuración PHP para cPanel
cat > "$DEPLOYMENT_DIR/config/php.ini" << 'EOF'
; Configuración PHP recomendada para Hotel Management System

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

; OPcache (if available)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1
EOF

# Crear script de instalación de dependencias alternativo
cat > "$DEPLOYMENT_DIR/scripts/install_dependencies.php" << 'EOF'
<?php
/**
 * Script alternativo para instalar dependencias cuando Composer no está disponible
 * Usar solo si no hay acceso SSH o Composer en el hosting
 */

echo "🔄 Descargando dependencias esenciales...\n";

// Definir dependencias mínimas críticas
$dependencies = [
    'laravel/framework' => '12.0',
    'spatie/laravel-permission' => '6.17',
    'jeroennoten/laravel-adminlte' => '3.15'
];

echo "⚠️  IMPORTANTE: Este script es solo para emergencias.\n";
echo "   Es ALTAMENTE recomendado usar Composer para instalar dependencias.\n";
echo "   Contacta a tu proveedor de hosting para habilitar Composer.\n\n";

echo "📞 Alternativas recomendadas:\n";
echo "   1. Subir carpeta vendor/ completa desde desarrollo local\n";
echo "   2. Solicitar SSH con Composer al hosting\n";
echo "   3. Usar hosting con soporte Laravel completo\n";
EOF

echo "✅ Preparación completa!"
echo ""
echo "📁 Archivos preparados en: $DEPLOYMENT_DIR"
echo ""
echo "📋 Siguiente pasos:"
echo "1. Revisar archivo .env en: $DEPLOYMENT_DIR/config/.env.cpanel"
echo "2. Subir archivos según documentación"
echo "3. Configurar base de datos"
echo "4. Configurar cronjobs"
echo ""
echo "📖 Ver documentación completa en: $DEPLOYMENT_DIR/README_CPANEL_DEPLOYMENT.md"