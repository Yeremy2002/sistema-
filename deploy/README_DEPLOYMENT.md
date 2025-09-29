# Hotel Casa Vieja - Management System Deployment Package

## 🏨 System Overview

The Hotel Casa Vieja Management System is a comprehensive Laravel 12-based application designed to handle all aspects of hotel operations including:

- **Reservation Management**: Full booking lifecycle from landing page to check-out
- **Room Management**: Categories, levels, availability tracking
- **Cash Register Control**: Shift-based financial operations
- **Client Management**: Customer database with origin tracking
- **Maintenance Tracking**: Room maintenance and cleaning schedules
- **User Management**: Role-based access control
- **Notification System**: Automated alerts and reminders
- **API Integration**: Public API for external booking systems

## 📦 Package Contents

This deployment package includes all necessary files and scripts for production deployment:

### Core Application
- **Laravel 12 Application** - Complete hotel management system
- **Optimized Dependencies** - Production-ready vendor packages
- **Compiled Assets** - Built CSS/JS files via Vite
- **Configuration Files** - Production-ready configurations

### Deployment Tools
- `deploy/cpanel_setup.php` - Automated database setup and configuration
- `deploy/database_backup.php` - Backup and restore functionality
- `deploy/file_permissions.sh` - Correct permission setup
- `deploy/optimize_production.php` - Laravel optimization script
- `deploy/build_deployment.sh` - Package builder script

### Documentation
- `deploy/DEPLOYMENT_INSTRUCTIONS.md` - Complete deployment guide
- `deploy/DEPLOYMENT_CHECKLIST.md` - Verification checklist
- `deploy/cron_setup.txt` - Cron job configuration
- `CLAUDE.md` - System architecture and development guide

## 🚀 Quick Start Guide

### 1. Upload and Extract
1. Upload the `.tar.gz` file to your cPanel File Manager
2. Extract to `public_html` directory
3. Move all files from extracted folder to `public_html` root

### 2. Database Setup
- **Database**: `casaviejagt_hotel_management`
- **Username**: `casaviejagt_hoteluser`
- **Password**: `SalesSystem2025!`

### 3. Run Setup Script
Visit: `https://casaviejagt.com/deploy/cpanel_setup.php`

This automated script will:
- Test database connection
- Run migrations
- Seed essential data
- Optimize Laravel for production
- Create storage links

### 4. Configure Cron Jobs
Add to cPanel Cron Jobs:
```bash
* * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Set File Permissions
If using SSH:
```bash
cd /home/casaviejagt/public_html
chmod +x deploy/file_permissions.sh
./deploy/file_permissions.sh
```

### 6. Test Installation
1. Visit: `https://casaviejagt.com`
2. Login with:
   - Email: `admin@hotel.com`
   - Password: `password`
3. **IMPORTANT**: Change default password immediately!

## 🔧 System Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 5.7 or higher (8.0 recommended)
- **Apache**: 2.4+ with mod_rewrite enabled
- **SSL Certificate**: Required for production

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML
- GD or Imagick

### Recommended Hosting Features
- SSH access (for easier management)
- Cron job support
- SSL certificate (Let's Encrypt acceptable)
- At least 1GB disk space
- 512MB PHP memory limit

## 🔒 Security Features

### Application Security
- **CSRF Protection** - All forms protected
- **SQL Injection Prevention** - Eloquent ORM with prepared statements
- **XSS Protection** - Input sanitization and output escaping
- **Role-Based Access Control** - Spatie Laravel Permission
- **Session Security** - Secure session configuration

### Server Security
- **HTTPS Enforcement** - Automatic HTTP to HTTPS redirects
- **Security Headers** - HSTS, CSP, X-Frame-Options, etc.
- **File Access Control** - Sensitive files protected via .htaccess
- **Directory Protection** - Framework directories access denied

## 📊 Key Features

### Reservation System
- **Multi-State Workflow**: Pending Confirmation → Pending → Check-in → Check-out
- **Automatic Expiration**: Landing page reservations expire after configurable time
- **Availability Checking**: Real-time room availability across date ranges
- **Financial Integration**: Cash register validation for all financial operations

### Cash Register Management
- **Shift-Based Operations**: Diurno (6 AM - 6 PM) and Nocturno (6 PM - 6 AM)
- **Automatic Verification**: Background checks for open registers
- **Financial Controls**: All monetary operations require open cash register
- **Audit Trail**: Complete logging of financial activities

### Notification System
- **Real-Time Alerts**: Dashboard notification counter
- **Multiple Types**: Cash register, reservations, maintenance alerts
- **Automatic Triggers**: Time-based and event-based notifications
- **Management Interface**: Mark as read, bulk operations

### API Integration
- **Public Endpoints**: Room availability, reservation creation
- **CORS Enabled**: Cross-origin requests for landing page integration
- **Client Origin Tracking**: Different validation rules for landing vs backend
- **Calendar Integration**: FullCalendar-compatible event feeds

## 🛠 Management and Maintenance

### Database Backups
```bash
# Create backup
cd deploy && php database_backup.php create

# List backups
php database_backup.php list

# Restore from backup
php database_backup.php restore <filename>

# Clean old backups
php database_backup.php clean
```

### Laravel Optimization
```bash
# Run full optimization
cd deploy && php optimize_production.php

# Individual optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Monitoring Commands
```bash
# Check reservation expiration
php artisan reservas:expirar

# Verify cash register closures
php artisan cajas:verificar-cierres

# Clean expired reservations
php artisan reservations:clean-expired

# Fix notification URLs
php artisan notifications:fix-urls
```

## 🔄 Scheduled Tasks

The system includes automated tasks that run via Laravel's scheduler:

### Every 5 Minutes (Configurable)
- **Reservation Expiration**: Automatically expire unconfirmed reservations
- **Cash Register Verification**: Check for unclosed registers

### Every 30 Minutes
- **Maintenance Alerts**: Notify about rooms requiring attention
- **System Health Checks**: Monitor application status

### Daily Tasks
- **Log Cleanup**: Remove old log files
- **Cache Refresh**: Clear stale cache entries
- **Backup Automation**: Create database backups

## 📞 Support and Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Verify credentials in `.env` file
   - Check database hostname
   - Ensure user has correct privileges

2. **Permission Errors**
   - Run `chmod -R 775 storage bootstrap/cache`
   - Check web server user ownership

3. **404 Errors**
   - Verify document root points to `public` directory
   - Check `.htaccess` file exists and is readable

4. **Cron Jobs Not Running**
   - Verify cron job syntax and paths
   - Check cPanel cron job logs
   - Test manually: `php artisan schedule:run`

### System Verification
```bash
# Test database connection
php artisan tinker
> DB::connection()->getPdo();

# Check scheduled tasks
php artisan schedule:list

# Verify optimization
ls -la bootstrap/cache/
```

### Log Locations
- **Laravel Logs**: `storage/logs/laravel.log`
- **Apache Logs**: Check cPanel error logs
- **Setup Logs**: `deploy/setup.log`
- **Backup Logs**: `deploy/backups/backup.log`

## 🎯 Post-Deployment Checklist

### Immediate Tasks
- [ ] Change default admin password
- [ ] Configure hotel settings (currency, timezone, checkout times)
- [ ] Test reservation system
- [ ] Verify email notifications
- [ ] Set up SSL certificate

### Configuration Tasks
- [ ] Add room categories and rooms
- [ ] Configure user roles and permissions
- [ ] Set up notification preferences
- [ ] Test API endpoints
- [ ] Configure backup schedule

### Monitoring Setup
- [ ] Monitor error logs
- [ ] Set up performance monitoring
- [ ] Configure backup verification
- [ ] Test restore procedures
- [ ] Document support procedures

## 📝 Additional Resources

### Documentation Files
- **CLAUDE.md** - Complete system architecture guide
- **DEPLOYMENT_INSTRUCTIONS.md** - Detailed deployment steps
- **DEPLOYMENT_CHECKLIST.md** - Verification checklist

### Development Information
- **Framework**: Laravel 12
- **Frontend**: AdminLTE 3, Bootstrap 5, Tailwind CSS
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Assets**: Vite build system

## 🏆 Success Metrics

Your deployment is successful when:
- ✅ Application loads without errors
- ✅ Admin login works correctly
- ✅ Dashboard displays properly
- ✅ Reservation system functions
- ✅ API endpoints respond correctly
- ✅ Cron jobs execute successfully
- ✅ Notifications work properly
- ✅ SSL certificate is active

## 📧 Contact Information

For technical support or questions about the hotel management system:
- Refer to system documentation in CLAUDE.md
- Check deployment logs in `/deploy/` directory
- Monitor Laravel logs in `storage/logs/`

**Hotel Casa Vieja Management System v1.0**
**Deployment Date**: [To be filled during deployment]
**Deployed By**: [To be filled during deployment]

---

*This deployment package was created with production best practices and security in mind. Regular maintenance and monitoring are recommended for optimal performance.*