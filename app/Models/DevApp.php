<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * An externally-developed application mounted under a path on the datakita
 * domain.
 *
 * datakita owns two things about it and nothing else:
 *   1. the routing — Traefik sends https://<host>/<slug> to the app's own
 *      container, which is built from the developer's own Git repo;
 *   2. the access decision — {@see self::allows()} is what the edge
 *      ForwardAuth endpoint calls on every single request.
 *
 * The application code itself runs in a separate container with a separate
 * environment. It never has datakita's database credentials.
 */
class DevApp extends Model
{
    use HasFactory, HasUuid;

    // ── Access modes ────────────────────────────────────────────────────
    public const AUTH_PUBLIC         = 'public';
    public const AUTH_LOGIN_REQUIRED = 'login_required';
    public const AUTH_ROLE           = 'role';
    public const AUTH_ALLOWLIST      = 'allowlist';
    public const AUTH_OWNER_ONLY     = 'owner_only';

    // ── Edge-protection states ──────────────────────────────────────────
    //
    // Whether the auth gate is actually installed in Traefik. This cannot be
    // inferred from anything else: if the forwardAuth middleware goes missing,
    // requests reach the app WITHOUT passing through datakita at all, so the
    // access decision in allows() is never consulted and cannot save us.
    // Enforcement therefore happens by stopping the container — see
    // AppProvisioner::verifyRouting().
    public const ROUTING_UNKNOWN      = 'unknown';       // never applied yet
    public const ROUTING_PROTECTED    = 'protected';     // read back, gate present
    public const ROUTING_UNPROTECTED  = 'unprotected';   // read back, gate MISSING
    public const ROUTING_UNVERIFIABLE = 'unverifiable';  // could not read it back

    // ── Lifecycle states ────────────────────────────────────────────────
    public const STATUS_DRAFT        = 'draft';
    public const STATUS_PROVISIONING = 'provisioning';
    public const STATUS_DEPLOYING    = 'deploying';
    public const STATUS_RUNNING      = 'running';
    public const STATUS_FAILED       = 'failed';
    public const STATUS_STOPPED      = 'stopped';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'owner_user_id',
        'git_repo',
        'git_branch',
        'git_build_path',
        'build_type',
        'dockerfile_path',
        'ssh_key_id',
        'container_port',
        'strip_prefix',
        'auth_mode',
        'allowed_roles',
        'enabled',
    ];

    protected $casts = [
        'allowed_roles'      => 'array',
        'enabled'            => 'boolean',
        'strip_prefix'       => 'boolean',
        'container_port'     => 'integer',
        'last_deployed_at'   => 'datetime',
        'routing_checked_at' => 'datetime',
    ];

    // ── Relations ───────────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function allowedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dev_app_allowed_users')->withTimestamps();
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(DevAppDeployment::class)->latest();
    }

    // ── Access decision ─────────────────────────────────────────────────

    /**
     * The single authority on whether a request may reach this app.
     *
     * Called by the edge ForwardAuth endpoint on EVERY request to the app,
     * including its static assets — keep it query-light and side-effect free.
     */
    public function allows(?User $user): bool
    {
        // A disabled or not-yet-running app is closed to everyone. This is
        // what makes the portal's on/off switch take effect immediately,
        // with no redeploy of the app itself.
        if (! $this->enabled) {
            return false;
        }

        if ($this->auth_mode === self::AUTH_PUBLIC) {
            return true;
        }

        // Every remaining mode requires a datakita session.
        if (! $user) {
            return false;
        }

        return match ($this->auth_mode) {
            self::AUTH_LOGIN_REQUIRED => true,
            self::AUTH_OWNER_ONLY     => $user->id === $this->owner_user_id,
            self::AUTH_ROLE           => in_array($user->role, $this->allowed_roles ?? [], true),
            self::AUTH_ALLOWLIST      => $user->id === $this->owner_user_id
                                         || $this->allowedUsers()->whereKey($user->id)->exists(),
            // Unknown mode stored by a future version → fail closed.
            default                   => false,
        };
    }

    /**
     * True when the app needs a logged-in datakita user, i.e. when an
     * anonymous visitor should be bounced to /login rather than 403'd.
     */
    public function requiresLogin(): bool
    {
        return $this->auth_mode !== self::AUTH_PUBLIC;
    }

    /**
     * Human-readable access modes for the portal's dropdown.
     *
     * @return array<string, array{label: string, hint: string}>
     */
    public static function authModeDefinitions(): array
    {
        return [
            self::AUTH_PUBLIC => [
                'label' => 'Publik',
                'hint'  => 'Siapa pun bisa membuka, tanpa login.',
            ],
            self::AUTH_LOGIN_REQUIRED => [
                'label' => 'Wajib login DataKita',
                'hint'  => 'Semua akun DataKita yang sudah login bisa mengakses.',
            ],
            self::AUTH_ROLE => [
                'label' => 'Role tertentu',
                'hint'  => 'Hanya akun dengan role yang dipilih di bawah.',
            ],
            self::AUTH_ALLOWLIST => [
                'label' => 'Daftar pengguna',
                'hint'  => 'Hanya akun yang dipilih satu per satu, plus pemilik aplikasi.',
            ],
            self::AUTH_OWNER_ONLY => [
                'label' => 'Hanya pemilik',
                'hint'  => 'Hanya akun pemilik aplikasi. Cocok untuk uji coba.',
            ],
        ];
    }

    // ── Routing ─────────────────────────────────────────────────────────

    /**
     * The public path this app is mounted at, e.g. "/survei-listrik".
     */
    public function mountPath(): string
    {
        $prefix = trim((string) config('devapps.mount_prefix', ''), '/');

        return $prefix === ''
            ? '/' . $this->slug
            : '/' . $prefix . '/' . $this->slug;
    }

    /**
     * The absolute public URL of the app.
     */
    public function publicUrl(): string
    {
        return rtrim(config('devapps.public_host_url') ?: config('app.url'), '/') . $this->mountPath();
    }

    /**
     * Slugs a dev app may never claim.
     *
     * This is a security control, not a convenience. Traefik matches the
     * more specific PathPrefix router before datakita's catch-all Host
     * router, so an app registered as "bps" would silently take over
     * datakita's own /bps area for every visitor. The list is derived from
     * the live route table so it can never drift from reality.
     *
     * @return list<string>
     */
    public static function reservedSlugs(): array
    {
        $reserved = config('devapps.reserved_slugs', []);

        foreach (Route::getRoutes() as $route) {
            $first = Str::of($route->uri())->before('/')->toString();

            // Skip the catch-all and parameterised first segments.
            if ($first === '' || $first === '/' || Str::startsWith($first, '{')) {
                continue;
            }

            $reserved[] = strtolower($first);
        }

        return array_values(array_unique($reserved));
    }

    /**
     * Whether a slug is free to claim.
     */
    public static function slugIsReserved(string $slug): bool
    {
        return in_array(strtolower(trim($slug, '/')), self::reservedSlugs(), true);
    }

    // ── Presentation ────────────────────────────────────────────────────

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_RUNNING                            => 'green',
            self::STATUS_DEPLOYING, self::STATUS_PROVISIONING => 'blue',
            self::STATUS_FAILED                             => 'red',
            self::STATUS_STOPPED                            => 'amber',
            default                                         => 'gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT        => 'Draf',
            self::STATUS_PROVISIONING => 'Menyiapkan',
            self::STATUS_DEPLOYING    => 'Deploy berjalan',
            self::STATUS_RUNNING      => 'Berjalan',
            self::STATUS_FAILED       => 'Gagal',
            self::STATUS_STOPPED      => 'Dihentikan',
            default                   => ucfirst((string) $this->status),
        };
    }

    /**
     * True once the app exists on Dokploy and can be deployed/stopped.
     */
    public function isProvisioned(): bool
    {
        return ! empty($this->dokploy_application_id);
    }

    // ── Edge protection ─────────────────────────────────────────────────

    /**
     * Whether the auth gate was positively confirmed present at the edge.
     *
     * Anything other than a confirmed yes is treated as a no by the callers —
     * "we couldn't check" is not the same as "it's fine".
     */
    public function isProtected(): bool
    {
        return $this->routing_status === self::ROUTING_PROTECTED;
    }

    /**
     * True when we positively read a config with the gate missing. This is
     * the state that justifies stopping the container.
     */
    public function isConfirmedUnprotected(): bool
    {
        return $this->routing_status === self::ROUTING_UNPROTECTED;
    }

    public function routingLabel(): string
    {
        return match ($this->routing_status) {
            self::ROUTING_PROTECTED    => 'Terlindungi',
            self::ROUTING_UNPROTECTED  => 'TIDAK terlindungi',
            self::ROUTING_UNVERIFIABLE => 'Tidak dapat diperiksa',
            default                    => 'Belum dipasang',
        };
    }

    public function routingBadge(): string
    {
        return match ($this->routing_status) {
            self::ROUTING_PROTECTED    => 'green',
            self::ROUTING_UNPROTECTED  => 'red',
            self::ROUTING_UNVERIFIABLE => 'amber',
            default                    => 'gray',
        };
    }
}
