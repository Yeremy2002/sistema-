#!/bin/bash

# Hotel Management System - Automated Backup Script
# This script creates comprehensive backups of the application

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BACKUP_BASE_DIR="${BACKUP_DIR:-/var/backups/hotel-management}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
S3_BUCKET="${S3_BUCKET:-}"
LOG_FILE="/var/log/hotel-management/backup.log"

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

# Create backup directory structure
setup_backup_directories() {
    local timestamp=$(date +%Y%m%d_%H%M%S)
    BACKUP_DIR="$BACKUP_BASE_DIR/$timestamp"

    mkdir -p "$BACKUP_DIR"/{database,storage,config,logs}

    log "Backup directory created: $BACKUP_DIR"
}

# Load environment variables
load_environment() {
    if [[ -f "$PROJECT_ROOT/.env" ]]; then
        source "$PROJECT_ROOT/.env"
    else
        error ".env file not found"
    fi
}

# Backup MySQL database
backup_mysql_database() {
    log "Backing up MySQL database..."

    local db_container="hotel-mysql"

    if ! docker ps | grep -q "$db_container"; then
        error "MySQL container is not running"
    fi

    # Create database dump
    docker exec "$db_container" mysqldump \
        -u "$DB_USERNAME" \
        -p"$DB_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --add-drop-database \
        --add-drop-table \
        --create-options \
        --disable-keys \
        --extended-insert \
        --quick \
        --lock-tables=false \
        "$DB_DATABASE" > "$BACKUP_DIR/database/database.sql"

    if [[ $? -eq 0 ]]; then
        # Compress database dump
        gzip "$BACKUP_DIR/database/database.sql"
        success "MySQL database backup completed"
    else
        error "MySQL database backup failed"
    fi

    # Create database schema only backup
    docker exec "$db_container" mysqldump \
        -u "$DB_USERNAME" \
        -p"$DB_PASSWORD" \
        --no-data \
        --routines \
        --triggers \
        --events \
        "$DB_DATABASE" > "$BACKUP_DIR/database/schema.sql"

    gzip "$BACKUP_DIR/database/schema.sql"
    success "Database schema backup completed"
}

# Backup PostgreSQL database (alternative)
backup_postgresql_database() {
    log "Backing up PostgreSQL database..."

    local db_container="hotel-postgres"

    if ! docker ps | grep -q "$db_container"; then
        error "PostgreSQL container is not running"
    fi

    # Create database dump
    docker exec "$db_container" pg_dump \
        -U "$DB_USERNAME" \
        -d "$DB_DATABASE" \
        --verbose \
        --clean \
        --if-exists \
        --create \
        --format=custom > "$BACKUP_DIR/database/database.dump"

    if [[ $? -eq 0 ]]; then
        success "PostgreSQL database backup completed"
    else
        error "PostgreSQL database backup failed"
    fi

    # Create SQL format backup
    docker exec "$db_container" pg_dump \
        -U "$DB_USERNAME" \
        -d "$DB_DATABASE" \
        --verbose \
        --clean \
        --if-exists \
        --create \
        --format=plain > "$BACKUP_DIR/database/database.sql"

    gzip "$BACKUP_DIR/database/database.sql"
    success "PostgreSQL SQL backup completed"
}

# Backup SQLite database
backup_sqlite_database() {
    log "Backing up SQLite database..."

    local sqlite_file="$PROJECT_ROOT/database/database.sqlite"

    if [[ -f "$sqlite_file" ]]; then
        cp "$sqlite_file" "$BACKUP_DIR/database/"
        gzip "$BACKUP_DIR/database/database.sqlite"
        success "SQLite database backup completed"
    else
        warning "SQLite database file not found"
    fi
}

# Backup storage files
backup_storage() {
    log "Backing up storage files..."

    if [[ -d "$PROJECT_ROOT/storage" ]]; then
        # Copy storage directory
        cp -r "$PROJECT_ROOT/storage" "$BACKUP_DIR/"

        # Create compressed archive
        tar -czf "$BACKUP_DIR/storage.tar.gz" -C "$BACKUP_DIR" storage
        rm -rf "$BACKUP_DIR/storage"

        success "Storage backup completed"
    else
        warning "Storage directory not found"
    fi
}

# Backup configuration files
backup_configuration() {
    log "Backing up configuration files..."

    # Backup .env file
    if [[ -f "$PROJECT_ROOT/.env" ]]; then
        cp "$PROJECT_ROOT/.env" "$BACKUP_DIR/config/"
    fi

    # Backup docker-compose.yml
    if [[ -f "$PROJECT_ROOT/docker-compose.yml" ]]; then
        cp "$PROJECT_ROOT/docker-compose.yml" "$BACKUP_DIR/config/"
    fi

    # Backup nginx configuration
    if [[ -d "$PROJECT_ROOT/docker/nginx" ]]; then
        cp -r "$PROJECT_ROOT/docker/nginx" "$BACKUP_DIR/config/"
    fi

    # Backup custom configuration files
    if [[ -d "$PROJECT_ROOT/config" ]]; then
        cp -r "$PROJECT_ROOT/config" "$BACKUP_DIR/config/laravel-config"
    fi

    success "Configuration backup completed"
}

# Backup application logs
backup_logs() {
    log "Backing up application logs..."

    # Laravel logs
    if [[ -d "$PROJECT_ROOT/storage/logs" ]]; then
        cp -r "$PROJECT_ROOT/storage/logs" "$BACKUP_DIR/logs/laravel"
    fi

    # Docker logs
    if command -v docker &> /dev/null; then
        local containers=("hotel-app" "hotel-mysql" "hotel-redis" "hotel-queue-worker")

        mkdir -p "$BACKUP_DIR/logs/docker"

        for container in "${containers[@]}"; do
            if docker ps -a | grep -q "$container"; then
                docker logs "$container" > "$BACKUP_DIR/logs/docker/${container}.log" 2>&1
            fi
        done
    fi

    # System logs
    if [[ -d "/var/log/hotel-management" ]]; then
        cp -r "/var/log/hotel-management" "$BACKUP_DIR/logs/system"
    fi

    success "Logs backup completed"
}

# Create backup metadata
create_backup_metadata() {
    log "Creating backup metadata..."

    cat > "$BACKUP_DIR/backup_info.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "hostname": "$(hostname)",
    "backup_type": "full",
    "application": "Hotel Management System",
    "environment": "${APP_ENV:-production}",
    "database_type": "${DB_CONNECTION:-sqlite}",
    "laravel_version": "$(cd "$PROJECT_ROOT" && php artisan --version 2>/dev/null || echo 'Unknown')",
    "git_commit": "$(cd "$PROJECT_ROOT" && git rev-parse HEAD 2>/dev/null || echo 'N/A')",
    "git_branch": "$(cd "$PROJECT_ROOT" && git branch --show-current 2>/dev/null || echo 'N/A')",
    "backup_size": "$(du -sh "$BACKUP_DIR" | cut -f1)",
    "files_count": $(find "$BACKUP_DIR" -type f | wc -l),
    "created_by": "$(whoami)",
    "retention_until": "$(date -d "+${RETENTION_DAYS} days" -Iseconds)"
}
EOF

    success "Backup metadata created"
}

# Verify backup integrity
verify_backup() {
    log "Verifying backup integrity..."

    local errors=0

    # Check if database backup exists
    if [[ "$DB_CONNECTION" == "mysql" ]]; then
        if [[ ! -f "$BACKUP_DIR/database/database.sql.gz" ]]; then
            error "MySQL database backup not found"
            ((errors++))
        fi
    elif [[ "$DB_CONNECTION" == "pgsql" ]]; then
        if [[ ! -f "$BACKUP_DIR/database/database.dump" ]]; then
            error "PostgreSQL database backup not found"
            ((errors++))
        fi
    elif [[ "$DB_CONNECTION" == "sqlite" ]]; then
        if [[ ! -f "$BACKUP_DIR/database/database.sqlite.gz" ]]; then
            warning "SQLite database backup not found"
        fi
    fi

    # Check storage backup
    if [[ ! -f "$BACKUP_DIR/storage.tar.gz" ]]; then
        warning "Storage backup not found"
    fi

    # Check configuration backup
    if [[ ! -f "$BACKUP_DIR/config/.env" ]]; then
        error "Environment configuration backup not found"
        ((errors++))
    fi

    # Check backup metadata
    if [[ ! -f "$BACKUP_DIR/backup_info.json" ]]; then
        error "Backup metadata not found"
        ((errors++))
    fi

    if [[ $errors -eq 0 ]]; then
        success "Backup integrity verification passed"
    else
        error "Backup integrity verification failed with $errors errors"
    fi
}

# Upload to S3 (if configured)
upload_to_s3() {
    if [[ -z "$S3_BUCKET" ]]; then
        log "S3 upload not configured, skipping..."
        return
    fi

    log "Uploading backup to S3..."

    # Check if AWS CLI is installed
    if ! command -v aws &> /dev/null; then
        warning "AWS CLI not installed, skipping S3 upload"
        return
    fi

    # Create compressed archive of entire backup
    local backup_archive="${BACKUP_DIR}.tar.gz"
    tar -czf "$backup_archive" -C "$BACKUP_BASE_DIR" "$(basename "$BACKUP_DIR")"

    # Upload to S3
    aws s3 cp "$backup_archive" "s3://$S3_BUCKET/backups/$(basename "$backup_archive")"

    if [[ $? -eq 0 ]]; then
        success "Backup uploaded to S3"
        rm "$backup_archive"
    else
        error "S3 upload failed"
    fi
}

# Cleanup old backups
cleanup_old_backups() {
    log "Cleaning up backups older than $RETENTION_DAYS days..."

    # Local cleanup
    find "$BACKUP_BASE_DIR" -type d -name "*_*" -mtime +$RETENTION_DAYS -exec rm -rf {} + 2>/dev/null || true

    # S3 cleanup (if configured)
    if [[ -n "$S3_BUCKET" ]] && command -v aws &> /dev/null; then
        aws s3 ls "s3://$S3_BUCKET/backups/" | while read -r line; do
            local backup_date=$(echo "$line" | awk '{print $1}')
            local backup_file=$(echo "$line" | awk '{print $4}')

            if [[ -n "$backup_date" ]] && [[ -n "$backup_file" ]]; then
                local backup_timestamp=$(date -d "$backup_date" +%s)
                local cutoff_timestamp=$(date -d "-${RETENTION_DAYS} days" +%s)

                if [[ $backup_timestamp -lt $cutoff_timestamp ]]; then
                    aws s3 rm "s3://$S3_BUCKET/backups/$backup_file"
                    log "Deleted old S3 backup: $backup_file"
                fi
            fi
        done
    fi

    success "Old backups cleaned up"
}

# Send notification (if configured)
send_notification() {
    local status="$1"
    local message="$2"

    # Email notification (if configured)
    if command -v mail &> /dev/null && [[ -n "${NOTIFICATION_EMAIL:-}" ]]; then
        echo "$message" | mail -s "Hotel Management Backup $status" "$NOTIFICATION_EMAIL"
    fi

    # Slack notification (if configured)
    if [[ -n "${SLACK_WEBHOOK_URL:-}" ]]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"Hotel Management Backup $status: $message\"}" \
            "$SLACK_WEBHOOK_URL" &> /dev/null || true
    fi
}

# Main backup function
main() {
    log "Starting backup process..."

    # Create log directory
    mkdir -p "$(dirname "$LOG_FILE")"

    # Load environment
    load_environment

    # Setup backup directories
    setup_backup_directories

    # Perform backup operations
    case "$DB_CONNECTION" in
        mysql)
            backup_mysql_database
            ;;
        pgsql)
            backup_postgresql_database
            ;;
        sqlite)
            backup_sqlite_database
            ;;
        *)
            warning "Unknown database connection type: $DB_CONNECTION"
            ;;
    esac

    backup_storage
    backup_configuration
    backup_logs
    create_backup_metadata
    verify_backup
    upload_to_s3
    cleanup_old_backups

    local backup_size=$(du -sh "$BACKUP_DIR" | cut -f1)
    success "Backup completed successfully! Size: $backup_size, Location: $BACKUP_DIR"

    send_notification "SUCCESS" "Backup completed successfully. Size: $backup_size"
}

# Show help
show_help() {
    cat << EOF
Hotel Management System - Backup Script

Usage: $0 [OPTIONS]

Options:
    -h, --help              Show this help message
    -r, --retention DAYS    Set retention period (default: 30 days)
    -d, --destination DIR   Set backup destination directory
    -s, --s3-bucket BUCKET  Set S3 bucket for remote backup

Environment Variables:
    BACKUP_DIR              Base backup directory (default: /var/backups/hotel-management)
    RETENTION_DAYS          Backup retention period (default: 30)
    S3_BUCKET               S3 bucket for remote backup
    NOTIFICATION_EMAIL      Email for backup notifications
    SLACK_WEBHOOK_URL       Slack webhook for notifications

Examples:
    $0                      # Standard backup
    $0 -r 7                 # Keep backups for 7 days
    $0 -s my-backup-bucket  # Upload to S3 bucket

EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -r|--retention)
            RETENTION_DAYS="$2"
            shift 2
            ;;
        -d|--destination)
            BACKUP_BASE_DIR="$2"
            shift 2
            ;;
        -s|--s3-bucket)
            S3_BUCKET="$2"
            shift 2
            ;;
        *)
            error "Unknown option: $1"
            ;;
    esac
done

# Run main function
main