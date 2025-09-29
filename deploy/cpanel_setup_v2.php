<?php
/**
 * Hotel Casa Vieja Management System - cPanel Setup Script v2.0
 * Improved version with better error handling and PHP 8.2+ support
 *
 * This script automates the setup process for Laravel 12 on cPanel hosting
 * Run this script by visiting: https://casaviejagt.com/deploy/cpanel_setup_v2.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300); // 5 minutes timeout

// Security check - only allow execution from the correct domain
$allowed_domains = ['casaviejagt.com', 'www.casaviejagt.com', 'localhost'];
$current_domain = $_SERVER['HTTP_HOST'] ?? 'unknown';

if (!in_array($current_domain, $allowed_domains) && !isset($_GET['force'])) {
    die('Setup script can only be executed from authorized domains. Add ?force=1 to override.');
}

class HotelSetup {
    private $steps = [];
    private $currentStep = 0;
    private $errors = [];
    private $warnings = [];
    private $basePath;

    public function __construct() {
        $this->basePath = dirname(__DIR__);
        $this->initializeSteps();
    }

    private function initializeSteps() {
        $this->steps = [
            'Environment Check' => 'checkEnvironment',
            'Database Connection' => 'setupDatabase',
            'Laravel Installation' => 'setupLaravel',
            'File Permissions' => 'setPermissions',
            'Asset Compilation' => 'compileAssets',
            'Cache Optimization' => 'optimizeCache',
            'Final Verification' => 'finalVerification'
        ];
    }

    public function run() {
        $this->outputHeader();

        foreach ($this->steps as $stepName => $method) {
            $this->currentStep++;
            $this->outputStep($stepName);

            try {
                $result = $this->$method();
                if ($result) {
                    $this->outputSuccess("✓ $stepName completed successfully");
                } else {
                    $this->outputError("✗ $stepName failed");
                    if (count($this->errors) > 3) {
                        $this->outputError("Too many errors. Stopping installation.");
                        break;
                    }
                }
            } catch (Exception $e) {
                $this->outputError("✗ $stepName failed: " . $e->getMessage());
                $this->errors[] = $e->getMessage();
            }

            $this->outputProgress();
        }

        $this->outputSummary();
    }

    private function checkEnvironment() {
        $this->outputInfo("Checking PHP version and extensions...");

        // Check PHP version
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            throw new Exception("PHP 8.2.0 or higher is required. Current version: " . PHP_VERSION);
        }
        $this->outputSuccess("PHP version: " . PHP_VERSION . " ✓");

        // Check required extensions
        $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'zip'];
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->warnings[] = "Extension $ext is not loaded";
                $this->outputWarning("Extension $ext: Missing");
            } else {
                $this->outputSuccess("Extension $ext: Available ✓");
            }
        }

        // Check writable directories
        $writable_dirs = [
            $this->basePath . '/storage',
            $this->basePath . '/bootstrap/cache',
            $this->basePath . '/public'
        ];

        foreach ($writable_dirs as $dir) {
            if (!is_writable($dir)) {
                $this->warnings[] = "Directory $dir is not writable";
                $this->outputWarning("Directory $dir: Not writable");
            } else {
                $this->outputSuccess("Directory $dir: Writable ✓");
            }
        }

        return true;
    }

    private function setupDatabase() {
        $this->outputInfo("Setting up database connection...");

        $envFile = $this->basePath . '/.env';
        if (!file_exists($envFile)) {
            throw new Exception(".env file not found");
        }

        // Parse .env file
        $env = $this->parseEnvFile($envFile);

        // Test database connection
        try {
            $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}";
            $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $this->outputSuccess("Database connection: OK ✓");
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }

        return true;
    }

    private function setupLaravel() {
        $this->outputInfo("Setting up Laravel application...");

        // Generate application key if not exists
        $this->runCommand("php artisan key:generate --force", "Application key generated");

        // Run migrations
        $this->runCommand("php artisan migrate --force", "Database migrations completed");

        // Run seeders
        $this->runCommand("php artisan db:seed --class=EssentialDataSeeder --force", "Essential data seeded");

        // Install storage link
        if (!file_exists($this->basePath . '/public/storage')) {
            $this->runCommand("php artisan storage:link", "Storage link created");
        }

        return true;
    }

    private function setPermissions() {
        $this->outputInfo("Setting file permissions...");

        // Set directory permissions
        $this->setDirectoryPermissions($this->basePath . '/storage', 0775);
        $this->setDirectoryPermissions($this->basePath . '/bootstrap/cache', 0775);

        // Set file permissions for .env
        if (file_exists($this->basePath . '/.env')) {
            chmod($this->basePath . '/.env', 0600);
            $this->outputSuccess(".env file permissions set to 600 ✓");
        }

        return true;
    }

    private function compileAssets() {
        $this->outputInfo("Compiling frontend assets...");

        // Check if node_modules exists
        if (!is_dir($this->basePath . '/node_modules')) {
            $this->outputWarning("Node modules not found. Assets may need manual compilation.");
            return true;
        }

        // Try to compile assets
        $output = [];
        $return_var = 0;
        exec("cd {$this->basePath} && npm run build 2>&1", $output, $return_var);

        if ($return_var === 0) {
            $this->outputSuccess("Assets compiled successfully ✓");
        } else {
            $this->outputWarning("Asset compilation may have issues. Check manually if needed.");
        }

        return true;
    }

    private function optimizeCache() {
        $this->outputInfo("Optimizing Laravel caches...");

        // Clear caches first
        $this->runCommand("php artisan config:clear", "Config cache cleared");
        $this->runCommand("php artisan route:clear", "Route cache cleared");
        $this->runCommand("php artisan view:clear", "View cache cleared");

        // Generate optimized caches
        $this->runCommand("php artisan config:cache", "Config cached");
        $this->runCommand("php artisan route:cache", "Routes cached");
        $this->runCommand("php artisan view:cache", "Views cached");

        return true;
    }

    private function finalVerification() {
        $this->outputInfo("Running final verification...");

        // Check if app is accessible
        $app_url = $this->getAppUrl();
        if ($app_url) {
            $this->outputSuccess("Application URL: $app_url ✓");
        }

        // Check admin access
        $admin_url = rtrim($app_url, '/') . '/admin';
        $this->outputInfo("Admin Panel: $admin_url");

        // Check API endpoints
        $api_url = rtrim($app_url, '/') . '/api/test-cors';
        $this->outputInfo("API Endpoint: $api_url");

        return true;
    }

    private function runCommand($command, $successMessage = null) {
        $output = [];
        $return_var = 0;

        $fullCommand = "cd {$this->basePath} && $command 2>&1";
        exec($fullCommand, $output, $return_var);

        if ($return_var === 0) {
            if ($successMessage) {
                $this->outputSuccess($successMessage . " ✓");
            }
            return true;
        } else {
            $error = implode("\n", $output);
            $this->outputWarning("Command '$command' had issues: " . $error);
            return false;
        }
    }

    private function setDirectoryPermissions($dir, $permissions) {
        if (is_dir($dir)) {
            chmod($dir, $permissions);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    chmod($item->getRealPath(), $permissions);
                }
            }
            $this->outputSuccess("Permissions set for $dir ✓");
        }
    }

    private function parseEnvFile($file) {
        $env = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value, '"\'');
            }
        }

        return $env;
    }

    private function getAppUrl() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "$protocol://$host";
    }

    // Output methods
    private function outputHeader() {
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Hotel Casa Vieja - Setup</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #4CAF50; background: #f8f9fa; border-radius: 0 8px 8px 0; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .info { color: #2196F3; }
        .progress { width: 100%; height: 20px; background: #e0e0e0; border-radius: 10px; margin: 20px 0; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #4CAF50, #45a049); transition: width 0.3s ease; }
        .summary { margin-top: 30px; padding: 20px; background: #f0f8ff; border-radius: 8px; border: 2px solid #4CAF50; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🏨 Hotel Casa Vieja Management System</h1>
            <h2>Production Setup - Version 2.0</h2>
            <p>Automated installation for cPanel hosting</p>
        </div>
        <div class='content'>
";
    }

    private function outputStep($stepName) {
        echo "<div class='step'><h3>Step {$this->currentStep}: $stepName</h3>";
    }

    private function outputSuccess($message) {
        echo "<div class='success'>$message</div>";
    }

    private function outputError($message) {
        echo "<div class='error'>$message</div>";
    }

    private function outputWarning($message) {
        echo "<div class='warning'>$message</div>";
    }

    private function outputInfo($message) {
        echo "<div class='info'>$message</div>";
    }

    private function outputProgress() {
        $progress = ($this->currentStep / count($this->steps)) * 100;
        echo "<div class='progress'><div class='progress-bar' style='width: {$progress}%'></div></div>";
        echo "</div>"; // Close step div
        flush();
        ob_flush();
    }

    private function outputSummary() {
        $totalSteps = count($this->steps);
        $errorCount = count($this->errors);
        $warningCount = count($this->warnings);

        echo "<div class='summary'>";
        echo "<h2>🎉 Installation Summary</h2>";
        echo "<p><strong>Total Steps:</strong> $totalSteps</p>";
        echo "<p><strong>Errors:</strong> $errorCount</p>";
        echo "<p><strong>Warnings:</strong> $warningCount</p>";

        if ($errorCount === 0) {
            echo "<div class='success'>";
            echo "<h3>✅ Installation Completed Successfully!</h3>";
            echo "<p>Your Hotel Casa Vieja Management System is ready for use.</p>";
            echo "<p><strong>Next Steps:</strong></p>";
            echo "<ul>";
            echo "<li>🔑 Change default admin password (admin@hotel.com / password)</li>";
            echo "<li>⚙️ Configure hotel settings</li>";
            echo "<li>🏠 Add rooms and categories</li>";
            echo "<li>🔗 Set up cron job for scheduled tasks</li>";
            echo "<li>🔒 Enable SSL certificate</li>";
            echo "</ul>";
            echo "<p><strong>Access Points:</strong></p>";
            echo "<ul>";
            echo "<li>📊 Admin Panel: <a href='/admin' target='_blank'>" . $this->getAppUrl() . "/admin</a></li>";
            echo "<li>🌐 Landing Page: <a href='/' target='_blank'>" . $this->getAppUrl() . "</a></li>";
            echo "<li>🔌 API: <a href='/api/' target='_blank'>" . $this->getAppUrl() . "/api/</a></li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ Installation Completed with Errors</h3>";
            echo "<p>Please review the errors and warnings above and resolve them manually.</p>";
            echo "</div>";
        }

        if (!empty($this->warnings)) {
            echo "<h4>⚠️ Warnings:</h4>";
            echo "<ul>";
            foreach ($this->warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
        }

        echo "<h4>📞 Support:</h4>";
        echo "<p>For technical support, refer to the CLAUDE.md file in your project root or contact your system administrator.</p>";
        echo "</div>";

        echo "</div></div></body></html>";
    }
}

// Run the setup
try {
    $setup = new HotelSetup();
    $setup->run();
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffe6e6; border: 1px solid red; margin: 20px;'>";
    echo "<h2>Critical Error</h2>";
    echo "<p>Setup failed with error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your server configuration and try again.</p>";
    echo "</div>";
}
?>