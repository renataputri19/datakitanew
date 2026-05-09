#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[entrypoint] datakita container starting..."

# 1) Ensure storage dirs exist (volume mounts may start empty)
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# 2) Permissions for runtime-writable paths
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 3) Generate APP_KEY if missing (only happens when operator forgot to set it).
#    In production you SHOULD set APP_KEY explicitly via Dokploy env vars.
if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "[entrypoint] WARNING: APP_KEY not set. Generating an ephemeral key for this boot."
    echo "[entrypoint] Set APP_KEY in Dokploy env vars to make sessions/encryption stable across restarts."
    php artisan key:generate --show --no-interaction || true
fi

# 4) Public storage symlink (idempotent)
if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

# 5) Wait for the database (separate Dokploy MySQL service)
if [ -n "${DB_HOST:-}" ]; then
    echo "[entrypoint] waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    for i in $(seq 1 60); do
        if mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" --silent 2>/dev/null; then
            echo "[entrypoint] database is reachable."
            break
        fi
        sleep 2
        if [ "$i" = "60" ]; then
            echo "[entrypoint] database did not become ready in 120s; continuing anyway."
        fi
    done
fi

# 6) Run migrations on every boot (idempotent — no-op when up to date).
#    --isolated takes a DB lock so concurrent containers can't race.
#    Set RUN_MIGRATIONS=false to skip (e.g. emergency code rollback where
#    you don't want the new schema applied).
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[entrypoint] running migrations..."
    php artisan migrate --force --isolated --no-interaction
else
    echo "[entrypoint] RUN_MIGRATIONS=false — skipping migrations."
fi

# 7) Optimize caches for production (non-fatal if a config is missing)
echo "[entrypoint] caching config / routes / views..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan event:cache   || true

# 8) Hand off to whatever CMD was passed (supervisord by default)
echo "[entrypoint] handing off to: $*"
exec "$@"
