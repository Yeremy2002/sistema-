<?php
// Quick APP_KEY Generator
// Upload to: /home/casaviejagt/public_html/generate-key.php
// Visit: https://casaviejagt.com/generate-key.php
// Copy the key and paste it in .env
// DELETE this file after use!

$key = 'base64:' . base64_encode(random_bytes(32));

echo "<!DOCTYPE html>";
echo "<html><head><title>Generate APP_KEY</title></head><body>";
echo "<h1>🔑 Nueva APP_KEY</h1>";
echo "<p>Copia esta clave y pégala en tu archivo .env:</p>";
echo "<pre style='background: #f0f0f0; padding: 15px; font-size: 14px;'>";
echo "APP_KEY=" . $key;
echo "</pre>";
echo "<p><strong>Pasos:</strong></p>";
echo "<ol>";
echo "<li>Copia la línea completa de arriba</li>";
echo "<li>Abre /public_html/.env en File Manager</li>";
echo "<li>Busca la línea que dice APP_KEY=</li>";
echo "<li>Reemplázala con la línea copiada</li>";
echo "<li>Guarda el archivo</li>";
echo "<li>ELIMINA este archivo (generate-key.php)</li>";
echo "</ol>";
echo "</body></html>";
?>
