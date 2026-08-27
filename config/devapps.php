<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    |
    | When false the /develop portal 404s and the edge ForwardAuth endpoint
    | denies everything. Leave it off until the Traefik side is configured.
    |
    */
    'enabled' => env('DEVAPPS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Mount prefix
    |--------------------------------------------------------------------------
    |
    | '' mounts apps at the domain root — https://datakita.../survei-xyz.
    | Set to e.g. 'apps' to mount them at https://datakita.../apps/survei-xyz,
    | which removes any chance of an app shadowing a datakita route.
    |
    */
    'mount_prefix' => env('DEVAPPS_MOUNT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Edge mode
    |--------------------------------------------------------------------------
    |
    | Where the access gate runs.
    |
    |   'proxy'    DataKita authorises and forwards the request itself, via
    |              App\Http\Controllers\Develop\ProxyController. The default,
    |              and the only mode that works on this deployment: there is
    |              no Traefik here, SafeLine proxies straight to each app's
    |              published port, so there is no edge to hang a gate on.
    |
    |   'traefik'  Traefik routes the app directly and calls /develop/authz
    |              as a forwardAuth middleware. Correct code, currently unused
    |              — it becomes useful the day a Traefik actually fronts this
    |              server. See docs/DEVELOP_PORTAL.md.
    |
    */
    'edge_mode' => env('DEVAPPS_EDGE_MODE', 'proxy'),

    /*
    |--------------------------------------------------------------------------
    | Proxy
    |--------------------------------------------------------------------------
    |
    | Timeouts for the hop from DataKita to the dev app's container. Every
    | proxied request holds a PHP-FPM worker for its whole lifetime, and
    | pm.max_children is 20 — a generous timeout here is how one hung dev app
    | takes the portal down with it. Keep them short.
    |
    */
    'proxy' => [
        'connect_timeout' => (float) env('DEVAPPS_PROXY_CONNECT_TIMEOUT', 5),
        'timeout'         => (float) env('DEVAPPS_PROXY_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Public host
    |--------------------------------------------------------------------------
    |
    | The host Traefik serves both datakita and the dev apps on. Defaults to
    | APP_URL. Must match the Host() rule generated for each app's router.
    |
    */
    'public_host_url' => env('DEVAPPS_PUBLIC_HOST_URL', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | ForwardAuth address
    |--------------------------------------------------------------------------
    |
    | The URL Traefik calls to authorise each request. This is datakita's own
    | container, reached over the internal Docker network — never the public
    | hostname, which would send the check back out through the proxy.
    |
    */
    'forward_auth_base' => env('DEVAPPS_FORWARD_AUTH_BASE', 'http://datakita-app'),

    /*
    |--------------------------------------------------------------------------
    | Identity headers
    |--------------------------------------------------------------------------
    |
    | Set on the authorised request before it reaches the dev app. This is the
    | app's ONLY source of identity — it implements no login of its own.
    | Traefik must list exactly these in authResponseHeaders, otherwise they
    | are dropped.
    |
    */
    'identity_headers' => [
        'id'    => 'X-Datakita-User-Id',
        'name'  => 'X-Datakita-User-Name',
        'email' => 'X-Datakita-User-Email',
        'role'  => 'X-Datakita-User-Role',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reserved slugs
    |--------------------------------------------------------------------------
    |
    | Merged with every first path segment in the live route table by
    | DevApp::reservedSlugs(). Add anything that is served by nginx or Traefik
    | rather than by Laravel, since those never appear in the route table.
    |
    */
    'reserved_slugs' => [
        'build', 'storage', 'healthz', 'favicon.ico', 'robots.txt',
        'vendor', 'css', 'js', 'images', 'img', 'fonts', 'assets',
        'api', 'develop', 'admin', 'www', 'mail', 'static',
        'up', 'sanctum', 'livewire', 'broadcasting', 'telescope', 'horizon',
        '.well-known',
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug pattern
    |--------------------------------------------------------------------------
    |
    | Lowercase letters, digits and single hyphens. No dots (would collide
    | with file extensions in nginx), no underscores (inconsistent in URLs).
    |
    */
    'slug_pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

    /*
    |--------------------------------------------------------------------------
    | Per-app limits
    |--------------------------------------------------------------------------
    */
    'max_apps_per_owner' => env('DEVAPPS_MAX_PER_OWNER', 5),

    /*
    |--------------------------------------------------------------------------
    | Traefik
    |--------------------------------------------------------------------------
    |
    | dynamic_path  Directory Traefik watches for dynamic config, mounted into
    |               this container so the portal can drop one file per app.
    |               Dokploy's default is /etc/dokploy/traefik/dynamic. Leave
    |               null and the portal renders the YAML for manual pasting
    |               instead of writing it.
    |
    | priority      Must beat datakita's own router. Traefik defaults to rule
    |               length, and `Host(x) && PathPrefix(y)` is already longer
    |               than `Host(x)`, but pinning it makes the intent explicit.
    |
    | Apps share datakita's hostname, so they also share its TLS certificate —
    | no per-app cert issuance, no wildcard DNS.
    |
    */
    'traefik' => [
        'dynamic_path'  => env('DEVAPPS_TRAEFIK_DYNAMIC_PATH'),

        // Both entrypoints, matching what Dokploy generates for its own apps.
        //
        // Binding only to websecure looks right and silently fails whenever
        // something terminates TLS in front of Traefik — a WAF, a load
        // balancer, Cloudflare Tunnel — because Traefik then receives plain
        // HTTP on :80 and a websecure-only router never matches. The router
        // loads without error and simply never fires, which is a miserable
        // thing to debug.
        'entrypoints'   => explode(',', env('DEVAPPS_TRAEFIK_ENTRYPOINTS', 'web,websecure')),

        // Emitting a `tls:` block on the router would undo the above: a router
        // with TLS settings only matches TLS connections, so it would stop
        // matching on the `web` entrypoint. Traefik's websecure entrypoint
        // already carries a default certResolver, so HTTPS still works without
        // it. Set this only if your entrypoint has no TLS defaults.
        'cert_resolver' => env('DEVAPPS_TRAEFIK_CERT_RESOLVER'),

        'priority'      => (int) env('DEVAPPS_TRAEFIK_PRIORITY', 100),
    ],

];
