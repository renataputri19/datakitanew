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

# Drop public/hot — if present, laravel-vite-plugin emits dev-server URLs
# (http://[::1]:5173) that don't exist in production and break all assets.
if [ -e public/hot ]; then
    echo "[entrypoint] removing public/hot (would force Vite into dev-server mode)"
    rm -f public/hot
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

# 6) First-boot seed: if the target DB has zero tables, import the bundled
#    SQL dump (schema + data) before running migrations. The dump's own
#    `migrations` table records every migration that was already applied
#    when the dump was taken, so the migrate step that follows is a no-op
#    on a freshly-seeded DB and only runs *newer* migrations on later boots.
#
#    We deliberately skip if ANY tables exist — that's the "DB not empty"
#    contract the operator chose. To re-seed a populated DB, drop it
#    manually first. SEED_DB=false is an emergency kill-switch.
SEED_FILE=/var/www/html/docker/seed/datakita_seed.sql
DB_BOOTSTRAP=/var/www/html/docker/db-bootstrap.php
# We query via PDO (php helper) instead of the mysql/mariadb CLI: Alpine's
# mariadb-client can't authenticate against MySQL 8's caching_sha2_password
# default, but PHP's mysqlnd — the same driver Laravel uses — can.
if [ "${SEED_DB:-true}" = "true" ] && [ -f "$SEED_FILE" ]; then
    if [ -z "${DB_HOST:-}" ] || [ -z "${DB_DATABASE:-}" ] || [ -z "${DB_USERNAME:-}" ]; then
        echo "[entrypoint] WARNING: DB_* env not fully set. Skipping seed import."
    else
        echo "[entrypoint] checking whether DB '$DB_DATABASE' is empty (will retry up to 6 times)..."
        TABLE_COUNT=""
        for attempt in 1 2 3 4 5 6; do
            if TABLE_COUNT=$(php "$DB_BOOTSTRAP" table-count 2>&1); then
                break
            fi
            echo "[entrypoint] DB query failed (attempt $attempt): $TABLE_COUNT"
            TABLE_COUNT=""
            sleep 10
        done
        if [ -z "$TABLE_COUNT" ]; then
            echo "[entrypoint] WARNING: could not query DB after 6 attempts. Skipping seed."
        elif [ "$TABLE_COUNT" = "0" ]; then
            echo "[entrypoint] DB is empty — importing $SEED_FILE ($(wc -c <"$SEED_FILE") bytes)..."
            if php "$DB_BOOTSTRAP" import "$SEED_FILE"; then
                echo "[entrypoint] seed import OK"
            else
                echo "[entrypoint] WARNING: seed import failed. Migrations will create the schema from scratch."
            fi
        else
            echo "[entrypoint] DB already has $TABLE_COUNT table(s) — skipping seed import."
        fi
    fi
else
    echo "[entrypoint] SEED_DB disabled or seed file missing — skipping seed import."
fi

# 7) Run migrations on every boot (idempotent — no-op when up to date).
#    We use Laravel's own DB connection (same as runtime) for retries,
#    not mysqladmin — mysqladmin can fail for auth/plugin reasons even
#    when PDO works fine, leaving tables uncreated.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    if [ -z "${DB_HOST:-}" ]; then
        echo "[entrypoint] WARNING: DB_HOST not set. Skipping migrations."
    else
        echo "[entrypoint] running migrations (will retry up to 6 times if DB not ready)..."
        MIGRATE_OK=false
        for attempt in 1 2 3 4 5 6; do
            if php artisan migrate --force --isolated --no-interaction; then
                MIGRATE_OK=true
                break
            fi
            echo "[entrypoint] migrate attempt $attempt failed; retrying in 10s..."
            sleep 10
        done
        if [ "$MIGRATE_OK" = "false" ]; then
            echo "[entrypoint] WARNING: all 6 migrate attempts failed."
            echo "[entrypoint] Container will boot anyway so you can debug via Dokploy shell."
            echo "[entrypoint] Run: php artisan migrate --force"
        fi
    fi
else
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
