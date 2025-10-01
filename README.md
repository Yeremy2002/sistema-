# 🏨 Hotel Management System

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.x-0073E6?style=for-the-badge)
![API](https://img.shields.io/badge/API-REST-25A162?style=for-the-badge)
![Laragon](https://img.shields.io/badge/Laragon-Ready-00C9FF?style=for-the-badge)

**🎯 Sistema Integral de Gestión Hotelera**  
*Plataforma completa para automatizar operaciones hoteleras con API pública integrada*

[🚀 Instalación](#-instalación-con-laragon) • [📖 Guía de Desarrollo](#-desarrollo) • [🔧 Configuración](#-configuración) • [📚 API](#-api-endpoints)

</div>


---

## 📑 Tabla de Contenidos

1. [🎯 Características Principales](#-características-principales)
2. [🛠️ Requisitos del Sistema](#-requisitos-del-sistema)
3. [🚀 Instalación con Laragon](#-instalación-con-laragon)
4. [⚙️ Configuración](#-configuración)
5. [🏗️ Estructura del Proyecto](#-estructura-del-proyecto)
6. [📚 API Endpoints](#-api-endpoints)
7. [💻 Desarrollo](#-desarrollo)
8. [🧪 Testing](#-testing)
9. [🚦 Deployments](#-deployments)
10. [🤝 Contribución](#-contribución)

---

## 🎯 Características Principales

### ✨ **Funcionalidades Core**
- 🏠 **Gestión de Habitaciones**: Control completo de disponibilidad y estados
- 📋 **Sistema de Reservas**: Proceso completo desde solicitud hasta check-out
- 👥 **Gestión de Clientes**: Base de datos unificada con historial completo
- 💰 **Control de Caja**: Sistema de turnos con arqueos y reportes financieros
- 🌐 **API Pública REST**: Endpoints para integración con landing pages externas
- 📊 **Dashboard en Tiempo Real**: Métricas y KPIs actualizados instantáneamente
- 🔄 **Automatización**: Expiración automática de reservas no confirmadas
- 📱 **Responsive Design**: Interfaz optimizada para escritorio y móviles

### 🎨 **Tecnologías Implementadas**
- **Laravel 12** con arquitectura MVC moderna
- **AdminLTE 3** para interfaz administrativa
- **Alpine.js** para interactividad frontend
- **Tailwind CSS 4** con Bootstrap 5 para estilos
- **MySQL/PostgreSQL** con soporte SQLite para desarrollo
- **Redis** para cache y sesiones
- **Vite 6** para build system moderno

### 🔌 **Integraciones**
- Landing page con reservas directas
- Sistema CORS configurado para multi-dominio
- Notificaciones en tiempo real
- Backup automático de base de datos
- Logs estructurados con Laravel Pail

---

## 🛠️ Requisitos del Sistema

### **📋 Requisitos Obligatorios**

| Componente | Versión Mínima | Recomendado | Notas |
|------------|----------------|-------------|-------|
| **Windows** | Windows 10 | Windows 11 | Para Laragon |
| **Laragon** | 6.0+ | Laragon Full | Incluye todas las dependencias |
| **PHP** | 8.2 | 8.3+ | Incluido en Laragon |
| **MySQL** | 8.0 | 8.4+ | Incluido en Laragon |
| **Composer** | 2.x | Latest | Incluido en Laragon |
| **Node.js** | 18.x | 20.x+ | Para compilar assets |
| **npm** | 9.x | 10.x+ | Para gestión de dependencias JS |

### **⚡ Extensiones PHP Necesarias**
> 🟢 **Todas incluidas en Laragon por defecto**
- `ext-bcmath` - Operaciones matemáticas de precisión
- `ext-ctype` - Funciones de tipo de carácter
- `ext-fileinfo` - Información de archivos
- `ext-json` - Manipulación JSON
- `ext-mbstring` - Manejo de strings multibyte
- `ext-openssl` - Funciones criptográficas
- `ext-pdo` - PHP Data Objects
- `ext-tokenizer` - Tokenizador PHP
- `ext-xml` - Parser XML

### **💾 Requisitos de Hardware**
- **RAM**: 4GB mínimo, 8GB recomendado
- **Almacenamiento**: 2GB libres para el proyecto
- **Procesador**: x64 compatible

---

## 🚀 Instalación con Laragon

### **📦 Paso 1: Preparar el Entorno**

#### **1.1 Descargar e Instalar Laragon**
```powershell
# Descargar Laragon Full desde:
# https://laragon.org/download/
# Elegir "Laragon Full" que incluye PHP 8.2+, MySQL, Apache, etc.
```

#### **1.2 Configurar Laragon**
1. **Ejecutar Laragon como Administrador**
2. **Verificar configuración**:
   - Click en **"Menú"** → **"PHP"** → Verificar que sea **PHP 8.2+**
   - Click en **"Menú"** → **"Apache"** → Verificar que esté activo
   - Click en **"Menú"** → **"MySQL"** → Verificar que esté activo

#### **1.3 Instalar Node.js (si no está instalado)**
```powershell
# Descargar desde: https://nodejs.org/
# Instalar la versión LTS (20.x)
# Verificar instalación:
node --version
npm --version
```

### **📥 Paso 2: Clonar el Repositorio**

#### **2.1 Ubicar Carpeta de Laragon**
```powershell
# Navegar a la carpeta de proyectos de Laragon
# Por defecto: C:\laragon\www
cd C:\laragon\www
```

#### **2.2 Clonar Proyecto**
```powershell
# Clonar el repositorio
git clone https://github.com/Yeremy2002/sistema-.git hotel-management
cd hotel-management
```

### **⚙️ Paso 3: Configuración Inicial**

#### **3.1 Instalar Dependencias PHP**
```powershell
# Instalar paquetes de Composer
composer install

# Si encuentras problemas de memoria:
composer install --no-dev --optimize-autoloader
```

#### **3.2 Configurar Variables de Entorno**
```powershell
# Copiar archivo de configuración
copy .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

#### **3.3 Editar Configuración de Base de Datos**
Abrir `.env` y configurar:

```env
# Configuración para Laragon
APP_NAME="Hotel Management System"
APP_ENV=local
APP_KEY=base64:TU_CLAVE_GENERADA_AUTOMATICAMENTE
APP_DEBUG=true
APP_URL=http://hotel-management.test

# Base de datos MySQL (Laragon)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_management
DB_USERNAME=root
DB_PASSWORD=

# Configuración de desarrollo
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Configuración de correo (opcional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@hotel-management.test"
MAIL_FROM_NAME="${APP_NAME}"

# Configuración específica del hotel
HOTEL_DEFAULT_CURRENCY=Q
HOTEL_DEFAULT_TIMEZONE=America/Guatemala
HOTEL_CHECKIN_TIME=14:00
HOTEL_CHECKOUT_TIME=12:00
RESERVA_EXPIRATION_MINUTES=240
```

### **🗄️ Paso 4: Configurar Base de Datos**

#### **4.1 Crear Base de Datos en Laragon**
1. **Abrir Laragon** → Click en **"Database"** → **"Open"**
2. **En phpMyAdmin:**
   - Click en **"Nueva"** en el panel izquierdo
   - Nombre: `hotel_management`
   - Codificación: `utf8mb4_unicode_ci`
   - Click **"Crear"**

#### **4.2 Ejecutar Migraciones**
```powershell
# Ejecutar migraciones y seeders
php artisan migrate --seed

# Si hay problemas, forzar recreación:
php artisan migrate:fresh --seed
```

### **🎨 Paso 5: Configurar Frontend**

#### **5.1 Instalar Dependencias Node.js**
```powershell
# Instalar paquetes de npm
npm install

# Si hay conflictos, forzar instalación:
npm install --force
```

#### **5.2 Compilar Assets**
```powershell
# Para desarrollo (con watch mode):
npm run dev

# O compilar una sola vez:
npm run build
```

### **🌐 Paso 6: Configurar Host Virtual en Laragon**

#### **6.1 Método Automático (Recomendado)**
1. **En Laragon**: Click derecho en el proyecto → **"Quick add"** → **"hotel-management.test"**
2. **Laragon configurará automáticamente** el virtual host

#### **6.2 Método Manual (si el automático falla)**
1. **Click en Laragon** → **"Apache"** → **"sites-enabled"**
2. **Crear archivo**: `hotel-management.test.conf`
```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/hotel-management/public"
    ServerName hotel-management.test
    ServerAlias *.hotel-management.test
    <Directory "C:/laragon/www/hotel-management/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### **🚀 Paso 7: Iniciar Servicios y Verificar Instalación**

#### **7.1 Iniciar Servicios de Laragon**
```powershell
# En Laragon, hacer click en "Start All"
# Verificar que Apache y MySQL estén corriendo (íconos verdes)
```

#### **7.2 Verificar Instalación**
1. **Abrir navegador** → `http://hotel-management.test`
2. **Deberías ver** la pantalla de login del sistema
3. **Credenciales por defecto**:
   - **Usuario**: `admin@hotel.com`
   - **Password**: `password`

#### **7.3 Verificar API**
```powershell
# Probar endpoint de API
curl http://hotel-management.test/api/test-cors
# Debería retornar: {"message":"CORS test successful","timestamp":"..."}
```

### **✅ Paso 8: Configuración Post-Instalación**

#### **8.1 Configurar Scheduler (Opcional)**
Para tareas automáticas como limpiar reservas expiradas:
```powershell
# Agregar al Task Scheduler de Windows:
# Programa: C:\laragon\bin\php\php-8.2-Win32-vs16-x64\php.exe
# Argumentos: C:\laragon\www\hotel-management\artisan schedule:run
# Frecuencia: Cada minuto
```

#### **8.2 Optimizar para Desarrollo**
```powershell
# Limpiar caches de desarrollo
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Establecer permisos de storage
php artisan storage:link
```

---

## ⚙️ Configuración

### **🔧 Variables de Entorno Importantes**

#### **Configuración de Aplicación**
```env
APP_NAME="Hotel Management System"          # Nombre mostrado en la interfaz
APP_ENV=local                              # Entorno: local, staging, production
APP_DEBUG=true                             # Mostrar errores detallados (solo desarrollo)
APP_URL=http://hotel-management.test       # URL base de la aplicación
APP_TIMEZONE=America/Guatemala             # Zona horaria del hotel
```

#### **Configuración de Base de Datos**
```env
DB_CONNECTION=mysql                        # Tipo: mysql, pgsql, sqlite
DB_HOST=127.0.0.1                         # Host de la base de datos
DB_PORT=3306                              # Puerto MySQL
DB_DATABASE=hotel_management              # Nombre de la base de datos
DB_USERNAME=root                          # Usuario de base de datos
DB_PASSWORD=                              # Contraseña (vacía en Laragon)
```

#### **Configuración del Hotel**
```env
HOTEL_DEFAULT_CURRENCY=Q                  # Moneda: Q (Quetzal), $ (Dólar), € (Euro)
HOTEL_DEFAULT_TIMEZONE=America/Guatemala  # Zona horaria para fechas
HOTEL_CHECKIN_TIME=14:00                 # Hora de check-in
HOTEL_CHECKOUT_TIME=12:00                # Hora de check-out
RESERVA_EXPIRATION_MINUTES=240           # Minutos para expirar reservas (4 horas)
```

#### **Configuración CORS (Para Landing Pages)**
```env
CORS_ALLOWED_ORIGINS="http://hotel-management.test,http://localhost:3000,https://mi-landing-page.com"
```

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

**Nota Importante**: Este README incluye toda la información necesaria para ejecutar el proyecto en desarrollo y testing. Para configuraciones específicas de producción, consultar la documentación adicional incluida en el repositorio.
