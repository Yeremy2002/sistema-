<?php
/**
 * Script para generar imágenes JPG placeholder reales 
 * para reemplazar los SVG placeholder en el futuro
 */

// Lista de imágenes a generar
$images = [
    'hero-bg.jpg' => ['width' => 1920, 'height' => 1080, 'text' => 'IMAGEN HERO\nDEL HOTEL', 'color' => [139, 90, 60]],
    'promo-romantic.jpg' => ['width' => 400, 'height' => 300, 'text' => 'FIN DE SEMANA\nROMÁNTICO', 'color' => [180, 90, 120]],
    'promo-family.jpg' => ['width' => 400, 'height' => 300, 'text' => 'PLAN\nFAMILIAR', 'color' => [90, 150, 90]],
    'promo-adventure.jpg' => ['width' => 400, 'height' => 300, 'text' => 'AVENTURA EN\nLA MONTAÑA', 'color' => [60, 120, 180]],
    'room-standard.jpg' => ['width' => 400, 'height' => 300, 'text' => 'HABITACIÓN\nESTÁNDAR', 'color' => [160, 130, 100]],
    'room-deluxe.jpg' => ['width' => 400, 'height' => 300, 'text' => 'HABITACIÓN\nDELUXE', 'color' => [180, 150, 120]],
    'room-suite.jpg' => ['width' => 400, 'height' => 300, 'text' => 'SUITE\nFAMILIAR', 'color' => [200, 170, 140]],
];

$outputDir = __DIR__ . '/public/hotel-landing/images-jpg/';

// Crear directorio si no existe
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Verificar si GD está instalado
if (!extension_loaded('gd')) {
    echo "❌ La extensión GD no está instalada. No se pueden generar imágenes.\n";
    echo "Para instalar GD en macOS: brew install php-gd\n";
    echo "O simplemente usa las imágenes SVG que ya funcionan correctamente.\n";
    exit(1);
}

echo "🖼️  Generando imágenes placeholder...\n\n";

foreach ($images as $filename => $config) {
    $width = $config['width'];
    $height = $config['height'];
    $text = $config['text'];
    $color = $config['color'];
    
    // Crear imagen
    $image = imagecreatetruecolor($width, $height);
    
    // Colores
    $bgColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $borderColor = imagecolorallocate($image, $color[0] - 30, $color[1] - 30, $color[2] - 30);
    
    // Llenar fondo
    imagefill($image, 0, 0, $bgColor);
    
    // Dibujar borde
    imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);
    imagerectangle($image, 5, 5, $width-6, $height-6, $borderColor);
    
    // Configurar texto
    $fontSize = min($width, $height) / 20;
    $font = 5; // Fuente built-in de GD
    
    // Calcular posición del texto
    $lines = explode("\n", $text);
    $totalHeight = count($lines) * 20;
    $startY = ($height - $totalHeight) / 2;
    
    // Dibujar cada línea
    foreach ($lines as $index => $line) {
        $textWidth = strlen($line) * 10; // Aproximación para fuente built-in
        $x = ($width - $textWidth) / 2;
        $y = $startY + ($index * 20);
        
        imagestring($image, $font, $x, $y, $line, $textColor);
    }
    
    // Agregar tamaño en la esquina
    $sizeText = "{$width}x{$height}";
    imagestring($image, 3, 10, $height - 20, $sizeText, $textColor);
    
    // Guardar imagen
    $filepath = $outputDir . $filename;
    imagejpeg($image, $filepath, 85);
    imagedestroy($image);
    
    echo "✅ Generada: $filename ({$width}x{$height})\n";
}

echo "\n🎉 ¡Imágenes generadas exitosamente!\n";
echo "📁 Ubicación: $outputDir\n\n";
echo "💡 Para usar estas imágenes:\n";
echo "1. Copia las imágenes de 'images-jpg/' a 'images/'\n";
echo "2. Cambia las extensiones en dynamic.blade.php de .svg a .jpg\n";
echo "3. O simplemente mantén los SVG que ya funcionan correctamente\n\n";
echo "🔍 Las imágenes SVG actuales son placeholders funcionales y se ven bien.\n";
echo "   Solo reemplázalas con imágenes reales cuando las tengas disponibles.\n";
?>