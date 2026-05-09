# Deploying datakita to Dokploy

This repo ships a production-ready Docker setup. Dokploy builds the
`Dockerfile` and runs the resulting container behind its built-in Traefik
proxy. The MySQL database lives in a separate Dokploy service.

## What's in the image

- `php:8.2-fpm-alpine` runtime
- Nginx + PHP-FPM managed by `supervisord`
- Vite assets built in a multi-stage step (no Node at runtime)
- Composer dependencies installed with `--no-dev`
- Required PHP extensions for Laravel 10, `maatwebsite/excel`, `dompdf`

## One-time setup in Dokploy

1. **Create a MySQL service** (Databases → MySQL). Note the internal
   hostname Dokploy assigns (e.g. `datakita-mysql`). Create a database +
   user for the app.
2. **Create an Application** pointing at this Git repo.
   - Build type: **Dockerfile**
   - Build context: `/` (repo root)
   - Dockerfile path: `Dockerfile`
3. **Environment tab** — paste the values from `.env.docker.example` and
   fill in the secrets:
   - `APP_KEY` — generate locally once with `php artisan key:generate --show`
   - `DB_HOST` — the MySQL service hostname from step 1
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `APP_URL` — your public domain
4. **Domain tab** — bind the domain Traefik should serve. Container port = `80`.
5. **Volumes** — configure in **Advanced → Volumes** in the Dokploy UI
   (NOT via `docker-compose.yml`; the compose `volumes:` block only
   applies to Compose-type deploys, not Application/Dockerfile deploys).

   Add these two **Volume Mounts** (or Bind Mounts if you prefer a known
   host path for backups):

   | Volume name             | Mount path (container)            | Why                                            |
   |-------------------------|-----------------------------------|------------------------------------------------|
   | `datakita_storage_app`  | `/var/www/html/storage/app`       | User uploads, generated PDFs, Excel imports — must survive redeploys |
   | `datakita_storage_logs` | `/var/www/html/storage/logs`      | Only needed if `LOG_CHANNEL=stack`. Skip if using `LOG_CHANNEL=stderr` (Dokploy captures stderr). |

   Do **not** mount `storage/framework/*` or `bootstrap/cache` — those
   are regenerated on every boot and mounting them just slows things
   down and can cause stale-cache bugs. Do not mount `vendor` or
   `public/build` — those are baked into the image at build time.

## First deploy

1. Click **Deploy**. The entrypoint will:
   - wait for MySQL,
   - run `php artisan migrate --force --isolated` (creates `migrations`
     table + applies all pending migrations on a fresh DB; no-op when up
     to date),
   - cache config / routes / views,
   - start Nginx + PHP-FPM.
2. Seeders are **never** run automatically. Run them once from the
   Dokploy shell if you need them:
   ```sh
   php artisan db:seed --force
   ```

## Migrations on redeploy

Migrations run on **every** container boot by default. This is safe:

- `migrate --force` is idempotent — no pending migrations means no-op.
- `--isolated` takes a DB lock so even if you scale to multiple
   replicas later, only one container applies migrations.
- Seeders are excluded (only `migrate`, not `migrate --seed`).

**Kill-switch:** set `RUN_MIGRATIONS=false` in the env tab when you need
to deploy a code rollback without applying the newer schema. Flip back
to `true` (or remove it) once you're back on forward-compatible code.

## Health check

Nginx exposes `GET /healthz → 200 ok`. Dokploy's health probe and the
compose `healthcheck` use it.

## Production safety

- `APP_DEBUG=false`
- `APP_ENV=production`
- `DEV_AUTH_ENABLED=false` — the dev login overlay must never be on in prod.
- `LOG_CHANNEL=stderr` so logs go to Dokploy's log viewer.

## Local smoke test (optional)

```sh
docker compose --env-file .env.docker.example up --build
# then open http://localhost (after mapping ports if you want to test locally)
```

For local browser testing, expose port 80 by adding to the `app` service:

```yaml
ports:
  - "8080:80"
```

## Files

| Path                          | Purpose                                           |
|-------------------------------|---------------------------------------------------|
| `Dockerfile`                  | 3-stage build: frontend → vendor → runtime        |
| `.dockerignore`               | Slims build context, excludes secrets / scratch   |
| `docker/nginx.conf`           | Main Nginx config, gzip, real-IP from Traefik     |
| `docker/default.conf`         | Server block — Laravel `public/`, `/healthz`      |
| `docker/php.ini`              | OPcache + upload limits + Asia/Jakarta tz         |
| `docker/php-fpm.pool.conf`    | FPM pool (dynamic, 20 children) → stderr logs     |
| `docker/supervisord.conf`     | Runs nginx + php-fpm together; queue worker stub  |
| `docker/entrypoint.sh`        | Boots storage perms, waits for DB, optional migrate, caches |
| `docker-compose.yml`          | Reference compose used by Dokploy                 |
| `.env.docker.example`         | Template for the Dokploy Environment tab          |

## Queue worker (when you need it)

By default the queue runs synchronously. To run a worker, either:

- Uncomment the `[program:laravel-queue]` block in `docker/supervisord.conf`
  (cheapest — same container), OR
- Create a second Dokploy application from the same image, override the
  command to `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`,
  and don't expose any port. (Recommended for heavier workloads.)

## Troubleshooting

- **Vite manifest not found** — frontend stage failed during build. Check
  Dokploy build logs for the `npm run build` step. Confirm
  `vite.config.js`, `tailwind.config.*`, `postcss.config.*`,
  `package-lock.json` are committed.
- **500 on first request** — usually a missing `APP_KEY` or DB env vars.
  Check Dokploy logs; the entrypoint logs each step it runs.
- **Permission errors writing to storage** — the entrypoint chowns
  `storage/` and `bootstrap/cache/` on every start. If you mounted a
  volume, the first boot fixes it.
- **Old assets after deploy** — Vite hashes filenames, so this only
  happens if a CDN / browser is caching `index.php`. Clear Laravel cache
  via `php artisan optimize:clear` in the Dokploy shell.
