# Deployment ke VPS (Module 9)

Panduan men-deploy Task Management MCP ke VPS Linux (Ubuntu) dengan **Nginx +
PHP-FPM + HTTPS**. Endpoint MCP (`POST /mcp/task-management`) adalah route Laravel
biasa, jadi ia otomatis terlayani lewat web server yang sama.

Artefak siap-pakai ada di folder `deploy/`.

---

## 1. Persiapan server

```bash
sudo apt update
sudo apt install -y nginx php8.4-fpm php8.4-cli php8.4-mbstring php8.4-xml \
    php8.4-curl php8.4-mysql php8.4-zip unzip git
# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 2. Deploy kode

```bash
sudo git clone <repo> /var/www/task-management
cd /var/www/task-management
composer install --no-dev --optimize-autoloader

cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://mcp.example.com,
# koneksi DB, dst.
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force   # opsional (data demo)

# Passport (OAuth 2.1): kunci enkripsi — JANGAN commit, dan pastikan hanya
# www-data yang bisa baca.
php artisan passport:keys --force
```

## 3. Permission & cache

```bash
sudo chown -R www-data:www-data /var/www/task-management
sudo chmod -R 775 storage bootstrap/cache
chmod 600 storage/oauth-*.key

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Nginx + SSL

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/task-management
sudo ln -s /etc/nginx/sites-available/task-management /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# SSL gratis via Certbot (mengisi blok 443 otomatis)
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d mcp.example.com
```

> Catatan: blok `location = /mcp/task-management` di `nginx.conf` mematikan buffering
> (`fastcgi_buffering off`) karena transport MCP memakai `text/event-stream`
> (streaming/progress notification).

## 5. (Opsional) Scheduler / worker

```bash
sudo cp deploy/task-management-scheduler.service /etc/systemd/system/
sudo cp deploy/task-management-scheduler.timer   /etc/systemd/system/
sudo systemctl enable --now task-management-scheduler.timer
```

## 6. Uji endpoint

```bash
# Tanpa token -> 401
curl -i -X POST https://mcp.example.com/mcp/task-management \
  -H 'Content-Type: application/json' -H 'Accept: application/json, text/event-stream' \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"t","version":"1"}}}'

# Discovery OAuth
curl https://mcp.example.com/.well-known/oauth-authorization-server
```

Sambungkan client MCP cukup dengan URL `https://mcp.example.com/mcp/task-management`
(lihat `WORKSPACE.md`).

---

## 7. Logging & Monitoring MCP

Tersedia channel log khusus (`config/logging.php` → `mcp`), tulis ke
`storage/logs/mcp.log`. Contoh mencatat pemanggilan tool (mis. di sebuah middleware
atau di dalam tool):

```php
use Illuminate\Support\Facades\Log;

Log::channel('mcp')->info('tool.called', [
    'tool'   => 'create-task-tool',
    'user'   => $request->user()?->id,
    'args'   => $request->all(),
]);
```

Pantau:
```bash
tail -f storage/logs/mcp.log
tail -f /var/log/nginx/task-management.error.log
```

Rekomendasi produksi lanjutan:
- Agregasi log (Papertrail / Loki / ELK) via channel `syslog` atau `stack`.
- Uptime & error tracking (Sentry) — `composer require sentry/sentry-laravel`.
- Rotasi kunci token & audit percobaan auth gagal.

---

## Checklist produksi

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] HTTPS aktif (Certbot auto-renew: `systemctl status certbot.timer`)
- [ ] `config:cache` / `route:cache` / `view:cache`
- [ ] `storage/oauth-*.key` mode 600, tidak ter-commit
- [ ] Rate limiter `mcp` aktif (sudah di `AppServiceProvider`)
- [ ] Backup database terjadwal
- [ ] Log MCP dipantau/diagregasi
