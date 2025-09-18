# Guía de Deployment en cPanel - Sistema de Gestión Hotelera

Este documento proporciona instrucciones paso a paso para deployar el sistema de gestión hotelera Laravel 12 en un hosting cPanel.

## 📋 Preparación Previa

### Requisitos del Hosting
- PHP 8.2 o superior
- MySQL 5.7 o superior
- Soporte para Laravel (Composer disponible)
- Al menos 512MB de memoria PHP
- Extensiones PHP: PDO, mbstring, OpenSSL, BCMath, Ctype, Fileinfo, JSON, Tokenizer, XML

### Archivos Incluidos
```
cpanel_deployment/
├── README_CPANEL_DEPLOYMENT.md     # Esta guía
├── prepare_for_cpanel.sh           # Script de preparación
├── cpanel_files/                   # Archivos organizados para cPanel
│   ├── public_html/               # Contenido público
│   └── private_laravel/           # Aplicación Laravel
├── database/
│   ├── hotel_management.sql       # Script SQL completo
│   └── create_database.sql        # Script para crear BD
├── config/
│   ├── .env.cpanel                # Configuración para cPanel
│   ├── .htaccess.public_html      # .htaccess para public_html
│   └── index.php.cpanel           # index.php modificado
└── docs/
    ├── INSTALLATION_STEPS.md      # Pasos detallados
    ├── CRONJOBS_SETUP.md          # Configuración de cronjobs
    └── TROUBLESHOOTING.md         # Solución de problemas
```

## 🚀 Proceso de Deployment

### Paso 1: Preparar Archivos Localmente

1. Ejecutar el script de preparación:
```bash
cd /path/to/your/project
chmod +x cpanel_deployment/prepare_for_cpanel.sh
./cpanel_deployment/prepare_for_cpanel.sh
```

### Paso 2: Crear Base de Datos en cPanel

1. Acceder a cPanel → MySQL Databases
2. Crear nueva base de datos: `hotel_management`
3. Crear usuario con permisos completos
4. Anotar: nombre_bd, usuario, contraseña, host

### Paso 3: Subir Archivos

#### Opción A: File Manager de cPanel
1. **Contenido público:**
   - Ir a File Manager → public_html
   - Subir todo el contenido de `cpanel_files/public_html/`

2. **Aplicación Laravel:**
   - Crear carpeta en raíz: `private_laravel`
   - Subir todo el contenido de `cpanel_files/private_laravel/`

#### Opción B: FTP
```bash
# Subir contenido público
scp -r cpanel_files/public_html/* user@yourhost:public_html/

# Subir aplicación Laravel
scp -r cpanel_files/private_laravel/* user@yourhost:private_laravel/
```

### Paso 4: Configurar Base de Datos

1. Acceder a phpMyAdmin desde cPanel
2. Seleccionar base de datos creada
3. Importar archivo: `database/hotel_management.sql`

### Paso 5: Configurar Variables de Entorno

1. Editar archivo `.env` en `private_laravel/`
2. Configurar credenciales de base de datos
3. Configurar URL del sitio
4. Generar nueva APP_KEY

### Paso 6: Configurar Permisos

```bash
# En terminal SSH (si disponible) o via File Manager
chmod -R 755 private_laravel/
chmod -R 777 private_laravel/storage/
chmod -R 777 private_laravel/bootstrap/cache/
```

### Paso 7: Instalar Dependencias

Si tienes acceso SSH:
```bash
cd private_laravel
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si NO tienes SSH, usar Alternative Package Installer (incluido).

### Paso 8: Configurar Cronjobs

En cPanel → Cron Jobs, agregar:
```bash
# Scheduler principal (cada minuto)
* * * * * cd /home/username/private_laravel && php artisan schedule:run >> /dev/null 2>&1

# Limpieza de logs (diario a las 2 AM)
0 2 * * * cd /home/username/private_laravel && php artisan log:clear >> /dev/null 2>&1
```

### Paso 9: Configurar SSL (Recomendado)

1. En cPanel → SSL/TLS
2. Configurar Let's Encrypt o certificado personalizado
3. Forzar HTTPS en .htaccess

### Paso 10: Testing

1. Acceder a tu dominio
2. Probar login con credenciales por defecto:
   - Email: admin@hotel.com
   - Password: admin123

## 🔧 Configuraciones Específicas para cPanel

### Estructura de Directorios
```
/home/username/
├── public_html/                    # Contenido web público
│   ├── index.php                  # Punto de entrada Laravel
│   ├── .htaccess                  # Reglas de rewrite
│   ├── assets/                    # CSS, JS, imágenes compiladas
│   └── storage -> ../private_laravel/storage/app/public
├── private_laravel/                # Aplicación Laravel (fuera de public_html)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
└── logs/                          # Logs del sistema
```

### Variables de Entorno Críticas

```env
# Actualizar estos valores específicamente
APP_URL=https://tudominio.com
DB_HOST=localhost
DB_DATABASE=username_hotel_management
DB_USERNAME=username_hoteluser
DB_PASSWORD=tu_password_segura

# Rutas específicas para cPanel
STORAGE_PATH=/home/username/private_laravel/storage
```

## 📚 Documentación Adicional

- **INSTALLATION_STEPS.md**: Pasos detallados con capturas
- **CRONJOBS_SETUP.md**: Configuración de tareas programadas
- **TROUBLESHOOTING.md**: Solución de problemas comunes

## 🛡️ Seguridad

- Aplicación Laravel fuera de public_html
- Archivos .env protegidos
- Directorios storage y cache con permisos correctos
- SSL configurado y forzado
- Credenciales por defecto deben cambiarse inmediatamente

## 📞 Soporte

Si encuentras problemas durante el deployment:

1. Revisar logs en `private_laravel/storage/logs/`
2. Verificar configuración de PHP en cPanel
3. Consultar documentación de troubleshooting
4. Contactar soporte del hosting para requisitos específicos

## ✅ Checklist Post-Deployment

- [ ] Sitio web accesible
- [ ] Login funcionando
- [ ] Base de datos poblada
- [ ] Cronjobs configurados
- [ ] SSL activo
- [ ] Credenciales cambiadas
- [ ] Backup inicial creado
- [ ] Monitoreo configurado

---

**Última actualización**: Septiembre 2025
**Versión Laravel**: 12.x
**Tipo de Hosting**: cPanel Shared/VPS