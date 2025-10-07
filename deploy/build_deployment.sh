#!/bin/bash

# Hotel Management System - Final Deployment Package Builder
# This script creates the final deployment package for cPanel

set -e

echo "🏨 Building Hotel Management System Deployment Package"
echo "======================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if we're in the Laravel project root
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel project root directory"
    exit 1
fi

# Create deployment timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DEPLOY_NAME="hotel_casavieja_production_${TIMESTAMP}"
DEPLOY_DIR="../${DEPLOY_NAME}"

print_header "1. Pre-build Verification"

# Check required files
REQUIRED_FILES=(".env.production" "composer.json" "artisan")
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        print_error "Required file missing: $file"
        exit 1
    fi
done
print_status "All required files present"

# Check if Node.js is available for asset building
if ! command -v npm >/dev/null 2>&1; then
    print_warning "npm not found - skipping frontend build"
    SKIP_NPM=true
else
    SKIP_NPM=false
fi

print_header "2. Installing Production Dependencies"

# Install Composer dependencies
print_status "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install and build frontend assets
if [ "$SKIP_NPM" = false ]; then
    print_status "Installing npm dependencies..."
    npm ci --omit=dev

    print_status "Building frontend assets..."
    if npm run build; then
        print_status "Assets built successfully"
    else
        print_warning "Asset build failed, trying with npx vite build..."
        if npx vite build; then
            print_status "Assets built successfully with npx"
        else
            print_error "Failed to build assets. Continuing without rebuild..."
        fi
    fi
else
    print_warning "Skipping npm build - ensure assets are already built"
fi

print_header "3. Laravel Optimization (Development Environment)"

# Clear existing caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

print_status "Caches cleared for clean build"

print_header "4. Preparing Production Environment"

# Copy production environment
cp .env.production .env
print_status "Production environment configured"

print_status "Skipping cache generation (will be done after deployment)"

print_header "5. Creating Deployment Directory"

# Create deployment directory
mkdir -p "$DEPLOY_DIR"
print_status "Created deployment directory: $DEPLOY_NAME"

print_header "6. Copying Application Files"

# Copy files using rsync with exclusions
rsync -av \
    --exclude='.git/' \
    --exclude='.gitignore' \
    --exclude='.gitattributes' \
    --exclude='.env.example' \
    --exclude='.env.local' \
    --exclude='.env.testing' \
    --exclude='.env.dusk.local' \
    --exclude='node_modules/' \
    --exclude='npm-debug.log*' \
    --exclude='yarn-debug.log*' \
    --exclude='yarn-error.log*' \
    --exclude='.vscode/' \
    --exclude='.idea/' \
    --exclude='*.swp' \
    --exclude='*.swo' \
    --exclude='.DS_Store' \
    --exclude='tests/' \
    --exclude='phpunit.xml' \
    --exclude='.phpunit.result.cache' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='README.md' \
    --exclude='CRITICAL_FIXES_APPLIED.md' \
    --exclude='MODAL_UX_IMPROVEMENTS.md' \
    --exclude='gestion_hotel/' \
    --exclude='test_fixes_verification.php' \
    --exclude='public/test_modal_fixes.html' \
    . "$DEPLOY_DIR/"

print_status "Application files copied"

print_header "7. Setting Up Production Structure"

# Create necessary directories
mkdir -p "$DEPLOY_DIR/storage/framework/"{cache/data,sessions,views}
mkdir -p "$DEPLOY_DIR/storage/logs"
mkdir -p "$DEPLOY_DIR/bootstrap/cache"

# Set initial permissions
find "$DEPLOY_DIR" -type d -exec chmod 755 {} \;
find "$DEPLOY_DIR" -type f -exec chmod 644 {} \;
chmod -R 775 "$DEPLOY_DIR/storage"
chmod -R 775 "$DEPLOY_DIR/bootstrap/cache"
chmod 600 "$DEPLOY_DIR/.env"

print_status "Directory structure and permissions set"

print_header "8. Creating Deployment Package"

# Create compressed archive
cd ..
tar -czf "${DEPLOY_NAME}.tar.gz" "$DEPLOY_NAME"

# Get file size
PACKAGE_SIZE=$(du -h "${DEPLOY_NAME}.tar.gz" | cut -f1)

print_status "Deployment package created: ${DEPLOY_NAME}.tar.gz (${PACKAGE_SIZE})"

# Clean up directory
rm -rf "$DEPLOY_NAME"

print_header "9. Creating Installation Summary"

# Create installation summary
cat > "${DEPLOY_NAME}_INSTALLATION.txt" << EOF
Hotel Casa Vieja Management System - Production Deployment Package
==================================================================

Package: ${DEPLOY_NAME}.tar.gz
Created: $(date)
Size: ${PACKAGE_SIZE}

INSTALLATION INSTRUCTIONS:
1. Upload ${DEPLOY_NAME}.tar.gz to your cPanel File Manager
2. Extract to public_html directory
3. Move all files from extracted folder to public_html root
4. Run the setup script: https://casaviejagt.com/deploy/cpanel_setup.php
5. Configure cron jobs as specified in deploy/cron_setup.txt
6. Set up SSL certificate and force HTTPS

DATABASE CONFIGURATION:
- Database: casaviejagt_hotel_management
- Username: casaviejagt_hoteluser
- Password: SalesSystem2025!

DEFAULT ADMIN CREDENTIALS:
- Email: admin@hotel.com
- Password: password
- IMPORTANT: Change password immediately after installation!

REQUIRED CRON JOB:
* * * * * cd /home/casaviejagt/public_html && php artisan schedule:run >> /dev/null 2>&1

For detailed instructions, see deploy/DEPLOYMENT_INSTRUCTIONS.md

SUPPORT FILES INCLUDED:
- deploy/DEPLOYMENT_INSTRUCTIONS.md - Complete deployment guide
- deploy/DEPLOYMENT_CHECKLIST.md - Verification checklist
- deploy/cpanel_setup.php - Automated setup script
- deploy/database_backup.php - Backup and restore tool
- deploy/file_permissions.sh - Permission setup script
- deploy/cron_setup.txt - Cron job configuration
- deploy/optimize_production.php - Production optimization

NEXT STEPS AFTER DEPLOYMENT:
1. Change default admin password
2. Configure hotel settings
3. Add rooms and categories
4. Test reservation system
5. Set up monitoring and backups

Technical Support: Check documentation for system architecture
EOF

print_status "Installation summary created: ${DEPLOY_NAME}_INSTALLATION.txt"

print_header "10. Final Verification"

# Verify package contents
if [ -f "${DEPLOY_NAME}.tar.gz" ]; then
    print_status "✓ Deployment package verified"
else
    print_error "✗ Deployment package creation failed"
    exit 1
fi

# Check package contents
PACKAGE_CONTENTS=$(tar -tzf "${DEPLOY_NAME}.tar.gz" | wc -l)
print_status "✓ Package contains $PACKAGE_CONTENTS files/directories"

# Restore original environment
cd hotel-management-project 2>/dev/null || cd laravel12_migracion
cp .env.production .env.production.backup
git checkout .env 2>/dev/null || cp .env.example .env 2>/dev/null || true

print_header "🎉 Deployment Package Ready!"
echo ""
echo "📦 Package: ${DEPLOY_NAME}.tar.gz"
echo "📋 Instructions: ${DEPLOY_NAME}_INSTALLATION.txt"
echo "🌐 Target Domain: casaviejagt.com"
echo "🗄️  Database: casaviejagt_hotel_management"
echo ""
echo "Upload these files to your cPanel hosting:"
echo "1. ${DEPLOY_NAME}.tar.gz"
echo "2. ${DEPLOY_NAME}_INSTALLATION.txt"
echo ""
echo "Then follow the deployment instructions for a smooth installation."
echo ""
echo "🚀 Ready for production deployment!"