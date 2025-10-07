<?php
/**
 * Fix Permissions Script for cPanel (No SSH)
 *
 * Upload to: /home/casaviejagt/public_html/fix-permissions.php
 * Visit: https://casaviejagt.com/fix-permissions.php
 *
 * ⚠️ DELETE after use!
 */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Permissions - Laravel</title>
    <style>
        body {
            font-family: monospace;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #569cd6; }
        pre { line-height: 1.6; }
    </style>
</head>
<body>
    <h2>🔧 Fix Permissions Script</h2>
    <pre>
<?php

function setPermissionsRecursive($path, $permission = 0775) {
    $count = 0;
    $errors = 0;

    if (!file_exists($path)) {
        echo "<span class='error'>✗ Path not found: $path</span>\n";
        return [$count, $errors];
    }

    try {
        chmod($path, $permission);
        $count++;

        if (is_dir($path)) {
            $items = scandir($path);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;

                $fullPath = $path . DIRECTORY_SEPARATOR . $item;
                list($c, $e) = setPermissionsRecursive($fullPath, $permission);
                $count += $c;
                $errors += $e;
            }
        }
    } catch (Exception $e) {
        echo "<span class='error'>✗ Error: {$e->getMessage()}</span>\n";
        $errors++;
    }

    return [$count, $errors];
}

echo "<span class='info'>Starting permission fix...</span>\n\n";

// Fix storage/
echo "📁 Fixing storage/ permissions...\n";
list($storageCount, $storageErrors) = setPermissionsRecursive(__DIR__ . '/storage', 0775);
if ($storageErrors === 0) {
    echo "<span class='success'>✓ storage/ fixed ($storageCount files/dirs)</span>\n\n";
} else {
    echo "<span class='error'>⚠ storage/ had $storageErrors errors</span>\n\n";
}

// Fix bootstrap/cache/
echo "📁 Fixing bootstrap/cache/ permissions...\n";
list($bootstrapCount, $bootstrapErrors) = setPermissionsRecursive(__DIR__ . '/bootstrap/cache', 0775);
if ($bootstrapErrors === 0) {
    echo "<span class='success'>✓ bootstrap/cache/ fixed ($bootstrapCount files/dirs)</span>\n\n";
} else {
    echo "<span class='error'>⚠ bootstrap/cache/ had $bootstrapErrors errors</span>\n\n";
}

echo "\n<span class='success'>═══════════════════════════════════</span>\n";
echo "<span class='success'>✅ Permission fix completed!</span>\n";
echo "<span class='success'>═══════════════════════════════════</span>\n\n";

echo "<span class='error'>⚠️  DELETE THIS FILE NOW!</span>\n";
echo "rm /home/casaviejagt/public_html/fix-permissions.php\n";

?>
    </pre>
</body>
</html>
