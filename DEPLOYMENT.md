# Hotel Management System - Production Deployment Guide

This comprehensive guide covers deploying the Laravel 12 Hotel Management System to production using Docker containers.

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Pre-deployment Setup](#pre-deployment-setup)
3. [Environment Configuration](#environment-configuration)
4. [Database Setup](#database-setup)
5. [SSL Configuration](#ssl-configuration)
6. [Docker Deployment](#docker-deployment)
7. [Post-deployment Configuration](#post-deployment-configuration)
8. [Monitoring and Maintenance](#monitoring-and-maintenance)
9. [Backup and Recovery](#backup-and-recovery)
10. [Troubleshooting](#troubleshooting)

## System Requirements

### Minimum Requirements
- **OS**: Ubuntu 20.04+ / CentOS 8+ / Debian 10+
- **RAM**: 4GB (8GB recommended for production)
- **Storage**: 50GB SSD (100GB+ recommended)
- **CPU**: 2 cores (4+ cores recommended)
- **Network**: Static IP address, ports 80, 443, 22 open

### Software Requirements
- Docker 24.0+
- Docker Compose 2.20+
- Git 2.30+
- Nginx (for reverse proxy, optional)
- Certbot (for SSL certificates)

## Pre-deployment Setup

### 1. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y curl wget git unzip software-properties-common

# Create application user
sudo useradd -m -s /bin/bash hotel
sudo usermod -aG sudo hotel
```

### 2. Install Docker

Use the automated setup script:

```bash
sudo ./scripts/deployment/setup-production.sh
```

Or install manually:

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
```

### 3. Configure Firewall

```bash
# Ubuntu/Debian
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp  # Application port

# CentOS/RHEL
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-port=8080/tcp
sudo firewall-cmd --reload
```

## Environment Configuration

### 1. Clone Repository

```bash
cd /opt
sudo git clone https://github.com/your-repo/hotel-management.git
sudo chown -R hotel:hotel hotel-management
cd hotel-management
```

### 2. Configure Environment Variables

```bash
# Copy environment template
cp .env.example .env

# Edit environment file
nano .env
```

### Essential Production Settings

```bash
# Application Configuration
APP_NAME="Your Hotel Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
APP_KEY=base64:your-generated-key
BCRYPT_ROUNDS=12

# Database Configuration (MySQL recommended for production)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hotel_management
DB_USERNAME=hotel_user
DB_PASSWORD=your-secure-password

# Cache & Sessions (Redis recommended)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=your-redis-password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# CORS for Landing Page
CORS_ALLOWED_ORIGINS="https://yourdomain.com,https://www.yourdomain.com,https://landing.yourdomain.com"

# Backup Configuration
S3_BUCKET=your-backup-bucket
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret

# Monitoring
ALERT_EMAIL=admin@yourdomain.com
SLACK_WEBHOOK_URL=your-slack-webhook
```

## Database Setup

### MySQL Production Configuration

1. **Create docker-compose override for production:**

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  mysql:
    environment:
      MYSQL_ROOT_PASSWORD: your-secure-root-password
      MYSQL_DATABASE: hotel_management
      MYSQL_USER: hotel_user
      MYSQL_PASSWORD: your-secure-password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init:/docker-entrypoint-initdb.d
      - ./docker/mysql/conf.d:/etc/mysql/conf.d
    command: >
      --default-authentication-plugin=mysql_native_password
      --innodb-buffer-pool-size=1G
      --innodb-log-file-size=256M
      --max-connections=200
      --query-cache-size=64M
```

2. **MySQL configuration file:**

```bash
# Create MySQL configuration
mkdir -p docker/mysql/conf.d
cat > docker/mysql/conf.d/mysql.cnf << 'EOF'
[mysqld]
# Performance optimizations
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table = 1

# Connection settings
max_connections = 200
max_connect_errors = 10000
wait_timeout = 600
interactive_timeout = 600

# Query cache
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

[mysql]
default-character-set = utf8mb4

[client]
default-character-set = utf8mb4
EOF
```

## SSL Configuration

### 1. Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install certbot

# Obtain SSL certificate
sudo certbot certonly --standalone -d yourdomain.com

# Verify certificate
sudo certbot certificates
```

### 2. Configure SSL in Nginx

```bash
# Create Nginx SSL configuration
cat > /etc/nginx/sites-available/hotel-management << 'EOF'
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security headers
    add_header Strict-Transport-Security "max-age=63072000" always;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/hotel-management /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 3. Auto-renewal Setup

```bash
# Add to crontab
echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
```

## Docker Deployment

### 1. Build and Start Services

```bash
# Build images
docker-compose build --no-cache

# Start services
docker-compose up -d

# Check status
docker-compose ps
```

### 2. Initialize Application

```bash
# Generate application key
docker-compose exec app php artisan key:generate --force

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed essential data
docker-compose exec app php artisan db:seed --class=EssentialDataSeeder --force

# Optimize application
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 3. Verify Deployment

```bash
# Check application health
curl -f http://localhost:8080/health

# Check database connection
docker-compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database OK';"

# Check queue workers
docker-compose exec app php artisan queue:restart
```

## Post-deployment Configuration

### 1. Create Admin User

```bash
docker-compose exec app php artisan tinker
```

```php
// In Tinker console
$user = new App\Models\User();
$user->name = 'Administrator';
$user->email = 'admin@yourdomain.com';
$user->password = bcrypt('your-secure-password');
$user->save();

// Assign admin role
$user->assignRole('Administrador');
```

### 2. Configure Hotel Settings

Access the admin panel at `https://yourdomain.com/admin` and configure:

- Hotel information
- Currency settings
- Check-in/check-out times
- Notification settings
- Room categories and rooms

### 3. Test Landing Page Integration

If using a separate landing page:

1. Update CORS settings in `.env`
2. Test API endpoints: `/api/reservas/disponibilidad`
3. Verify reservation creation flow

## Monitoring and Maintenance

### 1. Setup Monitoring

```bash
# Make scripts executable
chmod +x scripts/maintenance/monitor.sh
chmod +x scripts/maintenance/optimize.sh

# Add monitoring to crontab
(crontab -l 2>/dev/null; echo "*/5 * * * * /opt/hotel-management/scripts/maintenance/monitor.sh") | crontab -

# Add weekly optimization
(crontab -l 2>/dev/null; echo "0 2 * * 0 /opt/hotel-management/scripts/maintenance/optimize.sh") | crontab -
```

### 2. Setup Log Rotation

```bash
# Create logrotate configuration
sudo cat > /etc/logrotate.d/hotel-management << 'EOF'
/var/log/hotel-management/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 hotel hotel
    postrotate
        docker-compose -f /opt/hotel-management/docker-compose.yml restart app > /dev/null 2>&1 || true
    endscript
}
EOF
```

### 3. Health Checks

The application includes built-in health checks:

- Application: `https://yourdomain.com/health`
- Container health: `docker-compose ps`
- System monitoring: `./scripts/maintenance/monitor.sh`

## Backup and Recovery

### 1. Automated Backups

```bash
# Make backup script executable
chmod +x scripts/backup/backup.sh

# Add daily backup to crontab
(crontab -l 2>/dev/null; echo "0 3 * * * /opt/hotel-management/scripts/backup/backup.sh") | crontab -

# Configure S3 backup (optional)
export S3_BUCKET=your-backup-bucket
export AWS_ACCESS_KEY_ID=your-aws-key
export AWS_SECRET_ACCESS_KEY=your-aws-secret
```

### 2. Manual Backup

```bash
# Create backup
./scripts/backup/backup.sh

# List backups
ls -la /var/backups/hotel-management/
```

### 3. Restore from Backup

```bash
# Interactive restore
./scripts/backup/restore.sh

# Restore specific backup
./scripts/backup/restore.sh /var/backups/hotel-management/20241225_120000
```

## Security Checklist

### Production Security Setup

- [ ] SSL certificate installed and configured
- [ ] Firewall configured (only necessary ports open)
- [ ] Strong passwords for all services
- [ ] Regular security updates scheduled
- [ ] Database access restricted
- [ ] Environment variables secured
- [ ] Backup encryption enabled
- [ ] Access logs monitored
- [ ] Failed login attempts monitored

### Application Security

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] CSRF protection enabled
- [ ] SQL injection protection verified
- [ ] XSS protection enabled
- [ ] File upload restrictions configured
- [ ] Rate limiting enabled
- [ ] User permissions properly configured

## Performance Optimization

### 1. Application Optimization

```bash
# Run optimization script
./scripts/maintenance/optimize.sh

# Configure opcache in PHP
# (Already configured in docker/php/php.ini)
```

### 2. Database Optimization

```bash
# MySQL optimization (included in optimize.sh)
docker exec hotel-mysql mysql -u root -p -e "OPTIMIZE TABLE table_name;"

# Monitor slow queries
docker exec hotel-mysql mysql -u root -p -e "SHOW PROCESSLIST;"
```

### 3. Caching Strategy

The application uses multi-layer caching:

- **Opcache**: PHP bytecode caching
- **Redis**: Session and application cache
- **Laravel Cache**: Config, routes, views
- **Database**: Query result caching

## Scaling Considerations

### Horizontal Scaling

For high-traffic deployments:

1. **Load Balancer**: Use nginx load balancer configuration
2. **Multiple App Instances**: Scale with `docker-compose up --scale app=3`
3. **Database Clustering**: Consider MySQL replication
4. **Redis Clustering**: For cache distribution
5. **CDN**: For static assets

### Resource Monitoring

Monitor these metrics:

- CPU usage (threshold: 80%)
- Memory usage (threshold: 85%)
- Disk usage (threshold: 85%)
- Response time (threshold: 5 seconds)
- Database connections
- Queue job processing

## Troubleshooting

### Common Issues

#### 1. Container Won't Start
```bash
# Check logs
docker-compose logs app

# Check system resources
docker system df
df -h
free -h
```

#### 2. Database Connection Failed
```bash
# Check MySQL status
docker-compose logs mysql

# Verify credentials
docker-compose exec mysql mysql -u hotel_user -p hotel_management
```

#### 3. SSL Certificate Issues
```bash
# Check certificate
sudo certbot certificates

# Renew certificate
sudo certbot renew --dry-run
```

#### 4. High Memory Usage
```bash
# Restart containers
docker-compose restart

# Clear application cache
docker-compose exec app php artisan cache:clear
```

#### 5. Queue Jobs Not Processing
```bash
# Check queue worker
docker-compose logs queue-worker

# Restart queue worker
docker-compose restart queue-worker

# Check failed jobs
docker-compose exec app php artisan queue:failed
```

### Emergency Procedures

#### Application Recovery
1. Check monitoring alerts
2. Review recent logs
3. Restart affected services
4. Restore from backup if necessary

#### Database Recovery
1. Stop application containers
2. Restore database from backup
3. Verify data integrity
4. Restart application

#### Complete System Recovery
1. Assess damage scope
2. Restore from full backup
3. Update DNS if necessary
4. Notify users of downtime

## Support and Maintenance

### Regular Maintenance Tasks

- **Daily**: Monitor system health
- **Weekly**: Review logs and optimize
- **Monthly**: Update dependencies
- **Quarterly**: Security audit
- **Annually**: Disaster recovery test

### Monitoring Dashboards

Access monitoring at:
- Application health: `https://yourdomain.com/health`
- System logs: `/var/log/hotel-management/`
- Database performance: phpMyAdmin (development only)
- Container stats: `docker stats`

### Getting Help

For technical support:
1. Check application logs first
2. Review this documentation
3. Search GitHub issues
4. Contact development team

---

## Deployment Checklist

Use this checklist for deployment verification:

### Pre-deployment
- [ ] Server meets minimum requirements
- [ ] Docker and Docker Compose installed
- [ ] SSL certificate obtained
- [ ] Domain DNS configured
- [ ] Firewall rules configured
- [ ] Environment variables configured

### Deployment
- [ ] Application containers started
- [ ] Database migrations completed
- [ ] Essential data seeded
- [ ] Admin user created
- [ ] Application caches optimized
- [ ] Health checks passing

### Post-deployment
- [ ] SSL redirects working
- [ ] Landing page integration tested
- [ ] Backup system configured
- [ ] Monitoring system active
- [ ] Log rotation configured
- [ ] Performance optimization applied

### Final Verification
- [ ] Application accessible via HTTPS
- [ ] Login functionality working
- [ ] Reservation system functional
- [ ] Email notifications working
- [ ] API endpoints responding
- [ ] Queue jobs processing
- [ ] Scheduled tasks running

---

This deployment guide provides comprehensive instructions for setting up the Hotel Management System in production. Follow each section carefully and refer to the troubleshooting section if you encounter any issues.