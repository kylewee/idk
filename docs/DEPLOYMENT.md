# Deployment Guide

This guide covers deployment of the Mechanic Saint Augustine platform to production environments.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Deployment Options](#deployment-options)
- [Docker Deployment](#docker-deployment)
- [Manual Deployment](#manual-deployment)
- [Configuration](#configuration)
- [Twilio Setup](#twilio-setup)
- [CRM Setup](#crm-setup)
- [Post-Deployment](#post-deployment)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Server Requirements

**Minimum**:
- 2 CPU cores
- 4 GB RAM
- 20 GB disk space
- Ubuntu 20.04 LTS or later

**Recommended**:
- 4 CPU cores
- 8 GB RAM
- 50 GB SSD
- Ubuntu 22.04 LTS

### Software Requirements

- Docker 20.10+ and Docker Compose 2.0+ (for Docker deployment)
- OR PHP 8.2+, MariaDB 10.11+, Caddy 2+ (for manual deployment)
- Git
- Domain name with DNS configured
- SSL certificate (Caddy provides automatic Let's Encrypt)

### External Services

- Twilio account with phone number
- OpenAI API account (optional, for AI features)
- Email service (SMTP)

---

## Deployment Options

### Option 1: Docker (Recommended)

**Pros**:
- Consistent environment
- Easy updates
- Simplified dependency management
- Quick rollback

**Cons**:
- Additional resource overhead
- Requires Docker knowledge

### Option 2: Manual Installation

**Pros**:
- Lower resource usage
- More control over configuration
- Better performance

**Cons**:
- Complex setup
- Manual dependency management
- OS-specific issues

---

## Docker Deployment

### 1. Prepare Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose-plugin

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker

# Verify installation
docker --version
docker compose version
```

### 2. Clone Repository

```bash
# Clone from GitHub
git clone https://github.com/kylewee/idk.git
cd idk

# Checkout production branch (if different)
git checkout main
```

### 3. Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env
```

**Required Configuration**:

```bash
# Twilio
TWILIO_ACCOUNT_SID=your_account_sid_here
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_SMS_FROM=+19045551234
TWILIO_FORWARD_TO=+19045556789

# OpenAI (optional)
OPENAI_API_KEY=sk-your-key-here

# CRM
CRM_API_URL=https://yourdomain.com/crm/api/rest.php
CRM_API_KEY=your_crm_api_key
CRM_USERNAME=admin
CRM_PASSWORD=secure_password_here
CRM_LEADS_ENTITY_ID=26

# Database
DB_SERVER=mariadb
DB_SERVER_USERNAME=mechanic
DB_SERVER_PASSWORD=secure_db_password
DB_DATABASE=rukovoditel

# Security
VOICE_RECORDINGS_TOKEN=random_token_here
```

### 4. Update Caddyfile for Production

Edit `Caddyfile`:

```caddy
mechanicstaugustine.com {
    encode gzip

    # Root directory
    root * /var/www/html

    # PHP-FPM
    php_fastcgi php:9000

    # File server
    file_server

    # Logging
    log {
        output file /var/log/caddy/access.log
        level INFO
    }

    # Security headers
    header {
        Strict-Transport-Security "max-age=31536000;"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
        Referrer-Policy "strict-origin-when-cross-origin"
    }
}
```

### 5. Start Services

```bash
# Start in detached mode
docker compose up -d

# View logs
docker compose logs -f

# Check status
docker compose ps
```

### 6. Initialize Database

```bash
# Access CRM via browser
https://yourdomain.com/crm

# Follow Rukovoditel installation wizard
# Database host: mariadb
# Database name: rukovoditel
# Username: mechanic
# Password: (from .env)
```

### 7. Verify Deployment

```bash
# Test website
curl https://yourdomain.com

# Test quote endpoint
curl -X POST https://yourdomain.com/quote/quote_intake_handler.php \
  -H "Content-Type: application/json" \
  -d '{"first_name":"Test","last_name":"User","phone":"9045551234"}'

# Check logs
docker compose logs php
docker compose logs caddy
```

---

## Manual Deployment

### 1. Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-curl \
  php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl

# Install MariaDB
sudo apt install -y mariadb-server
sudo mysql_secure_installation

# Install Caddy
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update
sudo apt install caddy

# Verify installations
php -v
mysql --version
caddy version
```

### 2. Configure Database

```bash
# Create database and user
sudo mysql -u root -p

# In MySQL prompt:
CREATE DATABASE rukovoditel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mechanic'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON rukovoditel.* TO 'mechanic'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clone and Configure Application

```bash
# Create web directory
sudo mkdir -p /var/www/mechanicstaugustine
cd /var/www/mechanicstaugustine

# Clone repository
sudo git clone https://github.com/kylewee/idk.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/mechanicstaugustine
sudo chmod -R 755 /var/www/mechanicstaugustine

# Configure environment
sudo cp .env.example .env
sudo nano .env
# (Edit with your values)

# Set proper permissions for .env
sudo chmod 600 .env
```

### 4. Configure PHP-FPM

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[memory_limit] = 256M
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

### 5. Configure Caddy

Create `/etc/caddy/Caddyfile`:

```caddy
mechanicstaugustine.com {
    root * /var/www/mechanicstaugustine
    encode gzip

    php_fastcgi unix//run/php/php8.2-fpm.sock
    file_server

    log {
        output file /var/log/caddy/mechanicstaugustine.log
        format json
    }

    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
    }
}
```

Start Caddy:

```bash
sudo systemctl restart caddy
sudo systemctl enable caddy
```

### 6. Configure Logging

```bash
# Create log directories
sudo mkdir -p /var/www/mechanicstaugustine/voice
sudo mkdir -p /var/www/mechanicstaugustine/quote

# Set permissions
sudo chown -R www-data:www-data /var/www/mechanicstaugustine
sudo chmod -R 755 /var/www/mechanicstaugustine

# Configure logrotate
sudo nano /etc/logrotate.d/mechanicstaugustine
```

Add to logrotate config:

```
/var/www/mechanicstaugustine/voice/*.log
/var/www/mechanicstaugustine/quote/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

---

## Configuration

### Environment Variables

See `.env.example` for all available options.

**Critical Variables**:

```bash
# Never commit these to version control!
TWILIO_AUTH_TOKEN=...
OPENAI_API_KEY=...
CRM_PASSWORD=...
DB_SERVER_PASSWORD=...
```

### File Permissions

```bash
# Application files
sudo chown -R www-data:www-data /var/www/mechanicstaugustine
sudo find /var/www/mechanicstaugustine -type d -exec chmod 755 {} \;
sudo find /var/www/mechanicstaugustine -type f -exec chmod 644 {} \;

# Environment file (extra security)
sudo chmod 600 /var/www/mechanicstaugustine/.env

# Make writable directories
sudo chmod 775 /var/www/mechanicstaugustine/voice
sudo chmod 775 /var/www/mechanicstaugustine/quote
sudo chmod 775 /var/www/mechanicstaugustine/crm/uploads
```

---

## Twilio Setup

### 1. Purchase Phone Number

1. Log into Twilio Console
2. Phone Numbers → Buy a Number
3. Select a number in your area code
4. Purchase

### 2. Configure Webhooks

**Voice Configuration**:

1. Go to Phone Numbers → Active Numbers
2. Click your number
3. Under "Voice & Fax" section:
   - **A CALL COMES IN**: Webhook → `https://mechanicstaugustine.com/voice/incoming.php` → HTTP POST
4. Save

**Recording Configuration**:

Webhooks are set in TwiML (no additional config needed here)

### 3. Test Voice System

```bash
# Call your Twilio number
# Should forward to your business phone
# After hanging up, check logs:
tail -f /var/www/mechanicstaugustine/voice/voice.log
```

---

## CRM Setup

### 1. Install Rukovoditel

1. Navigate to `https://mechanicstaugustine.com/crm`
2. Follow installation wizard
3. Configure database connection
4. Create admin account

### 2. Configure Leads Entity

1. Log in as admin
2. Go to **Settings → Entities**
3. Create or configure "Leads" entity
4. Note the Entity ID (visible in URL)
5. Update `.env`: `CRM_LEADS_ENTITY_ID=26`

### 3. Map Custom Fields

1. In Leads entity, create fields:
   - First Name (text)
   - Last Name (text)
   - Phone (text)
   - Address (textarea)
   - Vehicle Year (number)
   - Vehicle Make (text)
   - Vehicle Model (text)
   - Notes (textarea)
2. Note each field's ID (visible when editing)
3. Update `.env` `CRM_FIELD_MAP`:

```json
{
  "first_name": 219,
  "last_name": 220,
  "phone": 227,
  "address": 234,
  "year": 231,
  "make": 232,
  "model": 233,
  "notes": 230
}
```

### 4. Generate API Key

1. Settings → API
2. Enable API access
3. Generate new API key
4. Update `.env`: `CRM_API_KEY=...`

---

## Post-Deployment

### Security Checklist

- [ ] Change all default passwords
- [ ] Configure firewall (UFW)
- [ ] Enable HTTPS (Caddy auto-provisions)
- [ ] Set up automatic security updates
- [ ] Configure fail2ban
- [ ] Restrict database access to localhost
- [ ] Review file permissions
- [ ] Enable audit logging

### UFW Firewall

```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

### Automatic Updates

```bash
sudo apt install unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

### Backup Configuration

```bash
# Create backup script
sudo nano /usr/local/bin/backup-mechanicstaugustine.sh
```

Script content:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/mechanicstaugustine"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup database
mysqldump -u mechanic -p'your_password' rukovoditel | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup files
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" /var/www/mechanicstaugustine

# Keep only last 30 days
find "$BACKUP_DIR" -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable and schedule:

```bash
sudo chmod +x /usr/local/bin/backup-mechanicstaugustine.sh
sudo crontab -e

# Add daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-mechanicstaugustine.sh
```

---

## Monitoring

### Application Monitoring

```bash
# Monitor logs in real-time
tail -f /var/www/mechanicstaugustine/voice/voice.log
tail -f /var/www/mechanicstaugustine/quote/quote_intake.log

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check Caddy status
sudo systemctl status caddy

# Check database status
sudo systemctl status mariadb
```

### Resource Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Monitor CPU/Memory
htop

# Monitor disk I/O
sudo iotop

# Monitor network
sudo nethogs
```

### Uptime Monitoring

**Recommended Services**:
- UptimeRobot (free tier)
- Pingdom
- StatusCake

Configure to monitor:
- `https://mechanicstaugustine.com` (200 OK)
- `https://mechanicstaugustine.com/quote/` (200 OK)

---

## Troubleshooting

### Common Issues

#### 1. "502 Bad Gateway"

**Cause**: PHP-FPM not running

**Solution**:
```bash
sudo systemctl status php8.2-fpm
sudo systemctl restart php8.2-fpm
sudo journalctl -u php8.2-fpm -n 50
```

#### 2. "Permission denied" errors

**Cause**: Wrong file permissions

**Solution**:
```bash
sudo chown -R www-data:www-data /var/www/mechanicstaugustine
sudo chmod -R 755 /var/www/mechanicstaugustine
```

#### 3. Database connection failed

**Cause**: Wrong credentials or database not running

**Solution**:
```bash
# Check MariaDB status
sudo systemctl status mariadb

# Test connection
mysql -u mechanic -p rukovoditel

# Verify .env settings
grep DB_ /var/www/mechanicstaugustine/.env
```

#### 4. Twilio webhooks not working

**Cause**: URL not publicly accessible or wrong configuration

**Solution**:
```bash
# Test publicly
curl https://mechanicstaugustine.com/voice/incoming.php

# Check Twilio debugger
# Visit: https://www.twilio.com/console/debugger
```

#### 5. CRM API errors

**Cause**: Wrong API key or entity ID

**Solution**:
```bash
# Test API manually
curl -X POST https://mechanicstaugustine.com/crm/api/rest.php \
  -d "action=login&username=admin&password=yourpass"

# Verify entity ID in CRM
# Check URL when viewing Leads entity
```

---

## Rollback Procedure

### Docker Deployment

```bash
# Stop current version
docker compose down

# Checkout previous version
git checkout previous-tag

# Restart
docker compose up -d
```

### Manual Deployment

```bash
# Stop services
sudo systemctl stop caddy php8.2-fpm

# Restore from backup
sudo tar -xzf /var/backups/mechanicstaugustine/files_DATE.tar.gz -C /

# Restore database
zcat /var/backups/mechanicstaugustine/db_DATE.sql.gz | mysql -u mechanic -p rukovoditel

# Restart services
sudo systemctl start php8.2-fpm caddy
```

---

## Maintenance

### Regular Tasks

**Daily**:
- Check error logs
- Monitor disk space
- Review new leads in CRM

**Weekly**:
- Review backup integrity
- Check for security updates
- Monitor resource usage

**Monthly**:
- Update dependencies
- Review and rotate API keys
- Test disaster recovery

---

## Support

For deployment issues:
- GitHub Issues: https://github.com/kylewee/idk/issues
- Documentation: https://github.com/kylewee/idk/wiki
