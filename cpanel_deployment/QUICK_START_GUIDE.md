# 🚀 Guía Rápida - Deployment en cPanel

**Sistema**: Hotel Management System Laravel 12
**Tiempo estimado**: 30-60 minutos
**Nivel**: Intermedio

## 📋 Lista de Verificación Rápida

### ✅ Antes de Empezar
- [ ] Acceso a cPanel
- [ ] PHP 8.2+ disponible
- [ ] MySQL disponible
- [ ] Al menos 1GB de espacio
- [ ] Dominio/subdominio configurado

## 🎯 Pasos Rápidos (30 minutos)

### 1. Preparar Base de Datos (5 min)
```
cPanel → MySQL Databases
├── Crear BD: hotel_management
├── Crear Usuario: hoteluser
├── Asignar permisos: ALL PRIVILEGES
└── Anotar credenciales completas
```

### 2. Subir Archivos (15 min)
```
File Manager → Subir:
├── cpanel_files/public_html/* → /public_html/
└── cpanel_files/private_laravel/* → /private_laravel/
```

### 3. Configurar Variables (5 min)
```
Editar: /private_laravel/.env
├── APP_URL=https://tudominio.com
├── DB_DATABASE=username_hotel_management
├── DB_USERNAME=username_hoteluser
├── DB_PASSWORD=tu_password
└── MAIL_HOST=mail.tudominio.com
```

### 4. Importar Base de Datos (3 min)
```
cPanel → phpMyAdmin
├── Seleccionar BD creada
└── Importar: database/hotel_management.sql
```

### 5. Configurar Permisos (2 min)
```
File Manager → Permisos:
├── private_laravel/storage/ → 755 (recursivo)
├── private_laravel/bootstrap/cache/ → 755 (recursivo)
└── private_laravel/artisan → 755
```

## 🔧 Configuración Avanzada (15 min)

### 6. Generar APP_KEY
```bash
# Via SSH (si disponible):
cd /home/username/private_laravel
php artisan key:generate

# Sin SSH: usar generador online Laravel
```

### 7. Configurar Cronjobs
```
cPanel → Cron Jobs → Agregar:
* * * * * cd /home/username/private_laravel && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Habilitar SSL
```
cPanel → SSL/TLS → Let's Encrypt → Activar
```

## ✅ Verificación (5 min)

### Probar Funcionamiento
1. **Sitio Web**: https://tudominio.com
2. **Login**: admin@hotel.com / admin123
3. **API**: https://tudominio.com/api/test-cors
4. **Sistema**: https://tudominio.com/system-check.php?token=hotel_check_2024

### Checklist Final
- [ ] Sitio carga correctamente
- [ ] Login funciona
- [ ] Dashboard se muestra
- [ ] No hay errores 500
- [ ] SSL activo (🔒)

## 🚨 Solución Rápida de Problemas

### Error 500
```bash
# Verificar logs:
cPanel → Error Logs

# Verificar permisos:
storage/ → 755
.env → existe y configurado
```

### No carga CSS/JS
```bash
# Verificar:
public_html/build/ → existe
public_html/.htaccess → existe
```

### Error Base de Datos
```bash
# Verificar en .env:
DB_HOST=localhost
DB_DATABASE=username_hotel_management (nombre completo)
DB_USERNAME=username_hoteluser (usuario completo)
```

## 📞 Soporte Rápido

### Si algo no funciona:
1. **Revisar logs**: cPanel → Error Logs
2. **Verificar documentación**: `docs/TROUBLESHOOTING.md`
3. **Verificar permisos**: Todos los archivos/carpetas
4. **Contactar hosting**: Para problemas de PHP/MySQL

### URLs Útiles Post-Deployment:
- **Sistema**: https://tudominio.com
- **Admin**: https://tudominio.com/login
- **API Test**: https://tudominio.com/api/test-cors
- **Health Check**: https://tudominio.com/system-check.php?token=hotel_check_2024

---

## 🎉 ¡Listo!

Una vez completados estos pasos, tu sistema de gestión hotelera estará funcionando en cPanel.

**Credenciales por defecto:**
- Email: admin@hotel.com
- Password: admin123

**⚠️ IMPORTANTE**: Cambia las credenciales inmediatamente después del primer login.

---

### 📚 Documentación Completa
Para pasos detallados, consulta:
- `README_CPANEL_DEPLOYMENT.md` - Guía completa
- `docs/INSTALLATION_STEPS.md` - Pasos detallados
- `docs/CRONJOBS_SETUP.md` - Configuración de tareas
- `docs/TROUBLESHOOTING.md` - Solución de problemas