# Developer Portal (`/develop`)

Lets a BPS user run an application built in **their own Git repository** under
a path on the datakita domain, gated by datakita's own login — without their
code ever entering this repo or this container.

```
https://datakita.angkabatam.id/survei-xyz
        │                         │
        │                         └── somebody else's container, built from
        │                             their GitHub repo
        └── datakita's domain, datakita's TLS cert, datakita's login
```

---

## How a request flows

There are two possible gates, selected by `DEVAPPS_EDGE_MODE`.

### `proxy` — the default, and what this deployment runs

DataKita authorises the request and forwards it itself. There is **no Traefik
on this server**: SafeLine proxies straight to each app's published port, so
there is no edge at which to hang a gate.

```
browser ──▶ Cloudflare ──▶ SafeLine ──▶ datakita
                                          │
                                          │ 1. route {slug}/{path?} matches,
                                          │    after every DataKita route
                                          │
                                          │ 2. DevApp::allows(Auth::user())
                                          │      ├─ 302 → /login (anonymous)
                                          │      ├─ 403 / 503
                                          │      └─ allowed ↓
                                          │
                                          │ 3. drop DataKita's cookies,
                                          │    set X-Datakita-User-* headers
                                          │
                                          │ 4. strip prefix (optional)
                                          │
                                          └─▶ http://{appName}:{port}/…
                                                the dev app's container,
                                                reachable only on the
                                                Docker network
```

`App\Http\Controllers\Develop\ProxyController`. The app's container is given no
Dokploy domain and no published host port, so that internal hostname is the
only way in — which is what makes the gate unbypassable rather than merely
present.

### `traefik` — unused here, kept working

The original design: Traefik routes each app directly and calls
`/develop/authz/{slug}` as a forwardAuth middleware before every request.

```
browser ──▶ Traefik
              │
              │ 1. router matches Host(datakita…) && PathPrefix(/survei-xyz)
              │
              ├─▶ 2. forwardAuth ──▶ datakita  GET /develop/authz/survei-xyz
              │                        │        (sees the session cookie)
              │                        ├─ 200 + X-Datakita-User-* headers
              │                        ├─ 302 → /login
              │                        └─ 403 / 503
              │
              ├─▶ 3. scrub-cookie middleware  ← deletes the Cookie header
              │
              ├─▶ 4. stripPrefix (optional)   ← /survei-xyz/a → /a
              │
              └─▶ 5. the dev app's container
```

Everything in the "Traefik dynamic config" and "ForwardAuth address" sections
below applies to this mode only. **It is not in use on this deployment** — the
portal hides the Traefik card and the routing badge unless
`DEVAPPS_EDGE_MODE=traefik`, because they would describe a mechanism that is
not running. The code is retained and still tested, for the day a Traefik does
front this server.

### What the proxy does and does not carry

| | Behaviour |
|---|---|
| Method, query, body | Forwarded. Multipart uploads are rebuilt from the parsed request, since PHP consumes `php://input` for those. |
| DataKita's cookies | **Never forwarded** — session cookie, `XSRF-TOKEN`, `remember_web_*`. The app could otherwise act as the visitor. |
| The app's own cookies | Forwarded both ways. DataKita's `EncryptCookies` encrypts them in the browser and decrypts them back before forwarding, so this is invisible to the app. A `Set-Cookie` naming one of *DataKita's* cookies is dropped. |
| Identity headers | Always set by us, never passed through from the client. |
| `Location` on redirects | Rewritten to stay under `/{slug}`. External redirects are left alone. |
| Hop-by-hop headers | Dropped in both directions. |
| WebSockets | **Not supported.** The proxy cannot upgrade a connection. |
| Cost | Each proxied request holds a PHP-FPM worker for its lifetime. `DEVAPPS_PROXY_TIMEOUT` bounds it. |

The dev app implements **no login**. It reads the user from request headers:

| Header                    | Example            |
|---------------------------|--------------------|
| `X-Datakita-User-Id`      | `9f8c…` (UUID)     |
| `X-Datakita-User-Name`    | `Renata Putri`     |
| `X-Datakita-User-Email`   | `renata@bps.go.id` |
| `X-Datakita-User-Role`    | `admin`            |

On a **public** app with an anonymous visitor these are sent as empty strings
rather than omitted — an omitted header would be passed through from the
client, letting a visitor claim to be anyone.

---

## Access modes

Set per app in the portal. Enforced by `DevApp::allows()`, read live on every
request — **changes take effect immediately, with no redeploy and no proxy
reload.**

| Mode             | Who gets in                                   |
|------------------|-----------------------------------------------|
| `public`         | anyone, no login                              |
| `login_required` | any signed-in datakita account                |
| `role`           | accounts holding one of the selected roles    |
| `allowlist`      | explicitly chosen accounts, plus the owner    |
| `owner_only`     | the owner only                                |

Unknown modes and half-configured ones (e.g. `role` with an empty list) **fail
closed**. So does a disabled app, for everyone including its owner — that's the
kill switch.

---

## Setup

### 1. Dokploy

1. Create a project to hold the dev apps (keep them out of datakita's project).
2. Settings → Profile → API/CLI → generate a key.
3. Set in datakita's environment:
   ```
   DOKPLOY_URL=http://dokploy:3000
   DOKPLOY_API_KEY=...
   DOKPLOY_ENVIRONMENT_ID=...   # 0.29+; see below
   ```

**Use Dokploy's internal address, not its public hostname.** datakita and
Dokploy share a Docker network, so `http://dokploy:3000` reaches the panel
directly. The public hostname routes through whatever sits in front of it —
on this deployment that's Cloudflare and a SafeLine WAF, and SafeLine's
user-group rule answers API calls with `HTTP 467` and a login page, because a
request carrying only `x-api-key` can't satisfy a browser-session gate. Going
internal sidesteps the WAF, TLS verification, and a round trip.

If you must use the public hostname, add a SafeLine bypass rule for `/api/*`
or for datakita's source IP — but prefer the internal address.

**Environments (0.29+).** Applications belong to an environment, not directly
to a project, so `application.create` wants `environmentId`. Both ids are in
the panel URL:

```
/dashboard/project/PSV6I7Aawe0YZICnFLC-U/environment/MhMEcDn-MUIn-XBCvAaOB
                   └──── projectId ────┘             └─── environmentId ───┘
```

Set `DOKPLOY_ENVIRONMENT_ID` and leave `DOKPLOY_PROJECT_ID` empty. On older
Dokploy without environments, do the reverse.

The API key can create and delete applications on the server. Treat it as a
root credential — Dokploy's environment tab, never the repo.

### 2. Traefik dynamic config

Nothing to configure — the portal writes each app's Traefik config through
Dokploy's API (`application.updateTraefikConfig`), then reads it back to
confirm it landed.

It writes the **whole** config document rather than merging into Dokploy's, so
the router, its middleware chain and the service always agree with each other.

Two fallbacks exist if the API route is unavailable:

1. `DEVAPPS_TRAEFIK_DYNAMIC_PATH` — a mounted Traefik dynamic directory the
   portal writes to directly. **Not recommended:** it gives the datakita
   container standing write access to Traefik's routing, so any file-write bug
   in datakita becomes "reroute any URL on this domain to anywhere", with no
   audit trail. The API route grants no such standing access and every change
   lands in Dokploy's Audit Logs.
2. Neither — the detail page renders the config for manual copy-paste into
   Dokploy's **Traefik File System** page.

#### Entrypoints: bind both, always

Generated routers bind to **`web` and `websecure`**, and carry **no
router-level `tls:` block**. Both details matter, and both are easy to get
wrong in a way that fails silently.

This deployment runs Cloudflare → SafeLine WAF → Traefik, and SafeLine's
upstream is `http://127.0.0.1:21711` — plain HTTP. TLS is terminated *before*
Traefik, so requests arrive on the **`web`** entrypoint. A router bound only to
`websecure` loads without any error and simply never matches: the request falls
through to datakita's `Host()` router, hits `Route::fallback()`, and the visitor
lands on the homepage looking like the app doesn't exist.

The `tls:` block is omitted for the same reason — a router with TLS settings
matches TLS connections *only*, which would cancel out the `web` binding.
Traefik's `websecure` entrypoint already declares a default `certResolver`, so
HTTPS still works. Set `DEVAPPS_TRAEFIK_CERT_RESOLVER` only if your entrypoint
has no TLS defaults, and expect to drop `web` from the entrypoint list if you do.

Dokploy's own generated configs sidestep this by defining two routers per app,
one per entrypoint. Binding a single router to both achieves the same thing.

#### Why the read-back matters

Dokploy generates each app's Traefik config from its own domain settings, so a
deploy can overwrite ours and drop the forwardAuth middleware. When that
happens the app keeps serving traffic and **nothing looks broken — it is just
no longer protected**. That is the worst failure this feature has.

It cannot be defended against from inside datakita: with the middleware gone,
requests reach the app without touching datakita at all, so `DevApp::allows()`
is never consulted. The only enforcement available is to **stop the
container**, and that is what `AppProvisioner::verifyRouting()` does.

Each app therefore carries a `routing_status`:

| Status | Meaning | Action |
|---|---|---|
| `protected` | read back, gate confirmed present | none |
| `unprotected` | read back, gate **missing** | container stopped, banner shown |
| `unverifiable` | config could not be read | flagged, app left running |
| `unknown` | never applied, or a deploy invalidated the last check | re-verified on refresh |

`unverifiable` deliberately does **not** stop the app — a transient API error
must not take a healthy app offline. But it is never reported as protected
either: "we could not check" is not "it is fine".

Verification runs after every apply and after every successful deploy.

### 3. ForwardAuth address

```
DEVAPPS_FORWARD_AUTH_BASE=http://datakita-app
```

Must be datakita's **container name on the Docker network**, not the public
hostname — a public address would send every authorisation check back out
through the proxy and into the router it is trying to authorise.

### 4. Turn it on

```
DEVAPPS_ENABLED=true
```

Then verify:

```sh
php artisan dokploy:ping
```

It checks the flag, the host, the Traefik mount, and — the one that usually
bites — whether the configured Dokploy endpoint names exist on your install.

---

## Dokploy endpoint names

Dokploy renames API endpoints between releases, so they live in
`config/dokploy.php` as config rather than code, each overridable by env.

**The defaults are correct for 0.29.2** and were verified against this
deployment's `/swagger`. You only need an override if you're on a version
where a name differs. The one known case: releases before ~0.24 spelled it
`application.saveGitProdiver` — with the typo — so on those you'd set

```
DOKPLOY_EP_SAVE_GIT=application.saveGitProdiver
```

On 0.29.2 the spelling is correct and no override is needed.

Note the panel's Swagger UI is reachable in a browser even when API calls from
the server are not — the WAF gates programmatic requests, not your session.

A 404 from `dokploy:ping` almost always means a renamed endpoint, not a
broken token.

---

## Slug safety

An app's slug becomes a `PathPrefix` router. Traefik matches the more specific
router **before** datakita's catch-all `Host()` router — so an app registered
as `bps` would silently take over datakita's own admin area for every visitor.

`DevApp::reservedSlugs()` therefore blocks:

- every first path segment in the **live route table**, derived at validation
  time so it can never drift from the real routes;
- a static list in `config('devapps.reserved_slugs')` for paths served by nginx
  or Traefik rather than Laravel (`/build`, `/storage`, `/healthz`, …).

If you add routes served outside Laravel, add them to that list.

Setting `DEVAPPS_MOUNT_PREFIX=apps` moves every app under `/apps/<slug>`, which
removes the collision class entirely at the cost of longer URLs.

---

## Residual risk — read this before granting access

Path-prefix mounting puts dev apps on **datakita's own origin**. The generated
middleware chain removes the session cookie before the request reaches the dev
app's container, so the app's *server* never sees a datakita session token.

**It does not sandbox the app's browser-side JavaScript.** Same-origin JS can
still issue credentialed `fetch()` calls to `/bps/*`, `/superadmin/*`, and every
other datakita route, because the browser attaches the session cookie to those
requests directly — the proxy is not involved. `HttpOnly` stops JS from
*reading* the cookie; it does not stop it from *using* it.

Concretely, a hostile or compromised dev app can act as any user who visits it.

What follows from that:

- **Only grant `/develop` to developers you would give a code-review seat to.**
  This is a trust-based control, not a technical boundary.
- Review what gets deployed, the same way you'd review a PR to this repo.
- A subdomain per app (`survei-xyz.datakita…`) would make this a browser-enforced
  boundary instead of a policy one, at the cost of wildcard DNS and a wildcard
  certificate. The switch is `DEVAPPS_MOUNT_PREFIX` plus a change to the router
  rule in `TraefikConfigBuilder::build()` — worth revisiting if the portal is
  ever opened beyond a small, trusted group.

Deliberately **not** shared with dev apps: `APP_KEY`, database credentials, any
datakita secret. An app that needs datakita data should get a scoped API token,
not a DB connection.

---

## Files

| Path | Role |
|------|------|
| [app/Models/DevApp.php](../app/Models/DevApp.php) | the app record; `allows()` is the access decision, `reservedSlugs()` the collision guard |
| [app/Http/Controllers/Develop/AuthzController.php](../app/Http/Controllers/Develop/AuthzController.php) | the ForwardAuth endpoint Traefik calls per request |
| [app/Http/Controllers/Develop/DevAppController.php](../app/Http/Controllers/Develop/DevAppController.php) | the portal CRUD + deploy actions |
| [app/Services/DevApps/TraefikConfigBuilder.php](../app/Services/DevApps/TraefikConfigBuilder.php) | generates the router + middleware chain |
| [app/Services/DevApps/AppProvisioner.php](../app/Services/DevApps/AppProvisioner.php) | orchestrates create → git → build → env → deploy |
| [app/Services/Dokploy/DokployClient.php](../app/Services/Dokploy/DokployClient.php) | thin Dokploy API wrapper |
| [config/devapps.php](../config/devapps.php), [config/dokploy.php](../config/dokploy.php) | all tunables |
| [tests/Feature/DevAppAuthzTest.php](../tests/Feature/DevAppAuthzTest.php) | every branch of the access decision, including the denials |
| [tests/Feature/DevAppPortalTest.php](../tests/Feature/DevAppPortalTest.php) | slug guards, ownership, generated config |
| [tests/Feature/DevAppRoutingVerificationTest.php](../tests/Feature/DevAppRoutingVerificationTest.php) | read-back verification and the stop-the-container enforcement |

Tests need MySQL (the suite's sqlite config doesn't work for this project):

```sh
DB_CONNECTION=mysql DB_DATABASE=datakita_devapps_test php artisan test --filter=DevApp
```

---

## Writing an app for this portal

Minimal Express example:

```js
const PORT = process.env.PORT || 3000

app.use((req, res, next) => {
  // Set by the proxy after datakita authorised the request. Empty on a
  // public app with an anonymous visitor.
  req.user = req.headers['x-datakita-user-id']
    ? {
        id:    req.headers['x-datakita-user-id'],
        name:  req.headers['x-datakita-user-name'],
        email: req.headers['x-datakita-user-email'],
        role:  req.headers['x-datakita-user-role'],
      }
    : null
  next()
})
```

Your container also receives `DATAKITA_BASE_PATH` (empty when prefix stripping
is on) and `DATAKITA_PUBLIC_URL`.

Do not accept identity from a query string, a body field, or a cookie — only
from these headers, and only because the proxy overwrites them on every
request.
