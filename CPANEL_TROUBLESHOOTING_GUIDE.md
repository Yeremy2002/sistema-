# 🔧 Guía de Solución de Problemas - Hotel Casa Vieja Management System

## 🚨 Problemas Comunes y Soluciones

### 1. **Error: "PHP version >= 8.2.0 required"**

**Problema:** El servidor tiene una versión de PHP menor a 8.2.0

**Solución:**
1. Accede a **cPanel → Software → Select PHP Version**
2. Cambia la versión a **PHP 8.2** o superior
3. Asegúrate de que esté **activada** para tu dominio
4. Reinicia el script de instalación

**Verificación:**
```bash
# Crear un archivo phpinfo.php para verificar
<?php phpinfo(); ?>
```

---

### 2. **Error: "Database connection failed"**

**Problema:** No se puede conectar a la base de datos MySQL

**Solución:**
1. Verifica las credenciales en cPanel → **MySQL Databases**
2. Confirma que la base de datos existe: `casaviejagt_hotel_management`
3. Verifica el usuario: `casaviejagt_hoteluser`
4. Asegúrate que el usuario tenga **ALL PRIVILEGES** en la base de datos

**Verificar conexión:**
```php
<?php
$host = 'localhost';
$db = 'casaviejagt_hotel_management';
$user = 'casaviejagt_hoteluser';
$pass = 'SalesSystem2025!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ Conexión exitosa";
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

---

### 3. **Error: "Permission denied" en directorios**

**Problema:** Permisos incorrectos en carpetas storage y bootstrap

**Solución:**
1. Accede a **cPanel → File Manager**
2. Navega a tu dominio en `public_html`
3. Selecciona la carpeta **storage** → **Permissions** → **775**
4. Selecciona la carpeta **bootstrap/cache** → **Permissions** → **775**
5. Para el archivo **.env** → **Permissions** → **600**

**Via SSH (si tienes acceso):**
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod 600 .env
```

---

### 4. **Error: "Class not found" o "Autoload issues"**

**Problema:** Composer no se ejecutó correctamente

**Solución:**
1. Accede via **SSH** o **Terminal** en cPanel
2. Navega a tu directorio: `cd public_html`
3. Ejecuta: `composer install --no-dev --optimize-autoloader`
4. Si no tienes Composer, descárgalo:
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

---

### 5. **Error: "Assets not found" - CSS/JS missing**

**Problema:** Los assets frontend no se compilaron

**Solución Simple (Sin Node.js):**
1. Los assets ya están precompilados en el paquete
2. Verifica que existe la carpeta `public/build/`
3. Si no existe, copia desde el deployment package

**Solución Completa (Con Node.js):**
```bash
# Si tienes acceso a Node.js en el servidor
npm install
npm run build
```

---

### 6. **Error: "Key not set" - Application Key**

**Problema:** La clave de aplicación Laravel no está configurada

**Solución:**
```bash
cd public_html
php artisan key:generate --force
```

---

### 7. **Error 500 - Internal Server Error**

**Problema:** Error general del servidor

**Diagnóstico:**
1. Revisa los **Error Logs** en cPanel
2. Verifica el archivo `.env` existe y tiene contenido correcto
3. Comprueba permisos de archivos y carpetas

**Pasos de solución:**
```bash
# Limpiar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 8. **Error: "Storage link not working"**

**Problema:** Las imágenes subidas no se muestran

**Solución:**
```bash
php artisan storage:link
```

---

### 9. **Error: "Cron jobs not working"**

**Problema:** Las tareas programadas no se ejecutan

**Solución:**
1. Accede a **cPanel → Cron Jobs**
2. Agrega esta línea:
```bash
* * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1
```
3. Asegúrate de usar la **ruta completa** a tu directorio

---

### 10. **Error: "SSL Certificate issues"**

**Problema:** Certificado SSL no funciona

**Solución:**
1. Accede a **cPanel → SSL/TLS**
2. Activa **Force HTTPS Redirect**
3. Instala certificado **Let's Encrypt** gratuito
4. Actualiza la URL en `.env`:
```
APP_URL=https://casaviejagt.com
```

---

## 🛠️ Scripts de Emergencia

### Script de Diagnóstico Rápido
Crea `diagnostic.php` en tu raíz:

```php
<?php
echo "<h2>🔍 Diagnóstico del Sistema</h2>";

// PHP Version
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Extensions
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl'];
echo "<h3>Extensiones PHP:</h3>";
foreach($required as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "<p>$status $ext</p>";
}

// Permissions
echo "<h3>Permisos de Directorio:</h3>";
$dirs = ['storage', 'bootstrap/cache', 'public'];
foreach($dirs as $dir) {
    $writable = is_writable($dir) ? "✅ Escribible" : "❌ No escribible";
    echo "<p>$dir: $writable</p>";
}

// Database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=casaviejagt_hotel_management",
                   "casaviejagt_hoteluser", "SalesSystem2025!");
    echo "<p>✅ Conexión a base de datos: OK</p>";
} catch(Exception $e) {
    echo "<p>❌ Conexión a base de datos: " . $e->getMessage() . "</p>";
}

// Laravel specific
if(file_exists('artisan')) {
    echo "<p>✅ Laravel artisan: Encontrado</p>";
} else {
    echo "<p>❌ Laravel artisan: No encontrado</p>";
}

if(file_exists('.env')) {
    echo "<p>✅ Archivo .env: Encontrado</p>";
} else {
    echo "<p>❌ Archivo .env: No encontrado</p>";
}
?>
```

### Script de Reparación Automática
Crea `repair.php`:

```php
<?php
echo "<h2>🔧 Reparación Automática</h2>";

$commands = [
    'php artisan config:clear' => 'Limpiar config cache',
    'php artisan route:clear' => 'Limpiar route cache',
    'php artisan view:clear' => 'Limpiar view cache',
    'php artisan cache:clear' => 'Limpiar application cache',
    'php artisan storage:link' => 'Crear storage link',
    'php artisan config:cache' => 'Generar config cache',
    'php artisan route:cache' => 'Generar route cache'
];

foreach($commands as $cmd => $desc) {
    echo "<p>Ejecutando: $desc</p>";
    $output = shell_exec($cmd . ' 2>&1');
    echo "<pre>$output</pre>";
}

echo "<h3>✅ Reparación completada</h3>";
?>
```

---

## 📞 Contacto y Soporte

### URLs de Acceso
- **Admin Panel:** https://casaviejagt.com/admin
- **Landing Page:** https://casaviejagt.com/
- **API:** https://casaviejagt.com/api/

### Scripts de Setup
- **Setup Principal:** https://casaviejagt.com/deploy/cpanel_setup.php
- **Setup Mejorado:** https://casaviejagt.com/deploy/cpanel_setup_v2.php

### Credenciales por Defecto
- **Email:** admin@hotel.com
- **Password:** password
- **⚠️ CAMBIAR INMEDIATAMENTE DESPUÉS DE LA INSTALACIÓN**

### Información del Sistema
- **Laravel Version:** 12.x
- **PHP Required:** 8.2.0+
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Web Server:** Apache with mod_rewrite

---

## ✅ Lista de Verificación Post-Instalación

- [ ] PHP 8.2+ activado en cPanel
- [ ] Base de datos creada y usuario configurado
- [ ] Permisos de carpetas correctos (775 para storage, 600 para .env)
- [ ] Aplicación accesible via web
- [ ] Admin panel funcional
- [ ] Cron job configurado
- [ ] SSL activado y funcionando
- [ ] Contraseña de admin cambiada
- [ ] Configuración del hotel completada
- [ ] Habitaciones y categorías agregadas
- [ ] Sistema de reservas probado

**🎉 ¡Tu Hotel Casa Vieja Management System está listo para producción!**