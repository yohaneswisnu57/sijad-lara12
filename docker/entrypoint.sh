#!/bin/sh
set -e

echo "═══════════════════════════════════════════"
echo "  sijad-lara12 — Container Starting"
echo "═══════════════════════════════════════════"

# ── 1. Tunggu koneksi ke DB eksternal ────────────────────────────────────────
echo "[entrypoint] Menunggu koneksi ke database..."
MAX_RETRY=30
RETRY=0
until php artisan db:show --no-interaction > /dev/null 2>&1; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRY ]; then
        echo "[entrypoint] ERROR: Tidak bisa terhubung ke database setelah $MAX_RETRY percobaan."
        echo "[entrypoint] Pastikan DB_HOST (${DB_HOST}) dapat diakses dari container."
        exit 1
    fi
    echo "[entrypoint] Database belum tersedia... ($RETRY/$MAX_RETRY)"
    sleep 2
done
echo "[entrypoint] Database terhubung ✓"

# ── 2. Jalankan migration ─────────────────────────────────────────────────────
echo "[entrypoint] Menjalankan migration..."
php artisan migrate --force --no-interaction

# ── 3. Optimasi untuk production ──────────────────────────────────────────────
echo "[entrypoint] Menjalankan package:discover (composer --no-scripts)..."
php artisan package:discover --no-interaction

echo "[entrypoint] Optimasi aplikasi..."
php artisan config:cache --no-interaction
php artisan route:cache  --no-interaction
php artisan view:cache   --no-interaction
php artisan event:cache  --no-interaction

# ── 4. Storage link ───────────────────────────────────────────────────────────
echo "[entrypoint] Membuat storage symlink..."
php artisan storage:link --no-interaction 2>/dev/null || true

# ── 5. Set permission ─────────────────────────────────────────────────────────
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "[entrypoint] Siap! Menjalankan Supervisor... ✓"
echo "═══════════════════════════════════════════"

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
