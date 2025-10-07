#!/bin/bash

###############################################################################
# cPanel Deployment Preparation Script
# Laravel 12 Hotel Management System - casaviejagt.com
#
# This script prepares the application for production deployment to cPanel
#
# Usage: bash deploy-prepare.sh
###############################################################################

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/Users/richardortiz/workspace/gestion_hotel/laravel12_migracion"
DEPLOY_PACKAGE="hotel_casavieja_deployment_$(date +%Y%m%d_%H%M%S).tar.gz"

###############################################################################
# Functions
###############################################################################

print_header() {
    echo -e "\n${BLUE}================================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

check_command() {
    if ! command -v $1 &> /dev/null; then
        print_error "$1 is not installed"
        return 1
    fi
    return 0
}

###############################################################################
# Pre-flight Checks
###############################################################################

print_header "PRE-FLIGHT CHECKS"

# Check if we're in the project directory
if [ ! -f "artisan" ]; then
    print_error "Not in Laravel project directory"
    exit 1
fi
print_success "Laravel project detected"

# Check required commands
check_command "php" || exit 1
check_command "composer" || exit 1
check_command "npm" || exit 1
check_command "tar" || exit 1
print_success "Required commands available"

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
print_info "PHP Version: $PHP_VERSION"
if [[ ! "$PHP_VERSION" =~ ^8\.[2-9] ]]; then
    print_warning "PHP 8.2+ recommended (current: $PHP_VERSION)"
fi

###############################################################################
# Step 1: Update config/queue.php
###############################################################################

print_header "STEP 1: Update Queue Configuration"

print_info "Updating config/queue.php for MySQL compatibility..."

# Backup original
cp config/queue.php config/queue.php.backup

# Update batching database connection
sed -i.bak "s/'database' => env('DB_CONNECTION', 'sqlite'),/'database' => env('DB_CONNECTION', 'mysql'),/g" config/queue.php

# Remove backup file
rm config/queue.php.bak

print_success "Queue configuration updated"

###############################################################################
# Step 2: Clear All Caches
###############################################################################

print_header "STEP 2: Clear Development Caches"

php artisan config:clear
print_success "Config cache cleared"

php artisan route:clear
print_success "Route cache cleared"

php artisan view:clear
print_success "View cache cleared"

php artisan cache:clear
print_success "Application cache cleared"

###############################################################################
# Step 3: Build Frontend Assets
###############################################################################

print_header "STEP 3: Build Frontend Assets"

print_info "Installing NPM dependencies..."
npm install --silent
print_success "NPM dependencies installed"

print_info "Building production assets..."
npm run build
print_success "Assets built successfully"

# Verify build output
if [ -d "public/build" ]; then
    BUILD_SIZE=$(du -sh public/build | cut -f1)
    print_success "Build directory created (Size: $BUILD_SIZE)"
else
    print_error "Build directory not found!"
    exit 1
fi

###############################################################################
# Step 4: Optimize Composer
###############################################################################

print_header "STEP 4: Optimize Composer Dependencies"

print_info "Installing production dependencies..."
composer install --optimize-autoloader --no-dev --quiet
print_success "Composer dependencies optimized"

###############################################################################
# Step 5: Create Deployment Package
###############################################################################

print_header "STEP 5: Create Deployment Package"

print_info "Creating deployment archive..."

# Create archive excluding unnecessary files
tar -czf "$DEPLOY_PACKAGE" \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='database/database.sqlite' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/app/public/*' \
    --exclude='.env' \
    --exclude='.env.local' \
    --exclude='.env.example' \
    --exclude='*.log' \
    --exclude='.DS_Store' \
    --exclude='*.md' \
    --exclude='deploy-prepare.sh' \
    --exclude='*.backup' \
    .

PACKAGE_SIZE=$(du -sh "$DEPLOY_PACKAGE" | cut -f1)
print_success "Deployment package created: $DEPLOY_PACKAGE ($PACKAGE_SIZE)"

###############################################################################
# Step 6: Generate Deployment Checklist
###############################################################################

print_header "STEP 6: Generate Deployment Checklist"

CHECKLIST_FILE="deployment_checklist_$(date +%Y%m%d_%H%M%S).txt"

cat > "$CHECKLIST_FILE" << 'EOF'
╔═══════════════════════════════════════════════════════════════╗
║     cPanel DEPLOYMENT CHECKLIST - Hotel Casa Vieja            ║
║                   casaviejagt.com                             ║
╚═══════════════════════════════════════════════════════════════╝

PRE-DEPLOYMENT TASKS:
---------------------
[ ] Review CPANEL_DEPLOYMENT_ANALYSIS.md
[ ] Backup existing production site (if any)
[ ] Verify FTP credentials
[ ] Verify database credentials
[ ] Test build assets locally (npm run build)

CPANEL PREPARATION:
-------------------
[ ] Login to cPanel (casaviejagt.com/cpanel)
[ ] Verify database exists: casaviejagt_hotel_management
[ ] Verify database user: casaviejagt_hoteluser
[ ] Check database user privileges (ALL PRIVILEGES)
[ ] Set PHP version to 8.2 or higher
[ ] Verify required PHP extensions enabled
[ ] Set document root to: public_html/public

FILE UPLOAD:
------------
[ ] Upload deployment package via FTP
[ ] Extract package in /home/casaviejagt/public_html/
[ ] Upload .env.production as .env
[ ] Edit .env and add email password
[ ] Generate new APP_KEY: php artisan key:generate

FILE PERMISSIONS:
-----------------
[ ] Set storage/ permissions to 775 (recursive)
[ ] Set bootstrap/cache/ permissions to 775 (recursive)
[ ] Verify ownership: casaviejagt:casaviejagt

DATABASE SETUP:
---------------
[ ] Run: php artisan migrate --force
[ ] Run: php artisan db:seed --class=EssentialDataSeeder --force
[ ] Verify tables created in phpMyAdmin
[ ] Verify admin user exists

OPTIMIZATION:
-------------
[ ] Run: php artisan config:cache
[ ] Run: php artisan route:cache
[ ] Run: php artisan view:cache
[ ] Run: php artisan storage:link
[ ] Verify cache files created

CRON JOBS SETUP:
----------------
[ ] Add Laravel Scheduler:
    */5 * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1

[ ] Add Queue Worker:
    */2 * * * * cd /home/casaviejagt/public_html && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1

[ ] Verify PHP path (use: which php or whereis php)
[ ] Save cron jobs

SSL CERTIFICATE:
----------------
[ ] Navigate to SSL/TLS Status in cPanel
[ ] Run AutoSSL for casaviejagt.com
[ ] Verify HTTPS redirect working
[ ] Test: https://casaviejagt.com

ROOT .htaccess:
---------------
[ ] Create /home/casaviejagt/public_html/.htaccess with:

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>

TESTING:
--------
[ ] Test homepage: https://casaviejagt.com
[ ] Test login: https://casaviejagt.com/login
[ ] Login with admin credentials
[ ] Create test room
[ ] Create test reservation
[ ] Test cash register (caja) operations
[ ] Test API endpoint: /api/test-cors
[ ] Test API endpoint: /api/reservas/disponibilidad
[ ] Send test notification
[ ] Verify email sending

MONITORING:
-----------
[ ] Check Laravel logs: storage/logs/laravel.log
[ ] Monitor cron execution (first 1 hour)
[ ] Check queue processing
[ ] Verify scheduled tasks running
[ ] Monitor error logs in cPanel

POST-DEPLOYMENT:
----------------
[ ] Update admin password
[ ] Configure hotel settings
[ ] Upload room images
[ ] Set up email notifications
[ ] Configure CORS for landing page
[ ] Create database backup
[ ] Setup automated backups
[ ] Configure monitoring (UptimeRobot)

ROLLBACK PLAN:
--------------
[ ] Keep backup of previous version
[ ] Document rollback steps
[ ] Have database dump ready

CREDENTIALS REFERENCE:
----------------------
FTP:
  Host: casaviejagt.com
  User: casaviejagt
  Pass: S5{MfMTTw[{@mkM)

Database:
  Name: casaviejagt_hotel_management
  User: casaviejagt_hoteluser
  Pass: eIckhwtXHPr3YV0Y

Admin (from seeder):
  Email: admin@casaviejagt.com
  Password: [Check EssentialDataSeeder.php]

IMPORTANT NOTES:
----------------
• NEVER set APP_DEBUG=true in production
• ALWAYS regenerate APP_KEY on production
• MONITOR logs for first 24 hours
• Test all critical user flows
• Verify cron jobs are running
• Check queue is processing jobs

SUPPORT:
--------
• Laravel Docs: https://laravel.com/docs/12.x
• cPanel Docs: https://docs.cpanel.net/
• Deployment Guide: CPANEL_DEPLOYMENT_ANALYSIS.md

╔═══════════════════════════════════════════════════════════════╗
║  Deployment Date: _____________                               ║
║  Deployed By: _____________                                   ║
║  Status: [ ] Success  [ ] Failed  [ ] Rollback                ║
╚═══════════════════════════════════════════════════════════════╝
EOF

print_success "Deployment checklist created: $CHECKLIST_FILE"

###############################################################################
# Step 7: Restore Development Dependencies
###############################################################################

print_header "STEP 7: Restore Development Environment"

print_info "Restoring development dependencies..."
composer install --quiet
print_success "Development dependencies restored"

###############################################################################
# Summary
###############################################################################

print_header "DEPLOYMENT PREPARATION COMPLETE"

echo -e "${GREEN}Deployment package ready!${NC}\n"

echo -e "${BLUE}Files Created:${NC}"
echo -e "  1. $DEPLOY_PACKAGE ($PACKAGE_SIZE)"
echo -e "  2. $CHECKLIST_FILE"
echo -e "  3. .env.production (template)"
echo -e "  4. config/queue.php.backup (backup)\n"

echo -e "${BLUE}Next Steps:${NC}"
echo -e "  1. Review ${YELLOW}CPANEL_DEPLOYMENT_ANALYSIS.md${NC}"
echo -e "  2. Review ${YELLOW}$CHECKLIST_FILE${NC}"
echo -e "  3. Upload ${YELLOW}$DEPLOY_PACKAGE${NC} to cPanel"
echo -e "  4. Follow deployment steps in analysis document\n"

echo -e "${YELLOW}Important Reminders:${NC}"
echo -e "  • Backup production database before deployment"
echo -e "  • Regenerate APP_KEY on production server"
echo -e "  • Set correct file permissions (775 for storage/)"
echo -e "  • Configure cron jobs immediately after deployment"
echo -e "  • Monitor logs for first 24 hours\n"

print_success "Ready for deployment to casaviejagt.com"
