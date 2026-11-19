#!/bin/bash
set -e
cd /var/www/kicauscore

cp -n .env.example .env || true
sed -i 's|APP_NAME=.*|APP_NAME=KicauScore|g' .env
sed -i 's|APP_ENV=.*|APP_ENV=production|g' .env
sed -i 's|APP_DEBUG=.*|APP_DEBUG=false|g' .env
sed -i 's|APP_URL=.*|APP_URL=https://kicaumania.ardmst.my.id|g' .env
sed -i 's|DB_CONNECTION=.*|DB_CONNECTION=sqlite|g' .env
sed -i 's|SESSION_DRIVER=.*|SESSION_DRIVER=database|g' .env
sed -i 's|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|g' .env

composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate:fresh --seed --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setup completed successfully."
