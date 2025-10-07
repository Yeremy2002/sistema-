#!/bin/bash

# Hotel Management System - Restore Script
# This script restores the application from a backup

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BACKUP_BASE_DIR="${BACKUP_DIR:-/var/backups/hotel-management}"
LOG_FILE="/var/log/hotel-management/restore.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# List available backups
list_backups() {
    log "Available backups:"
    echo

    if [[ ! -d "$BACKUP_BASE_DIR" ]]; then
        error "Backup directory not found: $BACKUP_BASE_DIR"
    fi

    local backups=($(find "$BACKUP_BASE_DIR" -maxdepth 1 -type d -name "*_*" | sort -r))

    if [[ ${#backups[@]} -eq 0 ]]; then
        error "No backups found in $BACKUP_BASE_DIR"
    fi

    local count=1
    for backup in "${backups[@]}"; do
        local backup_name=$(basename "$backup")
        local backup_date=$(echo "$backup_name" | cut -d'_' -f1)
        local backup_time=$(echo "$backup_name" | cut -d'_' -f2)
        local formatted_date=$(date -d "${backup_date:0:4}-${backup_date:4:2}-${backup_date:6:2}" "+%Y-%m-%d" 2>/dev/null || echo "$backup_date")
        local formatted_time=$(echo "$backup_time" | sed 's/\(..\)\(..\)\(..\)/\1:\2:\3/')
        local size=$(du -sh "$backup" | cut -f1)

        echo "  $count) $backup_name"
        echo "     Date: $formatted_date $formatted_time"
        echo "     Size: $size"

        # Show backup metadata if available
        if [[ -f "$backup/backup_info.json" ]]; then
            local git_commit=$(jq -r '.git_commit // "N/A"' "$backup/backup_info.json" 2>/dev/null || echo "N/A")
            local environment=$(jq -r '.environment // "Unknown"' "$backup/backup_info.json" 2>/dev/null || echo "Unknown")
            echo "     Environment: $environment"
            echo "     Git Commit: ${git_commit:0:8}"
        fi

        echo

        ((count++))
    done
}

# Select backup to restore
select_backup() {
    list_backups

    echo "Enter the number of the backup to restore (or 'q' to quit):"
    read -r choice

    if [[ "$choice" == "q" ]]; then
        log "Restore cancelled by user"
        exit 0
    fi

    if ! [[ "$choice" =~ ^[0-9]+$ ]]; then
        error "Invalid selection: $choice"
    fi

    local backups=($(find "$BACKUP_BASE_DIR" -maxdepth 1 -type d -name "*_*" | sort -r))
    local index=$((choice - 1))

    if [[ $index -lt 0 || $index -ge ${#backups[@]} ]]; then
        error "Invalid backup selection: $choice"
    fi

    SELECTED_BACKUP="${backups[$index]}"
    log "Selected backup: $(basename "$SELECTED_BACKUP")"
}

# Verify backup integrity
verify_backup_integrity() {
    log "Verifying backup integrity..."

    if [[ ! -f "$SELECTED_BACKUP/backup_info.json" ]]; then
        warning "Backup metadata not found, proceeding anyway..."
    else
        log "Backup metadata found and valid"
    fi

    # Check for essential files
    local required_files=(".env")
    local missing_files=()

    for file in "${required_files[@]}"; do
        if [[ ! -f "$SELECTED_BACKUP/config/$file" ]]; then
            missing_files+=("$file")
        fi
    done

    if [[ ${#missing_files[@]} -gt 0 ]]; then
        error "Missing required files: ${missing_files[*]}"
    fi

    success "Backup integrity verification passed"
}

# Create pre-restore backup
create_pre_restore_backup() {
    log "Creating pre-restore backup..."

    local timestamp=$(date +%Y%m%d_%H%M%S)
    local pre_restore_backup_dir="$BACKUP_BASE_DIR/pre-restore-$timestamp"

    mkdir -p "$pre_restore_backup_dir"

    # Backup current state
    if [[ -f "$PROJECT_ROOT/.env" ]]; then
        mkdir -p "$pre_restore_backup_dir/config"
        cp "$PROJECT_ROOT/.env" "$pre_restore_backup_dir/config/"
    fi

    if [[ -d "$PROJECT_ROOT/storage" ]]; then
        cp -r "$PROJECT_ROOT/storage" "$pre_restore_backup_dir/"
    fi

    # Create metadata
    cat > "$pre_restore_backup_dir/backup_info.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "type": "pre-restore",
    "created_by": "restore script",
    "original_backup": "$(basename "$SELECTED_BACKUP")"
}
EOF

    success "Pre-restore backup created: $pre_restore_backup_dir"
    echo "$pre_restore_backup_dir" > /tmp/hotel_pre_restore_backup
}

# Stop application services
stop_services() {
    log "Stopping application services..."

    cd "$PROJECT_ROOT"

    # Stop Docker containers
    docker-compose down --timeout 30

    success "Services stopped"
}

# Restore database
restore_database() {
    log "Restoring database..."

    # Load environment to get database type
    if [[ -f "$SELECTED_BACKUP/config/.env" ]]; then
        source "$SELECTED_BACKUP/config/.env"
    else
        error "Environment file not found in backup"
    fi

    case "$DB_CONNECTION" in
        mysql)
            restore_mysql_database
            ;;
        pgsql)
            restore_postgresql_database
            ;;
        sqlite)
            restore_sqlite_database
            ;;
        *)
            error "Unsupported database connection: $DB_CONNECTION"
            ;;
    esac
}

# Restore MySQL database
restore_mysql_database() {
    log "Restoring MySQL database..."

    # Start MySQL container
    cd "$PROJECT_ROOT"
    docker-compose up -d mysql
    sleep 20

    # Check for database backup
    local db_backup=""
    if [[ -f "$SELECTED_BACKUP/database/database.sql.gz" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.sql.gz"
    elif [[ -f "$SELECTED_BACKUP/database/database.sql" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.sql"
    else
        error "MySQL database backup not found"
    fi

    # Restore database
    if [[ "$db_backup" == *.gz ]]; then
        gunzip -c "$db_backup" | docker exec -i hotel-mysql mysql \
            -u "$DB_USERNAME" \
            -p"$DB_PASSWORD"
    else
        docker exec -i hotel-mysql mysql \
            -u "$DB_USERNAME" \
            -p"$DB_PASSWORD" < "$db_backup"
    fi

    if [[ $? -eq 0 ]]; then
        success "MySQL database restored successfully"
    else
        error "MySQL database restore failed"
    fi
}

# Restore PostgreSQL database
restore_postgresql_database() {
    log "Restoring PostgreSQL database..."

    # Start PostgreSQL container
    cd "$PROJECT_ROOT"
    docker-compose up -d postgres
    sleep 20

    # Check for database backup
    local db_backup=""
    if [[ -f "$SELECTED_BACKUP/database/database.dump" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.dump"
    elif [[ -f "$SELECTED_BACKUP/database/database.sql.gz" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.sql.gz"
    elif [[ -f "$SELECTED_BACKUP/database/database.sql" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.sql"
    else
        error "PostgreSQL database backup not found"
    fi

    # Drop and recreate database
    docker exec hotel-postgres dropdb -U "$DB_USERNAME" "$DB_DATABASE" --if-exists
    docker exec hotel-postgres createdb -U "$DB_USERNAME" "$DB_DATABASE"

    # Restore database
    if [[ "$db_backup" == *.dump ]]; then
        docker exec -i hotel-postgres pg_restore \
            -U "$DB_USERNAME" \
            -d "$DB_DATABASE" \
            --verbose \
            --clean \
            --if-exists < "$db_backup"
    elif [[ "$db_backup" == *.gz ]]; then
        gunzip -c "$db_backup" | docker exec -i hotel-postgres psql \
            -U "$DB_USERNAME" \
            -d "$DB_DATABASE"
    else
        docker exec -i hotel-postgres psql \
            -U "$DB_USERNAME" \
            -d "$DB_DATABASE" < "$db_backup"
    fi

    if [[ $? -eq 0 ]]; then
        success "PostgreSQL database restored successfully"
    else
        error "PostgreSQL database restore failed"
    fi
}

# Restore SQLite database
restore_sqlite_database() {
    log "Restoring SQLite database..."

    local db_backup=""
    if [[ -f "$SELECTED_BACKUP/database/database.sqlite.gz" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.sqlite.gz"
    elif [[ -f "$SELECTED_BACKUP/database/database.sqlite" ]]; then
        db_backup="$SELECTED_BACKUP/database/database.sqlite"
    else
        error "SQLite database backup not found"
    fi

    # Remove existing database
    rm -f "$PROJECT_ROOT/database/database.sqlite"

    # Restore database
    if [[ "$db_backup" == *.gz ]]; then
        gunzip -c "$db_backup" > "$PROJECT_ROOT/database/database.sqlite"
    else
        cp "$db_backup" "$PROJECT_ROOT/database/database.sqlite"
    fi

    success "SQLite database restored successfully"
}

# Restore storage files
restore_storage() {
    log "Restoring storage files..."

    # Remove existing storage
    if [[ -d "$PROJECT_ROOT/storage" ]]; then
        rm -rf "$PROJECT_ROOT/storage"
    fi

    # Restore from backup
    if [[ -f "$SELECTED_BACKUP/storage.tar.gz" ]]; then
        tar -xzf "$SELECTED_BACKUP/storage.tar.gz" -C "$PROJECT_ROOT/"
        success "Storage files restored from archive"
    elif [[ -d "$SELECTED_BACKUP/storage" ]]; then
        cp -r "$SELECTED_BACKUP/storage" "$PROJECT_ROOT/"
        success "Storage files restored from directory"
    else
        warning "Storage backup not found, creating default storage structure"
        mkdir -p "$PROJECT_ROOT/storage"/{app,framework,logs}
        mkdir -p "$PROJECT_ROOT/storage/framework"/{cache,sessions,views}
    fi

    # Set proper permissions
    chown -R www-data:www-data "$PROJECT_ROOT/storage" 2>/dev/null || true
    chmod -R 775 "$PROJECT_ROOT/storage"
}

# Restore configuration
restore_configuration() {
    log "Restoring configuration files..."

    # Restore .env file
    if [[ -f "$SELECTED_BACKUP/config/.env" ]]; then
        cp "$SELECTED_BACKUP/config/.env" "$PROJECT_ROOT/"
        success "Environment configuration restored"
    else
        error "Environment configuration not found in backup"
    fi

    # Restore other configuration files if they exist
    if [[ -f "$SELECTED_BACKUP/config/docker-compose.yml" ]]; then
        cp "$SELECTED_BACKUP/config/docker-compose.yml" "$PROJECT_ROOT/"
        log "Docker Compose configuration restored"
    fi

    if [[ -d "$SELECTED_BACKUP/config/nginx" ]]; then
        cp -r "$SELECTED_BACKUP/config/nginx" "$PROJECT_ROOT/docker/"
        log "Nginx configuration restored"
    fi
}

# Start services and verify
start_and_verify() {
    log "Starting services..."

    cd "$PROJECT_ROOT"

    # Start all services
    docker-compose up -d

    # Wait for services to start
    log "Waiting for services to start..."
    sleep 60

    # Verify database connection
    log "Verifying database connection..."
    docker-compose exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database OK';"

    if [[ $? -ne 0 ]]; then
        error "Database connection verification failed"
    fi

    # Clear and rebuild caches
    log "Rebuilding application caches..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache

    # Health check
    log "Performing application health check..."
    local max_attempts=10
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

    success "Services started and verified successfully"
}

# Main restore function
main() {
    log "Starting restore process..."

    # Create log directory
    mkdir -p "$(dirname "$LOG_FILE")"

    # If backup path is provided as argument, use it
    if [[ -n "${1:-}" ]]; then
        if [[ -d "$1" ]]; then
            SELECTED_BACKUP="$1"
            log "Using provided backup path: $SELECTED_BACKUP"
        else
            error "Provided backup path does not exist: $1"
        fi
    else
        select_backup
    fi

    verify_backup_integrity

    # Confirmation prompt
    echo
    warning "This will restore the application from backup: $(basename "$SELECTED_BACKUP")"
    warning "All current data will be replaced!"
    echo
    read -p "Are you sure you want to continue? (yes/no): " -r confirmation

    if [[ "$confirmation" != "yes" ]]; then
        log "Restore cancelled by user"
        exit 0
    fi

    create_pre_restore_backup
    stop_services
    restore_configuration
    restore_database
    restore_storage
    start_and_verify

    success "Restore completed successfully!"

    echo
    echo "=========================================="
    echo "Restore Summary"
    echo "=========================================="
    echo "Restored from: $(basename "$SELECTED_BACKUP")"
    echo "Pre-restore backup: $(cat /tmp/hotel_pre_restore_backup 2>/dev/null || echo 'Not created')"
    echo "Application URL: $(grep APP_URL "$PROJECT_ROOT/.env" | cut -d'=' -f2)"
    echo
    echo "Please verify that the application is working correctly."
    echo "If there are issues, you can restore from the pre-restore backup."
    echo
}

# Show help
show_help() {
    cat << EOF
Hotel Management System - Restore Script

Usage: $0 [backup_path]

Arguments:
    backup_path             Path to specific backup directory (optional)

If no backup path is provided, the script will show available backups
and prompt you to select one.

Examples:
    $0                                          # Interactive mode
    $0 /var/backups/hotel-management/20241225_120000  # Restore specific backup

The script will:
1. List available backups (if no path provided)
2. Verify backup integrity
3. Create a pre-restore backup
4. Stop application services
5. Restore database, storage, and configuration
6. Start services and verify functionality

EOF
}

case "${1:-}" in
    -h|--help)
        show_help
        exit 0
        ;;
    *)
        main "$@"
        ;;
esac