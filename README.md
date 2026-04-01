# Statisus

A self-hosted uptime and status page monitor built with Laravel + Filament.

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- SQLite (default) or MySQL/PostgreSQL

## Quick Start (Local)

```bash
git clone https://github.com/Roreos/statisus.git
cd statisus

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Assets
npm run build

# Storage symlink
php artisan storage:link

# Start (all-in-one dev command)
composer run dev
```

The seeder creates an admin user. Set credentials via `.env` before seeding:

```env
ADMIN_NAME=Admin
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=changeme
```

Then visit `http://localhost:8000/admin`.

---

## Production Deployment

### 1. Server setup

```bash
# Clone and install
git clone https://github.com/Roreos/statisus.git /var/www/statisus
cd /var/www/statisus

composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate

# Edit .env:
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://status.yourcompany.com
# DB_CONNECTION=sqlite  (or mysql/pgsql)
# QUEUE_CONNECTION=database
# MAIL_MAILER=smtp  (configure your mail provider)
```

### 3. Database & storage

```bash
touch database/database.sqlite   # if using SQLite
php artisan migrate --force
php artisan db:seed
php artisan storage:link
```

### 4. Permissions

```bash
chown -R www-data:www-data /var/www/statisus
chmod -R 755 /var/www/statisus/storage
chmod -R 755 /var/www/statisus/bootstrap/cache
```

### 5. Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Web server

**Caddy** (recommended — auto SSL):

```
# /etc/caddy/Caddyfile
status.yourcompany.com {
    root * /var/www/statisus/public
    php_fastcgi unix//run/php/php8.5-fpm.sock
    file_server
    encode gzip
}
```

**Nginx:**

```nginx
server {
    listen 443 ssl;
    server_name status.yourcompany.com;
    root /var/www/statisus/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/status.yourcompany.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/status.yourcompany.com/privkey.pem;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Scheduler (cron)

Add one cron entry — Laravel handles everything else:

```bash
* * * * * cd /var/www/statisus && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Queue worker (systemd)

Create `/etc/systemd/system/statisus-worker.service`:

```ini
[Unit]
Description=Statisus Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/statisus
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=60 --max-time=3600
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
systemctl enable statisus-worker
systemctl start statisus-worker
```

---

## Roles

| Role   | Access |
|--------|--------|
| admin  | Full access — manage monitors, alerts, users, settings |
| viewer | Read-only — can view dashboard and resources |

The first user created by the seeder is `admin`. Invite additional users via **Settings → Invitations**.

## Monitor Types

| Type        | Description |
|-------------|-------------|
| HTTP/HTTPS  | HTTP status code, body content, response time |
| TCP/Port    | TCP socket connectivity |
| Ping (ICMP) | ICMP ping |
| DNS         | DNS record resolution and value matching |
| SSL Cert    | Certificate validity and expiry warning |
