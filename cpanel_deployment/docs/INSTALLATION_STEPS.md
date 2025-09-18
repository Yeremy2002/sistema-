# Pasos Detallados de Instalación en cPanel

Esta guía proporciona instrucciones paso a paso con capturas conceptuales para instalar el Sistema de Gestión Hotelera en cPanel.

## 📋 Pre-requisitos

### Verificar Requisitos del Hosting

1. **Acceder a cPanel**
   - URL: `https://tudominio.com/cpanel` o `https://tudominio.com:2083`
   - Ingresar credenciales de hosting

2. **Verificar Versión PHP**
   - Buscar "PHP Selector" o "Select PHP Version"
   - Verificar que esté disponible PHP 8.2 o superior
   - Si no está disponible, contactar soporte del hosting

3. **Verificar Extensiones PHP**
   - En PHP Selector, revisar extensiones activas
   - Extensiones requeridas:
     ```
     ✅ PDO
     ✅ pdo_mysql
     ✅ mbstring
     ✅ openssl
     ✅ bcmath
     ✅ ctype
     ✅ fileinfo
     ✅ json
     ✅ tokenizer
     ✅ xml
     ✅ zip
     ✅ gd
     ```

## 🗄️ Configuración de Base de Datos

### Paso 1: Crear Base de Datos MySQL

1. **Acceder a MySQL Databases**
   - En cPanel, buscar "MySQL Databases"
   - Click en el ícono

2. **Crear Nueva Base de Datos**
   ```
   Nombre: hotel_management
   ```
   - Click "Create Database"
   - **Anotar**: El nombre completo será `username_hotel_management`

3. **Crear Usuario de Base de Datos**
   ```
   Username: hoteluser
   Password: [generar contraseña segura]
   ```
   - Click "Create User"
   - **Anotar**: Usuario completo será `username_hoteluser`

4. **Asignar Usuario a Base de Datos**
   - Seleccionar usuario creado
   - Seleccionar base de datos creada
   - Marcar "ALL PRIVILEGES"
   - Click "Make Changes"

### Paso 2: Importar Estructura de Base de Datos

1. **Acceder a phpMyAdmin**
   - En cPanel, click "phpMyAdmin"
   - Seleccionar base de datos `username_hotel_management`

2. **Importar Archivo SQL**
   - Click pestaña "Import"
   - Click "Choose File"
   - Seleccionar `database/hotel_management.sql`
   - Click "Go"

3. **Verificar Importación**
   - Revisar que se crearon las tablas:
     ```
     ✅ users
     ✅ hotels
     ✅ habitacions
     ✅ categorias
     ✅ nivels
     ✅ reservas
     ✅ clientes
     ✅ cajas
     ✅ permissions
     ✅ roles
     ✅ role_has_permissions
     ✅ model_has_permissions
     ✅ model_has_roles
     ✅ notifications
     ✅ system_settings
     ```

## 📁 Subida de Archivos

### Método A: File Manager (Recomendado para principiantes)

#### Paso 1: Preparar Estructura
1. **Acceder a File Manager**
   - En cPanel, click "File Manager"
   - Navegar a directorio principal (`/home/username/`)

2. **Crear Directorio Laravel**
   - Click "New Folder"
   - Nombre: `private_laravel`
   - Este directorio estará FUERA de `public_html` por seguridad

#### Paso 2: Subir Aplicación Laravel
1. **Navegar a `private_laravel`**
   - Double-click en la carpeta creada

2. **Subir Archivos**
   - Click "Upload"
   - Seleccionar todos los archivos de `cpanel_files/private_laravel/`
   - **Nota**: Esto puede tomar tiempo dependiendo del tamaño

3. **Extraer si es ZIP**
   - Si subiste como ZIP, click derecho → "Extract"

#### Paso 3: Subir Contenido Público
1. **Navegar a `public_html`**
   - Regresar al directorio principal
   - Entrar a `public_html`

2. **Limpiar Contenido Existente**
   - Seleccionar archivos existentes (excepto `.htaccess` si existe)
   - Click "Delete"

3. **Subir Archivos Públicos**
   - Subir todo el contenido de `cpanel_files/public_html/`
   - Incluye: `index.php`, `.htaccess`, assets, etc.

### Método B: FTP (Para usuarios avanzados)

```bash
# Configurar cliente FTP (FileZilla, WinSCP, etc.)
Host: ftp.tudominio.com
Usuario: tu_usuario_cpanel
Password: tu_password_cpanel
Puerto: 21

# Subir aplicación Laravel
Directorio local: cpanel_files/private_laravel/
Directorio remoto: /home/username/private_laravel/

# Subir contenido público
Directorio local: cpanel_files/public_html/
Directorio remoto: /home/username/public_html/
```

## ⚙️ Configuración de Variables de Entorno

### Paso 1: Configurar .env

1. **Acceder al archivo .env**
   - En File Manager, navegar a `private_laravel`
   - Buscar archivo `.env`
   - Si no existe, renombrar `.env.example` a `.env`

2. **Editar Configuración**
   - Click derecho en `.env` → "Edit"
   - Actualizar las siguientes variables:

```env
# Información del sitio
APP_NAME="Tu Hotel"
APP_URL=https://tudominio.com

# Base de datos (ACTUALIZAR con tus datos)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=username_hotel_management
DB_USERNAME=username_hoteluser
DB_PASSWORD=tu_password_de_bd

# Mail (ACTUALIZAR con tu configuración)
MAIL_HOST=mail.tudominio.com
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_password_email
MAIL_FROM_ADDRESS=noreply@tudominio.com

# URL del sitio
CORS_ALLOWED_ORIGINS="https://tudominio.com,https://www.tudominio.com"
```

### Paso 2: Generar APP_KEY

**Si tienes acceso SSH:**
```bash
cd /home/username/private_laravel
php artisan key:generate
```

**Si NO tienes SSH:**
1. Usar generador online de APP_KEY de Laravel
2. Copiar la clave generada al archivo `.env`
3. Formato: `APP_KEY=base64:TuClaveGeneradaAqui`

## 🔧 Configuración de Permisos

### Via File Manager

1. **Permisos para Storage**
   - Navegar a `private_laravel/storage`
   - Seleccionar carpeta `storage`
   - Click derecho → "Permissions"
   - Establecer: `755` (rwxr-xr-x)
   - Marcar "Recurse into subdirectories"
   - Click "Change Permissions"

2. **Permisos para Bootstrap Cache**
   - Navegar a `private_laravel/bootstrap/cache`
   - Establecer permisos `755`

3. **Verificar Permisos de Archivos**
   ```
   Directorios: 755
   Archivos: 644
   Ejecutables (artisan): 755
   ```

### Via SSH (si disponible)

```bash
cd /home/username/private_laravel
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
chmod 755 artisan
```

## 📦 Instalación de Dependencias

### Opción A: Con Composer (SSH requerido)

```bash
cd /home/username/private_laravel
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### Opción B: Sin Composer

1. **Subir vendor/ desde desarrollo local**
   - En tu máquina local, ejecutar `composer install`
   - Comprimir carpeta `vendor/`
   - Subir y extraer en `private_laravel/vendor/`

2. **Optimizar manualmente**
   - Ejecutar comandos básicos vía File Manager
   - O solicitar al hosting que ejecute los comandos

## ⏰ Configuración de Cronjobs

### Paso 1: Acceder a Cron Jobs

1. **En cPanel**
   - Buscar "Cron Jobs"
   - Click en el ícono

### Paso 2: Configurar Tareas

1. **Scheduler Principal (Crítico)**
   ```
   Minuto: *
   Hora: *
   Día: *
   Mes: *
   Día de la semana: *
   Comando: cd /home/username/private_laravel && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **Limpieza de Logs (Opcional)**
   ```
   Minuto: 0
   Hora: 2
   Día: *
   Mes: *
   Día de la semana: *
   Comando: cd /home/username/private_laravel && php artisan log:clear >> /dev/null 2>&1
   ```

3. **Backup Automático (Opcional)**
   ```
   Minuto: 0
   Hora: 3
   Día: *
   Mes: *
   Día de la semana: 0
   Comando: cd /home/username/private_laravel && php artisan backup:run >> /dev/null 2>&1
   ```

## 🔐 Configuración SSL

### Paso 1: Habilitar SSL

1. **Let's Encrypt (Gratuito)**
   - En cPanel, buscar "SSL/TLS"
   - Click "Let's Encrypt"
   - Seleccionar dominio
   - Click "Issue"

2. **Certificado Personalizado**
   - Subir certificado en "SSL/TLS" → "Manage SSL sites"

### Paso 2: Forzar HTTPS

1. **Vía cPanel**
   - En "SSL/TLS", activar "Force HTTPS Redirect"

2. **Vía .htaccess** (ya incluido en nuestro .htaccess)
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

## ✅ Verificación Final

### Paso 1: Probar Acceso

1. **Acceder al sitio**
   - Ir a: `https://tudominio.com`
   - Debe mostrar página de login

2. **Credenciales por defecto**
   ```
   Email: admin@hotel.com
   Password: admin123
   ```

### Paso 2: Verificar Funcionalidades

1. **Dashboard**
   - Verificar que carga correctamente
   - Revisar calendario y estadísticas

2. **Gestión de Habitaciones**
   - Crear/editar habitación de prueba

3. **Gestión de Reservas**
   - Crear reserva de prueba

4. **API Pública**
   - Probar: `https://tudominio.com/api/test-cors`
   - Debe devolver JSON con éxito

### Paso 3: Cambiar Credenciales

1. **Cambiar contraseña admin**
   - Login → Perfil → Cambiar contraseña

2. **Configurar información del hotel**
   - Ir a Configuración → Hotel
   - Actualizar datos

## 🚨 Solución de Problemas Comunes

### Error 500 - Internal Server Error

1. **Verificar logs**
   - cPanel → Error Logs
   - Revisar `/home/username/private_laravel/storage/logs/`

2. **Causas comunes**
   - Permisos incorrectos
   - .env mal configurado
   - APP_KEY no generada
   - Dependencias faltantes

### Error de Base de Datos

1. **Verificar credenciales en .env**
2. **Probar conexión en phpMyAdmin**
3. **Verificar que las tablas existen**

### Archivos CSS/JS no cargan

1. **Verificar que assets fueron subidos**
2. **Revisar permisos de archivos**
3. **Verificar .htaccess**

### Cronjobs no funcionan

1. **Verificar ruta absoluta en comando**
2. **Probar comando manualmente vía SSH**
3. **Revisar logs de cron en cPanel**

## 📞 Soporte Adicional

### Recursos de Ayuda

1. **Documentación Laravel**: https://laravel.com/docs
2. **Soporte del Hosting**: Contactar vía ticket
3. **Logs del Sistema**: Revisar siempre los logs para diagnóstico

### Información para Soporte

Cuando contactes soporte, incluye:
- Versión PHP utilizada
- Error exacto (con logs)
- Pasos que llevaron al error
- Configuración de hosting

---

**Importante**: Después de la instalación, cambia inmediatamente las credenciales por defecto y configura backups regulares.