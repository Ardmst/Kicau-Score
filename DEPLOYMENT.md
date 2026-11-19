# Panduan Deployment KicauScore ke VPS

- **Subdomain**: `kicaumania.ardmst.my.id`
- **Target IP VPS**: `85.211.245.219`
- **Nameserver Cloudflare**: `emma.ns.cloudflare.com` & `nero.ns.cloudflare.com`

---

## Langkah 1: Tambah DNS Record di Cloudflare Dashboard

Karena sistem asisten tidak memiliki akses login langsung ke sesi browser Cloudflare Anda, Anda cukup menambahkan 1 baris DNS record berikut di [dash.cloudflare.com](https://dash.cloudflare.com/):

1. Masuk ke Cloudflare Dashboard &rarr; pilih domain **`ardmst.my.id`**.
2. Klik tab **DNS** &rarr; **Records** &rarr; tombol **Add Record**.
3. Isi kolom sebagai berikut:
   - **Type**: `A`
   - **Name**: `kicaumania`
   - **IPv4 address**: `85.211.245.219`
   - **Proxy status**: `Proxied` (Awan Oranye) &mdash; *opsional: jika ingin SSL & proteksi DDoS otomatis dari Cloudflare*.
   - **TTL**: `Auto`
4. Klik **Save**. Subdomain `kicaumania.ardmst.my.id` kini aktif dalam hitungan detik!

*(Alternatif: Jika Anda memiliki Cloudflare API Token, bisa menjalankan cURL di bawah ini):*
```bash
curl -X POST "https://api.cloudflare.com/client/v4/zones/<ZONE_ID>/dns_records" \
     -H "Authorization: Bearer <API_TOKEN>" \
     -H "Content-Type: application/json" \
     --data '{"type":"A","name":"kicaumania","content":"85.211.245.219","ttl":1,"proxied":true}'
```

---

## Langkah 2: Setup di VPS (`85.211.245.219`)

Masuk ke VPS via SSH:
```bash
ssh root@85.211.245.219
```

### 1. Clone Repositori ke `/var/www/kicauscore`
```bash
cd /var/www
git clone <URL_REPO_GITHUB_ANDA> kicauscore
cd kicauscore
```

### 2. Konfigurasi `.env` Production
```bash
cp .env.example .env
nano .env
```
Pastikan pengaturan utama:
```dotenv
APP_NAME=KicauScore
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://kicaumania.ardmst.my.id

DB_CONNECTION=sqlite
# DB_DATABASE=/var/www/kicauscore/database/database.sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

Jalankan:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force --seed
php artisan storage:link
```

### 3. Izin Akses Direktori (Permissions)
```bash
chown -R www-data:www-data /var/www/kicauscore/storage /var/www/kicauscore/bootstrap/cache /var/www/kicauscore/database
chmod -R 775 /var/www/kicauscore/storage /var/www/kicauscore/bootstrap/cache /var/www/kicauscore/database
chmod 664 /var/www/kicauscore/database/database.sqlite
```

---

## Langkah 3: Konfigurasi Nginx & SSL

### 1. Pasang Virtual Host Nginx
Salin template konfigurasi yang sudah disediakan:
```bash
cp /var/www/kicauscore/nginx.kicauscore.conf.example /etc/nginx/sites-available/kicaumania.ardmst.my.id
ln -s /etc/nginx/sites-available/kicaumania.ardmst.my.id /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 2. Pasang Sertifikat SSL Gratis (Let's Encrypt Certbot)
```bash
apt update && apt install -y certbot python3-certbot-nginx
certbot --nginx -d kicaumania.ardmst.my.id
```
*(Jika di Cloudflare proxy awan oranye aktif, pilih mode SSL **Full (Strict)** di Cloudflare Dashboard menu SSL/TLS).*

---

## Langkah 4: Rilis Update Otomatis di Masa Depan
Kapanpun ada kode baru, Anda cukup mengeksekusi skrip deploy otomatis:
```bash
cd /var/www/kicauscore
bash deploy.sh
```
