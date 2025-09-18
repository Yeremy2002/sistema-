-- MySQL dump para Hotel Management System
-- Generado: 2025-09-18 10:55:10
-- Versión del servidor: MySQL 5.7+
-- Versión de PHP: 8.3.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: hotel_management
--

-- Estructura de tabla para `cache`
CREATE TABLE "cache" ("key" varchar not null, "value" TEXT not null, "expiration" INT not null, primary key ("key")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla cache del sistema hotelero';

-- Datos para tabla `cache`
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:29:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:16:"ver habitaciones";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:18:"crear habitaciones";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:19:"editar habitaciones";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:21:"eliminar habitaciones";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:27:"cambiar estado habitaciones";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:12:"ver reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:14:"crear reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:15:"editar reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:17:"eliminar reservas";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:13:"hacer checkin";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:14:"hacer checkout";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:12:"ver clientes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:14:"crear clientes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:15:"editar clientes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:17:"eliminar clientes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:14:"aperturar caja";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:11:"cerrar caja";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:11:"ver arqueos";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:15:"generar arqueos";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:12:"ver usuarios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:14:"crear usuarios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:15:"editar usuarios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:19:"desactivar usuarios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:12:"ver reportes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:16:"generar reportes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:17:"ver configuracion";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:20:"editar configuracion";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:18:"registrar limpieza";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:20:"registrar reparacion";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}}s:5:"roles";a:4:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:19:"Super Administrador";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:7:"Gerente";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:3;s:1:"b";s:13:"Administrador";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:4;s:1:"b";s:8:"Conserje";s:1:"c";s:3:"web";}}}', 1746048303);

-- Estructura de tabla para `cache_locks`
CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" INT not null, primary key ("key")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla cache_locks del sistema hotelero';

-- Estructura de tabla para `categorias`
CREATE TABLE "categorias" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "nombre" varchar not null, "descripcion" TEXT, "estado" tinyint(1) not null default '1', "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "deleted_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla categorias del sistema hotelero';

-- Datos para tabla `categorias`
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Individual', '1 cama', 1, '2025-04-29 16:01:10', '2025-04-29 16:01:10', NULL),
(2, 'Doble', '2 camas', 1, '2025-04-29 16:01:20', '2025-04-29 16:01:20', NULL),
(3, 'Triple', '3 camas', 1, '2025-04-29 16:01:33', '2025-04-29 16:01:33', NULL),
(4, 'Mini Suite', 'Mini Suite', 1, '2025-04-29 16:01:45', '2025-04-29 16:01:45', NULL),
(5, 'Suite', 'Suite completa', 1, '2025-04-29 16:02:01', '2025-04-29 16:02:01', NULL);

-- Estructura de tabla para `clientes`
CREATE TABLE "clientes" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "created_at" TIMESTAMP, "updated_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla clientes del sistema hotelero';

-- Estructura de tabla para `failed_jobs`
CREATE TABLE "failed_jobs" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "uuid" varchar not null, "connection" TEXT not null, "queue" TEXT not null, "payload" TEXT not null, "exception" TEXT not null, "failed_at" TIMESTAMP not null default CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla failed_jobs del sistema hotelero';

-- Estructura de tabla para `habitacions`
CREATE TABLE "habitacions" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "numero" varchar not null, "categoria_id" INT not null, "nivel_id" INT not null, "descripcion" TEXT, "caracteristicas" varchar, "precio" numeric not null, "estado" varchar check ("estado" in ('Disponible', 'Ocupada', 'Mantenimiento')) not null default 'Disponible', "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "deleted_at" TIMESTAMP, foreign key("categoria_id") references "categorias"("id"), foreign key("nivel_id") references "nivels"("id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla habitacions del sistema hotelero';

-- Datos para tabla `habitacions`
INSERT INTO `habitacions` (`id`, `numero`, `categoria_id`, `nivel_id`, `descripcion`, `caracteristicas`, `precio`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 'Habitación Individual sencilla con 1 cama king 1 un baño sencillo', 'Habitación Individual sencilla con 1 cama king 1 un baño sencillo', 205, 'Disponible', '2025-04-29 16:08:49', '2025-04-29 21:37:21', '2025-04-29 21:37:21');

-- Estructura de tabla para `job_batches`
CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" INT not null, "pending_jobs" INT not null, "failed_jobs" INT not null, "failed_job_ids" TEXT not null, "options" TEXT, "cancelled_at" INT, "created_at" INT not null, "finished_at" INT, primary key ("id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla job_batches del sistema hotelero';

-- Estructura de tabla para `jobs`
CREATE TABLE "jobs" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "queue" varchar not null, "payload" TEXT not null, "attempts" INT not null, "reserved_at" INT, "available_at" INT not null, "created_at" INT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla jobs del sistema hotelero';

-- Estructura de tabla para `limpiezas`
CREATE TABLE "limpiezas" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "habitacion_id" INT not null, "user_id" INT not null, "fecha" date not null, "hora" time not null, "estado" varchar check ("estado" in ('pendiente', 'en_proceso', 'completada')) not null, "observaciones" TEXT, "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "deleted_at" TIMESTAMP, foreign key("habitacion_id") references "habitacions"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla limpiezas del sistema hotelero';

-- Estructura de tabla para `migrations`
CREATE TABLE "migrations" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "migration" varchar not null, "batch" INT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla migrations del sistema hotelero';

-- Datos para tabla `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_28_212016_create_habitacions_table', 1),
(5, '2025_04_28_212025_create_reservas_table', 1),
(6, '2025_04_28_212030_create_clientes_table', 1),
(7, '2025_04_28_212050_create_categorias_table', 1),
(8, '2025_04_28_212050_create_nivels_table', 1),
(9, '2025_04_28_233336_create_settings_table', 1),
(10, '2025_04_28_233500_add_active_to_users_table', 1),
(11, '2025_04_28_233622_create_personal_access_tokens_table', 2),
(12, '2025_04_28_233800_create_permission_tables', 3),
(13, '2025_04_28_234405_create_limpiezas_table', 4),
(14, '2025_04_28_234405_create_reparacions_table', 4);

-- Estructura de tabla para `model_has_permissions`
CREATE TABLE "model_has_permissions" ("permission_id" INT not null, "model_type" varchar not null, "model_id" INT not null, foreign key("permission_id") references "permissions"("id") on delete cascade, primary key ("permission_id", "model_id", "model_type")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla model_has_permissions del sistema hotelero';

-- Estructura de tabla para `model_has_roles`
CREATE TABLE "model_has_roles" ("role_id" INT not null, "model_type" varchar not null, "model_id" INT not null, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("role_id", "model_id", "model_type")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla model_has_roles del sistema hotelero';

-- Datos para tabla `model_has_roles`
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\Models\User', 1),
(1, 'App\Models\User', 2);

-- Estructura de tabla para `nivels`
CREATE TABLE "nivels" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "nombre" varchar not null, "descripcion" TEXT, "estado" tinyint(1) not null default '1', "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "deleted_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla nivels del sistema hotelero';

-- Datos para tabla `nivels`
INSERT INTO `nivels` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Primer Nivel', 'Habitaciones ubicadas en el primer nivel', 1, '2025-04-29 16:07:31', '2025-04-29 16:07:31', NULL),
(2, 'Segundo Nivel', 'Habitaciones ubicadas en el segundo nivel', 1, '2025-04-29 16:07:46', '2025-04-29 16:07:46', NULL);

-- Estructura de tabla para `password_reset_tokens`
CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" TIMESTAMP, primary key ("email")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla password_reset_tokens del sistema hotelero';

-- Estructura de tabla para `permissions`
CREATE TABLE "permissions" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "name" varchar not null, "guard_name" varchar not null, "created_at" TIMESTAMP, "updated_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla permissions del sistema hotelero';

-- Datos para tabla `permissions`
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'ver habitaciones', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(2, 'crear habitaciones', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(3, 'editar habitaciones', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(4, 'eliminar habitaciones', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(5, 'cambiar estado habitaciones', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(6, 'ver reservas', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(7, 'crear reservas', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(8, 'editar reservas', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(9, 'eliminar reservas', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(10, 'hacer checkin', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(11, 'hacer checkout', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(12, 'ver clientes', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(13, 'crear clientes', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(14, 'editar clientes', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(15, 'eliminar clientes', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(16, 'aperturar caja', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(17, 'cerrar caja', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(18, 'ver arqueos', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(19, 'generar arqueos', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(20, 'ver usuarios', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(21, 'crear usuarios', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(22, 'editar usuarios', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(23, 'desactivar usuarios', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(24, 'ver reportes', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(25, 'generar reportes', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(26, 'ver configuracion', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(27, 'editar configuracion', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(28, 'registrar limpieza', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(29, 'registrar reparacion', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17');

-- Estructura de tabla para `personal_access_tokens`
CREATE TABLE "personal_access_tokens" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "tokenable_type" varchar not null, "tokenable_id" INT not null, "name" varchar not null, "token" varchar not null, "abilities" TEXT, "last_used_at" TIMESTAMP, "expires_at" TIMESTAMP, "created_at" TIMESTAMP, "updated_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla personal_access_tokens del sistema hotelero';

-- Estructura de tabla para `reparacions`
CREATE TABLE "reparacions" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "habitacion_id" INT not null, "user_id" INT not null, "fecha" date not null, "hora" time not null, "estado" varchar check ("estado" in ('pendiente', 'en_proceso', 'completada')) not null, "tipo_reparacion" varchar not null, "costo" numeric not null default '0', "descripcion" TEXT not null, "observaciones" TEXT, "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "deleted_at" TIMESTAMP, foreign key("habitacion_id") references "habitacions"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla reparacions del sistema hotelero';

-- Estructura de tabla para `reservas`
CREATE TABLE "reservas" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "habitacion_id" INT not null, "user_id" INT not null, "nombre_cliente" varchar not null, "documento_cliente" varchar not null, "telefono_cliente" varchar, "observaciones" TEXT, "fecha_entrada" TIMESTAMP not null, "fecha_salida" TIMESTAMP not null, "total" numeric not null, "adelanto" numeric not null default '0', "estado" varchar check ("estado" in ('Pendiente', 'Check-in', 'Check-out', 'Cancelada')) not null default 'Pendiente', "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "deleted_at" TIMESTAMP, foreign key("habitacion_id") references "habitacions"("id"), foreign key("user_id") references "users"("id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla reservas del sistema hotelero';

-- Estructura de tabla para `role_has_permissions`
CREATE TABLE "role_has_permissions" ("permission_id" INT not null, "role_id" INT not null, foreign key("permission_id") references "permissions"("id") on delete cascade, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("permission_id", "role_id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla role_has_permissions del sistema hotelero';

-- Datos para tabla `role_has_permissions`
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(1, 2),
(18, 2),
(19, 2),
(12, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(1, 3),
(2, 3),
(3, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 3),
(10, 3),
(11, 3),
(12, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3),
(1, 4),
(28, 4),
(29, 4);

-- Estructura de tabla para `roles`
CREATE TABLE "roles" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "name" varchar not null, "guard_name" varchar not null, "created_at" TIMESTAMP, "updated_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla roles del sistema hotelero';

-- Datos para tabla `roles`
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Administrador', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(2, 'Gerente', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(3, 'Administrador', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17'),
(4, 'Conserje', 'web', '2025-04-28 23:38:17', '2025-04-28 23:38:17');

-- Estructura de tabla para `sessions`
CREATE TABLE "sessions" ("id" varchar not null, "user_id" INT, "ip_address" varchar, "user_agent" TEXT, "payload" TEXT not null, "last_activity" INT not null, primary key ("id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla sessions del sistema hotelero';

-- Datos para tabla `sessions`
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('plqf35iepqnc4SloY6fXgfYxadtBMXlcsSfiV3L1', 2, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:137.0) Gecko/20100101 Firefox/137.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRktlWXNHVEJYS3hUd3E1aVNpSjF0V09IN2kxTWxkZ1ZLTDR1cTV2RSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC91c3Vhcmlvcy8yL2VkaXQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3NDU5NDU5Njk7fX0=', 1745947541),
('c0E6TAh0IcL4dHpr5sebWWSkqpltuvZmg05rGOJP', 2, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:137.0) Gecko/20100101 Firefox/137.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiQ09QOUM5a3VyczJKOUprSXZWaWN4SnUxY3pNUk9Ma28waXlzNVk1ZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9oYWJpdGFjaW9uZXMiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3NDU5NjE2ODg7fX0=', 1745962691);

-- Estructura de tabla para `settings`
CREATE TABLE "settings" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "hotel_name" varchar not null, "address" varchar not null, "nit" varchar not null, "logo_path" varchar, "phone" varchar, "email" varchar, "description" TEXT, "created_at" TIMESTAMP, "updated_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla settings del sistema hotelero';

-- Estructura de tabla para `users`
CREATE TABLE "users" ("id" BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY not null, "name" varchar not null, "email" varchar not null, "email_verified_at" TIMESTAMP, "password" varchar not null, "remember_token" varchar, "created_at" TIMESTAMP, "updated_at" TIMESTAMP, "active" tinyint(1) not null default '1', "deleted_at" TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla users del sistema hotelero';

-- Datos para tabla `users`
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `active`, `deleted_at`) VALUES
(1, 'Super Admin', 'admin@admin.com', NULL, '$2y$12$2AbLdW0l732wrp/N/pxXoeJl/oWFjgo2OvahdUEnRHzyc443MYsau', NULL, '2025-04-28 23:38:17', '2025-04-28 23:38:17', 1, NULL),
(2, 'Richard Ortiz', 'digicom.ortiz@gmail.com', NULL, '$2y$12$CF8IbkwB7TjEE82H62PkkOwuxMYlLLC02IQGLinJlf0OIIbWAjf4q', NULL, '2025-04-28 23:39:25', '2025-04-28 23:39:25', 1, NULL);


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
