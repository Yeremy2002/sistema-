# Configuración de Cronjobs para cPanel

Este documento explica cómo configurar las tareas programadas (cronjobs) necesarias para el funcionamiento óptimo del Sistema de Gestión Hotelera en cPanel.

## 📋 Tareas Programadas Requeridas

### 1. Scheduler Principal de Laravel (CRÍTICO)

**Propósito**: Ejecuta todas las tareas programadas de Laravel
**Frecuencia**: Cada minuto
**Importancia**: ⭐⭐⭐⭐⭐ (Crítico)

```bash
* * * * * cd /home/username/private_laravel && php artisan schedule:run >> /dev/null 2>&1
```

**Tareas que incluye:**
- Expiración automática de reservas
- Verificación de cierres de caja
- Notificaciones del sistema
- Limpieza de archivos temporales
- Envío de emails pendientes

### 2. Limpieza de Logs (RECOMENDADO)

**Propósito**: Limpia logs antiguos para liberar espacio
**Frecuencia**: Diario a las 2:00 AM
**Importancia**: ⭐⭐⭐⭐ (Recomendado)

```bash
0 2 * * * cd /home/username/private_laravel && php artisan log:clear >> /dev/null 2>&1
```

### 3. Backup Automático (OPCIONAL)

**Propósito**: Crear respaldo de base de datos
**Frecuencia**: Semanal (Domingos a las 3:00 AM)
**Importancia**: ⭐⭐⭐ (Opcional pero recomendado)

```bash
0 3 * * 0 cd /home/username/private_laravel && php artisan backup:run >> /dev/null 2>&1
```

### 4. Verificación de Sistema (OPCIONAL)

**Propósito**: Verifica salud del sistema
**Frecuencia**: Cada 6 horas
**Importancia**: ⭐⭐ (Opcional)

```bash
0 */6 * * * cd /home/username/private_laravel && php artisan system:health-check >> /dev/null 2>&1
```

## 🛠️ Configuración en cPanel

### Paso a Paso

1. **Acceder a Cron Jobs**
   - Login en cPanel
   - Buscar "Cron Jobs" en el panel principal
   - Click en el ícono

2. **Configurar Scheduler Principal**
   ```
   Minuto: *
   Hora: *
   Día del mes: *
   Mes: *
   Día de la semana: *
   Comando: cd /home/username/private_laravel && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Configurar Limpieza de Logs**
   ```
   Minuto: 0
   Hora: 2
   Día del mes: *
   Mes: *
   Día de la semana: *
   Comando: cd /home/username/private_laravel && php artisan log:clear >> /dev/null 2>&1
   ```

4. **Configurar Backup (Opcional)**
   ```
   Minuto: 0
   Hora: 3
   Día del mes: *
   Mes: *
   Día de la semana: 0
   Comando: cd /home/username/private_laravel && php artisan backup:run >> /dev/null 2>&1
   ```

### Formato de Tiempo Cron

```
* * * * * comando
│ │ │ │ │
│ │ │ │ └── Día de la semana (0-7, donde 0=Domingo)
│ │ │ └──── Mes (1-12)
│ │ └────── Día del mes (1-31)
│ └──────── Hora (0-23)
└────────── Minuto (0-59)
```

### Ejemplos de Horarios

```bash
# Cada minuto
* * * * *

# Cada hora en el minuto 0
0 * * * *

# Diario a las 2:30 AM
30 2 * * *

# Cada lunes a las 9:00 AM
0 9 * * 1

# Primer día de cada mes a medianoche
0 0 1 * *

# Cada 15 minutos
*/15 * * * *

# Cada 6 horas
0 */6 * * *
```

## 🔧 Comandos Artisan Disponibles

### Comandos del Sistema Hotelero

```bash
# Expirar reservas pendientes
php artisan reservas:expirar

# Limpiar reservas expiradas
php artisan reservations:clean-expired

# Verificar cierres de caja
php artisan cajas:verificar-cierres

# Arreglar URLs de notificaciones
php artisan notifications:fix-urls

# Limpiar logs del sistema
php artisan log:clear

# Ejecutar scheduler
php artisan schedule:run
```

### Comandos de Laravel

```bash
# Limpiar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Crear symlink de storage
php artisan storage:link
```

## 📊 Monitoreo de Cronjobs

### Verificar Ejecución

1. **Logs de Cron en cPanel**
   - Algunos hostings muestran logs de cron en cPanel
   - Buscar en "Logs" o "Cron Job Logs"

2. **Logs de Laravel**
   ```bash
   # Ver logs recientes
   tail -f /home/username/private_laravel/storage/logs/laravel.log
   ```

3. **Crear Script de Monitoreo**
   ```bash
   # Crear archivo: /home/username/private_laravel/monitor_cron.sh
   #!/bin/bash
   echo "$(date): Cron ejecutado" >> /home/username/cron_monitor.log
   ```

### Comando de Verificación Manual

```bash
# Probar scheduler manualmente
cd /home/username/private_laravel && php artisan schedule:run

# Ver próximas tareas programadas
cd /home/username/private_laravel && php artisan schedule:list
```

## 🚨 Solución de Problemas

### Problemas Comunes

#### 1. Cron no se ejecuta

**Síntomas:**
- Reservas no expiran automáticamente
- Notificaciones no se envían
- Logs no se limpian

**Soluciones:**
```bash
# Verificar ruta PHP
which php

# Usar ruta completa si es necesario
/usr/local/bin/php artisan schedule:run

# Verificar permisos
chmod +x /home/username/private_laravel/artisan
```

#### 2. Error de permisos

**Síntomas:**
- "Permission denied" en logs de cron

**Soluciones:**
```bash
# Establecer permisos correctos
chmod 755 /home/username/private_laravel/artisan
chmod -R 755 /home/username/private_laravel/storage
```

#### 3. Error de memoria

**Síntomas:**
- "Fatal error: Allowed memory size exhausted"

**Soluciones:**
```bash
# Usar límite de memoria específico
php -d memory_limit=512M artisan schedule:run

# O configurar en php.ini
memory_limit = 512M
```

#### 4. Ruta incorrecta

**Síntomas:**
- "No such file or directory"

**Soluciones:**
```bash
# Verificar ruta absoluta
pwd
# Usar ruta completa en cron:
cd /home/username/private_laravel && php artisan schedule:run
```

### Debugging de Cronjobs

#### 1. Crear Log Personalizado

```bash
# Modificar comando cron para logging
* * * * * cd /home/username/private_laravel && php artisan schedule:run >> /home/username/cron.log 2>&1
```

#### 2. Script de Debug

```bash
#!/bin/bash
# Archivo: debug_cron.sh

echo "=== Debug Cron $(date) ===" >> /home/username/debug.log
echo "Usuario: $(whoami)" >> /home/username/debug.log
echo "Directorio: $(pwd)" >> /home/username/debug.log
echo "PHP Version: $(php -v | head -1)" >> /home/username/debug.log

cd /home/username/private_laravel
echo "Laravel Status:" >> /home/username/debug.log
php artisan --version >> /home/username/debug.log 2>&1

echo "Ejecutando scheduler..." >> /home/username/debug.log
php artisan schedule:run >> /home/username/debug.log 2>&1
echo "=== Fin Debug ===" >> /home/username/debug.log
```

## 📅 Configuraciones Específicas por Hosting

### Shared Hosting

```bash
# Algunos shared hosting requieren ruta específica de PHP
/usr/local/bin/php artisan schedule:run

# O usar versión específica
/opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run
```

### VPS/Dedicated

```bash
# Generalmente usan ruta estándar
php artisan schedule:run

# Con systemd
systemctl status crond
```

### Hosting Específicos

#### Hostinger
```bash
/usr/local/bin/php artisan schedule:run
```

#### SiteGround
```bash
/usr/local/bin/php artisan schedule:run
```

#### GoDaddy
```bash
/web/cgi-bin/php artisan schedule:run
```

## 🔄 Migración de Cronjobs

### Desde Otro Servidor

1. **Exportar cronjobs actuales**
   ```bash
   crontab -l > cronjobs_backup.txt
   ```

2. **Adaptar rutas para nuevo servidor**
   ```bash
   # Cambiar rutas en archivo
   sed 's|/old/path|/new/path|g' cronjobs_backup.txt > new_cronjobs.txt
   ```

3. **Importar en cPanel**
   - Copiar comandos del archivo
   - Configurar uno por uno en cPanel

## 📋 Checklist de Configuración

### Verificación Inicial
- [ ] Scheduler principal configurado (cada minuto)
- [ ] Ruta absoluta correcta en comando
- [ ] Permisos correctos en artisan
- [ ] PHP accesible desde cron

### Verificación de Funcionamiento
- [ ] Cron se ejecuta sin errores
- [ ] Logs se generan correctamente
- [ ] Reservas expiran automáticamente
- [ ] Notificaciones se envían

### Optimización
- [ ] Limpieza de logs configurada
- [ ] Backup automático (opcional)
- [ ] Monitoreo de cronjobs activo
- [ ] Logs de debug deshabilitados en producción

## 📞 Contactar Soporte

### Información para Proveedor de Hosting

Cuando contactes soporte del hosting para problemas de cron:

```
Asunto: Configuración de Cronjobs para aplicación Laravel

Detalles:
- Aplicación: Laravel 12
- Comando requerido: php artisan schedule:run
- Frecuencia: Cada minuto
- Ruta: /home/username/private_laravel/
- Versión PHP: 8.2+

Problema específico:
[Describir el problema con logs si es posible]
```

---

**Nota**: Los cronjobs son esenciales para el funcionamiento correcto del sistema. Asegúrate de que el scheduler principal esté siempre activo.