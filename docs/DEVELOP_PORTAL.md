# Developer Portal (`/develop`)

Lets a BPS user run an application built in **their own Git repository** under
a path on the datakita domain, gated by datakita's own login — without their
code ever entering this repo or this container.

```
https://datakita.angkatabatam.id/survei-xyz
        │                         │
        │                         └── somebody else's container, built from
        │                             their GitHub repo
        └── datakita's domain, datakita's TLS cert, datakita's login
```

---

## How a request flows

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
2. Settings → API/CLI → generate a key.
3. Set in datakita's environment:
   ```
   DOKPLOY_URL=https://your-dokploy-host:3000
   DOKPLOY_API_KEY=...
   DOKPLOY_PROJECT_ID=...
   ```

The API key can create and delete applications on the server. Treat it as a
root credential — Dokploy's environment tab, never the repo.

### 2. Traefik dynamic config

The portal generates one YAML file per app. Mount Dokploy's Traefik dynamic
directory into the datakita container (Advanced → Volumes) and point the app
at it:

```
DEVAPPS_TRAEFIK_DYNAMIC_PATH=/etc/dokploy/traefik/dynamic
```

Without the mount everything still works, but each app's config has to be
downloaded from its detail page and copied across by hand.

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

Dokploy renames API endpoints between releases; 0.x has shipped both
`application.saveGitProdiver` (with the typo) and `application.saveGitProvider`.
They are therefore config, not code — `config/dokploy.php`, each overridable by
env. Check yours at `<DOKPLOY_URL>/swagger` and override any that differ:

```
DOKPLOY_EP_SAVE_GIT=application.saveGitProvider
```

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
