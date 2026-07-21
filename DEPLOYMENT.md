# Deploy VPS + SSL Let's Encrypt

Panduan ini untuk deploy Laravel backend Undangan Bali ke VPS Ubuntu dengan Nginx, PHP 8.2, MySQL, dan SSL Let's Encrypt.

## 1. Push Dari Laptop Ke GitHub

Setiap ada perubahan di laptop:

```bash
git status
git add -A
git commit -m "Update aplikasi"
git push origin main
```

Repository:

```text
https://github.com/komangindra86/undangan-bali.git
```

Jangan commit `.env`, `mobile/.env`, `vendor`, `node_modules`, cache, log, atau foto upload user.

## 2. Setup Awal Di VPS

Install paket dasar:

```bash
sudo apt update
sudo apt install -y nginx mysql-server git unzip curl certbot python3-certbot-nginx
```

Install PHP 8.2 dan extension Laravel:

```bash
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd
```

Install Composer bila belum ada:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Clone project:

```bash
sudo mkdir -p /var/www
sudo chown -R $USER:www-data /var/www
git clone https://github.com/komangindra86/undangan-bali.git /var/www/undangan-bali
cd /var/www/undangan-bali
```

Siapkan environment:

```bash
cp .env.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

Edit `.env` production:

```bash
nano .env
```

Minimal isi:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=undangan_bali
DB_USERNAME=undangan_bali
DB_PASSWORD=password-kuat

MIDTRANS_SERVER_KEY=isi-server-key
MIDTRANS_CLIENT_KEY=isi-client-key
MIDTRANS_IS_PRODUCTION=true

WEDDING_GIFT_FEE_FLAT_BELOW_AMOUNT=100000
WEDDING_GIFT_FEE_FLAT_VALUE=2000
WEDDING_GIFT_FEE_PERCENT_VALUE=2
```

Database:

```bash
sudo mysql
```

```sql
CREATE DATABASE undangan_bali CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'undangan_bali'@'localhost' IDENTIFIED BY 'password-kuat';
GRANT ALL PRIVILEGES ON undangan_bali.* TO 'undangan_bali'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Migrasi dan permission:

```bash
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache
```

## 3. Nginx

Buat config:

```bash
sudo nano /etc/nginx/sites-available/undangan-bali
```

Isi, ganti `domain-kamu.com`:

```nginx
server {
    listen 80;
    server_name domain-kamu.com www.domain-kamu.com;
    root /var/www/undangan-bali/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan:

```bash
sudo ln -s /etc/nginx/sites-available/undangan-bali /etc/nginx/sites-enabled/undangan-bali
sudo nginx -t
sudo systemctl reload nginx
```

Pastikan DNS domain sudah mengarah ke IP VPS sebelum lanjut SSL.

## 4. SSL Let's Encrypt

```bash
sudo certbot --nginx -d domain-kamu.com -d www.domain-kamu.com
```

Cek auto-renew:

```bash
sudo certbot renew --dry-run
```

## 5. Pull Update Dari VPS

Setiap sudah push dari laptop, masuk VPS:

```bash
cd /var/www/undangan-bali
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl restart undangan-bali-queue.service
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
```

Atau jalankan:

```bash
bash scripts/deploy-vps.sh
```

### Queue worker

Notifikasi push diproses lewat Laravel queue. Pasang service sekali setelah clone/deploy:

```bash
sudo cp scripts/undangan-bali-queue.service /etc/systemd/system/undangan-bali-queue.service
sudo systemctl daemon-reload
sudo systemctl enable --now undangan-bali-queue.service
sudo systemctl status undangan-bali-queue.service
```

### Push notification Android

Push perangkat memakai Expo Push Service dan FCM V1. Konfigurasi sekali sebelum build AAB:

1. Hubungkan project ke akun Expo dengan `eas init`, lalu isi `EXPO_PUBLIC_EAS_PROJECT_ID` pada environment build mobile.
2. Daftarkan package Android `com.balisantih.undanganbali` di Firebase.
3. Simpan `google-services.json` di folder `mobile/` dan tambahkan `android.googleServicesFile` ke `mobile/app.json`.
4. Upload Firebase service account FCM V1 melalui `eas credentials` atau dashboard EAS.
5. Jika enhanced push security Expo diaktifkan, isi `EXPO_PUSH_ACCESS_TOKEN` hanya di `.env` backend VPS.

Jangan commit `google-services.json`, Firebase service account, atau Expo access token.

## 6. Webhook Midtrans

Set Notification URL di dashboard Midtrans:

```text
https://domain-kamu.com/api/midtrans/webhook
```

Status Wedding Gift `paid` hanya diupdate dari webhook valid atau Get Status API backend.
