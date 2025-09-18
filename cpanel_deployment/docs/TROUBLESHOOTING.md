# Guía de Solución de Problemas - cPanel Deployment

Esta guía aborda los problemas más comunes que pueden surgir al deployar el Sistema de Gestión Hotelera en cPanel y sus soluciones.

## 🚨 Problemas Comunes y Soluciones

### 1. Error 500 - Internal Server Error

#### Síntomas
- Página web muestra "Error interno del servidor"
- No se puede acceder a ninguna página
- Sitio completamente inaccesible

#### Causas Posibles
1. **Permisos incorrectos**
2. **Configuración .env incorrecta**
3. **APP_KEY no generada**
4. **Dependencias faltantes**
5. **Errores en .htaccess**

#### Soluciones

##### A. Verificar Logs de Error
```bash
# 1. En cPanel → Error Logs
# 2. Revisar últimas entradas

# 3. Logs de Laravel (vía File Manager)
private_laravel/storage/logs/laravel.log
```

##### B. Verificar Permisos
```bash
# Via File Manager en cPanel:
# Permisos para directorios: 755
# Permisos para archivos: 644

# Específicamente verificar:
private_laravel/storage/ → 755 (recursivo)
private_laravel/bootstrap/cache/ → 755 (recursivo)
private_laravel/artisan → 755
```

##### C. Verificar .env
```env
# Verificar que estas variables están configuradas:
APP_KEY=base64:TuClaveGeneradaAqui
APP_URL=https://tudominio.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

##### D. Generar APP_KEY
```bash
# Si tienes SSH:
cd /home/username/private_laravel
php artisan key:generate

# Si NO tienes SSH:
# 1. Ir a https://generate-random.org/laravel-key-generator
# 2. Copiar clave generada
# 3. Pegar en .env como: APP_KEY=base64:tu_clave_aqui
```

##### E. Verificar .htaccess
```apache
# Archivo: public_html/.htaccess
# Debe contener reglas de rewrite para Laravel

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 2. Error de Conexión a Base de Datos

#### Síntomas
- "SQLSTATE[HY000] [1045] Access denied"
- "SQLSTATE[HY000] [2002] No such file or directory"
- Páginas cargan pero sin datos

#### Soluciones

##### A. Verificar Credenciales
```env
# En archivo .env, verificar:
DB_CONNECTION=mysql
DB_HOST=localhost  # O IP del servidor MySQL
DB_PORT=3306
DB_DATABASE=username_hotel_management  # Nombre completo con prefijo
DB_USERNAME=username_hoteluser         # Usuario completo con prefijo
DB_PASSWORD=password_exacto            # Sin espacios extra
```

##### B. Probar Conexión en phpMyAdmin
1. Acceder a phpMyAdmin desde cPanel
2. Usar las mismas credenciales del .env
3. Si no puede conectar, el problema está en las credenciales

##### C. Verificar Base de Datos
```sql
-- En phpMyAdmin, verificar que existen las tablas:
SHOW TABLES;

-- Debe mostrar tablas como:
-- users, hotels, habitacions, reservas, etc.
```

##### D. Recrear Usuario de BD
1. En cPanel → MySQL Databases
2. Eliminar usuario actual
3. Crear nuevo usuario
4. Asignar a base de datos con todos los privilegios

### 3. Archivos CSS/JS No Cargan

#### Síntomas
- Páginas sin estilos
- JavaScript no funciona
- Imágenes no se muestran

#### Soluciones

##### A. Verificar Estructura de Archivos
```
public_html/
├── index.php
├── .htaccess
├── build/           # Assets compilados de Vite
├── css/             # CSS personalizado
├── js/              # JavaScript personalizado
└── images/          # Imágenes
```

##### B. Verificar Permisos de Assets
```bash
# Permisos para archivos estáticos: 644
# Permisos para directorios: 755
```

##### C. Verificar URLs en el Código
```php
// En blade templates, usar:
{{ asset('css/app.css') }}
{{ asset('js/app.js') }}

// No usar rutas hardcoded como:
// "/css/app.css" (puede no funcionar en subdirectorios)
```

##### D. Regenerar Assets
```bash
# Si tienes SSH:
cd /home/username/private_laravel
npm run build

# Si NO tienes SSH:
# Compilar assets localmente y subir carpeta build/
```

### 4. Problemas con Cronjobs

#### Síntomas
- Reservas no expiran automáticamente
- Notificaciones no se envían
- Sistema no ejecuta tareas programadas

#### Soluciones

##### A. Verificar Configuración de Cron
```bash
# Comando correcto en cPanel Cron Jobs:
* * * * * cd /home/username/private_laravel && php artisan schedule:run >> /dev/null 2>&1

# Verificar:
# 1. Ruta absoluta correcta
# 2. Espacio después de &&
# 3. Sin caracteres especiales
```

##### B. Probar Comando Manualmente
```bash
# Via SSH (si disponible):
cd /home/username/private_laravel
php artisan schedule:run

# Debe mostrar las tareas ejecutadas
```

##### C. Verificar Logs de Cron
```bash
# Modificar comando cron temporalmente para ver errores:
* * * * * cd /home/username/private_laravel && php artisan schedule:run >> /home/username/cron.log 2>&1

# Revisar archivo cron.log después de unos minutos
```

##### D. Verificar Versión de PHP
```bash
# Algunos hostings requieren ruta específica:
/usr/local/bin/php artisan schedule:run
# O:
/opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run
```

### 5. Error de Memoria Agotada

#### Síntomas
- "Fatal error: Allowed memory size exhausted"
- Páginas se cargan parcialmente
- Timeout en operaciones

#### Soluciones

##### A. Aumentar Límite de Memoria
```ini
# En cPanel → PHP Selector → Options
# O crear archivo .htaccess con:
php_value memory_limit 512M
php_value max_execution_time 300
```

##### B. Optimizar Aplicación
```bash
# Via SSH:
cd /home/username/private_laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar caches innecesarios:
php artisan cache:clear
```

##### C. Verificar Consultas Pesadas
```php
// En el código, buscar queries N+1:
// Mal:
foreach ($reservas as $reserva) {
    echo $reserva->cliente->nombre; // Query por cada reserva
}

// Bien:
$reservas = Reserva::with('cliente')->get();
foreach ($reservas as $reserva) {
    echo $reserva->cliente->nombre; // Una sola query
}
```

### 6. Problemas con SSL/HTTPS

#### Síntomas
- "Su conexión no es privada"
- Mixed content warnings
- Redirects infinitos

#### Soluciones

##### A. Configurar SSL en cPanel
1. cPanel → SSL/TLS
2. Let's Encrypt (gratuito) o subir certificado
3. Force HTTPS Redirect

##### B. Configurar .env
```env
APP_URL=https://tudominio.com  # Usar HTTPS
SESSION_SECURE_COOKIE=true
FORCE_HTTPS=true
```

##### C. Verificar .htaccess
```apache
# Forzar HTTPS:
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 7. Problemas con Permisos

#### Síntomas
- "Permission denied"
- No se pueden subir archivos
- Logs no se escriben

#### Soluciones

##### A. Establecer Permisos Correctos
```
Directorios: 755 (rwxr-xr-x)
Archivos: 644 (rw-r--r--)
Ejecutables: 755 (rwxr-xr-x)

Específicamente:
storage/ → 755 (recursivo)
bootstrap/cache/ → 755 (recursivo)
artisan → 755
```

##### B. Via File Manager cPanel
1. Seleccionar archivos/carpetas
2. Click derecho → Permissions
3. Establecer valores numéricos
4. Marcar "Recurse into subdirectories" para carpetas

### 8. Problemas con Emails

#### Síntomas
- Notificaciones no se envían
- Emails van a spam
- Error de autenticación SMTP

#### Soluciones

##### A. Configurar SMTP Correctamente
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com  # O servidor SMTP del hosting
MAIL_PORT=587                 # O 465 para SSL
MAIL_USERNAME=tu_email@tudominio.com
MAIL_PASSWORD=tu_password_email
MAIL_ENCRYPTION=tls           # O ssl para puerto 465
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Tu Hotel"
```

##### B. Probar Configuración
```bash
# Via SSH:
cd /home/username/private_laravel
php artisan tinker

# En tinker:
Mail::raw('Test email', function($message) {
    $message->to('test@email.com')->subject('Test');
});
```

##### C. Usar Servicio Externo
```env
# Para Gmail:
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password  # No la contraseña normal
MAIL_ENCRYPTION=tls
```

### 9. Problemas con CORS/API

#### Síntomas
- API no responde desde landing page
- "CORS policy" errors en browser
- 404 en endpoints API

#### Soluciones

##### A. Verificar Configuración CORS
```env
# En .env:
CORS_ALLOWED_ORIGINS="https://tudominio.com,https://www.tudominio.com,https://landing.tudominio.com"
```

##### B. Verificar Rutas API
```bash
# Via SSH:
cd /home/username/private_laravel
php artisan route:list | grep api

# Verificar que las rutas existen
```

##### C. Probar API Directamente
```bash
# Probar endpoint:
curl https://tudominio.com/api/test-cors

# Debe devolver JSON válido
```

## 🔧 Herramientas de Debugging

### 1. Verificar Estado del Sistema

```bash
# Crear script de diagnóstico: check_system.php
<?php
echo "=== Diagnóstico del Sistema ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Laravel Directory: " . __DIR__ . "\n";
echo "Storage Writable: " . (is_writable('storage') ? 'YES' : 'NO') . "\n";
echo "Cache Writable: " . (is_writable('bootstrap/cache') ? 'YES' : 'NO') . "\n";
echo "ENV File: " . (file_exists('.env') ? 'EXISTS' : 'MISSING') . "\n";

if (file_exists('.env')) {
    $env = file_get_contents('.env');
    echo "APP_KEY Set: " . (strpos($env, 'APP_KEY=base64:') !== false ? 'YES' : 'NO') . "\n";
    echo "DB Configured: " . (strpos($env, 'DB_DATABASE=') !== false ? 'YES' : 'NO') . "\n";
}

echo "================================\n";
```

### 2. Log Personalizado

```php
// En cualquier controlador, agregar logging:
Log::info('Debug info', [
    'user_id' => auth()->id(),
    'request_data' => request()->all(),
    'session_data' => session()->all()
]);
```

### 3. Verificación de URLs

```bash
# Crear archivo test.php en public_html:
<?php
echo "URL actual: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
```

## 📞 Contactar Soporte

### Información para Incluir

Cuando contactes soporte del hosting, incluye:

```
Asunto: Problema con aplicación Laravel en cPanel

Información del sitio:
- Dominio: tudominio.com
- Panel: cPanel
- Aplicación: Laravel 12
- PHP Version: 8.2

Problema específico:
[Describir el problema]

Logs de error:
[Copiar logs relevantes]

Pasos para reproducir:
1. [Paso 1]
2. [Paso 2]
3. [Resultado inesperado]

Configuración actual:
[Incluir partes relevantes de .env sin passwords]
```

### Recursos de Soporte

1. **Documentación Laravel**: https://laravel.com/docs
2. **Foros de Hosting**: Buscar foros específicos de tu proveedor
3. **Stack Overflow**: Etiquetar con `laravel`, `cpanel`, `shared-hosting`

## 📋 Checklist de Verificación

### Antes de Contactar Soporte

- [ ] Verificado logs de error
- [ ] Probado soluciones básicas
- [ ] Verificado permisos
- [ ] Confirmado credenciales de BD
- [ ] Probado en modo debug local
- [ ] Revisado documentación

### Información de Debug Esencial

- [ ] URL exacta del problema
- [ ] Mensaje de error completo
- [ ] Logs de Laravel
- [ ] Logs de servidor
- [ ] Configuración PHP
- [ ] Pasos para reproducir

---

**Recordatorio**: Siempre haz backup antes de hacer cambios importantes en producción.