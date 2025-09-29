# Hotel Management System - cPanel Deployment Instructions

## Overview
This guide provides step-by-step instructions for deploying the Hotel Casa Vieja management system to cPanel hosting with the domain `casaviejagt.com`.

## ⚠️ CRITICAL Prerequisites

### Server Requirements
- **PHP Version:** 8.2.0 or higher (MANDATORY)
- **MySQL:** 5.7+ or MariaDB 10.3+
- **Apache:** with mod_rewrite enabled
- **Required PHP Extensions:** PDO, PDO_MySQL, mbstring, OpenSSL, JSON, cURL, Zip

### cPanel PHP Configuration (MUST DO FIRST!)
**BEFORE PROCEEDING:** You must update PHP version in cPanel:
1. Access **cPanel → Software → Select PHP Version**
2. Select **PHP 8.2** or higher
3. Make sure it's activated for your domain
4. Save changes and wait 2-3 minutes

### Database Configuration
- cPanel hosting account with database access
- SSH access (optional but recommended)
- Domain: casaviejagt.com
- Database: casaviejagt_hotel_management
- Database User: casaviejagt_hoteluser
- Database Password: SalesSystem2025!

## Pre-Deployment Preparation

### 1. Build Production Package
Run the production setup script on your local machine:

```bash
cd /path/to/hotel-management-project
chmod +x deploy/production_setup.sh
./deploy/production_setup.sh
```

This will create a compressed deployment package: `hotel_management_production_YYYYMMDD_HHMMSS.tar.gz`

## cPanel Deployment Steps

### Step 1: Database Setup

1. **Login to cPanel**
   - Access your cPanel dashboard

2. **Create Database** (if not already created)
   - Go to "MySQL Databases"
   - Create database: `casaviejagt_hotel_management`
   - Create user: `casaviejagt_hoteluser` with password: `SalesSystem2025!`
   - Add user to database with all privileges

3. **Verify Database Connection**
   - Note down the database hostname (usually `localhost`)
   - Test connection if possible

### Step 2: File Upload and Extraction

1. **Access File Manager**
   - Go to cPanel File Manager
   - Navigate to `public_html` directory

2. **Upload Deployment Package**
   - Upload the `hotel_management_production_*.tar.gz` file
   - Extract the archive in `public_html`
   - Move all files from the extracted folder to `public_html` root

3. **Verify File Structure**
   Your `public_html` should contain:
   ```
   public_html/
   ├── app/
   ├── bootstrap/
   ├── config/
   ├── database/
   ├── deploy/
   ├── public/
   ├── resources/
   ├── routes/
   ├── storage/
   ├── vendor/
   ├── .env.production
   ├── artisan
   ├── composer.json
   └── composer.lock
   ```

### Step 3: Environment Configuration

1. **Set up Environment File**
   ```bash
   # Rename the production environment file
   mv .env.production .env
   ```

2. **Verify Environment Settings**
   Edit `.env` file and confirm:
   ```
   APP_URL=https://casaviejagt.com
   DB_HOST=localhost
   DB_DATABASE=casaviejagt_hotel_management
   DB_USERNAME=casaviejagt_hoteluser
   DB_PASSWORD=SalesSystem2025!
   ```

### Step 4: Set File Permissions

1. **Using File Manager**
   - Right-click on folders: `storage`, `bootstrap/cache`
   - Set permissions to `775`
   - Set all other directories to `755`
   - Set all files to `644`

2. **Using SSH** (if available)
   ```bash
   cd /home/casaviejagt/public_html
   chmod +x deploy/file_permissions.sh
   ./deploy/file_permissions.sh
   ```

### Step 5: Database Migration and Setup

1. **Run Setup Script**
   - Access: `https://casaviejagt.com/deploy/cpanel_setup.php`
   - This will:
     - Test database connection
     - Run migrations
     - Seed essential data
     - Optimize Laravel for production
     - Create necessary directories

2. **Alternative: SSH Method** (if available)
   ```bash
   cd /home/casaviejagt/public_html
   php artisan migrate --force
   php artisan db:seed --class=EssentialDataSeeder --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link
   ```

### Step 6: Configure Document Root

1. **Update Document Root**
   - In cPanel, go to "Subdomains" or "Addon Domains"
   - Set document root to: `/public_html/public`
   - Or create a `.htaccess` in public_html root:
   ```apache
   RewriteEngine On
   RewriteRule ^(.*)$ public/$1 [L]
   ```

### Step 7: Set Up Cron Jobs

1. **Access Cron Jobs**
   - Go to cPanel "Cron Jobs"

2. **Add Required Cron Jobs**
   ```bash
   # Laravel Scheduler (REQUIRED)
   * * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1

   # Queue Worker Restart
   */15 * * * * cd /home/casaviejagt/public_html && php artisan queue:restart >> /dev/null 2>&1

   # Daily Database Backup
   0 2 * * * cd /home/casaviejagt/public_html/deploy && php database_backup.php create >> /dev/null 2>&1

   # Weekly Backup Cleanup
   0 3 * * 0 cd /home/casaviejagt/public_html/deploy && php database_backup.php clean 10 >> /dev/null 2>&1
   ```

### Step 8: SSL Certificate Setup

1. **Enable SSL**
   - Go to cPanel "SSL/TLS"
   - Enable "Force HTTPS Redirect"
   - Install Let's Encrypt certificate if available

2. **Update Environment**
   ```
   APP_URL=https://casaviejagt.com
   SESSION_SECURE_COOKIES=true
   ```

## Post-Deployment Verification

### Test Application Access
1. Visit: `https://casaviejagt.com`
2. Login with default credentials:
   - Email: `admin@hotel.com`
   - Password: `password`

### Verify Core Functionality
1. **Dashboard Access**
   - Check if dashboard loads correctly
   - Verify calendar functionality

2. **Reservation System**
   - Test room availability
   - Create a test reservation
   - Verify email notifications

3. **API Endpoints**
   - Test: `https://casaviejagt.com/api/test-cors`
   - Test: `https://casaviejagt.com/api/test-disponibilidad`

## Security Hardening

### 1. Change Default Credentials
- Login and change admin password immediately
- Update database passwords if needed

### 2. Hide Sensitive Files
Create/update `.htaccess` in public_html root:
```apache
# Deny access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Deny access to directories
RedirectMatch 404 /\..*$
RedirectMatch 404 /deploy/.*$
```

### 3. Database Security
- Use strong passwords
- Limit database user privileges
- Regular backups

## Monitoring and Maintenance

### Regular Tasks
1. **Weekly Database Backups**
   ```bash
   cd /home/casaviejagt/public_html/deploy
   php database_backup.php create
   ```

2. **Log Monitoring**
   - Check `storage/logs/` for errors
   - Monitor cPanel error logs

3. **Performance Optimization**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Backup Management
- Access backup tool: `cd deploy && php database_backup.php list`
- Restore if needed: `php database_backup.php restore <filename>`
- Clean old backups: `php database_backup.php clean`

## Troubleshooting

### Common Issues

1. **Permission Errors**
   - Run: `./deploy/file_permissions.sh`
   - Check storage and cache directory permissions

2. **Database Connection Issues**
   - Verify credentials in `.env`
   - Check database hostname
   - Ensure database user has correct privileges

3. **Cron Jobs Not Running**
   - Verify cron job paths
   - Check cPanel cron job logs
   - Test scheduler manually: `php artisan schedule:run`

4. **SSL Issues**
   - Force HTTPS in cPanel
   - Update `APP_URL` in `.env`
   - Clear browser cache

5. **500 Internal Server Error**
   - Check storage permissions
   - Verify `.env` configuration
   - Check cPanel error logs
   - Ensure all required PHP extensions are installed

### Support Information
- **System Requirements**: PHP 8.2+, MySQL 5.7+, Laravel 12
- **Key Dependencies**: AdminLTE, Spatie Permissions, Laravel Sanctum
- **File Structure**: Standard Laravel with custom hotel management modules

## Contact and Support
For technical issues specific to the hotel management system, refer to the CLAUDE.md file for architecture details and development commands.

**Important**: Always test changes in a staging environment before applying to production.