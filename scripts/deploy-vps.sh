#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize

sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "Deploy VPS selesai."
