# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12-based hotel management system ("Sistema de Gestión Hotelera") that manages hotel operations including reservations, rooms, cash management, clients, and maintenance. The system supports both web-based administration and a public API for landing page integration.

## Key Development Commands

### Development Server
```bash
# Start development server with all services
composer run dev
# This runs: server (port 8001), queue worker, logs, and Vite simultaneously

# Alternative manual setup
php artisan serve --port=8001
npm run dev
php artisan queue:listen --tries=1
```

### Testing
```bash
# Run all tests
composer run test
# This runs: config:clear and phpunit

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Seed database (includes essential data)
php artisan db:seed --class=EssentialDataSeeder

# Fresh install with seeders
php artisan migrate:fresh --seed
```

### Caching and Optimization
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Background Tasks
```bash
# Process expired reservations (runs every 5 minutes via scheduler)
php artisan reservas:expirar

# Clean expired reservations (frequency configurable per hotel)
php artisan reservations:clean-expired

# Verify cash register closures (runs every 30 minutes)
php artisan cajas:verificar-cierres

# Fix notification URLs if needed
php artisan notifications:fix-urls

# In production, add to crontab:
# * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Architecture Overview

### Core Models and Relationships
- **Hotel**: Central configuration model (currency, checkout times, notification settings)
- **Reserva**: Main booking model with states: 'Pendiente de Confirmación', 'Pendiente', 'Check-in', 'Check-out', 'Cancelada'
- **Habitacion**: Room model with states: 'Disponible', 'Reservada-Pendiente', 'Reservada-Confirmada', 'Ocupada', 'Limpieza', 'Mantenimiento'
- **Cliente**: Customer model with origin tracking ('landing' vs 'backend')
- **Caja**: Cash register model for financial control
- **User**: System users with role-based permissions

### Key Controllers
- **ReservaController**: Main reservation management (web interface)
- **ReservaApiController**: Public API for landing page integration
- **CajaController**: Cash register management with middleware protection
- **DashboardController**: Main administrative dashboard

### Middleware
- **VerificarCajaAbierta**: Ensures cash register is open before financial operations
  - Automatically detects financial parameters and prevents operations without open cash register
  - Handles turno (shift) management: 'diurno' (6 AM - 6 PM) and 'nocturno' (6 PM - 6 AM)
  - Sends notifications for pending closures and shift changes
  - Blocks operations for reservations, check-ins/outs, and maintenance with financial implications

## Reservation System

### Reservation States Flow
1. **'Pendiente de Confirmación'**: Initial state from landing page (expires automatically)
2. **'Pendiente'**: Confirmed by receptionist (ready for check-in)
3. **'Check-in'**: Guest is in the hotel
4. **'Check-out'**: Completed stay
5. **'Cancelada'**: Cancelled or expired reservation

### Important Business Rules
- Reservations from landing page expire after configured time (default: 240 minutes)
- Financial operations require an open cash register
- Room availability considers all overlapping reservations in active states
- Check-out is restricted to configured hours (except for administrators)

## API Endpoints (Public)

### Reservation API
- `GET /api/reservas/disponibilidad` - Check room availability with date filters
- `POST /api/reservas` - Create reservation from landing page (creates in 'Pendiente de Confirmación' state)
- `GET /api/reservas/calendario` - Calendar events for full calendar integration

### Client API  
- `GET /api/clientes/buscar` - Search clients by name or phone
- `GET /api/clientes/buscar-por-dpi/{dpi}` - Search by DPI (Guatemala ID)
- `GET /api/clientes/buscar-por-nit/{nit}` - Search by NIT (Tax ID)

### Test Endpoints
- `GET /api/test-cors` - CORS functionality test
- `GET /api/test-disponibilidad` - Availability system test

## Landing Page Integration

The system supports external landing pages through a public API:

### Integration Architecture
- **Separate frontend**: Landing page runs independently (typically on localhost:5500)
- **CORS enabled**: Configured to allow cross-origin requests from landing page
- **Client origin tracking**: Clients marked with 'landing' origin for different validation rules
- **Reservation flow**: Landing page creates reservations that require manual confirmation
- **Real-time availability**: API provides up-to-date room availability for booking calendar

## Database Configuration

The system uses SQLite by default (`database/database.sqlite`). Key configuration:
- Currency symbol stored in `hotels.simbolo_moneda` (default: 'Q.')
- Reservation expiration time in `hotels.reserva_tiempo_expiracion` (minutes, default: 240)
- Check-in/check-out times configurable per hotel in `hotels` table
- Scheduler frequency configurable in `hotels.scheduler_frecuencia` (5m, 10m, 15m, 30m, 1h, 2h, 4h, 6h, 12h, 24h)
- Notification settings in `hotels` table for various system alerts

## Frontend Architecture

### Views Structure
- **AdminLTE** theme integration via `jeroennoten/laravel-adminlte`
- Custom CSS in `public/css/admin_custom.css` with hotel-specific styles
- JavaScript uses event delegation for dynamic content
- SweetAlert2 for user confirmations and alerts

### Asset Building
- **Vite** for asset compilation
- **Tailwind CSS** and **Bootstrap 5** integration
- SASS processing for stylesheets

## Notification System

The system includes a comprehensive notification framework for operational alerts:

### Notification Types
- **RecordatorioCierreCaja**: Cash register closure reminders
- **ReservaPendienteNotification**: Pending reservations alerts  
- **LimpiezaMantenimientoNotification**: Maintenance and cleaning alerts

### Notification Triggers
- Cash register open beyond shift hours (automatic)
- Cash registers from previous days not closed (urgent)
- Reservations requiring confirmation from landing page
- Rooms needing maintenance or cleaning attention

### Notification Management
- Real-time notification count in dashboard header
- Notification panel accessible from all authenticated pages
- Mark individual or all notifications as read
- Automatic URL fixing command for broken notification links

## Security Considerations

### Authentication & Authorization
- **Spatie Laravel Permission** for role-based access control
- Roles: Administrador, Recepcionista, Mantenimiento
- CSRF protection on all forms
- Session-based authentication with dashboard redirect

### Financial Controls
- All financial operations require open cash register
- Audit trails for administrative actions outside normal hours
- Client data validation differs between landing page (permissive) and backend (strict)

## Important File Locations

### Core Application Files
- `app/Http/Controllers/ReservaController.php` - Main reservation logic
- `app/Http/Controllers/Api/ReservaApiController.php` - Public API
- `app/Http/Controllers/CajaController.php` - Cash register management
- `app/Http/Controllers/DashboardController.php` - Main dashboard with calendar
- `app/Http/Controllers/NotificationController.php` - System notifications
- `app/Http/Middleware/VerificarCajaAbierta.php` - Financial operation middleware

### Configuration
- `.env` - Environment configuration (ensure APP_URL includes port :8001 for development)
- `config/cors.php` - CORS settings for API access (allows localhost:5500 for landing page)
- `database/seeders/EssentialDataSeeder.php` - Required initial data (admin user, rooms, categories)
- `app/Console/Kernel.php` - Task scheduler configuration with hotel-specific frequencies

### Custom Commands
- `app/Console/Commands/DetectarReservasVencidas.php` - Expire reservations (reservas:expirar)
- `app/Console/Commands/CleanExpiredReservations.php` - Clean expired reservations (reservations:clean-expired)
- `app/Console/Commands/VerificarCierresCaja.php` - Verify cash register closures (cajas:verificar-cierres)  
- `app/Console/Commands/FixNotificationUrls.php` - Fix notification URLs (notifications:fix-urls)
- `app/Console/Commands/ExpirarReservasPendientes.php` - Process pending reservations

## Development Guidelines

### Code Style
- Follow Laravel conventions and PSR standards
- Use consistent naming: models in singular, controllers with descriptive names
- Financial amounts always handled as decimals with 2 precision
- Currency symbol retrieved from `$hotel->simbolo_moneda` (available in all views)

### Testing Approach
- Feature tests for reservation flows and API endpoints
- Test financial operations with cash register requirements
- Verify role-based access controls

### Common Pitfalls
- Always check if cash register is open before financial operations  
- Use event delegation for JavaScript on dynamic content
- Ensure CSRF tokens are included in AJAX requests
- Validate reservation date overlaps carefully
- Remember that landing page and backend have different validation rules
- Development server must run on port 8001 for proper integration with landing page
- Notification system requires proper URL configuration - use notifications:fix-urls if needed
- Cash register turno (shift) management is timezone-aware (America/Guatemala)

## Troubleshooting

### Common Issues
- **Notification URLs returning 404**: Run `php artisan notifications:fix-urls`
- **JavaScript functions not working**: Check if they're in global scope (`window.functionName`)
- **CORS errors**: Verify `config/cors.php` and middleware setup
- **Missing CSS**: Ensure `public/css/admin_custom.css` exists

### Debug Commands
- `php artisan route:list` - Check all registered routes
- `php artisan config:show app.url` - Verify URL configuration
- `php artisan tinker` - Interactive debugging console