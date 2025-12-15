# Deployment Guide - Logbook Generator API

Panduan lengkap untuk hosting aplikasi Laravel di server production menggunakan Nginx.

## Table of Contents
1. [Server Requirements](#server-requirements)
2. [Initial Server Setup](#initial-server-setup)
3. [Install Dependencies](#install-dependencies)
4. [Setup Database](#setup-database)
5. [Deploy Application](#deploy-application)
6. [Configure Nginx](#configure-nginx)
7. [SSL Certificate Setup](#ssl-certificate-setup)
8. [File Permissions & Access Management](#file-permissions--access-management)
9. [Environment Configuration](#environment-configuration)
10. [Maintenance & Updates](#maintenance--updates)

---

## Server Requirements

### Minimum Specifications
- **OS**: Ubuntu 22.04 LTS / Ubuntu 24.04 LTS
- **RAM**: 2GB minimum (4GB recommended)
- **Storage**: 20GB SSD
- **CPU**: 2 cores minimum

### Software Requirements
- PHP 8.2 or higher
- PostgreSQL 15 or higher
- Nginx 1.18 or higher
- Composer 2.x
- Git
- Node.js 18+ & NPM (untuk build assets)

---

## Initial Server Setup

### 1. Update System
```bash
# Login as root
sudo apt update && sudo apt upgrade -y

# Install basic utilities
sudo apt install -y software-properties-common curl wget git unzip
```

### 2. Create Deploy User
```bash
# Create user untuk deployment (jangan gunakan root)
sudo adduser deployer

# Add to sudo group
sudo usermod -aG sudo deployer

# Setup SSH key untuk user deployer
sudo su - deployer
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Upload SSH public key Anda ke ~/.ssh/authorized_keys
nano ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
exit
```

### 3. Configure Firewall
```bash
# Install UFW
sudo apt install -y ufw

# Allow SSH, HTTP, HTTPS
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable

# Check status
sudo ufw status
```

---

## Install Dependencies

### 1. Install PHP 8.2
```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP dan extensions yang dibutuhkan
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl \
    php8.2-zip php8.2-bcmath php8.2-intl php8.2-gd \
    php8.2-readline php8.2-opcache

# Verify installation
php -v
```

### 2. Install Composer
```bash
# Download dan install Composer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Verify
composer --version
```

### 3. Install PostgreSQL
```bash
# Install PostgreSQL 15
sudo apt install -y postgresql postgresql-contrib

# Start dan enable service
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Verify
sudo systemctl status postgresql
```

### 4. Install Nginx
```bash
# Install Nginx
sudo apt install -y nginx

# Start dan enable service
sudo systemctl start nginx
sudo systemctl enable nginx

# Verify
sudo systemctl status nginx
```

### 5. Install Node.js & NPM (Optional - untuk build assets)
```bash
# Install Node.js 18 LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node -v
npm -v
```

---

## Setup Database

### 1. Create Database & User
```bash
# Login as postgres user
sudo -u postgres psql

# Di PostgreSQL prompt:
CREATE DATABASE loggenerator_db;
CREATE USER loggenerator_user WITH ENCRYPTED PASSWORD 'your_secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE loggenerator_db TO loggenerator_user;

# Grant additional permissions (PostgreSQL 15+)
\c loggenerator_db
GRANT ALL ON SCHEMA public TO loggenerator_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO loggenerator_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO loggenerator_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO loggenerator_user;

# Exit
\q
```

### 2. Configure PostgreSQL untuk Remote Access (Optional)
```bash
# Edit postgresql.conf
sudo nano /etc/postgresql/15/main/postgresql.conf

# Uncomment dan ubah:
# listen_addresses = 'localhost'  # menjadi:
listen_addresses = '*'

# Edit pg_hba.conf
sudo nano /etc/postgresql/15/main/pg_hba.conf

# Tambahkan di bagian bawah:
host    loggenerator_db    loggenerator_user    0.0.0.0/0    md5

# Restart PostgreSQL
sudo systemctl restart postgresql
```

---

## Deploy Application

### 1. Clone Repository
```bash
# Login sebagai deployer user
sudo su - deployer

# Create directory
sudo mkdir -p /var/www/loggenerator
sudo chown -R deployer:deployer /var/www/loggenerator
cd /var/www/loggenerator

# Clone repository
git clone https://github.com/febryanalza/loggenerator_api.git .

# Atau jika menggunakan SSH:
# git clone git@github.com:febryanalza/loggenerator_api.git .
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install NPM dependencies (jika ada)
npm install
npm run build
```

### 3. Setup Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file
nano .env
```

### 4. Configure .env File
```env
APP_NAME="Logbook Generator API"
APP_ENV=production
APP_KEY=base64:... # sudah di-generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=loggenerator_db
DB_USERNAME=loggenerator_user
DB_PASSWORD=your_secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Sanctum
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

### 5. Run Migrations & Seeders
```bash
# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Configure Nginx

### 1. Create Nginx Configuration
```bash
# Create new site configuration
sudo nano /etc/nginx/sites-available/loggenerator
```

### 2. Basic Nginx Configuration (HTTP Only - untuk testing)
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    root /var/www/loggenerator/public;
    index index.php index.html index.htm;

    # Logging
    access_log /var/log/nginx/loggenerator-access.log;
    error_log /var/log/nginx/loggenerator-error.log;

    # Max upload size
    client_max_body_size 20M;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # FastCGI optimization
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to config files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 3. Enable Site
```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/loggenerator /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## SSL Certificate Setup

### 1. Install Certbot
```bash
# Install Certbot untuk Nginx
sudo apt install -y certbot python3-certbot-nginx
```

### 2. Obtain SSL Certificate
```bash
# Generate certificate (pastikan domain sudah pointing ke server)
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Follow prompts:
# - Enter email address
# - Agree to terms
# - Choose redirect HTTP to HTTPS (pilih option 2)
```

### 3. Updated Nginx Configuration (dengan SSL)
Certbot akan otomatis update configuration, tapi Anda bisa customize:

```bash
sudo nano /etc/nginx/sites-available/loggenerator
```

```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    root /var/www/loggenerator/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/loggenerator-access.log;
    error_log /var/log/nginx/loggenerator-error.log;

    # Max upload size
    client_max_body_size 20M;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # FastCGI optimization
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
    gzip_min_length 256;
}
```

### 4. Auto-Renewal SSL
```bash
# Test auto-renewal
sudo certbot renew --dry-run

# Certbot akan otomatis create cron job untuk renewal
# Verify dengan:
sudo systemctl list-timers | grep certbot
```

---

## File Permissions & Access Management

### 1. Set Ownership
```bash
# Set owner ke deployer user dan group ke www-data (Nginx user)
cd /var/www/loggenerator
sudo chown -R deployer:www-data .
```

### 2. Set Directory Permissions
```bash
# Base directory permissions
sudo find /var/www/loggenerator -type d -exec chmod 755 {} \;

# File permissions
sudo find /var/www/loggenerator -type f -exec chmod 644 {} \;
```

### 3. Set Storage & Cache Permissions (Critical!)
```bash
# Storage directory - MUST be writable by web server
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# Set group ownership
sudo chown -R deployer:www-data storage
sudo chown -R deployer:www-data bootstrap/cache

# Recursively set permissions
sudo find storage -type d -exec chmod 775 {} \;
sudo find storage -type f -exec chmod 664 {} \;
sudo find bootstrap/cache -type d -exec chmod 775 {} \;
sudo find bootstrap/cache -type f -exec chmod 664 {} \;
```

### 4. Public Directory Management
```bash
# Public directory (untuk uploaded files)
sudo mkdir -p storage/app/public
sudo chmod -R 775 storage/app/public
sudo chown -R deployer:www-data storage/app/public

# Create symbolic link dari public/storage ke storage/app/public
php artisan storage:link

# Verify symbolic link
ls -la public/storage
```

### 5. Protect Sensitive Files
```bash
# Protect .env file
sudo chmod 640 .env
sudo chown deployer:www-data .env

# Protect composer files
sudo chmod 644 composer.json composer.lock

# Artisan should be executable
sudo chmod 755 artisan
```

### 6. Setup Log Rotation
```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/loggenerator
```

Add content:
```
/var/www/loggenerator/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 deployer www-data
    sharedscripts
}
```

### 7. File Permission Summary

```
Directory/File Structure:
/var/www/loggenerator/
├── app/                    # 755 (directories), 644 (files)
├── bootstrap/
│   └── cache/             # 775 (writable by www-data)
├── config/                # 755 (directories), 644 (files)
├── database/              # 755 (directories), 644 (files)
├── public/                # 755 (directories), 644 (files)
│   └── storage/          # Symbolic link to storage/app/public
├── resources/             # 755 (directories), 644 (files)
├── routes/                # 755 (directories), 644 (files)
├── storage/               # 775 (writable by www-data)
│   ├── app/
│   │   └── public/       # 775 (for uploaded files)
│   ├── framework/        # 775
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/             # 775 (writable by www-data)
├── tests/                 # 755 (directories), 644 (files)
├── vendor/                # 755 (directories), 644 (files)
├── .env                   # 640 (sensitive!)
├── artisan                # 755 (executable)
└── composer.json          # 644

Ownership:
- Owner: deployer (untuk git pull dan composer update)
- Group: www-data (web server perlu write access ke storage & cache)
```

### 8. Create Management Script
```bash
# Create permission reset script
sudo nano /usr/local/bin/fix-laravel-permissions.sh
```

Add content:
```bash
#!/bin/bash

# Fix Laravel Permissions Script
# Usage: sudo fix-laravel-permissions.sh

LARAVEL_PATH="/var/www/loggenerator"
OWNER="deployer"
GROUP="www-data"

echo "Fixing Laravel permissions for: $LARAVEL_PATH"

# Set ownership
chown -R $OWNER:$GROUP $LARAVEL_PATH

# Set directory permissions
find $LARAVEL_PATH -type d -exec chmod 755 {} \;

# Set file permissions
find $LARAVEL_PATH -type f -exec chmod 644 {} \;

# Storage & cache must be writable
chmod -R 775 $LARAVEL_PATH/storage
chmod -R 775 $LARAVEL_PATH/bootstrap/cache

# Fix storage subdirectories
find $LARAVEL_PATH/storage -type d -exec chmod 775 {} \;
find $LARAVEL_PATH/storage -type f -exec chmod 664 {} \;
find $LARAVEL_PATH/bootstrap/cache -type d -exec chmod 775 {} \;
find $LARAVEL_PATH/bootstrap/cache -type f -exec chmod 664 {} \;

# Protect .env
chmod 640 $LARAVEL_PATH/.env

# Artisan executable
chmod 755 $LARAVEL_PATH/artisan

echo "Permissions fixed!"
```

Make script executable:
```bash
sudo chmod +x /usr/local/bin/fix-laravel-permissions.sh

# Run whenever needed:
sudo fix-laravel-permissions.sh
```

---

## Environment Configuration

### 1. PHP-FPM Optimization
```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Update these values:
```ini
; Process manager
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; PHP settings
php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = files
php_value[session.save_path] = /var/lib/php/sessions
php_value[upload_max_filesize] = 20M
php_value[post_max_size] = 20M
```

Create log directory:
```bash
sudo mkdir -p /var/log/php-fpm
sudo chown www-data:www-data /var/log/php-fpm
```

### 2. PHP.ini Optimization
```bash
# Edit PHP configuration
sudo nano /etc/php/8.2/fpm/php.ini
```

Update these values:
```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Jakarta

; OPcache settings (production optimization)
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=0
```

### 3. Restart Services
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## Maintenance & Updates

### 1. Git Deployment Workflow
```bash
# Login as deployer
sudo su - deployer
cd /var/www/loggenerator

# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations (if any)
php artisan migrate --force

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
sudo fix-laravel-permissions.sh

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 2. Create Deployment Script
```bash
# Create deployment script
nano ~/deploy.sh
```

Add content:
```bash
#!/bin/bash

set -e

APP_PATH="/var/www/loggenerator"
echo "🚀 Starting deployment..."

cd $APP_PATH

# Put application in maintenance mode
php artisan down --message="Updating application..." --retry=60

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize caches
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Fix permissions
echo "🔒 Fixing permissions..."
sudo /usr/local/bin/fix-laravel-permissions.sh

# Restart services
echo "🔄 Restarting services..."
sudo systemctl restart php8.2-fpm

# Bring application back online
php artisan up

echo "✅ Deployment completed!"
```

Make executable:
```bash
chmod +x ~/deploy.sh

# Run deployment:
./deploy.sh
```

### 3. Database Backup Script
```bash
# Create backup script
sudo nano /usr/local/bin/backup-database.sh
```

Add content:
```bash
#!/bin/bash

BACKUP_DIR="/var/backups/loggenerator"
DB_NAME="loggenerator_db"
DB_USER="loggenerator_user"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

echo "Creating database backup..."
PGPASSWORD='your_db_password' pg_dump -U $DB_USER -h localhost $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

Make executable and create cron:
```bash
sudo chmod +x /usr/local/bin/backup-database.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e

# Add line:
0 2 * * * /usr/local/bin/backup-database.sh >> /var/log/database-backup.log 2>&1
```

### 4. Monitoring Commands
```bash
# Check application logs
tail -f /var/www/loggenerator/storage/logs/laravel.log

# Check Nginx access logs
sudo tail -f /var/log/nginx/loggenerator-access.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/loggenerator-error.log

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check Nginx status
sudo systemctl status nginx

# Check disk usage
df -h

# Check memory usage
free -h

# Check running processes
ps aux | grep php
```

---

## Security Checklist

### ✅ Essential Security Steps

1. **Firewall Configuration**
   - [x] Enable UFW
   - [x] Allow only necessary ports (22, 80, 443)
   - [x] Close PostgreSQL port 5432 dari public (jika tidak perlu remote access)

2. **File Permissions**
   - [x] Set proper ownership (deployer:www-data)
   - [x] Protect .env file (640)
   - [x] Storage writable by www-data (775)
   - [x] Public files readable (755/644)

3. **Application Security**
   - [x] Set APP_DEBUG=false in production
   - [x] Use strong APP_KEY
   - [x] Configure CORS properly
   - [x] Set proper SESSION_DOMAIN

4. **Database Security**
   - [x] Use strong database password
   - [x] Limit database access to localhost only
   - [x] Regular backups

5. **SSL/TLS**
   - [x] Install SSL certificate
   - [x] Force HTTPS redirect
   - [x] Enable HSTS header

6. **Regular Updates**
   - [ ] Schedule monthly system updates
   - [ ] Keep PHP, Nginx, PostgreSQL updated
   - [ ] Monitor security advisories

---

## Troubleshooting

### 502 Bad Gateway
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check PHP-FPM logs
sudo tail -f /var/log/php-fpm/www-error.log

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Permission Denied Errors
```bash
# Run permission fix script
sudo fix-laravel-permissions.sh

# Check storage ownership
ls -la /var/www/loggenerator/storage
```

### Database Connection Failed
```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Test database connection
sudo -u postgres psql -d loggenerator_db -U loggenerator_user

# Check .env database credentials
cat /var/www/loggenerator/.env | grep DB_
```

### SSL Certificate Issues
```bash
# Renew certificate manually
sudo certbot renew

# Test SSL configuration
sudo nginx -t

# Check certificate expiry
sudo certbot certificates
```

---

## Quick Reference Commands

```bash
# Restart all services
sudo systemctl restart nginx php8.2-fpm postgresql

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Check logs
tail -f storage/logs/laravel.log
sudo tail -f /var/log/nginx/loggenerator-error.log

# Maintenance mode
php artisan down
php artisan up

# Deploy application
cd /var/www/loggenerator && ./deploy.sh

# Fix permissions
sudo fix-laravel-permissions.sh

# Backup database
sudo /usr/local/bin/backup-database.sh
```

---

## Support & Documentation

- Laravel Documentation: https://laravel.com/docs
- Nginx Documentation: https://nginx.org/en/docs/
- PostgreSQL Documentation: https://www.postgresql.org/docs/
- Let's Encrypt: https://letsencrypt.org/docs/

---

**Last Updated**: December 2025
