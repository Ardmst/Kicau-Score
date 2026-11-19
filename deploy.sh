#!/bin/bash
# ==============================================================
# Skrip Deployment Otomatis KicauScore untuk VPS (85.211.245.219)
# Domain: kicaumania.ardmst.my.id
# ==============================================================

set -e

echo "🚀 Memulai proses deployment KicauScore..."

# Masuk ke direktori webroot
cd /var/www/kicauscore || exit 1

# Aktifkan maintenance mode sementara
php artisan down --render="errors::503" --secret="kicausuperpass" || true

# Ambil update kode terbaru dari repository Git
echo "📦 Mengambil source code terbaru dari git..."
git pull origin main

# Install dependensi PHP untuk production (tanpa dev package)
echo "🐘 Menginstall composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Jalankan migrasi database
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

# Bersihkan dan pasang cache performa Laravel
echo "⚡ Mengoptimalkan cache konfigurasi, route, dan view..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Buat symbolic link storage berkas upload jika belum ada
php artisan storage:link || true

# Atur kepemilikan dan permission direktori storage dan cache
echo "🔒 Mengatur hak akses berkas..."
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache
if [ -f database/database.sqlite ]; then
    chmod 664 database/database.sqlite
    chmod 775 database
fi

# Matikan maintenance mode
php artisan up

# Restart PHP-FPM
echo "🔄 Merestart PHP-FPM & Nginx..."
systemctl reload php8.4-fpm || systemctl reload php8.3-fpm || true
systemctl reload nginx

echo "✅ Deployment KicauScore ke https://kicaumania.ardmst.my.id berhasil!"
