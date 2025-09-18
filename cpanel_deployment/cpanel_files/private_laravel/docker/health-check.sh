#!/bin/sh

# Health check script for Laravel Hotel Management System

# Check if nginx is running
if ! pgrep nginx > /dev/null; then
    echo "ERROR: nginx is not running"
    exit 1
fi

# Check if php-fpm is running
if ! pgrep php-fpm > /dev/null; then
    echo "ERROR: php-fpm is not running"
    exit 1
fi

# Check if application responds
if ! curl -f http://localhost/health > /dev/null 2>&1; then
    echo "ERROR: Application health check failed"
    exit 1
fi

# Check database connection (if using MySQL)
if [ "$DB_CONNECTION" = "mysql" ]; then
    if ! php /var/www/html/artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
        echo "ERROR: Database connection failed"
        exit 1
    fi
fi

# Check Redis connection (if using Redis)
if [ "$REDIS_HOST" ]; then
    if ! redis-cli -h "$REDIS_HOST" ping > /dev/null 2>&1; then
        echo "ERROR: Redis connection failed"
        exit 1
    fi
fi

echo "OK: All health checks passed"
exit 0