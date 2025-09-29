<?php

/**
 * Hotel Management System - cPanel Database Setup Script
 *
 * This script should be uploaded to your cPanel and run once to:
 * 1. Test database connection
 * 2. Run migrations
 * 3. Seed essential data
 * 4. Verify installation
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Configuration
$config = [
    'database' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'casaviejagt_hotel_management',
        'username'  => 'casaviejagt_hoteluser',
        'password'  => 'SalesSystem2025!',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ]
];

function printMessage($message, $type = 'info') {
    $colors = [
        'info' => '🔵',
        'success' => '✅',
        'warning' => '⚠️',
        'error' => '❌'
    ];

    echo $colors[$type] . ' ' . $message . "\n";

    // Also log to a file
    $logFile = __DIR__ . '/setup.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " [$type] $message\n", FILE_APPEND);
}

function testDatabaseConnection($config) {
    try {
        $capsule = new Capsule;
        $capsule->addConnection($config['database']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // Test the connection
        $pdo = $capsule->getConnection()->getPdo();
        printMessage("Database connection successful!", 'success');
        return true;
    } catch (Exception $e) {
        printMessage("Database connection failed: " . $e->getMessage(), 'error');
        return false;
    }
}

function runArtisanCommand($command) {
    $output = [];
    $returnCode = 0;

    printMessage("Running: php artisan $command", 'info');

    exec("cd " . __DIR__ . "/.. && php artisan $command 2>&1", $output, $returnCode);

    if ($returnCode === 0) {
        printMessage("Command completed successfully", 'success');
        return true;
    } else {
        printMessage("Command failed with code $returnCode", 'error');
        foreach ($output as $line) {
            printMessage("  $line", 'error');
        }
        return false;
    }
}

function setupCronJobs() {
    $cronCommands = [
        '* * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1'
    ];

    printMessage("Please add the following cron job to your cPanel:", 'warning');
    foreach ($cronCommands as $cmd) {
        printMessage("  $cmd", 'info');
    }
}

function createHtaccess() {
    $htaccessContent = <<<EOT
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable server signature
ServerSignature Off

# Hide PHP version
<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>
EOT;

    $htaccessPath = __DIR__ . '/../public/.htaccess';
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        printMessage(".htaccess file created successfully", 'success');
        return true;
    } else {
        printMessage("Failed to create .htaccess file", 'error');
        return false;
    }
}

// Main installation process
echo "🏨 Hotel Management System - cPanel Setup\n";
echo "==========================================\n\n";

printMessage("Starting installation process...", 'info');

// Step 1: Test database connection
printMessage("Step 1: Testing database connection", 'info');
if (!testDatabaseConnection($config)) {
    printMessage("Installation aborted due to database connection failure", 'error');
    exit(1);
}

// Step 2: Check Laravel installation
printMessage("Step 2: Checking Laravel installation", 'info');
if (!file_exists(__DIR__ . '/../artisan')) {
    printMessage("Laravel artisan command not found", 'error');
    exit(1);
}

// Step 3: Run migrations
printMessage("Step 3: Running database migrations", 'info');
if (!runArtisanCommand("migrate --force")) {
    printMessage("Migration failed", 'error');
    exit(1);
}

// Step 4: Seed essential data
printMessage("Step 4: Seeding essential data", 'info');
if (!runArtisanCommand("db:seed --class=EssentialDataSeeder --force")) {
    printMessage("Seeding failed", 'error');
    exit(1);
}

// Step 5: Optimize Laravel for production
printMessage("Step 5: Optimizing Laravel for production", 'info');
$optimizeCommands = [
    "config:cache",
    "route:cache",
    "view:cache",
    "event:cache"
];

foreach ($optimizeCommands as $cmd) {
    if (!runArtisanCommand($cmd)) {
        printMessage("Optimization command '$cmd' failed", 'warning');
    }
}

// Step 6: Create .htaccess
printMessage("Step 6: Creating .htaccess file", 'info');
createHtaccess();

// Step 7: Set up storage link
printMessage("Step 7: Creating storage link", 'info');
runArtisanCommand("storage:link");

// Step 8: Display cron job instructions
printMessage("Step 8: Cron job setup", 'info');
setupCronJobs();

// Final verification
printMessage("Final verification...", 'info');
try {
    // Test if we can connect to the app
    if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
        printMessage("Laravel application bootstrap file found", 'success');
    }

    // Check if essential tables exist
    $capsule = new Capsule;
    $capsule->addConnection($config['database']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $tables = ['users', 'roles', 'permissions', 'categorias', 'niveles', 'habitaciones'];
    foreach ($tables as $table) {
        if (Capsule::schema()->hasTable($table)) {
            printMessage("Table '$table' exists", 'success');
        } else {
            printMessage("Table '$table' missing", 'warning');
        }
    }

} catch (Exception $e) {
    printMessage("Verification error: " . $e->getMessage(), 'error');
}

echo "\n🎉 Installation Complete!\n";
echo "========================\n\n";
echo "Default Admin Credentials:\n";
echo "Email: admin@hotel.com\n";
echo "Password: password\n\n";
echo "⚠️  IMPORTANT: Change the default admin password immediately!\n\n";
echo "Your hotel management system is now ready at: https://casaviejagt.com\n\n";
echo "Next steps:\n";
echo "1. Set up the cron job as shown above\n";
echo "2. Change the default admin password\n";
echo "3. Configure hotel settings\n";
echo "4. Add rooms and categories as needed\n";
echo "5. Test the reservation system\n\n";

printMessage("Setup log saved to: " . __DIR__ . "/setup.log", 'info');