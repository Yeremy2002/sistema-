#!/bin/bash

# Hotel Management System - File Permissions Setup for cPanel
# This script sets the correct file permissions for Laravel in a shared hosting environment

set -e

echo "🔒 Setting up file permissions for cPanel hosting"
echo "================================================="

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the Laravel project root
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel project root directory"
    exit 1
fi

echo "Setting base permissions..."

# Set directory permissions to 755 (owner: rwx, group: rx, others: rx)
find . -type d -exec chmod 755 {} \;
print_status "Directory permissions set to 755"

# Set file permissions to 644 (owner: rw, group: r, others: r)
find . -type f -exec chmod 644 {} \;
print_status "File permissions set to 644"

echo ""
echo "Setting Laravel-specific permissions..."

# Storage directory needs to be writable (775)
if [ -d "storage" ]; then
    chmod -R 775 storage
    print_status "Storage directory set to 775"
else
    print_warning "Storage directory not found"
fi

# Bootstrap cache needs to be writable (775)
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    print_status "Bootstrap cache directory set to 775"
else
    mkdir -p bootstrap/cache
    chmod -R 775 bootstrap/cache
    print_status "Bootstrap cache directory created and set to 775"
fi

# Make artisan executable
if [ -f "artisan" ]; then
    chmod 755 artisan
    print_status "Artisan command made executable"
fi

# Set specific permissions for sensitive files
echo ""
echo "Securing sensitive files..."

# Environment file should be readable only by owner
if [ -f ".env" ]; then
    chmod 600 .env
    print_status ".env file secured (600)"
fi

# Configuration files
if [ -d "config" ]; then
    chmod -R 644 config/*
    print_status "Configuration files secured"
fi

# Public directory - web server needs access
if [ -d "public" ]; then
    chmod -R 755 public
    print_status "Public directory permissions set"
fi

# Vendor directory
if [ -d "vendor" ]; then
    find vendor -type d -exec chmod 755 {} \;
    find vendor -type f -exec chmod 644 {} \;
    print_status "Vendor directory permissions set"
fi

# Node modules (if present)
if [ -d "node_modules" ]; then
    print_warning "Node modules found - these should not be in production"
    print_warning "Consider removing node_modules and using built assets only"
fi

echo ""
echo "Creating additional required directories..."

# Ensure all Laravel directories exist with correct permissions
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

chmod -R 775 storage
print_status "All storage subdirectories created and secured"

# Create symbolic link for public storage (if not exists)
if [ ! -L "public/storage" ]; then
    ln -sf ../storage/app/public public/storage
    print_status "Storage symbolic link created"
fi

echo ""
echo "Setting SELinux contexts (if applicable)..."

# Set SELinux contexts for shared hosting that uses SELinux
if command -v setsebool >/dev/null 2>&1; then
    if sestatus 2>/dev/null | grep -q "enabled"; then
        print_warning "SELinux detected - you may need to set additional contexts"
        echo "If you encounter permission issues, contact your hosting provider"
        echo "or run: setsebool -P httpd_can_network_connect 1"
    fi
fi

echo ""
echo "📋 Permission Summary:"
echo "====================="
echo "✓ Directories: 755 (rwxr-xr-x)"
echo "✓ Files: 644 (rw-r--r--)"
echo "✓ Storage: 775 (rwxrwxr-x)"
echo "✓ Bootstrap cache: 775 (rwxrwxr-x)"
echo "✓ .env file: 600 (rw-------)"
echo "✓ Artisan: 755 (rwxr-xr-x)"
echo ""
echo "🔒 File permissions setup complete!"
echo ""
echo "If you encounter any permission issues:"
echo "1. Contact your hosting provider"
echo "2. Check if your hosting uses different permission requirements"
echo "3. Verify that the web server user has access to storage directories"