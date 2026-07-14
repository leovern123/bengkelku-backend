#!/bin/bash

# ============================================
# BengkelKu Backend Deployment Script
# Deploy Laravel to VPS (Ubuntu 22.04/24.04)
# ============================================

set -e

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Konfigurasi - UBAH SESUAI KEBUTUHAN
PROJECT_PATH="/var/www/bengkelku"
DOMAIN="103.253.213.184"
PORT=8000
DB_NAME="bengkelku"
DB_USER="bengkelku_user"
DB_PASS="CHANGE_THIS_PASSWORD"   # <-- GANTI dengan password yang kuat!
GIT_REPO=""                       # <-- ISI URL repo git Anda

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  BengkelKu Backend Deployment Script${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""

# ============================================
# STEP 1: Install system dependencies
# ============================================
echo -e "${YELLOW}[Step 1/10] Installing system dependencies...${NC}"

sudo apt update

# PENTING: Tambahkan PHP PPA repository untuk Ubuntu 22.04/24.04
# PHP 8.2 tidak ada di default Ubuntu repo, perlu PPA ondrej/php
echo -e "${BLUE}Adding PHP PPA repository (required for PHP 8.2)...${NC}"
sudo apt install -y software-properties
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

sudo apt install -y \
    nginx \
    mysql-server \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-curl \
    php8.2-gd \
    php8.2-zip \
    php8.2-intl \
    php8.2-tokenizer \
    unzip \
    git \
    supervisor \
    curl

# Install Composer if not installed
if ! command -v composer &> /dev/null; then
    echo -e "${BLUE}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

echo -e "${GREEN}[Step 1/10] Done!${NC}"
echo ""

# ============================================
# STEP 2: Setup MySQL database
# ============================================
echo -e "${YELLOW}[Step 2/10] Setting up MySQL database...${NC}"

sudo mysql <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}[Step 2/10] Database '${DB_NAME}' ready!${NC}"
echo ""

# ============================================
# STEP 3: Clone/update project
# ============================================
echo -e "${YELLOW}[Step 3/10] Setting up project files...${NC}"

if [ ! -d "$PROJECT_PATH" ]; then
    sudo mkdir -p "$PROJECT_PATH"
    sudo chown $USER:www-data "$PROJECT_PATH"
    sudo chmod 775 "$PROJECT_PATH"

    if [ -n "$GIT_REPO" ]; then
        echo "Cloning from git..."
        git clone "$GIT_REPO" "$PROJECT_PATH"
    else
        echo -e "${RED}ERROR: GIT_REPO is not set!${NC}"
        echo "Set GIT_REPO variable at the top of this script, or copy files manually to $PROJECT_PATH"
        echo "Then re-run this script."
        exit 1
    fi
else
    echo "Project directory exists, pulling updates..."
    cd "$PROJECT_PATH"
    git pull origin main || git pull origin master || echo "Git pull skipped"
fi

echo -e "${GREEN}[Step 3/10] Project files ready!${NC}"
echo ""

# ============================================
# STEP 4: Install PHP dependencies
# ============================================
echo -e "${YELLOW}[Step 4/10] Installing PHP dependencies...${NC}"

cd "$PROJECT_PATH"
composer install --optimize-autoloader --no-dev --no-interaction

echo -e "${GREEN}[Step 4/10] Dependencies installed!${NC}"
echo ""

# ============================================
# STEP 5: Configure environment
# ============================================
echo -e "${YELLOW}[Step 5/10] Configuring environment...${NC}"

cd "$PROJECT_PATH"

# Copy production env
cp .env.production .env

# Update database credentials in .env
sed -i "s/DB_DATABASE=bengkelku/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/DB_USERNAME=bengkelku_user/DB_USERNAME=${DB_USER}/" .env
sed -i "s/DB_PASSWORD=YOUR_MYSQL_PASSWORD_HERE/DB_PASSWORD=${DB_PASS}/" .env

# Generate app key
php artisan key:generate --force

echo -e "${GREEN}[Step 5/10] Environment configured!${NC}"
echo ""

# ============================================
# STEP 6: Run migrations & setup storage
# ============================================
echo -e "${YELLOW}[Step 6/10] Running migrations & storage setup...${NC}"

cd "$PROJECT_PATH"
php artisan migrate --force
php artisan storage:link

echo -e "${GREEN}[Step 6/10] Database migrated!${NC}"
echo ""

# ============================================
# STEP 7: Set permissions
# ============================================
echo -e "${YELLOW}[Step 7/10] Setting file permissions...${NC}"

cd "$PROJECT_PATH"
sudo chown -R $USER:www-data .
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 775 storage/app/public

echo -e "${GREEN}[Step 7/10] Permissions set!${NC}"
echo ""

# ============================================
# STEP 8: Configure Nginx
# ============================================
echo -e "${YELLOW}[Step 8/10] Configuring Nginx...${NC}"

# Remove default nginx site
sudo rm -f /etc/nginx/sites-enabled/default

# Copy Nginx config
sudo cp "$PROJECT_PATH/nginx.conf" /etc/nginx/sites-available/bengkelku
sudo ln -sf /etc/nginx/sites-available/bengkelku /etc/nginx/sites-enabled/bengkelku

# Test & restart Nginx
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable nginx

echo -e "${GREEN}[Step 8/10] Nginx configured on port ${PORT}!${NC}"
echo ""

# ============================================
# STEP 9: Configure Supervisor (Queue Worker)
# ============================================
echo -e "${YELLOW}[Step 9/10] Configuring Supervisor (auto-restart queue)...${NC}"

sudo cp "$PROJECT_PATH/supervisor.conf" /etc/supervisor/conf.d/bengkelku.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bengkelku-worker:* || echo "Workers may already be running"

# Enable supervisor on boot
sudo systemctl enable supervisor

echo -e "${GREEN}[Step 9/10] Supervisor configured!${NC}"
echo ""

# ============================================
# STEP 10: Optimize Laravel & final checks
# ============================================
echo -e "${YELLOW}[Step 10/10] Optimizing Laravel...${NC}"

cd "$PROJECT_PATH"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure PHP-FPM is running & enabled
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm

echo -e "${GREEN}[Step 10/10] Optimization complete!${NC}"
echo ""

# ============================================
# DONE
# ============================================
echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  ✅ Deployment Complete!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo -e "API URL:  ${BLUE}http://${DOMAIN}:${PORT}/api${NC}"
echo -e "Login:    ${BLUE}http://${DOMAIN}:${PORT}/api/login${NC}"
echo ""
echo -e "${YELLOW}Services yang berjalan:${NC}"
echo "  ✅ Nginx        → web server (port $PORT)"
echo "  ✅ PHP-FPM      → PHP processor"
echo "  ✅ MySQL        → database"
echo "  ✅ Supervisor   → queue worker (auto-restart)"
echo ""
echo -e "${YELLOW}Semua service akan auto-start saat VPS reboot.${NC}"
echo ""
echo -e "${YELLOW}Commands yang berguna:${NC}"
echo "  View logs:         tail -f $PROJECT_PATH/storage/logs/laravel.log"
echo "  Restart queue:     sudo supervisorctl restart bengkelku-worker:*"
echo "  Restart PHP:       sudo systemctl restart php8.2-fpm"
echo "  Restart Nginx:     sudo systemctl restart nginx"
echo "  Check status:      sudo systemctl status nginx php8.2-fpm"
echo "  Nginx error log:   tail -f /var/log/nginx/bengkelku-error.log"
echo ""
echo -e "${RED}⚠️  PENTING: Jangan lupa ganti DB_PASS di bagian atas script ini!${NC}"
echo ""