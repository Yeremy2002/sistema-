#!/bin/bash

# Hotel Management System - Monitoring Script
# This script monitors the application health and performance

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
LOG_FILE="/var/log/hotel-management/monitor.log"
ALERT_EMAIL="${ALERT_EMAIL:-}"
SLACK_WEBHOOK="${SLACK_WEBHOOK:-}"
CHECK_INTERVAL="${CHECK_INTERVAL:-300}" # 5 minutes default

# Thresholds
CPU_THRESHOLD="${CPU_THRESHOLD:-80}"
MEMORY_THRESHOLD="${MEMORY_THRESHOLD:-85}"
DISK_THRESHOLD="${DISK_THRESHOLD:-85}"
RESPONSE_TIME_THRESHOLD="${RESPONSE_TIME_THRESHOLD:-5}" # seconds

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
}

success() {
    echo -e "${GREEN}[OK] $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}" | tee -a "$LOG_FILE"
}

# Send alert notification
send_alert() {
    local level="$1"
    local message="$2"
    local subject="Hotel Management System - $level Alert"

    log "ALERT [$level]: $message"

    # Email notification
    if [[ -n "$ALERT_EMAIL" ]] && command -v mail &> /dev/null; then
        echo "$message" | mail -s "$subject" "$ALERT_EMAIL"
    fi

    # Slack notification
    if [[ -n "$SLACK_WEBHOOK" ]]; then
        local color="good"
        [[ "$level" == "WARNING" ]] && color="warning"
        [[ "$level" == "CRITICAL" ]] && color="danger"

        curl -X POST -H 'Content-type: application/json' \
            --data "{
                \"attachments\": [{
                    \"color\": \"$color\",
                    \"title\": \"$subject\",
                    \"text\": \"$message\",
                    \"ts\": $(date +%s)
                }]
            }" \
            "$SLACK_WEBHOOK" &> /dev/null || true
    fi
}

# Check Docker containers
check_containers() {
    log "Checking Docker containers..."

    local required_containers=("hotel-app" "hotel-mysql" "hotel-redis")
    local failed_containers=()

    for container in "${required_containers[@]}"; do
        if ! docker ps | grep -q "$container.*Up"; then
            failed_containers+=("$container")
        fi
    done

    if [[ ${#failed_containers[@]} -eq 0 ]]; then
        success "All containers are running"
    else
        error "Failed containers: ${failed_containers[*]}"
        send_alert "CRITICAL" "The following containers are not running: ${failed_containers[*]}"
        return 1
    fi
}

# Check application health
check_application_health() {
    log "Checking application health..."

    local start_time=$(date +%s.%N)
    local response_code=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/health 2>/dev/null || echo "000")
    local end_time=$(date +%s.%N)
    local response_time=$(echo "$end_time - $start_time" | bc 2>/dev/null || echo "0")

    if [[ "$response_code" == "200" ]]; then
        if (( $(echo "$response_time > $RESPONSE_TIME_THRESHOLD" | bc -l 2>/dev/null || echo "0") )); then
            warning "Application responding slowly: ${response_time}s"
            send_alert "WARNING" "Application response time is slow: ${response_time}s (threshold: ${RESPONSE_TIME_THRESHOLD}s)"
        else
            success "Application health check passed (${response_time}s)"
        fi
    else
        error "Application health check failed (HTTP $response_code)"
        send_alert "CRITICAL" "Application health check failed with HTTP code: $response_code"
        return 1
    fi
}

# Check database connectivity
check_database() {
    log "Checking database connectivity..."

    cd "$PROJECT_ROOT"

    if docker-compose exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" &> /dev/null; then
        success "Database connectivity check passed"
    else
        error "Database connectivity check failed"
        send_alert "CRITICAL" "Database connectivity check failed"
        return 1
    fi
}

# Check Redis connectivity
check_redis() {
    log "Checking Redis connectivity..."

    if docker exec hotel-redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
        success "Redis connectivity check passed"
    else
        error "Redis connectivity check failed"
        send_alert "CRITICAL" "Redis connectivity check failed"
        return 1
    fi
}

# Check queue workers
check_queue_workers() {
    log "Checking queue workers..."

    if docker ps | grep -q "hotel-queue-worker.*Up"; then
        # Check if queue jobs are being processed
        local failed_jobs=$(docker-compose exec -T app php artisan queue:failed --format=json 2>/dev/null | jq length 2>/dev/null || echo "0")

        if [[ "$failed_jobs" -gt 10 ]]; then
            warning "High number of failed queue jobs: $failed_jobs"
            send_alert "WARNING" "High number of failed queue jobs: $failed_jobs"
        else
            success "Queue workers are running ($failed_jobs failed jobs)"
        fi
    else
        error "Queue workers are not running"
        send_alert "CRITICAL" "Queue workers are not running"
        return 1
    fi
}

# Check scheduler
check_scheduler() {
    log "Checking scheduler..."

    if docker ps | grep -q "hotel-scheduler.*Up"; then
        success "Scheduler is running"
    else
        error "Scheduler is not running"
        send_alert "CRITICAL" "Scheduler is not running"
        return 1
    fi
}

# Check system resources
check_system_resources() {
    log "Checking system resources..."

    # CPU usage
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//')
    cpu_usage=${cpu_usage%.*} # Remove decimal part

    if [[ $cpu_usage -gt $CPU_THRESHOLD ]]; then
        warning "High CPU usage: ${cpu_usage}%"
        send_alert "WARNING" "High CPU usage detected: ${cpu_usage}% (threshold: ${CPU_THRESHOLD}%)"
    else
        success "CPU usage is normal: ${cpu_usage}%"
    fi

    # Memory usage
    local memory_usage=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100.0)}')

    if [[ $memory_usage -gt $MEMORY_THRESHOLD ]]; then
        warning "High memory usage: ${memory_usage}%"
        send_alert "WARNING" "High memory usage detected: ${memory_usage}% (threshold: ${MEMORY_THRESHOLD}%)"
    else
        success "Memory usage is normal: ${memory_usage}%"
    fi

    # Disk usage
    local disk_usage=$(df / | awk 'NR==2{print $5}' | sed 's/%//')

    if [[ $disk_usage -gt $DISK_THRESHOLD ]]; then
        warning "High disk usage: ${disk_usage}%"
        send_alert "WARNING" "High disk usage detected: ${disk_usage}% (threshold: ${DISK_THRESHOLD}%)"
    else
        success "Disk usage is normal: ${disk_usage}%"
    fi
}

# Check SSL certificate expiration
check_ssl_certificate() {
    log "Checking SSL certificate..."

    local domain=$(grep APP_URL "$PROJECT_ROOT/.env" 2>/dev/null | cut -d'=' -f2 | sed 's|https://||' | sed 's|http://||' || echo "localhost")

    if [[ "$domain" != "localhost" ]] && [[ "$domain" != *"127.0.0.1"* ]]; then
        local cert_expiry=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null | grep notAfter | cut -d'=' -f2)

        if [[ -n "$cert_expiry" ]]; then
            local expiry_timestamp=$(date -d "$cert_expiry" +%s 2>/dev/null || echo "0")
            local current_timestamp=$(date +%s)
            local days_until_expiry=$(( (expiry_timestamp - current_timestamp) / 86400 ))

            if [[ $days_until_expiry -lt 30 ]]; then
                warning "SSL certificate expires in $days_until_expiry days"
                send_alert "WARNING" "SSL certificate for $domain expires in $days_until_expiry days"
            else
                success "SSL certificate is valid ($days_until_expiry days until expiry)"
            fi
        else
            warning "Could not check SSL certificate for $domain"
        fi
    else
        log "Skipping SSL check for localhost/development environment"
    fi
}

# Check log file sizes
check_log_sizes() {
    log "Checking log file sizes..."

    local log_dirs=("/var/log/hotel-management" "$PROJECT_ROOT/storage/logs")
    local large_logs=()

    for log_dir in "${log_dirs[@]}"; do
        if [[ -d "$log_dir" ]]; then
            while IFS= read -r -d '' file; do
                local size=$(stat -c%s "$file" 2>/dev/null || echo "0")
                if [[ $size -gt 104857600 ]]; then # 100MB
                    large_logs+=("$file")
                fi
            done < <(find "$log_dir" -name "*.log" -print0 2>/dev/null)
        fi
    done

    if [[ ${#large_logs[@]} -gt 0 ]]; then
        warning "Large log files detected: ${large_logs[*]}"
        send_alert "WARNING" "Large log files detected (>100MB): ${large_logs[*]}"
    else
        success "Log file sizes are normal"
    fi
}

# Check backup status
check_backup_status() {
    log "Checking backup status..."

    local backup_dir="/var/backups/hotel-management"
    local latest_backup=""

    if [[ -d "$backup_dir" ]]; then
        latest_backup=$(find "$backup_dir" -maxdepth 1 -type d -name "*_*" | sort | tail -1)
    fi

    if [[ -n "$latest_backup" ]]; then
        local backup_timestamp=$(basename "$latest_backup" | cut -d'_' -f1)
        local backup_date=$(date -d "${backup_timestamp:0:4}-${backup_timestamp:4:2}-${backup_timestamp:6:2}" +%s 2>/dev/null || echo "0")
        local current_date=$(date +%s)
        local days_since_backup=$(( (current_date - backup_date) / 86400 ))

        if [[ $days_since_backup -gt 1 ]]; then
            warning "Last backup is $days_since_backup days old"
            send_alert "WARNING" "Last backup is $days_since_backup days old"
        else
            success "Recent backup found ($days_since_backup days old)"
        fi
    else
        warning "No backups found"
        send_alert "WARNING" "No backups found in $backup_dir"
    fi
}

# Generate monitoring report
generate_report() {
    log "Generating monitoring report..."

    local timestamp=$(date +'%Y-%m-%d %H:%M:%S')
    local report_file="/var/log/hotel-management/monitor-report-$(date +%Y%m%d).json"

    # Collect metrics
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//')
    local memory_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
    local disk_usage=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
    local container_count=$(docker ps | grep hotel | wc -l)

    cat > "$report_file" << EOF
{
    "timestamp": "$timestamp",
    "system": {
        "cpu_usage": "${cpu_usage%.*}",
        "memory_usage": "$memory_usage",
        "disk_usage": "$disk_usage",
        "uptime": "$(uptime -p)"
    },
    "containers": {
        "running": $container_count,
        "status": "$(docker ps --format 'table {{.Names}}\t{{.Status}}' | grep hotel || echo 'None')"
    },
    "application": {
        "health_check": "$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/health 2>/dev/null || echo '000')",
        "response_time": "$(curl -s -o /dev/null -w "%{time_total}" http://localhost:8080/health 2>/dev/null || echo '0')"
    }
}
EOF

    success "Monitoring report generated: $report_file"
}

# Auto-restart failed services
auto_restart_services() {
    if [[ "${AUTO_RESTART:-false}" == "true" ]]; then
        log "Checking for failed services to restart..."

        local failed_containers=($(docker ps -a --filter "status=exited" --format "{{.Names}}" | grep hotel || true))

        for container in "${failed_containers[@]}"; do
            warning "Restarting failed container: $container"
            docker restart "$container"
            send_alert "INFO" "Auto-restarted failed container: $container"
        done
    fi
}

# Main monitoring function
main_check() {
    log "Starting monitoring checks..."

    local failed_checks=0

    # Core checks
    check_containers || ((failed_checks++))
    check_application_health || ((failed_checks++))
    check_database || ((failed_checks++))
    check_redis || ((failed_checks++))
    check_queue_workers || ((failed_checks++))
    check_scheduler || ((failed_checks++))

    # System checks
    check_system_resources
    check_ssl_certificate
    check_log_sizes
    check_backup_status

    # Auto-restart if enabled
    auto_restart_services

    # Generate report
    generate_report

    if [[ $failed_checks -eq 0 ]]; then
        success "All monitoring checks passed"
    else
        error "$failed_checks monitoring checks failed"
        return 1
    fi
}

# Continuous monitoring mode
continuous_monitor() {
    log "Starting continuous monitoring (interval: ${CHECK_INTERVAL}s)..."

    while true; do
        main_check || true
        sleep "$CHECK_INTERVAL"
    done
}

# Show help
show_help() {
    cat << EOF
Hotel Management System - Monitoring Script

Usage: $0 [OPTIONS]

Options:
    -h, --help              Show this help message
    -c, --continuous        Run in continuous monitoring mode
    -i, --interval SECONDS  Set check interval for continuous mode (default: 300)
    -r, --restart          Enable auto-restart of failed services

Environment Variables:
    ALERT_EMAIL            Email address for alerts
    SLACK_WEBHOOK          Slack webhook URL for notifications
    CHECK_INTERVAL         Monitoring interval in seconds (default: 300)
    CPU_THRESHOLD          CPU usage alert threshold % (default: 80)
    MEMORY_THRESHOLD       Memory usage alert threshold % (default: 85)
    DISK_THRESHOLD         Disk usage alert threshold % (default: 85)
    RESPONSE_TIME_THRESHOLD Response time threshold in seconds (default: 5)
    AUTO_RESTART           Enable auto-restart of failed services (default: false)

Examples:
    $0                     # Run single check
    $0 -c                  # Run continuous monitoring
    $0 -c -i 60            # Run continuous monitoring every 60 seconds
    $0 -r                  # Run with auto-restart enabled

Setup for continuous monitoring:
    # Add to crontab for single checks every 5 minutes
    */5 * * * * /path/to/monitor.sh

    # Or run as systemd service for continuous monitoring
    sudo systemctl enable hotel-monitor
    sudo systemctl start hotel-monitor

EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -c|--continuous)
            CONTINUOUS_MODE=true
            shift
            ;;
        -i|--interval)
            CHECK_INTERVAL="$2"
            shift 2
            ;;
        -r|--restart)
            AUTO_RESTART=true
            shift
            ;;
        *)
            echo "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Create log directory
mkdir -p "$(dirname "$LOG_FILE")"

# Run monitoring
if [[ "${CONTINUOUS_MODE:-false}" == "true" ]]; then
    continuous_monitor
else
    main_check
fi