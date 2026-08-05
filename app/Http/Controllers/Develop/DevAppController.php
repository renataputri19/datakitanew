<?php

namespace App\Http\Controllers\Develop;

use App\Http\Controllers\Controller;
use App\Models\DevApp;
use App\Models\User;
use App\Services\Dokploy\DokployClient;
use App\Services\Dokploy\DokployException;
use App\Services\DevApps\AppProvisioner;
use App\Services\DevApps\TraefikConfigBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The /develop portal.
 *
 * Lets a BPS user register an application they built in their own Git repo,
 * have datakita create and deploy it as a separate container on Dokploy, and
 * choose who may open it — without ever touching datakita's own codebase or
 * container.
 *
 * Access: ['auth', 'is_bps'] on the route group. Ownership is enforced
 * per-app on top of that by {@see self::authorizeApp()}, so one BPS user
 * cannot redeploy or delete another's application.
 */
class DevAppController extends Controller
{
    public function __construct(
        private readonly AppProvisioner $provisioner,
        private readonly TraefikConfigBuilder $traefik,
        private readonly DokployClient $dokploy,
    ) {
        // The whole portal is invisible until an admin turns it on.
        $this->middleware(function ($request, $next) {
            abort_unless(config('devapps.enabled'), HttpResponse::HTTP_NOT_FOUND);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $apps = DevApp::with('owner')
            ->when(
                ! $this->canManageAll(),
                fn ($q) => $q->where('owner_user_id', Auth::id()),
            )
            ->orderBy('name')
            ->get();

        return view('develop.index', [
            'apps'            => $apps,
            'dokployReady'    => $this->dokploy->isConfigured(),
            'traefikWritable' => $this->traefik->canWrite(),
            'canManageAll'    => $this->canManageAll(),
        ]);
    }

    public function create()
    {
        $this->guardOwnerQuota();

        return view('develop.create', [
            'app'         => new DevApp(['build_type' => 'nixpacks', 'git_branch' => 'main', 'container_port' => 3000, 'strip_prefix' => true]),
            'authModes'   => DevApp::authModeDefinitions(),
            'roles'       => User::roleDefinitions(),
            'users'       => $this->assignableUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $this->guardOwnerQuota();

        $data = $this->validated($request);

        $app = new DevApp($data);
        $app->owner_user_id = Auth::id();
        $app->status = DevApp::STATUS_DRAFT;
        $app->save();

        $this->syncAllowlist($app, $request);

        return redirect()
            ->route('develop.show', $app)
            ->with('success', "Aplikasi \"{$app->name}\" terdaftar. Klik \"Siapkan & Deploy\" untuk membuatnya di Dokploy.");
    }

    public function show(DevApp $app)
    {
        $this->authorizeApp($app);

        $app->load(['owner', 'allowedUsers']);

        return view('develop.show', [
            'app'             => $app,
            'deployments'     => $app->deployments()->limit(10)->get(),
            'authModes'       => DevApp::authModeDefinitions(),
            'roles'           => User::roleDefinitions(),
            'traefikConfig'   => $this->traefik->build($app),
            'traefikWritable' => $this->traefik->canWrite(),
            'traefikFile'     => $this->traefik->fileName($app),
            'dokployReady'    => $this->dokploy->isConfigured(),
        ]);
    }

    public function edit(DevApp $app)
    {
        $this->authorizeApp($app);

        return view('develop.edit', [
            'app'       => $app,
            'authModes' => DevApp::authModeDefinitions(),
            'roles'     => User::roleDefinitions(),
            'users'     => $this->assignableUsers(),
        ]);
    }

    public function update(Request $request, DevApp $app)
    {
        $this->authorizeApp($app);

        $data = $this->validated($request, $app);

        // The slug is baked into the Traefik router name and the app's own
        // env, so a rename means the old routing file must go before the new
        // one is written — otherwise both routers stay live.
        $slugChanged = $app->slug !== $data['slug'];

        if ($slugChanged) {
            $this->traefik->remove($app);
        }

        $app->fill($data)->save();
        $this->syncAllowlist($app, $request);

        // Access changes must take effect now, not at the next deploy: the
        // ForwardAuth endpoint reads the row live, so it already has. Routing
        // changes do need the file rewritten.
        $this->provisioner->applyRouting($app);

        $note = $slugChanged && $app->isProvisioned()
            ? ' Alamat berubah — jalankan Deploy ulang agar variabel lingkungan aplikasi ikut diperbarui.'
            : '';

        return redirect()
            ->route('develop.show', $app)
            ->with('success', "Pengaturan \"{$app->name}\" diperbarui.{$note}");
    }

    /**
     * Create the app on Dokploy and wire source + routing, then build it.
     */
    public function deploy(DevApp $app)
    {
        $this->authorizeApp($app);

        try {
            $this->provisioner->deploy($app, Auth::user());
        } catch (DokployException $e) {
            return back()->with('error', 'Deploy gagal: ' . $e->summary());
        }

        return redirect()
            ->route('develop.show', $app)
            ->with('success', 'Deploy dimulai. Status akan diperbarui saat build selesai.');
    }

    /**
     * Pull the current build status/log from Dokploy.
     */
    public function refresh(DevApp $app)
    {
        $this->authorizeApp($app);

        $this->provisioner->refresh($app);

        return back()->with('success', 'Status diperbarui.');
    }

    public function stop(DevApp $app)
    {
        $this->authorizeApp($app);

        try {
            $this->provisioner->stop($app);
        } catch (DokployException $e) {
            return back()->with('error', 'Gagal menghentikan aplikasi: ' . $e->summary());
        }

        return back()->with('success', 'Aplikasi dihentikan.');
    }

    public function start(DevApp $app)
    {
        $this->authorizeApp($app);

        try {
            $this->provisioner->start($app);
        } catch (DokployException $e) {
            return back()->with('error', 'Gagal menjalankan aplikasi: ' . $e->summary());
        }

        return back()->with('success', 'Aplikasi dijalankan.');
    }

    /**
     * The kill switch. Takes effect on the very next request because the
     * ForwardAuth endpoint reads `enabled` live — no redeploy, no proxy
     * reload.
     */
    public function toggle(DevApp $app)
    {
        $this->authorizeApp($app);

        $app->update(['enabled' => ! $app->enabled]);

        return back()->with(
            'success',
            $app->enabled
                ? 'Aplikasi diaktifkan kembali.'
                : 'Aplikasi dinonaktifkan. Semua pengunjung akan ditolak mulai permintaan berikutnya.',
        );
    }

    /**
     * Download the generated routing config, for installs where the portal
     * has no write access to Traefik's dynamic directory.
     */
    public function traefikConfig(DevApp $app)
    {
        $this->authorizeApp($app);

        return response($this->traefik->build($app), 200, [
            'Content-Type'        => 'text/yaml; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $this->traefik->fileName($app) . '"',
        ]);
    }

    public function destroy(DevApp $app)
    {
        $this->authorizeApp($app);

        $name = $app->name;
        $this->provisioner->destroy($app);

        return redirect()
            ->route('develop.index')
            ->with('success', "Aplikasi \"{$name}\" dihapus beserta kontainernya.");
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /**
     * Shared validation for store/update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?DevApp $app = null): array
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'min:3', 'max:150'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'slug'            => [
                'required', 'string', 'min:2', 'max:60',
                'regex:' . config('devapps.slug_pattern'),
                Rule::unique('dev_apps', 'slug')->ignore($app?->id),
                function ($attribute, $value, $fail) {
                    // Guards against an app claiming a path that Traefik
                    // would then route away from datakita itself.
                    if (DevApp::slugIsReserved($value)) {
                        $fail('Alamat "/' . $value . '" sudah dipakai DataKita. Pilih nama lain.');
                    }
                },
            ],
            'git_repo'        => ['required', 'string', 'max:500', 'regex:#^(https://|git@)#'],
            'git_branch'      => ['required', 'string', 'max:100'],
            'git_build_path'  => ['nullable', 'string', 'max:255'],
            'build_type'      => ['required', Rule::in(['nixpacks', 'dockerfile', 'heroku_buildpacks', 'paketo'])],
            'dockerfile_path' => ['nullable', 'string', 'max:255', 'required_if:build_type,dockerfile'],
            'ssh_key_id'      => ['nullable', 'string', 'max:100'],
            'container_port'  => ['required', 'integer', 'min:1', 'max:65535'],
            'strip_prefix'    => ['nullable', 'boolean'],
            'auth_mode'       => ['required', Rule::in(array_keys(DevApp::authModeDefinitions()))],
            'allowed_roles'   => ['nullable', 'array'],
            'allowed_roles.*' => [Rule::in(array_keys(User::roleDefinitions()))],
        ], $this->messages());

        $validated['git_build_path'] = $validated['git_build_path'] ?: '/';
        $validated['strip_prefix']   = $request->boolean('strip_prefix');

        // Role list only means anything in role mode; clearing it otherwise
        // stops a stale list from silently widening access after a mode change.
        if ($validated['auth_mode'] !== DevApp::AUTH_ROLE) {
            $validated['allowed_roles'] = null;
        }

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required'            => 'Nama aplikasi wajib diisi.',
            'slug.required'            => 'Alamat aplikasi wajib diisi.',
            'slug.regex'               => 'Alamat hanya boleh huruf kecil, angka, dan tanda hubung. Contoh: survei-listrik.',
            'slug.unique'              => 'Alamat ini sudah dipakai aplikasi lain.',
            'git_repo.required'        => 'URL repositori Git wajib diisi.',
            'git_repo.regex'           => 'URL repositori harus diawali https:// atau git@.',
            'git_branch.required'      => 'Branch wajib diisi.',
            'build_type.required'      => 'Metode build wajib dipilih.',
            'dockerfile_path.required_if' => 'Lokasi Dockerfile wajib diisi untuk build type Dockerfile.',
            'container_port.required'  => 'Port aplikasi wajib diisi.',
            'container_port.integer'   => 'Port harus berupa angka.',
            'auth_mode.required'       => 'Mode akses wajib dipilih.',
        ];
    }

    /**
     * Persist the explicit user grants used by allowlist mode.
     */
    private function syncAllowlist(DevApp $app, Request $request): void
    {
        if ($app->auth_mode !== DevApp::AUTH_ALLOWLIST) {
            $app->allowedUsers()->sync([]);

            return;
        }

        $ids = collect($request->input('allowed_users', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        // Only real users — a stale id from a tampered form must not create
        // a dangling grant.
        $app->allowedUsers()->sync(User::whereIn('id', $ids)->pluck('id')->all());
    }

    /**
     * A BPS user manages their own apps; a superadmin manages all of them.
     */
    private function authorizeApp(DevApp $app): void
    {
        abort_unless(
            $this->canManageAll() || $app->owner_user_id === Auth::id(),
            HttpResponse::HTTP_FORBIDDEN,
            'Anda hanya dapat mengelola aplikasi milik Anda sendiri.',
        );
    }

    private function canManageAll(): bool
    {
        return (bool) (Auth::user()?->is_superadmin);
    }

    private function guardOwnerQuota(): void
    {
        if ($this->canManageAll()) {
            return;
        }

        $max = (int) config('devapps.max_apps_per_owner', 5);
        $own = DevApp::where('owner_user_id', Auth::id())->count();

        abort_if(
            $max > 0 && $own >= $max,
            HttpResponse::HTTP_FORBIDDEN,
            "Batas {$max} aplikasi per pengguna tercapai. Hapus salah satu aplikasi lama terlebih dahulu.",
        );
    }

    /**
     * Candidates for the allowlist picker.
     */
    private function assignableUsers()
    {
        return User::orderBy('name')->get(['id', 'name', 'email']);
    }
}
