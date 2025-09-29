<?php

/**
 * Hotel Management System - Database Backup and Restore Script
 *
 * This script provides functionality to:
 * 1. Create database backups
 * 2. Restore from backups
 * 3. Export data for migration
 */

class DatabaseBackup
{
    private $host;
    private $username;
    private $password;
    private $database;
    private $backupDir;

    public function __construct($config = null)
    {
        // Default configuration for production
        $this->host = $config['host'] ?? 'localhost';
        $this->username = $config['username'] ?? 'casaviejagt_hoteluser';
        $this->password = $config['password'] ?? 'SalesSystem2025!';
        $this->database = $config['database'] ?? 'casaviejagt_hotel_management';
        $this->backupDir = __DIR__ . '/backups';

        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a full database backup
     */
    public function createBackup($filename = null)
    {
        if (!$filename) {
            $filename = 'hotel_backup_' . date('Y-m-d_H-i-s') . '.sql';
        }

        $backupPath = $this->backupDir . '/' . $filename;

        // Use mysqldump to create backup
        $command = sprintf(
            'mysqldump --single-transaction --routines --triggers -h %s -u %s -p%s %s > %s',
            escapeshellarg($this->host),
            escapeshellarg($this->username),
            escapeshellarg($this->password),
            escapeshellarg($this->database),
            escapeshellarg($backupPath)
        );

        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar === 0) {
            $this->log("Backup created successfully: $filename");
            $this->compressBackup($backupPath);
            return $backupPath;
        } else {
            $this->log("Backup failed: " . implode("\n", $output), 'error');
            return false;
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup($backupFile)
    {
        $backupPath = $this->backupDir . '/' . $backupFile;

        if (!file_exists($backupPath)) {
            $this->log("Backup file not found: $backupFile", 'error');
            return false;
        }

        // If file is compressed, decompress it first
        if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'gz') {
            $decompressed = $this->decompressBackup($backupPath);
            if (!$decompressed) {
                return false;
            }
            $backupPath = $decompressed;
        }

        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < %s',
            escapeshellarg($this->host),
            escapeshellarg($this->username),
            escapeshellarg($this->password),
            escapeshellarg($this->database),
            escapeshellarg($backupPath)
        );

        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar === 0) {
            $this->log("Database restored successfully from: $backupFile");
            return true;
        } else {
            $this->log("Restore failed: " . implode("\n", $output), 'error');
            return false;
        }
    }

    /**
     * List available backups
     */
    public function listBackups()
    {
        $backups = [];
        $files = glob($this->backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);

        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        // Sort by date, newest first
        usort($backups, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return $backups;
    }

    /**
     * Compress backup file
     */
    private function compressBackup($filePath)
    {
        if (function_exists('gzencode')) {
            $data = file_get_contents($filePath);
            $compressed = gzencode($data, 9);
            $compressedPath = $filePath . '.gz';

            if (file_put_contents($compressedPath, $compressed)) {
                unlink($filePath); // Remove original
                $this->log("Backup compressed: " . basename($compressedPath));
                return $compressedPath;
            }
        }
        return $filePath;
    }

    /**
     * Decompress backup file
     */
    private function decompressBackup($filePath)
    {
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gz') {
            $compressed = file_get_contents($filePath);
            $data = gzdecode($compressed);
            $decompressedPath = str_replace('.gz', '', $filePath);

            if (file_put_contents($decompressedPath, $data)) {
                $this->log("Backup decompressed: " . basename($decompressedPath));
                return $decompressedPath;
            }
        }
        return false;
    }

    /**
     * Format file size
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Log messages
     */
    private function log($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";

        echo $logMessage;
        file_put_contents($this->backupDir . '/backup.log', $logMessage, FILE_APPEND);
    }

    /**
     * Clean old backups (keep only specified number)
     */
    public function cleanOldBackups($keepCount = 10)
    {
        $backups = $this->listBackups();

        if (count($backups) > $keepCount) {
            $toDelete = array_slice($backups, $keepCount);

            foreach ($toDelete as $backup) {
                $filePath = $this->backupDir . '/' . $backup['filename'];
                if (unlink($filePath)) {
                    $this->log("Deleted old backup: " . $backup['filename']);
                }
            }
        }
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'help';

    $backup = new DatabaseBackup();

    switch ($action) {
        case 'create':
            echo "Creating database backup...\n";
            $result = $backup->createBackup();
            if ($result) {
                echo "Backup created successfully: $result\n";
            } else {
                echo "Backup failed!\n";
                exit(1);
            }
            break;

        case 'list':
            echo "Available backups:\n";
            echo str_repeat("=", 50) . "\n";
            $backups = $backup->listBackups();
            if (empty($backups)) {
                echo "No backups found.\n";
            } else {
                foreach ($backups as $b) {
                    printf("%-30s %8s %s\n", $b['filename'], $b['size'], $b['date']);
                }
            }
            break;

        case 'restore':
            $filename = $argv[2] ?? null;
            if (!$filename) {
                echo "Usage: php database_backup.php restore <filename>\n";
                exit(1);
            }
            echo "Restoring from backup: $filename\n";
            if ($backup->restoreBackup($filename)) {
                echo "Restore completed successfully!\n";
            } else {
                echo "Restore failed!\n";
                exit(1);
            }
            break;

        case 'clean':
            $keepCount = intval($argv[2] ?? 10);
            echo "Cleaning old backups (keeping $keepCount)...\n";
            $backup->cleanOldBackups($keepCount);
            echo "Cleanup completed.\n";
            break;

        case 'help':
        default:
            echo "Hotel Management System - Database Backup Tool\n";
            echo str_repeat("=", 50) . "\n";
            echo "Usage: php database_backup.php <action> [options]\n\n";
            echo "Actions:\n";
            echo "  create                 Create a new backup\n";
            echo "  list                   List all available backups\n";
            echo "  restore <filename>     Restore from backup\n";
            echo "  clean [keep_count]     Clean old backups (default: keep 10)\n";
            echo "  help                   Show this help\n\n";
            echo "Examples:\n";
            echo "  php database_backup.php create\n";
            echo "  php database_backup.php list\n";
            echo "  php database_backup.php restore hotel_backup_2024-01-01_12-00-00.sql.gz\n";
            echo "  php database_backup.php clean 5\n";
            break;
    }
}