<?php
// ULTRA SIMPLE FIX - No Laravel dependencies
// Just fixes .env directly

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Fix</title>
    <style>
        body { font-family: monospace; background: #000; color: #0f0; padding: 20px; }
        pre { background: #111; padding: 15px; border: 1px solid #0f0; }
        .error { color: #f00; }
        .success { color: #0f0; }
        .warning { color: #ff0; }
    </style>
</head>
<body>
    <h1>Laravel Quick Fix</h1>
    <pre><?php

// Step 1: Generate APP_KEY
echo "Step 1: Generating new APP_KEY...\n";
$newKey = 'base64:' . base64_encode(random_bytes(32));
echo "<span class='success'>✓ Generated: " . substr($newKey, 0, 40) . "...</span>\n\n";

// Step 2: Read .env
echo "Step 2: Reading .env file...\n";
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "<span class='error'>✗ .env not found!</span>\n";
    if (file_exists(__DIR__ . '/.env.production')) {
        echo "  → Copying from .env.production...\n";
        copy(__DIR__ . '/.env.production', $envPath);
        echo "<span class='success'>  ✓ Created .env</span>\n";
    } else {
        die("<span class='error'>ERROR: No .env or .env.production found!</span>\n");
    }
}

$envContent = file_get_contents($envPath);
echo "<span class='success'>✓ Read " . strlen($envContent) . " bytes</span>\n\n";

// Step 3: Replace APP_KEY
echo "Step 3: Updating APP_KEY...\n";
if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $newKey, $envContent);
    echo "<span class='success'>✓ Replaced existing key</span>\n";
} else {
    $envContent .= "\nAPP_KEY=" . $newKey . "\n";
    echo "<span class='success'>✓ Added new key</span>\n";
}

// Step 4: Save .env
echo "\nStep 4: Saving .env...\n";
$saved = file_put_contents($envPath, $envContent);
if ($saved) {
    echo "<span class='success'>✓ Saved " . $saved . " bytes</span>\n";
} else {
    die("<span class='error'>✗ Could not save .env!</span>\n");
}

// Step 5: Clear bootstrap cache
echo "\nStep 5: Clearing caches...\n";
$cacheDir = __DIR__ . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.php');
    $deleted = 0;
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore') {
            @unlink($file);
            $deleted++;
        }
    }
    echo "<span class='success'>✓ Deleted $deleted cache files</span>\n";
}

// Step 6: Create storage dirs
echo "\nStep 6: Creating storage directories...\n";
$dirs = [
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs'
];
foreach ($dirs as $dir) {
    $path = __DIR__ . $dir;
    if (!is_dir($path)) {
        @mkdir($path, 0775, true);
        echo "<span class='success'>+ Created $dir</span>\n";
    }
}

// Step 7: Set permissions
echo "\nStep 7: Setting permissions...\n";
@chmod(__DIR__ . '/storage', 0775);
@chmod(__DIR__ . '/bootstrap/cache', 0775);
echo "<span class='success'>✓ Permissions set</span>\n";

// Done
echo "\n" . str_repeat('=', 50) . "\n";
echo "<span class='success'>SUCCESS! All fixes applied.</span>\n";
echo str_repeat('=', 50) . "\n\n";

echo "Next steps:\n";
echo "1. Visit: <a href='/' style='color:#0f0'>https://casaviejagt.com</a>\n";
echo "2. DELETE this file: quick-fix.php\n";
echo "3. If working, run migrations via setup.php\n\n";

echo "Your new APP_KEY (for reference):\n";
echo "<span class='warning'>$newKey</span>\n";

    ?></pre>
</body>
</html>
