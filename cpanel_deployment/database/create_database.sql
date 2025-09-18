-- Script para crear base de datos en cPanel
-- Ejecutar en phpMyAdmin o via línea de comandos MySQL

-- Crear base de datos (ajustar nombre según prefijo del hosting)
-- CREATE DATABASE IF NOT EXISTS `username_hotel_management`
-- DEFAULT CHARACTER SET utf8mb4
-- COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
-- USE `username_hotel_management`;

-- Crear usuario (si no se ha creado desde cPanel)
-- CREATE USER IF NOT EXISTS 'username_hoteluser'@'localhost' IDENTIFIED BY 'password_segura_aqui';

-- Otorgar permisos
-- GRANT ALL PRIVILEGES ON `username_hotel_management`.* TO 'username_hoteluser'@'localhost';

-- Aplicar cambios
-- FLUSH PRIVILEGES;

-- Configurar timezone para Guatemala
SET time_zone = '-06:00';

-- Configurar modo SQL
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Verificar que la base de datos está configurada correctamente
SELECT
    'Base de datos creada correctamente' as status,
    DATABASE() as database_name,
    @@character_set_database as charset,
    @@collation_database as collation,
    NOW() as timestamp;

COMMIT;