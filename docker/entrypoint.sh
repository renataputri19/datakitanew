#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[entrypoint] datakita container starting..."

# 1) Self-heal: a previous failed boot, or a Windows-built image, may have
#    left storage/app/public or public/storage as a broken / looping symlink
#    or stale file. Laravel expects storage/app/public to be a real directory
#    and public/storage to be a symlink created by `artisan storage:link`.
#    Anything else here breaks mkdir below with "Symbolic link loop" or
#    causes storage:link to error with "link already exists".
if [ -L storage/app/public ]; then
    echo "[entrypoint] storage/app/public is a symlink (should be a real dir) — removing"
    rm -f storage/app/public
fi
# Always clear public/storage; storage:link below will recreate it cleanly.
if [ -L public/storage ] || [ -e public/storage ]; then
    echo "[entrypoint] clearing pre-existing public/storage (will be recreated by storage:link)"
    rm -rf public/storage
fi

# 2) Ensure storage dirs exist (volume mounts may start empty)
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# 3) Permissions for runtime-writable paths
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 4) Generate APP_KEY if missing (only happens when operator forgot to set it).
#    In production you SHOULD set APP_KEY explicitly via Dokploy env vars.
if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "[entrypoint] WARNING: APP_KEY not set. Generating an ephemeral key for this boot."
    echo "[entrypoint] Set APP_KEY in Dokploy env vars to make sessions/encryption stable across restarts."
    php artisan key:generate --show --no-interaction || true
fi

# 5) Public storage symlink — always recreate (we cleared it in step 1).
#    --force makes Laravel overwrite if anything sneaks in between.
php artisan storage:link --no-interaction --force || true

# 6) Wait for the database (separate Dokploy MySQL service).
#    Track reachability so a missing/wrong DB_HOST doesn't kill boot.
DB_REACHABLE=false
if [ -n "${DB_HOST:-}" ]; then
    echo "[entrypoint] waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    for i in $(seq 1 60); do
        if mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" --silent 2>/dev/null; then
            echo "[entrypoint] database is reachable."
            DB_REACHABLE=true
            break
        fi
        sleep 2
    done
    if [ "$DB_REACHABLE" = "false" ]; then
        echo "[entrypoint] WARNING: database unreachable after 120s. Skipping migrations."
        echo "[entrypoint] Check DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD env vars in Dokploy."
    fi
else
    echo "[entrypoint] WARNING: DB_HOST not set. Skipping DB wait and migrations."
fi

# 7) Run migrations on every boot (idempotent — no-op when up to date).
#    Skipped if DB is unreachable so the container still boots and can be
#    debugged via Dokploy shell instead of crash-looping.
if [ "${RUN_MIGRATIONS:-true}" = "true" ] && [ "$DB_REACHABLE" = "true" ]; then
    echo "[entrypoint] running migrations..."
    php artisan migrate --force --isolated --no-interaction || \
        echo "[entrypoint] WARNING: migrations failed — continuing boot anyway."
elif [ "${RUN_MIGRATIONS:-true}" != "true" ]; then
    echo "[entrypoint] RUN_MIGRATIONS=false — skipping migrations."
fi

# 8) Optimize caches for production (non-fatal if a config is missing)
echo "[entrypoint] caching config / routes / views..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan event:cache   || true

# 9) Hand off to whatever CMD was passed (supervisord by default)
echo "[entrypoint] handing off to: $*"
exec "$@"
