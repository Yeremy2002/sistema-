# Hotel Management System - Security Guide

This document outlines security best practices, configurations, and procedures for the Hotel Management System.

## Table of Contents

1. [Security Overview](#security-overview)
2. [Application Security](#application-security)
3. [Infrastructure Security](#infrastructure-security)
4. [Database Security](#database-security)
5. [Network Security](#network-security)
6. [Authentication & Authorization](#authentication--authorization)
7. [Data Protection](#data-protection)
8. [Monitoring & Incident Response](#monitoring--incident-response)
9. [Security Maintenance](#security-maintenance)
10. [Compliance](#compliance)

## Security Overview

The Hotel Management System implements multiple layers of security to protect sensitive customer data, financial information, and operational data.

### Security Principles
- **Defense in Depth**: Multiple security layers
- **Least Privilege**: Minimal access rights
- **Zero Trust**: Verify everything
- **Data Minimization**: Collect only necessary data
- **Regular Updates**: Keep systems current
- **Incident Preparedness**: Rapid response capability

## Application Security

### 1. Laravel Security Features

#### CSRF Protection
```php
// All forms include CSRF tokens
@csrf

// API endpoints use Sanctum tokens
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reservas', ReservaApiController::class);
});
```

#### Input Validation
```php
// Request validation classes
class ReservaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'habitacion_id' => 'required|exists:habitacions,id',
            'fecha_entrada' => 'required|date|after:today',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'total' => 'required|numeric|min:0',
        ];
    }
}
```

#### SQL Injection Prevention
```php
// Always use Eloquent ORM or prepared statements
$reservas = Reserva::where('cliente_id', $clienteId)
    ->whereDate('fecha_entrada', '>=', $fechaInicio)
    ->get();

// Never use raw SQL with user input
// WRONG: DB::raw("SELECT * FROM reservas WHERE id = " . $id);
// RIGHT: DB::table('reservas')->where('id', $id)->get();
```

#### XSS Protection
```blade
{{-- Blade automatically escapes output --}}
<h1>{{ $hotel->nombre }}</h1>

{{-- Use {!! !!} only for trusted content --}}
{!! $trustedHtmlContent !!}

{{-- Sanitize user input --}}
{{ strip_tags($userInput) }}
```

### 2. Security Headers

Configure security headers in `docker/nginx/default.conf`:

```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### 3. Rate Limiting

```php
// In routes/web.php
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// API rate limiting
Route::middleware(['throttle:api'])->group(function () {
    Route::apiResource('reservas', ReservaApiController::class);
});
```

### 4. File Upload Security

```php
// Validate file uploads
public function rules()
{
    return [
        'documento' => 'file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        'imagen' => 'image|mimes:jpeg,png,jpg,gif|max:2048',   // 2MB max
    ];
}

// Store files securely
$path = $request->file('documento')->store('documentos', 'private');
```

## Infrastructure Security

### 1. Docker Security

#### Container Security
```dockerfile
# Use non-root user
RUN addgroup -g 1000 -S app && \
    adduser -u 1000 -S app -G app

USER app

# Read-only root filesystem (where possible)
--read-only --tmpfs /tmp --tmpfs /var/run

# Drop capabilities
--cap-drop=ALL --cap-add=CHOWN --cap-add=DAC_OVERRIDE
```

#### Container Hardening
```yaml
# docker-compose.yml security options
services:
  app:
    security_opt:
      - no-new-privileges:true
    read_only: true
    tmpfs:
      - /tmp
      - /var/run
```

### 2. System Security

#### Firewall Configuration
```bash
# Ubuntu/Debian UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# CentOS/RHEL Firewalld
sudo firewall-cmd --set-default-zone=public
sudo firewall-cmd --permanent --remove-service=ssh
sudo firewall-cmd --permanent --add-service=ssh --zone=public
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

#### SSH Hardening
```bash
# /etc/ssh/sshd_config
Protocol 2
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
X11Forwarding no
UsePAM yes
ClientAliveInterval 300
ClientAliveCountMax 2
MaxAuthTries 3
MaxSessions 2
```

### 3. SSL/TLS Configuration

#### Strong Cipher Suites
```nginx
# nginx SSL configuration
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384;
ssl_prefer_server_ciphers off;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 10m;

# HSTS
add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
```

#### Certificate Management
```bash
# Automated certificate renewal
0 12 * * * /usr/bin/certbot renew --quiet --post-hook "systemctl reload nginx"

# Certificate monitoring
*/6 * * * * /usr/local/bin/check-ssl-expiry.sh
```

## Database Security

### 1. MySQL Security Configuration

#### User Privileges
```sql
-- Create application user with minimal privileges
CREATE USER 'hotel_user'@'%' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON hotel_management.* TO 'hotel_user'@'%';

-- Read-only user for backups
CREATE USER 'hotel_backup'@'%' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW ON hotel_management.* TO 'hotel_backup'@'%';

-- Remove anonymous users and test database
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
FLUSH PRIVILEGES;
```

#### Connection Security
```bash
# MySQL configuration (my.cnf)
[mysqld]
# Require SSL connections
require_secure_transport = ON

# Disable remote root login
skip-networking=false
bind-address = 127.0.0.1

# Enable query logging for security analysis
general_log = 1
general_log_file = /var/log/mysql/general.log

# Enable slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 2. Data Encryption

#### Encrypt Sensitive Data
```php
// Use Laravel's encryption for sensitive data
use Illuminate\Support\Facades\Crypt;

// Encrypt before storing
$cliente->dpi = Crypt::encryptString($request->dpi);

// Decrypt when retrieving
$dpi = Crypt::decryptString($cliente->dpi);
```

#### Database Encryption
```sql
-- Enable encryption at rest
CREATE TABLE clientes (
    id INT PRIMARY KEY,
    nombre VARCHAR(255),
    telefono VARCHAR(20),
    dpi VARBINARY(255), -- Encrypted field
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENCRYPTION='Y';
```

### 3. Backup Security

```bash
# Encrypted backup script
backup_with_encryption() {
    local backup_file="backup_$(date +%Y%m%d_%H%M%S).sql"
    local encrypted_file="${backup_file}.gpg"

    # Create backup
    mysqldump --single-transaction hotel_management > "$backup_file"

    # Encrypt backup
    gpg --symmetric --cipher-algo AES256 --output "$encrypted_file" "$backup_file"

    # Remove unencrypted file
    rm "$backup_file"

    # Upload to secure storage
    aws s3 cp "$encrypted_file" s3://secure-backup-bucket/ --server-side-encryption AES256
}
```

## Network Security

### 1. Network Segmentation

```yaml
# Docker network isolation
networks:
  frontend:
    driver: bridge
  backend:
    driver: bridge
    internal: true

services:
  app:
    networks:
      - frontend
      - backend

  mysql:
    networks:
      - backend  # Only backend access
```

### 2. API Security

#### API Authentication
```php
// Sanctum token authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservas', [ReservaApiController::class, 'index']);
    Route::post('/reservas', [ReservaApiController::class, 'store']);
});

// Rate limiting for API
Route::middleware(['throttle:api'])->group(function () {
    Route::get('/api/reservas/disponibilidad', [ReservaApiController::class, 'disponibilidad']);
});
```

#### CORS Security
```php
// config/cors.php - Production settings
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
'allowed_origins_patterns' => [],
'allowed_headers' => ['Content-Type', 'Authorization'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

### 3. DDoS Protection

```nginx
# Rate limiting in nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

location /api/ {
    limit_req zone=api burst=20 nodelay;
}

location /login {
    limit_req zone=login burst=5 nodelay;
}
```

## Authentication & Authorization

### 1. User Authentication

#### Strong Password Policy
```php
// Password validation rules
'password' => [
    'required',
    'string',
    'min:8',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
    'confirmed',
],
```

#### Multi-Factor Authentication (Future Enhancement)
```php
// Google2FA implementation placeholder
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuth
{
    public function generateSecret()
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    public function verifyKey($secret, $key)
    {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($secret, $key);
    }
}
```

### 2. Role-Based Access Control

#### Permission System
```php
// Define permissions
Permission::create(['name' => 'view_reservas']);
Permission::create(['name' => 'create_reservas']);
Permission::create(['name' => 'edit_reservas']);
Permission::create(['name' => 'delete_reservas']);

// Assign to roles
$admin = Role::findByName('Administrador');
$admin->givePermissionTo('view_reservas', 'create_reservas', 'edit_reservas', 'delete_reservas');

$recepcionista = Role::findByName('Recepcionista');
$recepcionista->givePermissionTo('view_reservas', 'create_reservas', 'edit_reservas');
```

#### Middleware Protection
```php
// Route protection
Route::group(['middleware' => ['auth', 'permission:manage_reservas']], function() {
    Route::resource('reservas', ReservaController::class);
});

// Controller protection
class ReservaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_reservas')->only(['index', 'show']);
        $this->middleware('permission:create_reservas')->only(['create', 'store']);
        $this->middleware('permission:edit_reservas')->only(['edit', 'update']);
        $this->middleware('permission:delete_reservas')->only(['destroy']);
    }
}
```

### 3. Session Security

```php
// config/session.php - Production settings
'driver' => env('SESSION_DRIVER', 'redis'),
'lifetime' => 120, // 2 hours
'expire_on_close' => true,
'encrypt' => true,
'files' => storage_path('framework/sessions'),
'connection' => env('SESSION_REDIS_CONNECTION', 'default'),
'table' => 'sessions',
'store' => env('SESSION_STORE'),
'lottery' => [2, 100],
'cookie' => env('SESSION_COOKIE', 'laravel_session'),
'path' => '/',
'domain' => env('SESSION_DOMAIN'),
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
'partitioned' => false,
```

## Data Protection

### 1. GDPR Compliance

#### Data Processing Consent
```php
class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'dpi',
        'direccion',
        'consent_marketing',
        'consent_data_processing',
        'consent_date',
    ];

    protected $casts = [
        'consent_date' => 'datetime',
        'consent_marketing' => 'boolean',
        'consent_data_processing' => 'boolean',
    ];
}
```

#### Data Retention Policy
```php
// Artisan command for data cleanup
class CleanupExpiredData extends Command
{
    protected $signature = 'gdpr:cleanup';

    public function handle()
    {
        // Delete personal data of customers who haven't been active for 7 years
        Cliente::whereDoesntHave('reservas', function($query) {
            $query->where('created_at', '>', now()->subYears(7));
        })->where('created_at', '<', now()->subYears(7))->delete();

        // Anonymize old reservation data
        Reserva::where('created_at', '<', now()->subYears(5))
            ->update([
                'observaciones' => 'Datos anonimizados por política de retención',
                'updated_at' => now(),
            ]);
    }
}
```

### 2. Data Anonymization

```php
class DataAnonymizer
{
    public static function anonymizeCliente(Cliente $cliente)
    {
        $cliente->update([
            'nombre' => 'Cliente Anonimo ' . $cliente->id,
            'telefono' => '***-***-****',
            'email' => 'anonimo' . $cliente->id . '@example.com',
            'dpi' => '***********',
            'direccion' => 'Dirección anonimizada',
        ]);
    }
}
```

### 3. Audit Logging

```php
// Audit trail for sensitive operations
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}

// Usage in models
class Reserva extends Model
{
    protected static function booted()
    {
        static::updated(function ($reserva) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'model_type' => 'Reserva',
                'model_id' => $reserva->id,
                'old_values' => $reserva->getOriginal(),
                'new_values' => $reserva->getChanges(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
```

## Monitoring & Incident Response

### 1. Security Monitoring

#### Log Analysis
```bash
# Security event monitoring script
#!/bin/bash

LOG_FILE="/var/log/hotel-management/security.log"
ALERT_EMAIL="security@yourdomain.com"

# Monitor for failed login attempts
check_failed_logins() {
    local failed_attempts=$(grep "authentication attempt failed" /var/log/nginx/access.log | wc -l)

    if [[ $failed_attempts -gt 10 ]]; then
        echo "HIGH: $failed_attempts failed login attempts detected" | mail -s "Security Alert" $ALERT_EMAIL
    fi
}

# Monitor for SQL injection attempts
check_sql_injection() {
    local sql_attempts=$(grep -i "union\|select\|insert\|update\|delete\|drop" /var/log/nginx/access.log | grep -v "GET /api" | wc -l)

    if [[ $sql_attempts -gt 0 ]]; then
        echo "CRITICAL: Potential SQL injection attempts detected" | mail -s "Security Alert" $ALERT_EMAIL
    fi
}

# Monitor for suspicious file access
check_suspicious_access() {
    local suspicious_access=$(grep -E "\\.env|\\.git|/admin|wp-admin" /var/log/nginx/access.log | wc -l)

    if [[ $suspicious_access -gt 5 ]]; then
        echo "MEDIUM: Suspicious file access attempts detected" | mail -s "Security Alert" $ALERT_EMAIL
    fi
}

check_failed_logins
check_sql_injection
check_suspicious_access
```

#### Intrusion Detection
```bash
# Install and configure fail2ban
sudo apt install fail2ban

# Configure jail for nginx
cat > /etc/fail2ban/jail.local << 'EOF'
[nginx-auth]
enabled = true
filter = nginx-auth
logpath = /var/log/nginx/error.log
maxretry = 3
bantime = 3600

[nginx-noscript]
enabled = true
filter = nginx-noscript
logpath = /var/log/nginx/access.log
maxretry = 6
bantime = 86400

[nginx-badbots]
enabled = true
filter = nginx-badbots
logpath = /var/log/nginx/access.log
maxretry = 2
bantime = 86400
EOF
```

### 2. Incident Response Plan

#### Immediate Response
1. **Identify**: Determine the nature and scope of the incident
2. **Contain**: Isolate affected systems
3. **Assess**: Evaluate the impact and determine next steps
4. **Notify**: Alert stakeholders and authorities if required
5. **Document**: Record all actions taken

#### Response Scripts
```bash
# Emergency isolation script
#!/bin/bash

emergency_isolation() {
    echo "EMERGENCY: Isolating application..."

    # Stop application containers
    docker-compose down

    # Block all HTTP traffic except from admin IPs
    iptables -A INPUT -p tcp --dport 80 -s ADMIN_IP -j ACCEPT
    iptables -A INPUT -p tcp --dport 80 -j DROP
    iptables -A INPUT -p tcp --dport 443 -s ADMIN_IP -j ACCEPT
    iptables -A INPUT -p tcp --dport 443 -j DROP

    # Enable maintenance mode
    touch /var/www/html/maintenance.flag

    echo "Application isolated. Investigate the incident."
}

# Recovery script
recovery_mode() {
    echo "Starting recovery procedures..."

    # Restore from clean backup
    ./scripts/backup/restore.sh /var/backups/hotel-management/clean-backup

    # Reset all user passwords
    docker-compose exec app php artisan auth:reset-passwords

    # Regenerate all API tokens
    docker-compose exec app php artisan sanctum:prune-expired --hours=0

    # Clear all sessions
    docker-compose exec app php artisan session:clear

    echo "Recovery completed. Review security before going live."
}
```

## Security Maintenance

### 1. Regular Updates

#### Automated Security Updates
```bash
# Ubuntu/Debian unattended upgrades
sudo apt install unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades

# Configure automatic security updates
cat > /etc/apt/apt.conf.d/50unattended-upgrades << 'EOF'
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
};
Unattended-Upgrade::AutoFixInterruptedDpkg "true";
Unattended-Upgrade::MinimalSteps "true";
Unattended-Upgrade::Remove-Unused-Dependencies "true";
EOF
```

#### Application Updates
```bash
# Weekly security update script
#!/bin/bash

update_application() {
    # Update composer dependencies
    docker-compose exec app composer update --no-dev

    # Update npm packages
    docker-compose exec app npm audit fix

    # Rebuild containers with latest base images
    docker-compose build --no-cache --pull

    # Restart services
    docker-compose up -d

    # Run security scan
    ./scripts/security/scan.sh
}

# Add to crontab
0 2 * * 1 /opt/hotel-management/scripts/security/update.sh
```

### 2. Security Scanning

#### Vulnerability Scanning
```bash
# Docker security scan
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
    aquasec/trivy image hotel-management:latest

# Application security scan with PHPStan
docker-compose exec app ./vendor/bin/phpstan analyse

# Dependency vulnerability check
docker-compose exec app composer audit
```

#### Penetration Testing
```bash
# Automated security testing with OWASP ZAP
docker run -t owasp/zap2docker-stable zap-baseline.py \
    -t https://yourdomain.com \
    -J zap-report.json
```

### 3. Security Metrics

#### Key Security Indicators
- Failed login attempts per hour
- Unusual API access patterns
- Database query anomalies
- File system modifications
- Network traffic anomalies
- SSL certificate expiration dates

#### Security Dashboard
```php
// Security metrics controller
class SecurityMetricsController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'failed_logins_24h' => $this->getFailedLogins(),
            'active_sessions' => $this->getActiveSessions(),
            'recent_user_registrations' => $this->getRecentRegistrations(),
            'api_requests_per_hour' => $this->getApiUsage(),
            'ssl_certificate_expiry' => $this->getSslExpiry(),
            'last_security_scan' => $this->getLastSecurityScan(),
        ];

        return view('admin.security.dashboard', compact('metrics'));
    }
}
```

## Compliance

### 1. Data Protection Regulations

#### GDPR Compliance Checklist
- [ ] Data processing consent obtained
- [ ] Privacy policy published
- [ ] Data retention policy implemented
- [ ] Right to be forgotten functionality
- [ ] Data portability features
- [ ] Breach notification procedures
- [ ] Data Protection Officer appointed
- [ ] Regular privacy impact assessments

#### PCI DSS (if processing payments)
- [ ] Secure network configuration
- [ ] Strong access controls
- [ ] Encrypted cardholder data
- [ ] Regular security testing
- [ ] Secure development practices
- [ ] Information security policy

### 2. Industry Standards

#### ISO 27001 Controls
- Access control management
- Cryptography management
- Operations security
- Communications security
- System acquisition/development
- Supplier relationship security
- Incident management
- Business continuity

### 3. Audit Preparation

#### Documentation Required
- Security policies and procedures
- Risk assessment reports
- Incident response logs
- Security training records
- Vendor security assessments
- Penetration testing reports
- Compliance monitoring reports

---

## Security Checklist

### Production Deployment Security
- [ ] SSL/TLS properly configured
- [ ] Security headers implemented
- [ ] Firewall rules configured
- [ ] Strong passwords enforced
- [ ] Multi-factor authentication enabled
- [ ] Rate limiting configured
- [ ] CSRF protection enabled
- [ ] SQL injection prevention verified
- [ ] XSS protection implemented
- [ ] File upload security configured

### Operational Security
- [ ] Regular security updates scheduled
- [ ] Monitoring and alerting configured
- [ ] Backup encryption enabled
- [ ] Incident response plan documented
- [ ] Security training completed
- [ ] Vendor security assessments completed
- [ ] Penetration testing scheduled
- [ ] Compliance requirements met

### Ongoing Security
- [ ] Weekly vulnerability scans
- [ ] Monthly security metrics review
- [ ] Quarterly security assessments
- [ ] Annual penetration testing
- [ ] Regular staff security training
- [ ] Continuous monitoring active
- [ ] Incident response drills conducted

---

This security guide provides comprehensive protection for the Hotel Management System. Regular review and updates of security measures are essential to maintain protection against evolving threats.