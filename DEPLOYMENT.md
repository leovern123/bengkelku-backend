# BengkelKu Backend - Deployment Guide

Panduan lengkap untuk mendeploy backend Laravel BengkelKu ke VPS agar bisa diakses online.

## Requirements VPS

- Ubuntu 20.04/22.04 LTS
- PHP 8.2+
- MySQL 5.7+/8.0+
- Nginx
- Composer
- Git
- Supervisor

---

## Quick Start (Otomatis)

### 1. Upload file project ke VPS

**Opsi A - Via Git (recommended):**
```bash
# Di VPS
mkdir -p /var/www/bengkelku
cd /var/www/bengkelku
git clone YOUR_GIT_REPO_URL .
```

**Opsi B - Via SCP dari komputer lokal:**
```bash
# Dari komputer lokal (PowerShell/CMD)
cd bengkelku-backend
tar -czf bengkelku-backend.tar.gz --exclude='vendor' --exclude='.env' --exclude='storage/logs/*' .
scp bengkelku-backend.tar.gz user@103.253.213.184:/tmp/

# Di VPS
mkdir -p /var/www/bengkelku
cd /var/www/bengkelku
tar -xzf /tmp/bengkelku-backend.tar.gz
rm /tmp/bengkelku-backend.tar.gz
```

### 2. Jalankan deployment script
```bash
cd /var/www/bengkelku
chmod +x deploy.sh
sudo bash deploy.sh
```

---

## Manual Deployment Steps

### Step 1: Install Dependencies di VPS

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# ========================================
# PENTING: Tambahkan PHP repository terlebih dahulu
# ========================================
sudo apt install -y software-properties
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# Install PHP dan extensions
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl

# Verifikasi PHP version
php -v

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Git
sudo apt install -y git

# Install Supervisor
sudo apt install -y supervisor
```

### Step 2: Setup Project

```bash
# Create project directory
sudo mkdir -p /var/www/bengkelku
cd /var/www/bengkelku

# Upload/copy semua file Laravel ke sini
```

### Step 3: Install PHP Dependencies

```bash
cd /var/www/bengkelku
composer install --optimize-autoloader --no-dev
```

### Step 4: Configure Environment

```bash
# Copy production environment
cp .env.production .env

# Edit .env dengan nano atau vim
nano .env
```

**Update bagian berikut di `.env`:**
```
APP_NAME="BengkelKu"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://103.253.213.184:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bengkelku
DB_USERNAME=bengkelku_user
DB_PASSWORD=YOUR_MYSQL_PASSWORD_HERE
```

### Step 5: Generate Application Key

```bash
php artisan key:generate --force
```

### Step 6: Setup Database

```bash
# Login ke MySQL
sudo mysql

# Create database dan user
CREATE DATABASE bengkelku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bengkelku_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON bengkelku.* TO 'bengkelku_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Jalankan migrations
cd /var/www/bengkelku
php artisan migrate --force
```

### Step 7: Setup Nginx

```bash
# Copy nginx configuration
sudo cp /var/www/bengkelku/nginx.conf /etc/nginx/sites-available/bengkelku
sudo ln -sf /etc/nginx/sites-available/bengkelku /etc/nginx/sites-enabled/

# Test nginx configuration
sudo nginx -t

# Restart nginx
sudo systemctl restart nginx
```

### Step 8: Setup Supervisor (Queue Worker)

```bash
# Copy supervisor configuration
sudo cp /var/www/bengkelku/supervisor.conf /etc/supervisor/conf.d/bengkelku.conf

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bengkelku:*
```

### Step 9: Setup Storage Link

```bash
cd /var/www/bengkelku
php artisan storage:link
```

### Step 10: Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 11: Setup Firewall (Optional)

```bash
# Allow HTTP/SSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp

# Enable firewall
sudo ufw enable
```

---

## Verifikasi

Test API setelah deployment selesai:

```bash
# Test API health
curl http://103.253.213.184:8000/api/me

# Test login (ganti dengan kredensial Anda)
curl -X POST http://103.253.213.184:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

---

## Update Backend (Deploy Ulang)

Ketika ada perubahan kode, update di VPS:

```bash
cd /var/www/bengkelku

# Jika pakai Git:
git pull origin main

# Jika upload manual, upload file yang berubah

# Update dependencies
composer install --optimize-autoloader --no-dev

# Generate new key jika perlu
php artisan key:generate --force

# Migrate database jika ada perubahan
php artisan migrate --force

# Cache ulang
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue
sudo supervisorctl restart bengkelku:*
```

---

## Useful Commands

```bash
# View Laravel logs
tail -f /var/www/bengkelku/storage/logs/laravel.log

# View queue worker status
sudo supervisorctl status bengkelku:*

# Restart queue workers
sudo supervisorctl restart bengkelku:*

# Restart Nginx
sudo systemctl restart nginx

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Test Nginx config
sudo nginx -t

# Check MySQL status
sudo systemctl status mysql
```

---

## Troubleshooting

### Error: "Unable to locate package"
```bash
sudo apt update
```

### Error: Permission denied
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/bengkelku
sudo chmod -R 755 /var/www/bengkelku/storage
sudo chmod -R 755 /var/www/bengkelku/bootstrap/cache
```

### Error: Database connection
```bash
# Check MySQL is running
sudo systemctl status mysql

# Verify .env database credentials
cat /var/www/bengkelku/.env | grep DB_
```

### Error: CORS issues dari Flutter app
Pastikan nginx.conf sudah ada CORS headers (sudah termasuk di file nginx.conf yang disediakan).

### API tidak merespon
```bash
# Check nginx is running
sudo systemctl status nginx

# Check nginx error logs
sudo tail -f /var/log/nginx/bengkelku-error.log

# Check if port 8000 is accessible
sudo ufw status
curl http://localhost:8000/api/me
```

---

## Security Notes

1. **Selalu set `APP_DEBUG=false` di production**
2. **Gunakan password database yang kuat**
3. **Setup SSL/Certificate untuk HTTPS**
4. **Backup database secara berkala**
5. **Update Ubuntu dan packages secara berkala**

---

## File yang Disediakan

| File | Fungsi |
|------|--------|
| `.env.production` | Konfigurasi environment production |
| `deploy.sh` | Script deployment otomatis |
| `nginx.conf` | Konfigurasi web server Nginx |
| `supervisor.conf` | Konfigurasi queue worker |
| `DEPLOYMENT.md` | Panduan deployment (file ini) |