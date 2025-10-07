#!/bin/bash

# Hotel Management System - Optimization Script
# This script optimizes the application for better performance

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
LOG_FILE="/var/log/hotel-management/optimize.log"

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

# Clear and rebuild Laravel caches
optimize_laravel_caches() {
    log "Optimizing Laravel caches..."

    cd "$PROJECT_ROOT"

    # Clear all caches first
    log "Clearing existing caches..."
    docker-compose exec -T app php artisan config:clear
    docker-compose exec -T app php artisan route:clear
    docker-compose exec -T app php artisan view:clear
    docker-compose exec -T app php artisan cache:clear
    docker-compose exec -T app php artisan event:clear

    # Rebuild optimized caches
    log "Building optimized caches..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    docker-compose exec -T app php artisan event:cache

    # Optimize autoloader
    log "Optimizing Composer autoloader..."
    docker-compose exec -T app composer dump-autoload --optimize --no-dev

    success "Laravel caches optimized"
}

# Optimize database
optimize_database() {
    log "Optimizing database..."

    cd "$PROJECT_ROOT"

    # Load environment to check database type
    if [[ -f .env ]]; then
        source .env
    fi

    case "$DB_CONNECTION" in
        mysql)
            optimize_mysql_database
            ;;
        pgsql)
            optimize_postgresql_database
            ;;
        sqlite)
            optimize_sqlite_database
            ;;
        *)
            warning "Unknown database connection type: $DB_CONNECTION"
            ;;
    esac
}

# Optimize MySQL database
optimize_mysql_database() {
    log "Optimizing MySQL database..."

    # Analyze and optimize tables
    local tables=$(docker exec hotel-mysql mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE; SHOW TABLES;" | tail -n +2)

    while IFS= read -r table; do
        if [[ -n "$table" ]]; then
            log "Optimizing table: $table"
            docker exec hotel-mysql mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE; OPTIMIZE TABLE $table;"
        fi
    done <<< "$tables"

    # Update table statistics
    log "Updating table statistics..."
    docker exec hotel-mysql mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE; ANALYZE TABLE $(echo "$tables" | tr '\n' ',' | sed 's/,$//');"

    success "MySQL database optimized"
}

# Optimize PostgreSQL database
optimize_postgresql_database() {
    log "Optimizing PostgreSQL database..."

    # Vacuum and analyze
    log "Running VACUUM ANALYZE..."
    docker exec hotel-postgres psql -U "$DB_USERNAME" -d "$DB_DATABASE" -c "VACUUM ANALYZE;"

    # Reindex database
    log "Reindexing database..."
    docker exec hotel-postgres psql -U "$DB_USERNAME" -d "$DB_DATABASE" -c "REINDEX DATABASE $DB_DATABASE;"

    success "PostgreSQL database optimized"
}

# Optimize SQLite database
optimize_sqlite_database() {
    log "Optimizing SQLite database..."

    # Vacuum database
    docker-compose exec -T app php artisan tinker --execute="DB::statement('VACUUM;');"

    # Analyze database
    docker-compose exec -T app php artisan tinker --execute="DB::statement('ANALYZE;');"

    success "SQLite database optimized"
}

# Optimize Redis
optimize_redis() {
    log "Optimizing Redis..."

    # Get Redis info
    local redis_info=$(docker exec hotel-redis redis-cli info memory)
    local used_memory=$(echo "$redis_info" | grep "used_memory_human" | cut -d: -f2 | tr -d '\r')

    log "Redis memory usage: $used_memory"

    # Clean expired keys
    log "Cleaning expired keys..."
    docker exec hotel-redis redis-cli --scan --pattern "*" | while read -r key; do
        docker exec hotel-redis redis-cli ttl "$key" > /dev/null 2>&1
    done

    # Get stats after cleanup
    local redis_info_after=$(docker exec hotel-redis redis-cli info memory)
    local used_memory_after=$(echo "$redis_info_after" | grep "used_memory_human" | cut -d: -f2 | tr -d '\r')

    success "Redis optimized - Memory usage: $used_memory_after"
}

# Clean up old logs
cleanup_logs() {
    log "Cleaning up old logs..."

    local log_dirs=(
        "$PROJECT_ROOT/storage/logs"
        "/var/log/hotel-management"
        "/var/log/supervisor"
    )

    for log_dir in "${log_dirs[@]}"; do
        if [[ -d "$log_dir" ]]; then
            log "Cleaning logs in: $log_dir"

            # Compress logs older than 7 days
            find "$log_dir" -name "*.log" -type f -mtime +7 -exec gzip {} \;

            # Remove compressed logs older than 30 days
            find "$log_dir" -name "*.log.gz" -type f -mtime +30 -delete

            # Truncate large current log files (>100MB)
            find "$log_dir" -name "*.log" -type f -size +100M -exec truncate -s 50M {} \;
        fi
    done

    success "Log cleanup completed"
}

# Clean up old backups
cleanup_backups() {
    log "Cleaning up old backups..."

    local backup_dir="/var/backups/hotel-management"

    if [[ -d "$backup_dir" ]]; then
        # Keep only last 10 backups
        local backup_count=$(find "$backup_dir" -maxdepth 1 -type d -name "*_*" | wc -l)

        if [[ $backup_count -gt 10 ]]; then
            log "Found $backup_count backups, keeping only the 10 most recent"
            find "$backup_dir" -maxdepth 1 -type d -name "*_*" | sort | head -n $((backup_count - 10)) | xargs rm -rf
        else
            log "Found $backup_count backups, no cleanup needed"
        fi
    else
        warning "Backup directory not found: $backup_dir"
    fi

    success "Backup cleanup completed"
}

# Optimize Docker containers
optimize_docker() {
    log "Optimizing Docker containers..."

    cd "$PROJECT_ROOT"

    # Remove unused Docker images
    log "Removing unused Docker images..."
    docker image prune -f

    # Remove unused volumes
    log "Removing unused Docker volumes..."
    docker volume prune -f

    # Remove unused networks
    log "Removing unused Docker networks..."
    docker network prune -f

    # Restart containers to apply optimizations
    if [[ "${RESTART_CONTAINERS:-false}" == "true" ]]; then
        log "Restarting containers to apply optimizations..."
        docker-compose down
        docker-compose up -d
        sleep 30
    fi

    success "Docker optimization completed"
}

# Optimize file permissions
optimize_permissions() {
    log "Optimizing file permissions..."

    cd "$PROJECT_ROOT"

    # Set proper ownership for storage and cache directories
    docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache

    # Set proper permissions
    docker-compose exec -T app chmod -R 775 storage bootstrap/cache

    success "File permissions optimized"
}

# Clean up temporary files
cleanup_temp_files() {
    log "Cleaning up temporary files..."

    cd "$PROJECT_ROOT"

    # Clean Laravel temporary files
    docker-compose exec -T app find storage/framework/cache -name "*.php" -type f -mtime +1 -delete 2>/dev/null || true
    docker-compose exec -T app find storage/framework/sessions -name "sess_*" -type f -mtime +1 -delete 2>/dev/null || true
    docker-compose exec -T app find storage/framework/views -name "*.php" -type f -mtime +7 -delete 2>/dev/null || true

    # Clean system temporary files
    find /tmp -name "hotel_*" -type f -mtime +1 -delete 2>/dev/null || true

    success "Temporary files cleaned up"
}

# Optimize queue jobs
optimize_queue() {
    log "Optimizing queue jobs..."

    cd "$PROJECT_ROOT"

    # Restart queue workers to clear memory
    log "Restarting queue workers..."
    docker-compose restart queue-worker

    # Clear failed jobs older than 7 days
    log "Cleaning old failed jobs..."
    docker-compose exec -T app php artisan queue:flush

    success "Queue optimization completed"
}

# Generate optimization report
generate_optimization_report() {
    log "Generating optimization report..."

    local timestamp=$(date +'%Y-%m-%d %H:%M:%S')
    local report_file="/var/log/hotel-management/optimization-report-$(date +%Y%m%d).json"

    # Collect metrics
    local disk_usage_before="$1"
    local disk_usage_after=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
    local memory_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')

    cat > "$report_file" << EOF
{
    "timestamp": "$timestamp",
    "optimization_type": "full",
    "metrics": {
        "disk_usage_before": "$disk_usage_before",
        "disk_usage_after": "$disk_usage_after",
        "disk_space_freed": "$((disk_usage_before - disk_usage_after))",
        "memory_usage": "$memory_usage"
    },
    "operations_performed": [
        "laravel_cache_optimization",
        "database_optimization",
        "redis_optimization",
        "log_cleanup",
        "backup_cleanup",
        "docker_optimization",
        "permission_optimization",
        "temp_file_cleanup",
        "queue_optimization"
    ]
}
EOF

    success "Optimization report generated: $report_file"
}

# Run performance tests
run_performance_tests() {
    log "Running performance tests..."

    cd "$PROJECT_ROOT"

    # Test application response time
    local response_time=$(curl -s -o /dev/null -w "%{time_total}" http://localhost:8080/health 2>/dev/null || echo "0")
    log "Application response time: ${response_time}s"

    # Test database query performance
    local query_time=$(docker-compose exec -T app php artisan tinker --execute="
        \$start = microtime(true);
        DB::table('users')->count();
        echo microtime(true) - \$start;
    " 2>/dev/null | tail -1 || echo "0")
    log "Database query time: ${query_time}s"

    success "Performance tests completed"
}

# Main optimization function
main() {
    log "Starting system optimization..."

    # Create log directory
    mkdir -p "$(dirname "$LOG_FILE")"

    # Record disk usage before optimization
    local disk_usage_before=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
    log "Disk usage before optimization: ${disk_usage_before}%"

    # Run optimization tasks
    optimize_laravel_caches
    optimize_database
    optimize_redis
    cleanup_logs
    cleanup_backups
    optimize_docker
    optimize_permissions
    cleanup_temp_files
    optimize_queue

    # Generate report
    generate_optimization_report "$disk_usage_before"

    # Run performance tests
    run_performance_tests

    local disk_usage_after=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
    local space_freed=$((disk_usage_before - disk_usage_after))

    success "System optimization completed!"
    log "Disk usage after optimization: ${disk_usage_after}%"
    log "Disk space freed: ${space_freed}%"

    echo
    echo "=========================================="
    echo "Optimization Summary"
    echo "=========================================="
    echo "Started: $(head -1 "$LOG_FILE" | cut -d']' -f1 | tr -d '[')"
    echo "Completed: $(date +'%Y-%m-%d %H:%M:%S')"
    echo "Disk space freed: ${space_freed}%"
    echo "Current disk usage: ${disk_usage_after}%"
    echo "Optimization log: $LOG_FILE"
    echo
}

# Show help
show_help() {
    cat << EOF
Hotel Management System - Optimization Script

Usage: $0 [OPTIONS]

Options:
    -h, --help              Show this help message
    -r, --restart-containers Restart containers after optimization
    --cache-only            Only optimize caches (quick optimization)
    --database-only         Only optimize database
    --cleanup-only          Only perform cleanup operations

This script performs the following optimizations:
    - Clear and rebuild Laravel caches
    - Optimize database (analyze, vacuum, reindex)
    - Clean Redis expired keys
    - Clean up old logs and backups
    - Optimize Docker containers
    - Fix file permissions
    - Clean temporary files
    - Restart queue workers
    - Generate optimization report

Environment Variables:
    RESTART_CONTAINERS     Set to 'true' to restart containers (default: false)

Examples:
    $0                     # Full optimization
    $0 --cache-only        # Quick cache optimization only
    $0 -r                  # Full optimization with container restart

Schedule this script to run regularly:
    # Add to crontab for weekly optimization
    0 2 * * 0 /path/to/optimize.sh

    # Or run monthly with container restart
    0 3 1 * * /path/to/optimize.sh -r

EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -r|--restart-containers)
            RESTART_CONTAINERS=true
            shift
            ;;
        --cache-only)
            CACHE_ONLY=true
            shift
            ;;
        --database-only)
            DATABASE_ONLY=true
            shift
            ;;
        --cleanup-only)
            CLEANUP_ONLY=true
            shift
            ;;
        *)
            echo "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Run specific optimizations based on options
if [[ "${CACHE_ONLY:-false}" == "true" ]]; then
    log "Running cache-only optimization..."
    mkdir -p "$(dirname "$LOG_FILE")"
    optimize_laravel_caches
    success "Cache optimization completed!"

elif [[ "${DATABASE_ONLY:-false}" == "true" ]]; then
    log "Running database-only optimization..."
    mkdir -p "$(dirname "$LOG_FILE")"
    optimize_database
    success "Database optimization completed!"

elif [[ "${CLEANUP_ONLY:-false}" == "true" ]]; then
    log "Running cleanup-only optimization..."
    mkdir -p "$(dirname "$LOG_FILE")"
    cleanup_logs
    cleanup_backups
    cleanup_temp_files
    success "Cleanup optimization completed!"

else
    # Run full optimization
    main
fi