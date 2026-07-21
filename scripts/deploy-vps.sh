#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

git pull origin main
export COMPOSER_ALLOW_SUPERUSER=1
php8.2 /usr/bin/composer install --no-dev --optimize-autoloader
php8.2 artisan migrate --force
php8.2 artisan storage:link --force
php8.2 artisan optimize:clear
php8.2 artisan optimize

sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl is-enabled --quiet undangan-bali-queue.service && sudo systemctl restart undangan-bali-queue.service || true
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "Deploy VPS selesai."
