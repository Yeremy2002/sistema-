# 🚀 Opciones de Setup para Hotel Casa Vieja Management System

## 📦 **Nuevo Paquete Actualizado Disponible**

**Archivo:** `hotel_casavieja_production_20250927_234427.tar.gz` (85.5 MB)
**Instrucciones:** `hotel_casavieja_production_20250927_234427_INSTALLATION.txt`

### ✅ **Incluye las Mejoras Más Recientes:**
- ✅ Script de setup mejorado (cpanel_setup_v2.php)
- ✅ Guía completa de troubleshooting
- ✅ Documentación actualizada con requisitos de PHP 8.2+
- ✅ Scripts de diagnóstico automático
- ✅ Manejo mejorado de errores

---

## 🛠️ **Opciones de Instalación Disponibles**

### **Opción 1: Script Mejorado (RECOMENDADO)**
```
https://casaviejagt.com/deploy/cpanel_setup_v2.php
```

**✅ Ventajas del Script v2.0:**
- Verifica automáticamente PHP 8.2+
- Interfaz visual con progress bar
- Manejo inteligente de errores y warnings
- Diagnóstico completo del ambiente
- Instalación paso a paso con validación
- Resumen final con next steps
- Mejor experiencia de usuario

### **Opción 2: Script Original (Básico)**
```
https://casaviejagt.com/deploy/cpanel_setup.php
```

### **Opción 3: Instalación Manual**
Si los scripts automatizados fallan, puedes seguir los pasos manuales:

1. **Verificar PHP 8.2+**
2. **Ejecutar comandos Laravel manualmente:**
```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=EssentialDataSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 **Scripts de Diagnóstico (Incluidos en el Paquete)**

### **Script de Diagnóstico Rápido**
Crea `diagnostic.php` en tu raíz de cPanel y copia el código de `CPANEL_TROUBLESHOOTING_GUIDE.md`

### **Script de Reparación Automática**
Crea `repair.php` con el código de la guía de troubleshooting

---

## 📋 **Pasos de Instalación Actualizados**

### **1. Requisitos Previos (CRÍTICO)**
- **PHP 8.2+** configurado en cPanel
- Base de datos MySQL creada
- Credenciales de DB verificadas

### **2. Subir Nuevo Paquete**
- Sube: `hotel_casavieja_production_20250927_234427.tar.gz`
- Extrae en `public_html`
- Mueve archivos al directorio raíz

### **3. Ejecutar Setup Mejorado**
- Visita: `https://casaviejagt.com/deploy/cpanel_setup_v2.php`
- Sigue las instrucciones paso a paso
- El script te guiará automáticamente

### **4. Post-Instalación**
- Configura cron job
- Cambia contraseña de admin
- Activa SSL
- Configura datos del hotel

---

## 🆘 **En Caso de Problemas**

### **Si el Script Falla:**
1. Revisa los **Error Logs** en cPanel
2. Verifica que PHP sea 8.2+
3. Usa el script de diagnóstico
4. Consulta `CPANEL_TROUBLESHOOTING_GUIDE.md`

### **Problemas Más Comunes:**
- **PHP < 8.2:** Cambiar versión en cPanel
- **Permisos:** 775 para storage, 600 para .env
- **Base de Datos:** Verificar credenciales y conexión
- **Composer:** Instalar dependencias manualmente

---

## 📞 **URLs de Acceso Post-Instalación**

### **Administración:**
```
https://casaviejagt.com/admin
Usuario: admin@hotel.com
Contraseña: password (CAMBIAR INMEDIATAMENTE)
```

### **Landing Page:**
```
https://casaviejagt.com/
```

### **API:**
```
https://casaviejagt.com/api/
```

---

## 📁 **Archivos Clave en el Paquete**

```
deploy/
├── cpanel_setup_v2.php         ← NUEVO SCRIPT MEJORADO
├── cpanel_setup.php            ← Script original
├── DEPLOYMENT_INSTRUCTIONS.md  ← Instrucciones actualizadas
├── database_backup.php         ← Backup y restauración
├── file_permissions.sh         ← Configurar permisos
└── optimize_production.php     ← Optimización Laravel

CPANEL_TROUBLESHOOTING_GUIDE.md ← GUÍA DE PROBLEMAS
```

---

## 🎯 **Recomendación**

**Para la mejor experiencia:**
1. Usa el **nuevo paquete:** `hotel_casavieja_production_20250927_234427.tar.gz`
2. Ejecuta el **script v2.0:** `cpanel_setup_v2.php`
3. Ten a mano la **guía de troubleshooting** para cualquier problema

**¡El sistema está completamente actualizado y listo para deployment exitoso!** 🏨✨