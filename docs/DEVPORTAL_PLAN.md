# Developer portal — remaining work

Status as of 2026-08-06. Read [DEVELOP_PORTAL.md](DEVELOP_PORTAL.md) first for
the design, and the developer-portal section of [../CLAUDE.md](../CLAUDE.md) for
the decisions already settled.

---

## Where things stand

**Working, and covered by tests:**

- `/develop` portal — register, edit, deploy, stop, delete an app
- Access modes (`public` / `login_required` / `role` / `allowlist` / `owner_only`)
  and the enable/disable kill switch
- Dokploy provisioning: create app → git provider → build type → environment →
  deploy. A test app builds with Nixpacks and runs.
- `php artisan dokploy:ping` config pre-flight
- **Steps 1–4 below.** The gate now runs in-app; the Traefik path is preserved
  but switched off.

Suite: **140 passing**, up from 93. The 27 `SibstrNavigationTest` failures are
the documented pre-existing baseline — see [../CLAUDE.md](../CLAUDE.md).

**What was blocked, and how it was resolved:**

The edge auth gate assumed Traefik. **There is no Traefik on this server** —
SafeLine proxies straight to each app's published port. Every generated Traefik
router was correct and never used.

The gate therefore moved from the edge into DataKita itself: a proxy route that
runs the same `DevApp::allows()` check and forwards the request. Traefik mode is
still selectable via `DEVAPPS_EDGE_MODE=traefik` for the day one exists.

**Left to do: Step 5** — set the environment on datakitadev and walk the flow
end to end against a real container. That is a deploy action, not a code change.

---

## Step 1 — `APP_ROLE` split — **done**

DataKita and the portal are now separate route surfaces in one codebase.

- `routes/devportal.php` holds the `/develop/*` group, the authz endpoint and
  `/develop/masuk`, moved verbatim out of `routes/web.php`.
- `App\Support\AppRole` normalises `config('app.role')` and answers
  `servesDatakita()` / `servesDevPortal()`. An unrecognised value **throws**
  rather than falling back — a typo that quietly degraded to `datakita` would
  publish `/bps` and `/superadmin` on a portal-only host.
- `RouteServiceProvider` loads `api.php` + `web.php` when serving DataKita and
  `devportal.php` when serving the portal. `devportal.php` loads second; that
  is safe because no DataKita route matches `/develop/*` and the router always
  sorts `Route::fallback()` last regardless of declaration order.
- `config/app.php` gained `'role' => env('APP_ROLE')`.
- Both sidebar links in `layouts.bps` now check
  `config('devapps.enabled') && AppRole::servesDevPortal()`, so `route()`
  cannot be called for a route this container does not publish.
- `phpunit.xml` sets `APP_ROLE=all`, so the suite still exercises the same
  route table it did before the split.

`/healthz` needed no work — it is served by nginx (`docker/default.conf`),
not Laravel, so it exists in every role.

**Constraint met.** `route:list` under `APP_ROLE=all` is byte-identical to
pre-split HEAD (323 routes). With `APP_ROLE` unset the only delta is the 15
`develop/*` routes being absent; the other 308 match exactly, middleware
stacks included. Prod runs `DEVAPPS_ENABLED=false`, under which
`DevAppController` already aborted 404 and the sidebar link was already
hidden, so no reachable prod behaviour changes. The one nuance: for a
logged-in BPS user `/develop` used to 404 and now falls through to the
home redirect like any other unknown path.

**Covered by** `tests/Feature/AppRoleRoutingTest.php`.

**Note for the portal-only deployment (not yet live):** `DevApp::reservedSlugs()`
derives its blocklist from the live route table, so under `APP_ROLE=devportal`
that table no longer contains `bps`, `superadmin`, `survei`, … and those slugs
become claimable. Harmless today — phase 1 runs `APP_ROLE=all`, where the list
is complete — but it must be fixed before anything runs as `devportal`, and it
must stay derived rather than hard-coded.

---

## Step 2 — the proxy — **done**

`App\Http\Controllers\Develop\ProxyController` replaces the edge gate. Its
catch-all is registered last in `devportal.php`, and only when
`devapps.enabled` is on and `edge_mode` is `proxy` — otherwise the route table
stays exactly DataKita's.

The access decision is still `DevApp::allows()`, the same method the edge gate
called, so the two can never disagree. What they say about the visitor is now
shared outright, in `Develop\Concerns\GateResponses`.

**Two deviations from the original sketch, both deliberate:**

*Cookies are filtered by name, not stripped wholesale.* The requirement is that
the dev app never receives DataKita's session token — met by removing the
session cookie, `XSRF-TOKEN` and `remember_web_*`. Stripping the header
entirely would also have removed the app's **own** cookies, and an app that
cannot hold a session is an app nobody can build. Cookies are read from the
decrypted bag rather than the raw header, so `EncryptCookies` encrypting them
on the way out and decrypting on the way back in is transparent to the app —
and its cookies sit encrypted in the browser as a side benefit.

The reverse direction is guarded too: a `Set-Cookie` from the app naming one of
*ours* is dropped. The app shares our origin, so otherwise it could fixate a
visitor's DataKita session.

*A catch-all beats `Route::fallback()`.* The router always sorts fallbacks last,
so the proxy route would otherwise have taken over 404 handling for the entire
site. When no `DevApp` claims the slug and DataKita is served here, the proxy
hands back to the home redirect — the pre-proxy behaviour, preserved.

Also worth knowing: the route is exempted from `VerifyCsrfToken`. The dev app
runs its own framework with its own tokens, and DataKita's is meaningless to
it. Safe because the proxy changes no DataKita state and the session cookie
never crosses.

**Known limits, documented not solved:** no WebSocket upgrade; request bodies
are buffered in PHP (rebuilt from the parsed request for multipart uploads,
since PHP consumes `php://input` for those); every proxied request holds a
PHP-FPM worker for its lifetime — hence the short `DEVAPPS_PROXY_*` timeouts.

**Covered by** `tests/Feature/DevAppProxyTest.php` (24 tests).

---

## Step 3 — per-app environment variables — **done**

`saveEnvironment()` replaced an app's whole env block on every deploy, so
anything a developer set in Dokploy was wiped on the next one — which made it
impossible for an app to hold its own database credentials.

- `env_vars` text column on `dev_apps`
  (`2026_08_06_000001_add_env_vars_to_dev_apps`).
- "Variabel Lingkungan" textarea on the portal form. Blank lines and `#`
  comments are skipped, so a `.env` can be pasted straight in.
- `AppProvisioner::environmentBlock()` merges the owner's vars **first**, so
  the `DATAKITA_*` and `PORT` values overwrite on collision.
- Validation rejects reserved and malformed keys. This is an access-control
  boundary: `DATAKITA_HEADER_*` tells the app which header carries the
  visitor's identity, so an app able to repoint it could nominate a header the
  client controls and believe whatever it said.

**Deviation:** the plan said reject anything *starting with* `PORT`. Implemented
as an exact match on `PORT` plus the `DATAKITA_` prefix — a prefix match would
have rejected `PORTAL_URL`, which is the owner's own business and nothing to do
with the boundary.

Portal help text now points owners at their own database (Dokploy → Datakita
Dev Apps → Create Service → Database), never DataKita's.

**Covered by** `tests/Feature/DevAppEnvironmentTest.php` (12 tests).

---

## Step 4 — retire the Traefik path — **done**

`TraefikConfigBuilder` and the `routing_status` verification are kept intact and
still tested — they become useful the day Traefik exists — but gated behind
`config('devapps.edge_mode')`: `proxy` (default) or `traefik`.

The verifier was the urgent part. It stops any container whose Traefik gate it
cannot positively confirm, which is right under a Traefik edge and catastrophic
under the proxy: there is no Traefik config to find, so **every healthy app
would be stopped as unprotected**. `applyRouting()` and `verifyRouting()` now
short-circuit in proxy mode and mark the app protected, because the proxy gate
cannot go missing — it is the route.

In proxy mode the UI hides the "Konfigurasi Rute (Traefik)" card, the routing
badge, and both protection warnings. They would describe a mechanism that is
not running, and the badge would read "Belum dipasang" on a fully protected app.

`DevAppRoutingVerificationTest` now selects `edge_mode = traefik` explicitly,
since that is what it is about.

**Covered by** `tests/Feature/DevAppEdgeModeTest.php` (5 tests).

---

## Step 5 — deploy phase 1

The only step left, and the only one that is not a code change. On
**datakitadev only**, set:

```
APP_ROLE=all
DEVAPPS_ENABLED=true
DEVAPPS_EDGE_MODE=proxy
DEVAPPS_PUBLIC_HOST_URL=https://datakitadev.angkabatam.id
DOKPLOY_URL=http://dokploy:3000
DOKPLOY_API_KEY=...
DOKPLOY_ENVIRONMENT_ID=MhMEcDn-MUIn-XBCvAaOB
```

`DEVAPPS_EDGE_MODE` may be omitted — `proxy` is the default. Set it explicitly
anyway, so the mode is visible in the Dokploy env rather than implied.

Order matters on redeploy: `docker/entrypoint.sh` runs `config:cache` and
`route:cache` at container start, so the environment must be in place *before*
the container boots. The proxy route and the `APP_ROLE` split are both decided
at boot; changing either env var needs a restart, not just a config clear.

Then walk it:

1. `php artisan dokploy:ping` — config pre-flight.
2. Register a test app, give it `auth_mode = login_required`.
3. Deploy. Watch it reach `running`.
4. Visit `/{slug}` **logged out** → expect the bounce to `/login`, and to land
   back on `/{slug}` afterwards.
5. Visit `/{slug}` logged in → expect the app, and in its own request log:
   `X-Datakita-User-Email` populated, no `datakita_session` in `Cookie`.
6. Stop the container from Dokploy, reload → expect the "Aplikasi tidak
   merespons" page, status 502, no stack trace.
7. Set an env var on the app (e.g. `DB_HOST=...`), redeploy, confirm it is
   still there afterwards — that is what Step 3 fixed.

**Prod stays untouched.** `APP_ROLE` unset means no portal routes at all, and
`DEVAPPS_ENABLED` defaults to false, so the proxy catch-all is never
registered. Verified by diffing `route:list` against pre-split HEAD: identical
but for the 15 absent `develop/*` routes.

---

## Later, not now

- **Fix `reservedSlugs()` before anything runs as `APP_ROLE=devportal`** (see
  the note under Step 1). It derives its blocklist from the live route table,
  which is incomplete when `web.php` is not loaded. Not urgent — phase 1 runs
  `APP_ROLE=all`, where the list is complete — but it is a security control,
  and it must stay derived rather than hard-coded.
- Dedicated portal deployment on `apps.angkabatam.id` (`APP_ROLE=devportal`),
  with `RUN_MIGRATIONS=false` and a restricted MySQL user that cannot read
  survey tables. See DEVELOP_PORTAL.md.
- Per-app subdomains, so dev apps are isolated from each other, not just from
  DataKita. This is also what would let dev apps use cookies without sharing
  DataKita's origin at all.
- WebSocket support. The proxy cannot upgrade a connection, so an app needing
  live updates has to poll.
- Auto-provisioning a database per app via Dokploy's API.
