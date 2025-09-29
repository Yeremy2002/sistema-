# 🔧 Guía de Solución - Error 500 en Producción
## Hotel Casa Vieja Management System

---

## 🎯 Resumen del Problema

**Estado Actual:**
- ✅ **Local:** Funcionando correctamente con base de datos `hotel2`
- ❌ **Producción:** Error 500 en `https://casaviejagt.com/test_deploy.php`

---

## 🛠️ Solución Paso a Paso

### Paso 1: Subir Script de Diagnóstico

1. **Sube el archivo `test_deploy.php`** (creado en este proyecto) a la raíz de tu sitio web en cPanel
   - Ubicación en el servidor: `/home/casaviejagt/public_html/test_deploy.php`

2. **Accede al script** desde tu navegador:
   ```
   https://casaviejagt.com/test_deploy.php
   ```

3. **Analiza el resultado** - Este script te dirá exactamente qué está causando el error 500:
   - Versión de PHP
   - Extensiones faltantes
   - Permisos de archivos
   - Conexión a base de datos
   - Estado de Laravel

### Paso 2: Verificar y Corregir Configuración

**A) Archivo .env:**
- Sube el archivo `.env.production` al servidor y renómbralo a `.env`
- Verifica que las credenciales de base de datos sean correctas para tu hosting

**B) Base de Datos en cPanel:**
- Crear la base de datos: `casaviejagt_hotel_management`
- Crear el usuario: `casaviejagt_hoteluser` con contraseña `SalesSystem2025!`
- Asignar **ALL PRIVILEGES** al usuario sobre la base de datos

**C) Versión de PHP:**
- Desde cPanel → **Select PHP Version**
- Cambiar a **PHP 8.2** o superior
- Activar extensiones requeridas si faltan

### Paso 3: Ejecutar Instalación Automática

1. **Sube el archivo `install_production.php`** a la raíz del sitio
2. **Ejecuta la instalación**:
   ```
   https://casaviejagt.com/install_production.php
   ```
3. **Este script automáticamente:**
   - Verifica requisitos
   - Configura variables de entorno
   - Ejecuta migraciones
   - Optimiza Laravel para producción
   - Configura permisos

### Paso 4: Verificar Permisos de Archivos

**Desde cPanel File Manager:**
```bash
# Directorios
storage/ → 775
bootstrap/cache/ → 775
public/ → 755

# Archivos
.env → 600
```

**O desde SSH (si tienes acceso):**
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod 755 public/
chmod 600 .env
```

### Paso 5: Configurar Cron Job

**Desde cPanel → Cron Jobs:**
```bash
* * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📋 Lista de Verificación Post-Instalación

- [ ] `test_deploy.php` muestra todo en verde ✅
- [ ] `https://casaviejagt.com` carga la landing page
- [ ] `https://casaviejagt.com/admin` muestra el login
- [ ] Login funciona con:
  - **Email:** `admin@hotel.com`
  - **Password:** `password`
- [ ] Cambiar contraseña del administrador inmediatamente
- [ ] Cron job configurado y funcionando

---

## 🚨 Problemas Comunes y Soluciones Rápidas

### Error: "PHP version >= 8.2.0 required"
**Solución:** cPanel → Software → Select PHP Version → PHP 8.2+

### Error: "Database connection failed"
**Solución:** Verificar credenciales en cPanel → MySQL Databases

### Error: "Permission denied"
**Solución:** Configurar permisos 775 para `storage/` y `bootstrap/cache/`

### Error: "Class not found"
**Solución:** Ejecutar `composer install --no-dev --optimize-autoloader`

---

## 📞 URLs Importantes

- **Diagnóstico:** https://casaviejagt.com/test_deploy.php
- **Instalación:** https://casaviejagt.com/install_production.php
- **Sistema:** https://casaviejagt.com
- **Admin:** https://casaviejagt.com/admin

---

## 🎯 Archivos Clave para Subir al Servidor

1. `test_deploy.php` - Script de diagnóstico
2. `install_production.php` - Script de instalación automática
3. `.env.production` - Configuración de producción (renombrar a `.env`)
4. Todo el código Laravel (vendor/, app/, etc.)

---

## 🔍 Comando de Diagnóstico Rápido

Si tienes acceso SSH, ejecuta:
```bash
cd /home/casaviejagt/public_html
php -v                          # Verificar versión PHP
php artisan --version          # Verificar Laravel
php artisan migrate:status     # Estado de migraciones
```

---

**🎉 Una vez completado, tu sistema estará funcional en:** https://casaviejagt.com