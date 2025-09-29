<?php

/**
 * Hotel Management System - Production Optimization Script
 *
 * This script optimizes Laravel application for production environment
 * by clearing caches, optimizing configurations, and setting up proper caching
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ProductionOptimizer
{
    private $basePath;
    private $artisanPath;

    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__);
        $this->artisanPath = $this->basePath . '/artisan';

        if (!file_exists($this->artisanPath)) {
            throw new Exception("Laravel artisan command not found at: {$this->artisanPath}");
        }
    }

    /**
     * Run artisan command and return output
     */
    private function runArtisan($command, $description = null)
    {
        $fullCommand = "cd {$this->basePath} && php artisan {$command} 2>&1";

        if ($description) {
            $this->log("Running: {$description}", 'info');
        }

        $this->log("Command: php artisan {$command}", 'debug');

        $output = [];
        $returnCode = 0;
        exec($fullCommand, $output, $returnCode);

        if ($returnCode === 0) {
            $this->log("✓ Success", 'success');
            return true;
        } else {
            $this->log("✗ Failed (Code: {$returnCode})", 'error');
            foreach ($output as $line) {
                $this->log("  {$line}", 'error');
            }
            return false;
        }
    }

    /**
     * Clear all caches
     */
    public function clearCaches()
    {
        $this->log("=== CLEARING CACHES ===", 'header');

        $commands = [
            'config:clear' => 'Clear configuration cache',
            'route:clear' => 'Clear route cache',
            'view:clear' => 'Clear compiled views',
            'cache:clear' => 'Clear application cache',
            'event:clear' => 'Clear cached events',
            'queue:clear' => 'Clear queue cache',
        ];

        $success = true;
        foreach ($commands as $command => $description) {
            if (!$this->runArtisan($command, $description)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Generate production caches
     */
    public function generateCaches()
    {
        $this->log("=== GENERATING PRODUCTION CACHES ===", 'header');

        $commands = [
            'config:cache' => 'Cache configuration files',
            'route:cache' => 'Cache route definitions',
            'view:cache' => 'Compile view templates',
            'event:cache' => 'Cache event definitions',
        ];

        $success = true;
        foreach ($commands as $command => $description) {
            if (!$this->runArtisan($command, $description)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Optimize autoloader
     */
    public function optimizeAutoloader()
    {
        $this->log("=== OPTIMIZING AUTOLOADER ===", 'header');

        $command = "cd {$this->basePath} && composer dump-autoload --optimize --no-dev 2>&1";
        $this->log("Running: Optimize Composer autoloader", 'info');

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->log("✓ Autoloader optimized", 'success');
            return true;
        } else {
            $this->log("✗ Autoloader optimization failed", 'error');
            foreach ($output as $line) {
                $this->log("  {$line}", 'error');
            }
            return false;
        }
    }

    /**
     * Set up storage link
     */
    public function setupStorageLink()
    {
        $this->log("=== SETTING UP STORAGE LINK ===", 'header');

        return $this->runArtisan('storage:link', 'Create storage symbolic link');
    }

    /**
     * Optimize database queries
     */
    public function optimizeDatabase()
    {
        $this->log("=== DATABASE OPTIMIZATION ===", 'header');

        // Run model caching if available
        if ($this->commandExists('model:cache')) {
            $this->runArtisan('model:cache', 'Cache Eloquent models');
        }

        // Cache database schema if available
        if ($this->commandExists('schema:cache')) {
            $this->runArtisan('schema:cache', 'Cache database schema');
        }

        return true;
    }

    /**
     * Check if artisan command exists
     */
    private function commandExists($command)
    {
        $output = [];
        exec("cd {$this->basePath} && php artisan list | grep '{$command}'", $output);
        return !empty($output);
    }

    /**
     * Verify optimization results
     */
    public function verifyOptimization()
    {
        $this->log("=== VERIFICATION ===", 'header');

        $checks = [
            'bootstrap/cache/config.php' => 'Configuration cache',
            'bootstrap/cache/routes-v7.php' => 'Route cache',
            'bootstrap/cache/events.php' => 'Event cache',
            'public/storage' => 'Storage link',
        ];

        $allGood = true;
        foreach ($checks as $file => $description) {
            $path = $this->basePath . '/' . $file;
            if (file_exists($path) || is_link($path)) {
                $this->log("✓ {$description}: OK", 'success');
            } else {
                $this->log("✗ {$description}: Missing", 'warning');
                $allGood = false;
            }
        }

        // Check important directories and permissions
        $directories = [
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ];

        foreach ($directories as $dir) {
            $path = $this->basePath . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
                $this->log("✓ Created directory: {$dir}", 'success');
            } else {
                $this->log("✓ Directory exists: {$dir}", 'success');
            }
        }

        return $allGood;
    }

    /**
     * Generate optimization report
     */
    public function generateReport()
    {
        $this->log("=== OPTIMIZATION REPORT ===", 'header');

        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'laravel_version' => $this->getLaravelVersion(),
            'environment' => $this->getEnvironment(),
            'optimizations' => [
                'config_cached' => file_exists($this->basePath . '/bootstrap/cache/config.php'),
                'routes_cached' => file_exists($this->basePath . '/bootstrap/cache/routes-v7.php'),
                'views_cached' => file_exists($this->basePath . '/bootstrap/cache/events.php'),
                'storage_linked' => is_link($this->basePath . '/public/storage'),
                'autoloader_optimized' => file_exists($this->basePath . '/vendor/composer/autoload_classmap.php'),
            ]
        ];

        // Save report
        $reportPath = $this->basePath . '/deploy/optimization_report.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        $this->log("Report saved to: {$reportPath}", 'info');

        // Display summary
        $optimized = array_filter($report['optimizations']);
        $total = count($report['optimizations']);
        $optimizedCount = count($optimized);

        $this->log("Optimization Status: {$optimizedCount}/{$total} completed", 'info');

        foreach ($report['optimizations'] as $name => $status) {
            $icon = $status ? '✓' : '✗';
            $color = $status ? 'success' : 'warning';
            $this->log("{$icon} " . ucfirst(str_replace('_', ' ', $name)), $color);
        }

        return $report;
    }

    /**
     * Get Laravel version
     */
    private function getLaravelVersion()
    {
        $output = [];
        exec("cd {$this->basePath} && php artisan --version", $output);
        return isset($output[0]) ? trim($output[0]) : 'Unknown';
    }

    /**
     * Get environment
     */
    private function getEnvironment()
    {
        $envPath = $this->basePath . '/.env';
        if (file_exists($envPath)) {
            $env = file_get_contents($envPath);
            if (preg_match('/APP_ENV=(.+)/', $env, $matches)) {
                return trim($matches[1]);
            }
        }
        return 'Unknown';
    }

    /**
     * Log message with color coding
     */
    private function log($message, $type = 'info')
    {
        $colors = [
            'header' => "\033[1;34m", // Bold Blue
            'info' => "\033[0;36m",   // Cyan
            'success' => "\033[0;32m", // Green
            'warning' => "\033[1;33m", // Yellow
            'error' => "\033[0;31m",   // Red
            'debug' => "\033[0;37m"    // Light Gray
        ];

        $reset = "\033[0m";
        $color = $colors[$type] ?? $colors['info'];

        if (php_sapi_name() === 'cli') {
            echo $color . $message . $reset . "\n";
        } else {
            echo htmlspecialchars($message) . "<br>\n";
        }

        // Log to file
        $logFile = $this->basePath . '/deploy/optimization.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] [{$type}] {$message}\n", FILE_APPEND);
    }

    /**
     * Run full optimization process
     */
    public function optimize()
    {
        $this->log("🏨 Hotel Management System - Production Optimization", 'header');
        $this->log("Started at: " . date('Y-m-d H:i:s'), 'info');
        $this->log(str_repeat("=", 60), 'header');

        $steps = [
            'clearCaches' => 'Clear existing caches',
            'generateCaches' => 'Generate production caches',
            'optimizeAutoloader' => 'Optimize Composer autoloader',
            'setupStorageLink' => 'Set up storage link',
            'optimizeDatabase' => 'Optimize database',
            'verifyOptimization' => 'Verify optimization results',
        ];

        $success = true;
        foreach ($steps as $method => $description) {
            $this->log("\n📋 {$description}...", 'info');
            if (!$this->$method()) {
                $success = false;
                $this->log("❌ Step failed: {$description}", 'error');
            }
        }

        // Generate final report
        $this->log("", 'info');
        $report = $this->generateReport();

        $this->log("", 'info');
        if ($success) {
            $this->log("🎉 Optimization completed successfully!", 'success');
        } else {
            $this->log("⚠️  Optimization completed with warnings. Check logs for details.", 'warning');
        }

        $this->log("Finished at: " . date('Y-m-d H:i:s'), 'info');

        return $success;
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'optimize';

    try {
        $optimizer = new ProductionOptimizer();

        switch ($action) {
            case 'clear':
                $optimizer->clearCaches();
                break;

            case 'cache':
                $optimizer->generateCaches();
                break;

            case 'autoloader':
                $optimizer->optimizeAutoloader();
                break;

            case 'verify':
                $optimizer->verifyOptimization();
                break;

            case 'optimize':
            default:
                $optimizer->optimize();
                break;
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}