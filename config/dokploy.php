<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dokploy API
    |--------------------------------------------------------------------------
    |
    | base_url  Root of the Dokploy panel, e.g. https://dokploy.internal:3000
    | api_key   Generated in Dokploy under Settings → API/CLI. Sent as x-api-key.
    |
    | This token can create and delete applications on the server. Treat it
    | like a root credential: it belongs in the Dokploy environment tab, never
    | in the repo.
    |
    */
    'base_url' => rtrim((string) env('DOKPLOY_URL', ''), '/'),
    'api_key'  => env('DOKPLOY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Target project / environment / server
    |--------------------------------------------------------------------------
    |
    | Every dev app is created here, so they stay grouped and separate from
    | datakita itself.
    |
    | Dokploy 0.29 introduced Environments inside projects (production / dev),
    | and on those versions applications belong to an *environment*, not
    | directly to a project — application.create wants environmentId. Older
    | versions want projectId. Set whichever your install uses: when
    | DOKPLOY_ENVIRONMENT_ID is present it wins, otherwise we fall back to
    | DOKPLOY_PROJECT_ID. `php artisan dokploy:ping` prints both for every
    | project so you can copy the right id.
    |
    | server_id is only needed on multi-server installs; leave null for a
    | single-node setup.
    |
    */
    'project_id'     => env('DOKPLOY_PROJECT_ID'),
    'environment_id' => env('DOKPLOY_ENVIRONMENT_ID'),
    'server_id'      => env('DOKPLOY_SERVER_ID'),

    /*
    |--------------------------------------------------------------------------
    | HTTP behaviour
    |--------------------------------------------------------------------------
    */
    'timeout'      => (int) env('DOKPLOY_TIMEOUT', 30),
    'verify_tls'   => (bool) env('DOKPLOY_VERIFY_TLS', true),

    /*
    |--------------------------------------------------------------------------
    | Endpoint map
    |--------------------------------------------------------------------------
    |
    | Dokploy renames endpoints between releases (0.x has shipped both
    | `application.saveGitProdiver` — with the typo — and
    | `application.saveGitProvider`). Rather than hard-code them, they live
    | here so a mismatch is an env change, not a code change.
    |
    | Verify the names for your install at <DOKPLOY_URL>/swagger, then run
    | `php artisan dokploy:ping` to confirm the client can reach them.
    |
    */
    'endpoints' => [
        'project_all'        => env('DOKPLOY_EP_PROJECT_ALL', 'project.all'),
        'application_one'    => env('DOKPLOY_EP_APP_ONE', 'application.one'),
        'application_create' => env('DOKPLOY_EP_APP_CREATE', 'application.create'),
        'application_delete' => env('DOKPLOY_EP_APP_DELETE', 'application.delete'),
        'application_deploy' => env('DOKPLOY_EP_APP_DEPLOY', 'application.deploy'),
        'application_stop'   => env('DOKPLOY_EP_APP_STOP', 'application.stop'),
        'application_start'  => env('DOKPLOY_EP_APP_START', 'application.start'),
        'save_git_provider'  => env('DOKPLOY_EP_SAVE_GIT', 'application.saveGitProdiver'),
        'save_build_type'    => env('DOKPLOY_EP_SAVE_BUILD', 'application.saveBuildType'),
        'save_environment'   => env('DOKPLOY_EP_SAVE_ENV', 'application.saveEnvironment'),
        'domain_create'      => env('DOKPLOY_EP_DOMAIN_CREATE', 'domain.create'),
        'domain_delete'      => env('DOKPLOY_EP_DOMAIN_DELETE', 'domain.delete'),
        'deployment_all'     => env('DOKPLOY_EP_DEPLOYMENT_ALL', 'deployment.all'),
    ],

];
