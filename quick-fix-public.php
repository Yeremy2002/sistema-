<?php
// ULTRA SIMPLE FIX - Works from public/ directory
// No Laravel dependencies

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
    <h1>Laravel Quick Fix (from public/)</h1>
    <pre><?php

// Base path is one level up from public/
$basePath = dirname(__DIR__);
echo "Base path: $basePath\n\n";

// Step 1: Generate APP_KEY
echo "Step 1: Generating new APP_KEY...\n";
$newKey = 'base64:' . base64_encode(random_bytes(32));
echo "<span class='success'>✓ Generated: " . substr($newKey, 0, 40) . "...</span>\n\n";

// Step 2: Read .env
echo "Step 2: Reading .env file...\n";
$envPath = $basePath . '/.env';

if (!file_exists($envPath)) {
    echo "<span class='error'>✗ .env not found at: $envPath</span>\n";
    if (file_exists($basePath . '/.env.production')) {
        echo "  → Copying from .env.production...\n";
        copy($basePath . '/.env.production', $envPath);
        echo "<span class='success'>  ✓ Created .env</span>\n";
    } else {
        die("<span class='error'>ERROR: No .env or .env.production found!</span>\n");
    }
} else {
    echo "<span class='success'>✓ Found .env at: $envPath</span>\n";
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
$cacheDir = $basePath . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.php');
    $deleted = 0;
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore') {
            if (@unlink($file)) {
                $deleted++;
            }
        }
    }
    echo "<span class='success'>✓ Deleted $deleted cache files from bootstrap/cache/</span>\n";
}

// Clear storage framework cache
$storageCacheDirs = [
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views'
];

foreach ($storageCacheDirs as $dir) {
    $fullPath = $basePath . $dir;
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '/*');
        $deleted = 0;
        foreach ($files as $file) {
            if (basename($file) !== '.gitignore' && is_file($file)) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }
        if ($deleted > 0) {
            echo "<span class='success'>✓ Deleted $deleted files from $dir</span>\n";
        }
    }
}

// Step 6: Create storage dirs
echo "\nStep 6: Creating storage directories...\n";
$dirs = [
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs'
];
$created = 0;
foreach ($dirs as $dir) {
    $path = $basePath . $dir;
    if (!is_dir($path)) {
        if (@mkdir($path, 0775, true)) {
            echo "<span class='success'>+ Created $dir</span>\n";
            $created++;
        }
    }
}
if ($created === 0) {
    echo "<span class='success'>✓ All directories exist</span>\n";
}

// Step 7: Set permissions
echo "\nStep 7: Setting permissions...\n";
@chmod($basePath . '/storage', 0775);
@chmod($basePath . '/bootstrap/cache', 0775);
echo "<span class='success'>✓ Permissions set on storage/ and bootstrap/cache/</span>\n";

// Done
echo "\n" . str_repeat('=', 60) . "\n";
echo "<span class='success'>✅ SUCCESS! All fixes applied.</span>\n";
echo str_repeat('=', 60) . "\n\n";

echo "Next steps:\n";
echo "1. Visit: <a href='/' style='color:#0f0'>https://casaviejagt.com</a>\n";
echo "2. DELETE this file: public/quick-fix-public.php\n";
echo "3. If Laravel loads, you're good!\n";
echo "4. If still error, check the NEW logs\n\n";

echo "Your new APP_KEY (saved to .env):\n";
echo "<span class='warning'>$newKey</span>\n\n";

echo "Files checked:\n";
echo "- .env: $envPath\n";
echo "- Bootstrap cache: $cacheDir\n";
echo "- Storage: {$basePath}/storage/\n";

    ?></pre>
</body>
</html>
