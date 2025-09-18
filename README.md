# Sistema de Gestión Hotelera

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel" alt="Laravel Version">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php" alt="PHP Version">
    <img src="https://img.shields.io/badge/SQLite-Database-003B57?style=flat&logo=sqlite" alt="Database">
    <img src="https://img.shields.io/badge/AdminLTE-3.x-0073E6?style=flat" alt="AdminLTE">
</p>

## Descripción del Proyecto

Sistema completo de gestión hotelera desarrollado en Laravel 12 que administra operaciones hoteleras incluyendo reservas, habitaciones, control de caja, clientes y mantenimiento. El sistema incluye una interfaz de administración web y una API pública para integración con páginas de aterrizaje.

### Características Principales

- **Gestión de Reservas**: Sistema completo de estados de reserva con expiración automática
- **Control de Habitaciones**: Estados dinámicos (Disponible, Ocupada, Limpieza, Mantenimiento)
- **Control de Caja**: Sistema de turnos con middleware de protección financiera
- **API Pública**: Endpoints para integración con landing pages externas
- **Sistema de Notificaciones**: Alertas automáticas para operaciones críticas
- **Control de Acceso**: Roles y permisos con Spatie Laravel Permission
- **Interfaz Moderna**: AdminLTE con Tailwind CSS y Bootstrap 5

## Requisitos del Sistema

### Software Requerido

- **PHP**: 8.2 o superior
- **Composer**: 2.x para gestión de dependencias PHP
- **Node.js**: 18.x o superior para assets frontend
- **NPM**: Para gestión de dependencias JavaScript
- **SQLite**: Base de datos (incluida por defecto en PHP)

### Dependencias Principales

**Backend (PHP):**
- Laravel Framework 12.x
- Laravel Sanctum (API authentication)
- Laravel AdminLTE (interfaz administrativa)
- Spatie Laravel Permission (roles y permisos)
- Laravel UI (componentes de interfaz)

**Frontend (JavaScript):**
- Vite (build tool)
- Tailwind CSS 4.x
- Bootstrap 5.3.x
- Axios (HTTP client)
- Concurrently (parallel command execution)

## Instalación Paso a Paso

### 1. Clonar el Repositorio

```bash
git clone <repository-url>
cd laravel12_migracion
```

### 2. Instalar Dependencias PHP

```bash
composer install
```

### 3. Instalar Dependencias Node.js

```bash
npm install
```

### 4. Configuración del Entorno

```bash
# Copiar archivo de configuración (si no existe)
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configurar Base de Datos

El proyecto usa SQLite por defecto. El archivo de base de datos se crea automáticamente:

```bash
# Crear archivo de base de datos (si no existe)
touch database/database.sqlite

# Ejecutar migraciones
php artisan migrate

# Cargar datos esenciales
php artisan db:seed --class=EssentialDataSeeder
```

### 6. Configuración Inicial Completa

Para una instalación completa desde cero:

```bash
# Reinstalar base de datos con todos los seeders
php artisan migrate:fresh --seed
```

## Configuración del Entorno (.env)

Asegúrate de configurar las siguientes variables en tu archivo `.env`:

```env
APP_NAME="Sistema de Gestión Hotelera"
APP_ENV=local
APP_KEY=base64:... # Generado automáticamente
APP_DEBUG=true
APP_TIMEZONE=America/Guatemala
APP_URL=http://localhost:8001

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# Para integración con landing page
CORS_ALLOWED_ORIGINS=http://localhost:5500
```

**Importante**: El servidor debe ejecutarse en el puerto 8001 para la correcta integración con landing pages externas.

## Ejecutar el Proyecto

### Comando de Desarrollo Completo (Recomendado)

```bash
composer run dev
```

Este comando ejecuta simultáneamente:
- Servidor Laravel (puerto 8001)
- Worker de colas (queue listener)
- Monitor de logs (Laravel Pail)
- Compilador de assets (Vite)

### Ejecución Manual por Componentes

Si prefieres ejecutar cada servicio por separado:

```bash
# Terminal 1: Servidor Laravel
php artisan serve --port=8001

# Terminal 2: Worker de colas
php artisan queue:listen --tries=1

# Terminal 3: Monitor de logs
php artisan pail --timeout=0

# Terminal 4: Compilador de assets
npm run dev
```

### Acceso al Sistema

- **Aplicación Web**: http://localhost:8001
- **Usuario por defecto**: admin@admin.com
- **Contraseña por defecto**: password

## Comandos de Testing

### Ejecutar Todas las Pruebas

```bash
composer run test
```

Este comando ejecuta:
1. `php artisan config:clear` - Limpia caché de configuración
2. `php artisan test` - Ejecuta todas las pruebas

### Pruebas Específicas por Tipo

```bash
# Pruebas de funcionalidad (Feature Tests)
php artisan test --testsuite=Feature

# Pruebas unitarias (Unit Tests)
php artisan test --testsuite=Unit

# Prueba específica
php artisan test tests/Feature/ExampleTest.php

# Con verbose output
php artisan test --verbose
```

### Cobertura de Código

```bash
# Generar reporte de cobertura (requiere Xdebug)
php artisan test --coverage

# Cobertura mínima
php artisan test --coverage --min=80
```

## Estructura del Proyecto

```
laravel12_migracion/
├── app/
│   ├── Console/Commands/          # Comandos artisan personalizados
│   │   ├── DetectarReservasVencidas.php
│   │   ├── CleanExpiredReservations.php
│   │   └── VerificarCierresCaja.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/               # Controladores de API pública
│   │   │   │   └── ReservaApiController.php
│   │   │   ├── ReservaController.php
│   │   │   ├── CajaController.php
│   │   │   ├── DashboardController.php
│   │   │   └── NotificationController.php
│   │   └── Middleware/
│   │       └── VerificarCajaAbierta.php
│   ├── Models/                    # Modelos Eloquent
│   │   ├── Hotel.php
│   │   ├── Reserva.php
│   │   ├── Habitacion.php
│   │   ├── Cliente.php
│   │   └── Caja.php
│   └── Notifications/             # Sistema de notificaciones
├── database/
│   ├── migrations/                # Migraciones de base de datos
│   ├── seeders/
│   │   └── EssentialDataSeeder.php
│   └── database.sqlite           # Base de datos SQLite
├── resources/
│   ├── views/                    # Vistas Blade
│   │   ├── admin/               # Panel administrativo
│   │   ├── reservas/            # Gestión de reservas
│   │   ├── habitaciones/        # Gestión de habitaciones
│   │   └── cajas/               # Control de caja
│   └── js/                      # Assets JavaScript
├── routes/
│   ├── web.php                  # Rutas web
│   └── api.php                  # Rutas API
└── tests/
    ├── Feature/                 # Pruebas de funcionalidad
    └── Unit/                    # Pruebas unitarias
```

## API Endpoints Principales

### API de Reservas (Público)

```http
GET /api/reservas/disponibilidad
```
Consulta disponibilidad de habitaciones con filtros de fecha.

```http
POST /api/reservas
```
Crea reserva desde landing page (estado: 'Pendiente de Confirmación').

```http
GET /api/reservas/calendario
```
Eventos de calendario para integración FullCalendar.

### API de Clientes (Público)

```http
GET /api/clientes/buscar?q={query}
```
Busca clientes por nombre o teléfono.

```http
GET /api/clientes/buscar-por-dpi/{dpi}
```
Busca cliente por DPI (Guatemala).

```http
GET /api/clientes/buscar-por-nit/{nit}
```
Busca cliente por NIT (Tax ID).

### Endpoints de Prueba

```http
GET /api/test-cors
```
Prueba funcionalidad CORS.

```http
GET /api/test-disponibilidad
```
Prueba sistema de disponibilidad.

## Comandos de Desarrollo

### Gestión de Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate

# Rollback de migraciones
php artisan migrate:rollback

# Recargar base de datos completa
php artisan migrate:fresh --seed

# Ver estado de migraciones
php artisan migrate:status
```

### Comandos Personalizados del Sistema

```bash
# Procesar reservas expiradas
php artisan reservas:expirar

# Limpiar reservas expiradas (configurable por hotel)
php artisan reservations:clean-expired

# Verificar cierres de caja pendientes
php artisan cajas:verificar-cierres

# Arreglar URLs de notificaciones
php artisan notifications:fix-urls
```

### Gestión de Cache

```bash
# Limpiar todos los caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Comandos de Debug

```bash
# Listar todas las rutas
php artisan route:list

# Verificar configuración
php artisan config:show app.url

# Consola interactiva
php artisan tinker

# Ver logs en tiempo real
php artisan pail
```

## Gestión de Assets Frontend

### Desarrollo

```bash
# Compilar assets en modo desarrollo
npm run dev

# Compilar y observar cambios
npm run dev -- --watch
```

### Producción

```bash
# Compilar assets para producción
npm run build
```

## Sistema de Roles y Permisos

El sistema incluye tres roles principales:

- **Administrador**: Acceso completo al sistema
- **Recepcionista**: Gestión de reservas y check-in/check-out
- **Mantenimiento**: Gestión de habitaciones y mantenimiento

Los permisos se manejan automáticamente a través de Spatie Laravel Permission.

## Configuración para Producción

### Scheduler de Laravel

Para que las tareas programadas funcionen en producción, agregar al crontab:

```bash
# Editar crontab
crontab -e

# Agregar línea (cambiar la ruta por la ruta real del proyecto)
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Optimizaciones

```bash
# Optimizar para producción
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

## Troubleshooting Común

### Problemas Frecuentes

**1. URLs de notificaciones devuelven 404**
```bash
php artisan notifications:fix-urls
```

**2. Funciones JavaScript no funcionan**
- Verificar que estén en el scope global (`window.functionName`)
- Comprobar que el evento delegation esté configurado correctamente

**3. Errores de CORS**
```bash
# Verificar configuración CORS
php artisan config:show cors
```

**4. CSS faltante**
- Verificar que existe `public/css/admin_custom.css`
- Ejecutar `npm run dev` para compilar assets

**5. Base de datos bloqueada (SQLite)**
```bash
# Verificar permisos del archivo
chmod 664 database/database.sqlite
chmod 775 database/
```

### Comandos de Diagnóstico

```bash
# Verificar configuración general
php artisan about

# Verificar health de la aplicación
php artisan health

# Comprobar permisos de archivos
ls -la database/
ls -la storage/
```

## Integración con Landing Page

El sistema está configurado para trabajar con landing pages externas:

- **Puerto del servidor**: 8001 (requerido)
- **CORS habilitado**: Para localhost:5500
- **API pública**: Endpoints sin autenticación para reservas
- **Validación diferenciada**: Clientes desde landing page tienen validaciones más permisivas

## Contribuir al Proyecto

1. Fork el repositorio
2. Crear una rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit los cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

### Estándares de Código

- Seguir convenciones de Laravel y PSR
- Usar nombres descriptivos para controladores y modelos
- Montos financieros siempre como decimales con 2 decimales de precisión
- Incluir pruebas para nuevas funcionalidades

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## Soporte

Para reportar bugs o solicitar nuevas funcionalidades, crear un issue en el repositorio del proyecto.

---

**Nota Importante**: Este README incluye toda la información necesaria para ejecutar el proyecto en desarrollo y testing. Para configuraciones específicas de producción, consultar la documentación adicional en el archivo `CLAUDE.md`.