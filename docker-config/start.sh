#!/bin/bash
set -e

echo "======================================"
echo "  MultiDependencias - Starting Up"
echo "======================================"

cd /var/www/html

# ==========================================
# Paso 1: Generar .env desde variables de Render
# (PHP-FPM limpia el entorno, por eso lo escribimos antes)
# ==========================================
cat > .env << EOF
APP_NAME=${APP_NAME:-MultiDependencias}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-defaultdb}
DB_USERNAME=${DB_USERNAME:-avnadmin}
DB_PASSWORD=${DB_PASSWORD}

MYSQL_ATTR_SSL_CA=${MYSQL_ATTR_SSL_CA:-/etc/ssl/certs/ca-certificates.crt}

SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_DRIVER=${CACHE_DRIVER:-file}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
BROADCAST_CONNECTION=${BROADCAST_DRIVER:-log}

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=${LOG_LEVEL:-debug}
FILESYSTEM_DISK=local

MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME:-MultiDependencias}"
EOF

chown www-data:www-data .env
echo "[OK] .env generado desde variables de entorno de Render"

# ==========================================
# Paso 2: Asegurar directorios de storage
# ==========================================
mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "[OK] Permisos de storage configurados"

# ==========================================
# Paso 3: Optimizar Laravel
# ==========================================
php artisan config:clear  2>&1 || true
php artisan config:cache  2>&1
php artisan route:cache   2>&1 || true
php artisan view:cache    2>&1 || true
php artisan storage:link --force 2>&1 || true
echo "[OK] Laravel caches generados"

# ==========================================
# Paso 4: Arrancar servicios
# ==========================================
echo "-> Iniciando PHP-FPM..."
php-fpm -D

sleep 2

echo "-> Iniciando Nginx (foreground)..."
exec nginx -g "daemon off;"
