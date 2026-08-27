# DataKita — working notes

Laravel 10 app for BPS Kota Batam: statistical surveys (SIBSTR, UB, Listrik),
news, MONALISA assessments, and a developer portal. Deployed on Dokploy.

---

## Conventions that will bite you

**UUID primary keys everywhere.** Models use `App\Traits\HasUuid`; migrations use
`uuid('id')->primary()` and `foreignUuid(...)`. `foreignId()` fails against
`users.id`. Never cast a user id to `int` — `(int) "01a68d2e-…"` is `1`, and
MySQL then type-juggles the uuid column and matches the wrong rows. This has
already caused a real access-control bug.

**Tests need MySQL.** `phpunit.xml` says sqlite but the suite errors there. Run:
```sh
DB_CONNECTION=mysql DB_DATABASE=datakita_devapps_test php artisan test
```
27 failures in `SibstrNavigationTest` are pre-existing — check the baseline
before blaming your change.

**Run `npm run build` after touching Tailwind classes in Blade.** Production
serves pre-built Vite assets; new utility classes won't exist otherwise.

**Survey numbers are id-ID strings.** Inputs persist as `"12.312.312"`. Parse
with `SibstrFormat::num()`, never `is_numeric()` or `(float)`.

**Internal state is not mass-assignable.** On `DevApp`, columns like `status`,
`last_error`, `routing_status`, `dokploy_*` are deliberately outside
`$fillable`. Use `forceFill(...)->save()` — `update([...])` silently discards
them.

---

## Deployment topology (important, non-obvious)

```
Cloudflare → SafeLine WAF → the app container's published port
```

- **There is no Traefik.** Dokploy's `/etc/dokploy/traefik/` files exist but
  nothing reads them. Each app is published on a host port and SafeLine
  reverse-proxies to it (`datakitadev` → `127.0.0.1:21711`).
- SafeLine returns **HTTP 467** to API calls — a non-standard code meaning its
  user-group rule blocked the request before it reached the backend. Not an
  auth or endpoint error. Use internal addresses (`http://dokploy:3000`) to
  bypass it.
- The domain is **`angkabatam.id`**, not `angkatabatam.id`. Mistyping it looks
  like a network outage.
- Dokploy 0.29: applications live under an **environment**, so
  `application.create` needs `environmentId`, not `projectId`. Its API marks
  most payload fields `.nonoptional()` — partial payloads fail with a bare
  "Input validation failed", and the field names are in the `issues` array.
- Dokploy **appends a suffix** to the `appName` you submit. Always read the
  real one back with `application.one`.

Prod and dev use separate databases (`datakita_database_prod` /
`datakita_database_dev`), but dev has been restored from prod — **dev holds
real respondent data**, so it is not a sandbox.

---

## Developer portal (`/develop`)

Lets a BPS user run an app from their own Git repo under a DataKita URL, gated
by DataKita login. Their code runs in a **separate Dokploy container** and never
receives DataKita's database credentials.

See [docs/DEVELOP_PORTAL.md](docs/DEVELOP_PORTAL.md) for the full design and
[docs/DEVPORTAL_PLAN.md](docs/DEVPORTAL_PLAN.md) for outstanding work.

**Architecture decisions already made — do not relitigate:**

| Decision | Why |
|---|---|
| Same repo, split by `APP_ROLE` | The portal needs DataKita's `User` model, roles, session config and `APP_KEY`. Two repos means two auth stacks that drift. |
| Portal proxies in PHP, not at the edge | There is no Traefik to run a ForwardAuth gate. |
| Portal gets its own deployment in prod | `pm.max_children = 20`. Sharing workers means one slow dev app can take DataKita down. |
| Dev apps never get DataKita's DB | They are third-party code. Each gets its own database. |

`APP_ROLE` values: unset = DataKita only (prod) · `all` = both (datakitadev,
dev convenience only) · `devportal` = portal + proxy only (apps.angkabatam.id).
Read it through `App\Support\AppRole`, never `config('app.role')` directly —
it rejects unknown values instead of falling back to DataKita. `web.php` and
`devportal.php` are loaded separately by `RouteServiceProvider`, so **anything
linking to a `develop.*` route must guard on `AppRole::servesDevPortal()`** or
`route()` throws. `phpunit.xml` pins `APP_ROLE=all` so tests see both surfaces.

**Slug validation is a security control.** `DevApp::reservedSlugs()` derives its
blocklist from the live route table, so a dev app cannot claim `bps` and shadow
a real page. Keep it derived, never hard-code the list. (Known gap: under
`APP_ROLE=devportal` that table is incomplete — fix before deploying that mode.)

**Never let an unprotected dev app stay reachable.** If the access gate can't be
confirmed, stop the container. "Couldn't verify" is not "it's fine". This is the
`edge_mode=traefik` path; under the proxy the gate cannot go missing because it
*is* the route, so `verifyRouting()` short-circuits. Do not let it run in proxy
mode — it finds no Traefik config and stops every healthy container.

**The gate runs in PHP, in `ProxyController`.** `DEVAPPS_EDGE_MODE=proxy` is the
default. The dev app is third-party code on DataKita's origin, so the proxy
drops DataKita's cookies by name (session, `XSRF-TOKEN`, `remember_web_*`) in
both directions, and *sets* the `X-Datakita-User-*` headers rather than
forwarding them. Its catch-all is registered last and is matched ahead of
`Route::fallback()` — an unclaimed slug must hand back to the home redirect, or
the proxy silently owns the whole site's 404s.

**A dev app's environment is merged, ours last.** `env_vars` holds the owner's
`KEY=value` lines; `AppProvisioner::environmentBlock()` puts them first so
`DATAKITA_*` and `PORT` overwrite. Never reverse that: `DATAKITA_HEADER_*` names
the header carrying the visitor's identity.
