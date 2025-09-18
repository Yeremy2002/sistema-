#!/bin/bash

# Hotel Management System - Production Deployment Script
# Usage: ./deploy.sh [environment] [version]

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
ENVIRONMENT="${1:-production}"
VERSION="${2:-latest}"
BACKUP_DIR="/var/backups/hotel-management"
LOG_FILE="/var/log/hotel-deployment.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

success() {
    echo -e "${GREEN}[SUCCESS] $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}" | tee -a "$LOG_FILE"
}

# Check if running as root or with sudo
check_permissions() {
    if [[ $EUID -eq 0 ]]; then
        warning "Running as root. This is not recommended for production deployments."
    fi
}

# Pre-deployment checks
pre_deployment_checks() {
    log "Running pre-deployment checks..."

    # Check if Docker is installed and running
    if ! command -v docker &> /dev/null; then
        error "Docker is not installed"
    fi

    if ! docker info &> /dev/null; then
        error "Docker daemon is not running"
    fi

    # Check if docker-compose is available
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        error "Docker Compose is not installed"
    fi

    # Check if .env file exists
    if [[ ! -f "$PROJECT_ROOT/.env" ]]; then
        error ".env file not found. Please create it from .env.example"
    fi

    # Check if required directories exist
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$(dirname "$LOG_FILE")"

    success "Pre-deployment checks completed"
}

# Create application backup
create_backup() {
    log "Creating backup before deployment..."

    local backup_timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_path="$BACKUP_DIR/backup_$backup_timestamp"

    mkdir -p "$backup_path"

    # Backup database if MySQL container is running
    if docker ps | grep -q hotel-mysql; then
        log "Backing up MySQL database..."
        docker exec hotel-mysql mysqldump \
            -u "$(grep DB_USERNAME "$PROJECT_ROOT/.env" | cut -d'=' -f2)" \
            -p"$(grep DB_PASSWORD "$PROJECT_ROOT/.env" | cut -d'=' -f2)" \
            "$(grep DB_DATABASE "$PROJECT_ROOT/.env" | cut -d'=' -f2)" \
            > "$backup_path/database.sql"

        if [[ $? -eq 0 ]]; then
            success "Database backup created"
        else
            error "Database backup failed"
        fi
    fi

    # Backup storage files
    if [[ -d "$PROJECT_ROOT/storage" ]]; then
        log "Backing up storage files..."
        cp -r "$PROJECT_ROOT/storage" "$backup_path/"
        success "Storage backup created"
    fi

    # Backup .env file
    cp "$PROJECT_ROOT/.env" "$backup_path/"

    # Create backup info file
    cat > "$backup_path/backup_info.txt" << EOF
Backup created: $(date)
Environment: $ENVIRONMENT
Version: $VERSION
Git commit: $(cd "$PROJECT_ROOT" && git rev-parse HEAD 2>/dev/null || echo "N/A")
EOF

    success "Backup created at $backup_path"
    echo "$backup_path" > /tmp/hotel_backup_path
}

# Build and deploy application
deploy_application() {
    log "Deploying application (Environment: $ENVIRONMENT, Version: $VERSION)..."

    cd "$PROJECT_ROOT"

    # Pull latest code if this is a git repository
    if [[ -d .git ]]; then
        log "Pulling latest code..."
        git pull origin main || warning "Git pull failed, continuing with current code"
    fi

    # Build Docker images
    log "Building Docker images..."
    docker-compose build --no-cache

    if [[ $? -ne 0 ]]; then
        error "Docker build failed"
    fi

    # Stop existing containers gracefully
    log "Stopping existing containers..."
    docker-compose down --timeout 30

    # Start new containers
    log "Starting new containers..."
    docker-compose up -d

    if [[ $? -ne 0 ]]; then
        error "Container startup failed"
    fi

    # Wait for containers to be ready
    log "Waiting for containers to be ready..."
    sleep 30

    # Run database migrations
    log "Running database migrations..."
    docker-compose exec -T app php artisan migrate --force

    if [[ $? -ne 0 ]]; then
        error "Database migrations failed"
    fi

    # Clear and optimize caches
    log "Optimizing application..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    docker-compose exec -T app php artisan event:cache

    success "Application deployed successfully"
}

# Post-deployment verification
post_deployment_verification() {
    log "Running post-deployment verification..."

    # Check if containers are running
    local running_containers=$(docker-compose ps -q)
    if [[ -z "$running_containers" ]]; then
        error "No containers are running"
    fi

    # Health check
    log "Performing health checks..."

    # Wait for application to be ready
    local max_attempts=30
    local attempt=1

    while [[ $attempt -le $max_attempts ]]; do
        if curl -f http://localhost:8080/health &> /dev/null; then
            success "Application health check passed"
            break
        else
            log "Health check attempt $attempt/$max_attempts failed, retrying..."
            sleep 10
            ((attempt++))
        fi
    done

    if [[ $attempt -gt $max_attempts ]]; then
        error "Application health check failed after $max_attempts attempts"
    fi

    # Check database connectivity
    log "Checking database connectivity..."
    docker-compose exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection OK';"

    if [[ $? -ne 0 ]]; then
        error "Database connectivity check failed"
    fi

    # Check queue workers
    log "Checking queue workers..."
    if docker-compose ps | grep -q queue-worker; then
        success "Queue workers are running"
    else
        warning "Queue workers are not running"
    fi

    # Check scheduler
    log "Checking scheduler..."
    if docker-compose ps | grep -q scheduler; then
        success "Scheduler is running"
    else
        warning "Scheduler is not running"
    fi

    success "Post-deployment verification completed"
}

# Rollback function
rollback() {
    local backup_path="$1"

    if [[ ! -d "$backup_path" ]]; then
        error "Backup path not found: $backup_path"
    fi

    log "Rolling back to backup: $backup_path"

    # Stop current containers
    docker-compose down

    # Restore database
    if [[ -f "$backup_path/database.sql" ]]; then
        log "Restoring database..."
        docker-compose up -d mysql
        sleep 15
        docker exec -i hotel-mysql mysql \
            -u "$(grep DB_USERNAME "$PROJECT_ROOT/.env" | cut -d'=' -f2)" \
            -p"$(grep DB_PASSWORD "$PROJECT_ROOT/.env" | cut -d'=' -f2)" \
            "$(grep DB_DATABASE "$PROJECT_ROOT/.env" | cut -d'=' -f2)" \
            < "$backup_path/database.sql"
    fi

    # Restore storage
    if [[ -d "$backup_path/storage" ]]; then
        log "Restoring storage..."
        rm -rf "$PROJECT_ROOT/storage"
        cp -r "$backup_path/storage" "$PROJECT_ROOT/"
    fi

    # Restore .env
    if [[ -f "$backup_path/.env" ]]; then
        cp "$backup_path/.env" "$PROJECT_ROOT/"
    fi

    # Start containers
    docker-compose up -d

    success "Rollback completed"
}

# Cleanup old backups (keep last 5)
cleanup_backups() {
    log "Cleaning up old backups..."

    cd "$BACKUP_DIR"
    ls -t | tail -n +6 | xargs -r rm -rf

    success "Backup cleanup completed"
}

# Main deployment function
main() {
    log "Starting deployment process..."
    log "Environment: $ENVIRONMENT"
    log "Version: $VERSION"

    check_permissions
    pre_deployment_checks
    create_backup

    # Deploy with error handling
    if deploy_application && post_deployment_verification; then
        success "Deployment completed successfully!"
        cleanup_backups
    else
        error "Deployment failed!"

        # Ask for rollback
        read -p "Do you want to rollback? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            local backup_path=$(cat /tmp/hotel_backup_path 2>/dev/null || echo "")
            if [[ -n "$backup_path" ]]; then
                rollback "$backup_path"
            else
                error "Backup path not found, manual rollback required"
            fi
        fi

        exit 1
    fi
}

# Script help
show_help() {
    cat << EOF
Hotel Management System - Deployment Script

Usage: $0 [environment] [version]

Arguments:
    environment  Target environment (default: production)
    version      Version to deploy (default: latest)

Examples:
    $0                          # Deploy to production with latest version
    $0 staging                  # Deploy to staging environment
    $0 production v1.2.3        # Deploy specific version to production

Options:
    -h, --help                  Show this help message

EOF
}

# Handle script arguments
case "${1:-}" in
    -h|--help)
        show_help
        exit 0
        ;;
    *)
        main "$@"
        ;;
esac