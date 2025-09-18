-- MySQL initialization script for Hotel Management System

-- Create application database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create application user with proper permissions
CREATE USER IF NOT EXISTS 'hotel_user'@'%' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON hotel_management.* TO 'hotel_user'@'%';

-- Create backup user with read-only access
CREATE USER IF NOT EXISTS 'hotel_backup'@'%' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW ON hotel_management.* TO 'hotel_backup'@'%';

-- Create monitoring user
CREATE USER IF NOT EXISTS 'hotel_monitor'@'%' IDENTIFIED BY 'monitor_password';
GRANT PROCESS, REPLICATION CLIENT ON *.* TO 'hotel_monitor'@'%';
GRANT SELECT ON performance_schema.* TO 'hotel_monitor'@'%';

FLUSH PRIVILEGES;

-- Set MySQL configuration for Laravel
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
SET GLOBAL innodb_file_format = 'Barracuda';
SET GLOBAL innodb_file_per_table = 1;
SET GLOBAL innodb_large_prefix = 1;