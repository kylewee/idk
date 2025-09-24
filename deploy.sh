#!/bin/bash

# Deployment script for MechanicSaintAugustine.com
# Run this script as root or with appropriate sudo privileges

set -e

echo "🔧 MechanicSaintAugustine.com Deployment Script"
echo "=============================================="

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo "⚠️  Running as root. Please ensure you trust this script."
fi

# Configuration
WEB_ROOT="/var/www/mechanicsaintaugustine.com"
DB_NAME="mechanic_sa"
DB_USER="mechanic_user"

echo "📁 Setting up web directory..."
sudo mkdir -p $WEB_ROOT
sudo cp -R . $WEB_ROOT/
sudo chown -R www-data:www-data $WEB_ROOT
sudo chmod -R 755 $WEB_ROOT

echo "📁 Creating logs directory..."
sudo mkdir -p $WEB_ROOT/logs
sudo chown www-data:www-data $WEB_ROOT/logs
sudo chmod 755 $WEB_ROOT/logs

echo "🗄️  Setting up database..."
read -p "Enter MySQL root password: " -s mysql_root_password
echo

# Create database and user
mysql -u root -p$mysql_root_password -e "
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
"

# Import schema
mysql -u root -p$mysql_root_password $DB_NAME < $WEB_ROOT/api/database_schema.sql

echo "🔧 Installing PHP dependencies..."
sudo apt-get update
sudo apt-get install -y php8.2-fpm php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring

echo "🌐 Setting up Caddy..."
# Install Caddy if not present
if ! command -v caddy &> /dev/null; then
    echo "Installing Caddy..."
    sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
    sudo apt update
    sudo apt install -y caddy
fi

# Copy Caddyfile to system location
sudo cp $WEB_ROOT/Caddyfile /etc/caddy/
sudo systemctl reload caddy

echo "⚙️  Configuration reminder:"
echo "1. Edit $WEB_ROOT/api/.env.local.php with your:"
echo "   - Database password (change from 'secure_password_here')"
echo "   - Twilio credentials"
echo "   - Rukovoditel CRM settings"
echo "2. Update domain name in /etc/caddy/Caddyfile"
echo "3. Restart Caddy: sudo systemctl restart caddy"

echo "✅ Deployment completed!"
echo "🌐 Visit your domain to see the website"
echo "📋 Check logs in $WEB_ROOT/logs/ for troubleshooting"