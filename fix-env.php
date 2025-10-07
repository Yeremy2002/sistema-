<?php
/**
 * Ultra-Simple .env Fixer
 * No dependencies, pure PHP
 */

// Prevent any Laravel loading
define('LARAVEL_START', microtime(true));

$basePath = __DIR__;
$envPath = $basePath . '/.env';
$envProductionPath = $basePath . '/.env.production';

// Output as plain text
header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "  LARAVEL .env EMERGENCY FIX\n";
echo "========================================\n\n";

// Step 1: Check if .env exists
echo "[1] Checking .env file...\n";
if (file_exists($envPath)) {
    echo "    ✓ .env exists\n";
    $envExists = true;
} else {
    echo "    ✗ .env NOT found\n";
    $envExists = false;

    // Try to create from .env.production
    if (file_exists($envProductionPath)) {
        echo "    → Copying from .env.production...\n";
        copy($envProductionPath, $envPath);
        echo "    ✓ .env created\n";
        $envExists = true;
    } else {
        echo "    ✗ .env.production also not found!\n";
        echo "\nERROR: Cannot continue without .env file\n";
        echo "Please upload .env.production to the server\n";
        exit(1);
    }
}

// Step 2: Read .env content
echo "\n[2] Reading .env content...\n";
$envContent = file_get_contents($envPath);
echo "    ✓ Read " . strlen($envContent) . " bytes\n";

// Step 3: Generate new APP_KEY
echo "\n[3] Generating new APP_KEY...\n";
$newKey = 'base64:' . base64_encode(random_bytes(32));
echo "    ✓ Generated: " . substr($newKey, 0, 30) . "...\n";

// Step 4: Replace APP_KEY
echo "\n[4] Updating APP_KEY in .env...\n";
if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $newKey, $envContent);
    echo "    ✓ Replaced existing APP_KEY\n";
} else {
    // Add APP_KEY if not found
    $envContent .= "\nAPP_KEY=" . $newKey . "\n";
    echo "    ✓ Added new APP_KEY\n";
}

// Step 5: Write back to .env
echo "\n[5] Saving .env file...\n";
$written = file_put_contents($envPath, $envContent);
if ($written !== false) {
    echo "    ✓ Saved " . $written . " bytes\n";
} else {
    echo "    ✗ ERROR: Could not write to .env\n";
    echo "    Check file permissions!\n";
    exit(1);
}

// Step 6: Clear bootstrap cache
echo "\n[6] Clearing bootstrap cache...\n";
$bootstrapCache = $basePath . '/bootstrap/cache';
if (is_dir($bootstrapCache)) {
    $files = glob($bootstrapCache . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore') {
            if (@unlink($file)) {
                $count++;
            }
        }
    }
    echo "    ✓ Deleted $count cache files\n";
} else {
    echo "    ⚠ bootstrap/cache not found\n";
}

// Step 7: Fix permissions
echo "\n[7] Setting permissions...\n";
$dirs = [
    '/storage',
    '/storage/framework',
    '/storage/framework/cache',
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs',
    '/bootstrap/cache'
];

foreach ($dirs as $dir) {
    $fullPath = $basePath . $dir;
    if (is_dir($fullPath)) {
        @chmod($fullPath, 0775);
        echo "    ✓ $dir (775)\n";
    } else {
        @mkdir($fullPath, 0775, true);
        echo "    + Created $dir\n";
    }
}

// Summary
echo "\n========================================\n";
echo "  ✅ FIX COMPLETED SUCCESSFULLY!\n";
echo "========================================\n\n";

echo "Next steps:\n";
echo "1. Visit: https://casaviejagt.com\n";
echo "2. If it works, continue with setup\n";
echo "3. DELETE this file: fix-env.php\n\n";

echo "Your new APP_KEY:\n";
echo $newKey . "\n\n";

echo "========================================\n";
echo "Script completed at: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
?>
