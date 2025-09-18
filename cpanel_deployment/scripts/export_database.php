<?php
/**
 * Script para exportar base de datos SQLite a MySQL
 * Para uso en deployment de cPanel
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$sqliteFile = __DIR__ . '/../../database/database.sqlite';
$mysqlFile = __DIR__ . '/../database/hotel_management.sql';

echo "🔄 Iniciando conversión de SQLite a MySQL...\n";

if (!file_exists($sqliteFile)) {
    echo "❌ Archivo SQLite no encontrado: $sqliteFile\n";
    echo "ℹ️  Creando estructura MySQL desde migraciones...\n";
    createFromMigrations();
    exit(0);
}

try {
    $sqlite = new PDO("sqlite:$sqliteFile");
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $output = generateMySQLHeader();

    // Obtener todas las tablas
    $tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

    echo "📊 Tablas encontradas: " . count($tables) . "\n";

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
            $output .= "-- Datos para tabla `$table`\n";
            $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";

            $values = [];
            foreach ($data as $row) {
                $escapedValues = array_map(function($value) use ($sqlite) {
                    if ($value === null) return 'NULL';
                    if (is_numeric($value)) return $value;
                    return $sqlite->quote($value);
                }, array_values($row));
                $values[] = "(" . implode(", ", $escapedValues) . ")";
            }

            $output .= implode(",\n", $values) . ";\n\n";
        }
    }

    $output .= generateMySQLFooter();

    // Crear directorio si no existe
    if (!file_exists(dirname($mysqlFile))) {
        mkdir(dirname($mysqlFile), 0755, true);
    }

    file_put_contents($mysqlFile, $output);
    echo "✅ Archivo MySQL generado: $mysqlFile\n";
    echo "📏 Tamaño del archivo: " . formatBytes(filesize($mysqlFile)) . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function generateMySQLHeader() {
    return "-- MySQL dump para Hotel Management System
-- Generado: " . date('Y-m-d H:i:s') . "
-- Versión del servidor: MySQL 5.7+
-- Versión de PHP: " . phpversion() . "

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = \"+00:00\";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: hotel_management
--

";
}

function generateMySQLFooter() {
    return "
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
";
}

function convertSqliteToMysql($sqliteSchema, $tableName) {
    // Conversión de SQLite a MySQL
    $mysql = $sqliteSchema;

    // Reemplazar tipos de datos comunes
    $replacements = [
        '/INTEGER PRIMARY KEY AUTOINCREMENT/i' => 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        '/INTEGER PRIMARY KEY/i' => 'BIGINT UNSIGNED PRIMARY KEY',
        '/INTEGER/i' => 'INT',
        '/TEXT/i' => 'TEXT',
        '/REAL/i' => 'DECIMAL(10,2)',
        '/BLOB/i' => 'LONGBLOB',
        '/BOOLEAN/i' => 'TINYINT(1)',
        '/DATETIME/i' => 'TIMESTAMP',
    ];

    foreach ($replacements as $pattern => $replacement) {
        $mysql = preg_replace($pattern, $replacement, $mysql);
    }

    // Ajustes específicos para columnas comunes
    $columnAdjustments = [
        '/`id` INT/' => '`id` BIGINT UNSIGNED AUTO_INCREMENT',
        '/`created_at` TIMESTAMP/' => '`created_at` TIMESTAMP NULL DEFAULT NULL',
        '/`updated_at` TIMESTAMP/' => '`updated_at` TIMESTAMP NULL DEFAULT NULL',
        '/`deleted_at` TIMESTAMP/' => '`deleted_at` TIMESTAMP NULL DEFAULT NULL',
        '/`email_verified_at` TIMESTAMP/' => '`email_verified_at` TIMESTAMP NULL DEFAULT NULL',
    ];

    foreach ($columnAdjustments as $pattern => $replacement) {
        $mysql = preg_replace($pattern, $replacement, $mysql);
    }

    // Agregar ENGINE y CHARSET
    $mysql = rtrim($mysql, ';') . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

    // Agregar comentario de tabla
    $mysql = str_replace(';', " COMMENT='Tabla $tableName del sistema hotelero';", $mysql);

    return "-- Estructura de tabla para `$tableName`\n" . $mysql;
}

function createFromMigrations() {
    echo "🔄 Generando estructura desde migraciones Laravel...\n";

    $migrationPath = __DIR__ . '/../../database/migrations';
    $mysqlFile = __DIR__ . '/../database/hotel_management.sql';

    if (!is_dir($migrationPath)) {
        echo "❌ Directorio de migraciones no encontrado\n";
        return;
    }

    $output = generateMySQLHeader();
    $output .= "-- Estructura generada desde migraciones Laravel\n\n";

    // Estructura básica mínima para el sistema hotelero
    $basicStructure = getBasicHotelStructure();
    $output .= $basicStructure;

    $output .= generateMySQLFooter();

    // Crear directorio si no existe
    if (!file_exists(dirname($mysqlFile))) {
        mkdir(dirname($mysqlFile), 0755, true);
    }

    file_put_contents($mysqlFile, $output);
    echo "✅ Estructura básica MySQL generada: $mysqlFile\n";
}

function getBasicHotelStructure() {
    return "
-- Estructura básica del sistema hotelero

-- Tabla de usuarios
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de hoteles
CREATE TABLE `hotels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `direccion` text,
  `telefono` varchar(50),
  `email` varchar(255),
  `sitio_web` varchar(255),
  `simbolo_moneda` varchar(10) DEFAULT 'Q.',
  `checkin_time` time DEFAULT '14:00:00',
  `checkout_time` time DEFAULT '12:00:00',
  `reserva_tiempo_expiracion` int DEFAULT 240,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio_base` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de niveles
CREATE TABLE `nivels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de habitaciones
CREATE TABLE `habitacions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL,
  `categoria_id` bigint unsigned NOT NULL,
  `nivel_id` bigint unsigned NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` enum('Disponible','Reservada-Pendiente','Reservada-Confirmada','Ocupada','Limpieza','Mantenimiento') DEFAULT 'Disponible',
  `descripcion` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `habitacions_numero_unique` (`numero`),
  KEY `habitacions_categoria_id_foreign` (`categoria_id`),
  KEY `habitacions_nivel_id_foreign` (`nivel_id`),
  CONSTRAINT `habitacions_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `habitacions_nivel_id_foreign` FOREIGN KEY (`nivel_id`) REFERENCES `nivels` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clientes
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255),
  `email` varchar(255),
  `telefono` varchar(50),
  `dpi` varchar(20),
  `nit` varchar(20),
  `direccion` text,
  `origen` enum('backend','landing') DEFAULT 'backend',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de reservas
CREATE TABLE `reservas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `habitacion_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente de Confirmación','Pendiente','Check-in','Check-out','Cancelada') DEFAULT 'Pendiente',
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservas_habitacion_id_foreign` (`habitacion_id`),
  KEY `reservas_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `reservas_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitacions` (`id`),
  CONSTRAINT `reservas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cajas
CREATE TABLE `cajas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha_apertura` date NOT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `saldo_inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_final` decimal(10,2) DEFAULT NULL,
  `turno` enum('diurno','nocturno') NOT NULL,
  `estado` enum('abierta','cerrada') DEFAULT 'abierta',
  `usuario_apertura_id` bigint unsigned NOT NULL,
  `usuario_cierre_id` bigint unsigned DEFAULT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cajas_usuario_apertura_id_foreign` (`usuario_apertura_id`),
  KEY `cajas_usuario_cierre_id_foreign` (`usuario_cierre_id`),
  CONSTRAINT `cajas_usuario_apertura_id_foreign` FOREIGN KEY (`usuario_apertura_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cajas_usuario_cierre_id_foreign` FOREIGN KEY (`usuario_cierre_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos básicos
INSERT INTO `hotels` (`id`, `nombre`, `direccion`, `telefono`, `email`, `simbolo_moneda`, `created_at`, `updated_at`) VALUES
(1, 'Hotel Demo', 'Dirección del Hotel', '+502 1234-5678', 'info@hotel.com', 'Q.', NOW(), NOW());

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `precio_base`, `created_at`, `updated_at`) VALUES
(1, 'Estándar', 'Habitación estándar con servicios básicos', 200.00, NOW(), NOW()),
(2, 'Superior', 'Habitación superior con servicios adicionales', 300.00, NOW(), NOW()),
(3, 'Suite', 'Suite de lujo con todos los servicios', 500.00, NOW(), NOW());

INSERT INTO `nivels` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Primer Nivel', 'Habitaciones en el primer nivel', NOW(), NOW()),
(2, 'Segundo Nivel', 'Habitaciones en el segundo nivel', NOW(), NOW()),
(3, 'Tercer Nivel', 'Habitaciones en el tercer nivel', NOW(), NOW());

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@hotel.com', NOW(), '$2y$12$LfWR3FQZhqZI1r/jE0oWa.LTN8KzFQhJRR8XB1zVCnEGHQRvt5LhS', NOW(), NOW());
-- Password: admin123

";
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

echo "✅ Proceso completado\n";