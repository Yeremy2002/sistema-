#!/bin/bash

# Hotel Management System - Production Setup Script for cPanel
# This script prepares the Laravel application for production deployment

set -e

echo "🏨 Hotel Management System - Production Setup"
echo "============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
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

print_header "1. Installing Composer Dependencies for Production"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

print_header "2. Installing and Building Frontend Assets"
if [ -f "package.json" ]; then
    npm ci --omit=dev
    npm run build
    print_status "Frontend assets built successfully"
else
    print_warning "No package.json found, skipping npm build"
fi

print_header "3. Setting up Production Environment"
if [ -f ".env.production" ]; then
    cp .env.production .env
    print_status "Production environment file copied"
else
    print_error ".env.production file not found"
    exit 1
fi

print_header "4. Optimizing Laravel for Production"

# Clear all caches first
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Generate optimized files
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

print_status "Laravel optimized for production"

print_header "5. Setting up Storage and Cache Directories"
php artisan storage:link

# Create cache directories if they don't exist
mkdir -p bootstrap/cache
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs

print_status "Storage directories configured"

print_header "6. Setting File Permissions"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage
chmod -R 775 bootstrap/cache

print_status "File permissions set correctly"

print_header "7. Creating Deployment Package"
# Create a deployment directory
DEPLOY_DIR="hotel_management_production_$(date +%Y%m%d_%H%M%S)"
mkdir -p "../$DEPLOY_DIR"

# Copy all necessary files except development files
rsync -av --exclude-from=deploy/exclude_files.txt . "../$DEPLOY_DIR/"

# Create compressed archive
cd ..
tar -czf "${DEPLOY_DIR}.tar.gz" "$DEPLOY_DIR"
rm -rf "$DEPLOY_DIR"

print_status "Deployment package created: ${DEPLOY_DIR}.tar.gz"

print_header "Production Setup Complete!"
echo ""
echo "📦 Deployment package: ${DEPLOY_DIR}.tar.gz"
echo "🌐 Domain: casaviejagt.com"
echo "🗄️  Database: casaviejagt_hotel_management"
echo ""
echo "Next steps:"
echo "1. Upload the .tar.gz file to your cPanel File Manager"
echo "2. Extract it to public_html"
echo "3. Run the database setup script"
echo "4. Configure cron jobs"
echo ""
echo "See DEPLOYMENT_INSTRUCTIONS.md for detailed steps"