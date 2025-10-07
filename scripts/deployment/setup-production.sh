#!/bin/bash

# Hotel Management System - Production Setup Script
# This script sets up the production environment from scratch

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
LOG_FILE="/var/log/hotel-setup.log"

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

# Check system requirements
check_system_requirements() {
    log "Checking system requirements..."

    # Check OS
    if [[ ! -f /etc/os-release ]]; then
        error "Cannot determine operating system"
    fi

    . /etc/os-release
    log "Operating System: $PRETTY_NAME"

    # Check available memory
    local total_mem=$(free -m | awk 'NR==2{printf "%.0f", $2}')
    if [[ $total_mem -lt 2048 ]]; then
        warning "System has less than 2GB RAM. Minimum recommended is 2GB for production."
    fi

    # Check available disk space
    local available_space=$(df -h / | awk 'NR==2{print $4}' | sed 's/G//')
    if [[ ${available_space%.*} -lt 10 ]]; then
        warning "Less than 10GB disk space available. This may cause issues."
    fi

    success "System requirements check completed"
}

# Install Docker and Docker Compose
install_docker() {
    log "Installing Docker and Docker Compose..."

    # Check if Docker is already installed
    if command -v docker &> /dev/null; then
        log "Docker is already installed: $(docker --version)"
    else
        log "Installing Docker..."

        # Install Docker based on OS
        if [[ "$ID" == "ubuntu" ]] || [[ "$ID" == "debian" ]]; then
            # Ubuntu/Debian installation
            apt-get update
            apt-get install -y ca-certificates curl gnupg lsb-release

            # Add Docker's official GPG key
            mkdir -p /etc/apt/keyrings
            curl -fsSL https://download.docker.com/linux/$ID/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg

            # Add Docker repository
            echo \
              "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/$ID \
              $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

            # Install Docker Engine
            apt-get update
            apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

        elif [[ "$ID" == "centos" ]] || [[ "$ID" == "rhel" ]] || [[ "$ID" == "fedora" ]]; then
            # CentOS/RHEL/Fedora installation
            yum install -y yum-utils
            yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
            yum install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

        else
            error "Unsupported operating system: $ID"
        fi

        # Start and enable Docker
        systemctl start docker
        systemctl enable docker

        success "Docker installed successfully"
    fi

    # Check if Docker Compose is available
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        log "Installing Docker Compose..."

        # Install Docker Compose (standalone)
        local compose_version="2.24.0"
        curl -L "https://github.com/docker/compose/releases/download/v${compose_version}/docker-compose-$(uname -s)-$(uname -m)" \
             -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose

        success "Docker Compose installed successfully"
    else
        log "Docker Compose is already available"
    fi
}

# Setup application directories and permissions
setup_directories() {
    log "Setting up application directories..."

    # Create necessary directories
    mkdir -p /var/log/hotel-management
    mkdir -p /var/backups/hotel-management
    mkdir -p /etc/hotel-management

    # Create hotel user if it doesn't exist
    if ! id "hotel" &>/dev/null; then
        useradd -r -s /bin/false hotel
        log "Created hotel user"
    fi

    # Add current user to docker group
    usermod -aG docker $USER || true

    success "Directories and permissions set up"
}

# Configure firewall
configure_firewall() {
    log "Configuring firewall..."

    # Check if ufw is available (Ubuntu/Debian)
    if command -v ufw &> /dev/null; then
        # Enable firewall if not active
        if ! ufw status | grep -q "Status: active"; then
            ufw --force enable
        fi

        # Allow SSH
        ufw allow ssh

        # Allow HTTP and HTTPS
        ufw allow 80/tcp
        ufw allow 443/tcp

        # Allow application port
        ufw allow 8080/tcp

        success "UFW firewall configured"

    # Check if firewalld is available (CentOS/RHEL/Fedora)
    elif command -v firewall-cmd &> /dev/null; then
        systemctl start firewalld
        systemctl enable firewalld

        # Allow HTTP and HTTPS
        firewall-cmd --permanent --add-service=http
        firewall-cmd --permanent --add-service=https
        firewall-cmd --permanent --add-port=8080/tcp
        firewall-cmd --reload

        success "Firewalld configured"
    else
        warning "No firewall detected. Please configure firewall manually."
    fi
}

# Setup SSL certificates with Let's Encrypt
setup_ssl() {
    log "Setting up SSL certificates..."

    # Check if domain is provided
    read -p "Enter your domain name (e.g., hotel.example.com): " domain_name

    if [[ -z "$domain_name" ]]; then
        warning "No domain provided. SSL setup skipped."
        return
    fi

    # Install Certbot
    if [[ "$ID" == "ubuntu" ]] || [[ "$ID" == "debian" ]]; then
        apt-get update
        apt-get install -y certbot
    elif [[ "$ID" == "centos" ]] || [[ "$ID" == "rhel" ]] || [[ "$ID" == "fedora" ]]; then
        yum install -y certbot
    fi

    # Create SSL certificate
    log "Creating SSL certificate for $domain_name..."
    certbot certonly --standalone -d "$domain_name" --email "admin@$domain_name" --agree-tos --non-interactive

    if [[ $? -eq 0 ]]; then
        success "SSL certificate created for $domain_name"

        # Setup auto-renewal
        echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
        success "SSL auto-renewal configured"
    else
        error "SSL certificate creation failed"
    fi
}

# Create environment file
setup_environment() {
    log "Setting up environment configuration..."

    cd "$PROJECT_ROOT"

    if [[ ! -f .env ]]; then
        cp .env.example .env
        log "Created .env file from example"

        # Generate application key
        log "Generating application key..."
        # We'll do this after containers are running
    else
        log ".env file already exists"
    fi

    # Prompt for essential configuration
    read -p "Enter application name [Hotel Management System]: " app_name
    app_name=${app_name:-"Hotel Management System"}

    read -p "Enter application URL [https://localhost]: " app_url
    app_url=${app_url:-"https://localhost"}

    read -p "Enter database password: " -s db_password
    echo

    read -p "Enter Redis password (optional): " -s redis_password
    echo

    # Update .env file
    sed -i "s|APP_NAME=.*|APP_NAME=\"$app_name\"|" .env
    sed -i "s|APP_URL=.*|APP_URL=$app_url|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$db_password|" .env

    if [[ -n "$redis_password" ]]; then
        sed -i "s|REDIS_PASSWORD=.*|REDIS_PASSWORD=$redis_password|" .env
    fi

    success "Environment configuration completed"
}

# Initialize application
initialize_application() {
    log "Initializing application..."

    cd "$PROJECT_ROOT"

    # Build and start containers
    log "Building and starting containers..."
    docker-compose up -d --build

    # Wait for containers to be ready
    log "Waiting for containers to start..."
    sleep 60

    # Generate application key
    log "Generating application key..."
    docker-compose exec -T app php artisan key:generate --force

    # Run migrations and seeders
    log "Running database migrations..."
    docker-compose exec -T app php artisan migrate --force

    log "Running database seeders..."
    docker-compose exec -T app php artisan db:seed --class=EssentialDataSeeder --force

    # Optimize application
    log "Optimizing application..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache

    success "Application initialized successfully"
}

# Setup monitoring
setup_monitoring() {
    log "Setting up monitoring..."

    # Create monitoring script
    cat > /usr/local/bin/hotel-monitor << 'EOF'
#!/bin/bash

LOG_FILE="/var/log/hotel-management/monitor.log"

# Check if containers are running
if ! docker-compose -f /path/to/docker-compose.yml ps | grep -q "Up"; then
    echo "$(date): Containers are not running" >> "$LOG_FILE"
    # Send alert or restart containers
fi

# Check application health
if ! curl -f http://localhost:8080/health &> /dev/null; then
    echo "$(date): Application health check failed" >> "$LOG_FILE"
    # Send alert
fi

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
if [[ $DISK_USAGE -gt 85 ]]; then
    echo "$(date): Disk usage is ${DISK_USAGE}%" >> "$LOG_FILE"
    # Send alert
fi
EOF

    chmod +x /usr/local/bin/hotel-monitor

    # Add to crontab
    echo "*/5 * * * * /usr/local/bin/hotel-monitor" | crontab -

    success "Monitoring setup completed"
}

# Setup log rotation
setup_log_rotation() {
    log "Setting up log rotation..."

    cat > /etc/logrotate.d/hotel-management << 'EOF'
/var/log/hotel-management/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 hotel hotel
    postrotate
        docker-compose -f /path/to/docker-compose.yml exec app php artisan cache:clear > /dev/null 2>&1 || true
    endscript
}
EOF

    success "Log rotation configured"
}

# Main setup function
main() {
    log "Starting production setup for Hotel Management System..."

    # Check if running as root
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root (use sudo)"
    fi

    check_system_requirements
    install_docker
    setup_directories
    configure_firewall
    setup_ssl
    setup_environment
    initialize_application
    setup_monitoring
    setup_log_rotation

    success "Production setup completed successfully!"

    echo
    echo "=============================================="
    echo "Hotel Management System is now ready!"
    echo "=============================================="
    echo
    echo "Access your application at: $app_url"
    echo "Admin panel: $app_url/admin"
    echo
    echo "Default admin credentials:"
    echo "Email: admin@hotel.com"
    echo "Password: Check your database seeders"
    echo
    echo "Important next steps:"
    echo "1. Change default admin password"
    echo "2. Configure your hotel settings"
    echo "3. Set up your domain DNS"
    echo "4. Test the landing page integration"
    echo "5. Configure backup procedures"
    echo
    echo "Logs are available at: /var/log/hotel-management/"
    echo "Backups are stored at: /var/backups/hotel-management/"
    echo
}

# Show help
show_help() {
    cat << EOF
Hotel Management System - Production Setup Script

This script will:
- Install Docker and Docker Compose
- Set up system directories and permissions
- Configure firewall
- Set up SSL certificates
- Initialize the application
- Configure monitoring and logging

Usage: sudo $0

Requirements:
- Ubuntu 18.04+ / CentOS 7+ / Debian 9+
- Minimum 2GB RAM
- Minimum 10GB disk space
- Internet connection

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