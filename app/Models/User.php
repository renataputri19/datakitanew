<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid;

    /**
     * Canonical, mutually-exclusive role keys.
     *
     * A user has exactly ONE role. The role is stored across the legacy
     * boolean flag columns, but only one flag may be set at a time — see
     * {@see self::setRole()} which guarantees the flags never overlap.
     */
    public const ROLE_SUPERADMIN   = 'superadmin';
    public const ROLE_ADMIN        = 'admin';         // Admin BPS Batam — full /bps area
    public const ROLE_BPS_PROVINSI = 'bps_provinsi';  // Admin BPS Provinsi — /bps without Data Survei
    public const ROLE_KOMINFO      = 'kominfo';
    public const ROLE_MITRA        = 'mitra';
    public const ROLE_USER         = 'user';

    /**
     * Every boolean column that encodes a role. When a role is assigned,
     * all of these are cleared first so a user can never hold two roles.
     * (`is_admin` is legacy/retired and is always kept false.)
     *
     * @var array<int, string>
     */
    public const ROLE_FLAG_COLUMNS = [
        'is_superadmin',
        'is_bps',
        'is_bps_provinsi',
        'is_kominfo_user',
        'is_mitra',
        'is_admin',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_id',
        'is_bps',
        'is_kominfo_user',
        'is_admin',
        'is_superadmin',
        'is_mitra',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_bps' => 'boolean',
        'is_bps_provinsi' => 'boolean',
        'is_kominfo_user' => 'boolean',
        'is_admin' => 'boolean',
        'is_superadmin' => 'boolean',
        'is_mitra' => 'boolean',
    ];

    /**
     * Get the institution that the user belongs to.
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Metadata for every assignable role, keyed by role key.
     * Each role maps to a real, already-wired functional area of the app,
     * so an assigned role always grants actual access.
     *
     * `columns` are the flags setRole() switches on for the role.
     *
     * BPS Provinsi is deliberately NOT `is_bps`: it is a regular user who may
     * also open the four /bps/statistik dashboards (`bps_statistik` middleware).
     * Every `is_bps` gate — the rest of /bps, MONALISA BPS, Buku Tamu settings,
     * /develop, MONALISA notifications — therefore treats it as a regular user.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function roleDefinitions(): array
    {
        return [
            self::ROLE_SUPERADMIN => [
                'label'       => 'Super Admin',
                'short'       => 'Superadmin',
                'description' => 'Akses penuh: kelola pengguna, data submission SIBSTR, perusahaan, dan log unduhan.',
                'area'        => '/superadmin',
                'columns'     => ['is_superadmin'],
                'badge'       => 'red',
            ],
            self::ROLE_ADMIN => [
                'label'       => 'Admin BPS Batam',
                'short'       => 'BPS Batam',
                'description' => 'Area BPS lengkap: statistik, Data Survei (SIBSTR/UB/Listrik/Konstruksi), pengingat, berita, video, dan pengguna.',
                'area'        => '/bps',
                'columns'     => ['is_bps'],
                'badge'       => 'blue',
            ],
            self::ROLE_BPS_PROVINSI => [
                'label'       => 'BPS Provinsi',
                'short'       => 'BPS Provinsi',
                'description' => 'Seperti Pengguna biasa, ditambah akses dasbor Statistik BPS (SIBSTR, Listrik, UB, Konstruksi). Tanpa Data Survei, berita, video, MONALISA, atau manajemen pengguna.',
                'area'        => '/bps/statistik',
                'columns'     => ['is_bps_provinsi'],
                'badge'       => 'sky',
            ],
            self::ROLE_KOMINFO => [
                'label'       => 'Kominfo (OPD)',
                'short'       => 'Kominfo',
                'description' => 'Mengisi self-assessment MONALISA untuk instansi/OPD.',
                'area'        => '/monalisa/kominfo',
                'columns'     => ['is_kominfo_user'],
                'badge'       => 'purple',
            ],
            self::ROLE_MITRA => [
                'label'       => 'Mitra Survei',
                'short'       => 'Mitra',
                'description' => 'Input dan edit data survei lapangan (SIBSTR/UB).',
                'area'        => '/survei',
                'columns'     => ['is_mitra'],
                'badge'       => 'amber',
            ],
            self::ROLE_USER => [
                'label'       => 'Pengguna',
                'short'       => 'Pengguna',
                'description' => 'Akses dashboard publik dan layanan umum DataKita.',
                'area'        => '/dashboard',
                'columns'     => [],
                'badge'       => 'gray',
            ],
        ];
    }

    /**
     * The single canonical role of this user, resolved from the flag columns
     * by priority (highest privilege wins for any legacy overlapping data).
     * Keep in step with {@see self::scopeWithRole()}.
     */
    public function getRoleAttribute(): string
    {
        if ($this->is_superadmin)   return self::ROLE_SUPERADMIN;
        if ($this->is_bps)          return self::ROLE_ADMIN;
        if ($this->is_bps_provinsi) return self::ROLE_BPS_PROVINSI;
        if ($this->is_kominfo_user) return self::ROLE_KOMINFO;
        if ($this->is_mitra)        return self::ROLE_MITRA;

        return self::ROLE_USER;
    }

    /**
     * Users whose canonical role is $role — the query form of
     * {@see self::getRoleAttribute()}, so a filtered list always agrees with
     * the badge each row shows and with {@see self::roleCounts()}.
     */
    public function scopeWithRole(Builder $query, string $role): Builder
    {
        $on  = fn (string $column) => $query->where($column, true);
        $off = fn (string $column) => $query->where(fn (Builder $q) => $q->where($column, false)->orWhereNull($column));

        switch ($role) {
            case self::ROLE_SUPERADMIN:
                $on('is_superadmin');
                break;
            case self::ROLE_ADMIN:
                $off('is_superadmin'); $on('is_bps');
                break;
            case self::ROLE_BPS_PROVINSI:
                $off('is_superadmin'); $off('is_bps'); $on('is_bps_provinsi');
                break;
            case self::ROLE_KOMINFO:
                $off('is_superadmin'); $off('is_bps'); $off('is_bps_provinsi'); $on('is_kominfo_user');
                break;
            case self::ROLE_MITRA:
                $off('is_superadmin'); $off('is_bps'); $off('is_bps_provinsi'); $off('is_kominfo_user'); $on('is_mitra');
                break;
            case self::ROLE_USER:
                $query->respondents();
                break;
            default:
                throw new \InvalidArgumentException("Unknown role: {$role}");
        }

        return $query;
    }

    /**
     * BPS Provinsi: a regular user plus the BPS Statistik dashboards.
     */
    public function isBpsProvinsi(): bool
    {
        return $this->role === self::ROLE_BPS_PROVINSI;
    }

    /**
     * May open the four /bps/statistik dashboards: BPS Batam and BPS Provinsi.
     * Enforced by the `bps_statistik` middleware.
     */
    public function canViewBpsStatistik(): bool
    {
        return (bool) $this->is_bps || $this->isBpsProvinsi();
    }

    /**
     * Staff accounts: BPS Batam, BPS Provinsi and Super Admin. Their passwords
     * can only be reset by a Super Admin, and the staff password rotation
     * command covers exactly these.
     */
    public function isStaff(): bool
    {
        return (bool) $this->is_superadmin || (bool) $this->is_bps || (bool) $this->is_bps_provinsi;
    }

    /**
     * Whether this user may set $target's password from the BPS Manajemen
     * Pengguna page: BPS Batam only, and never for a staff target — otherwise
     * any BPS user could take over a Super Admin account.
     */
    public function canResetPasswordOf(self $target): bool
    {
        return (bool) $this->is_bps && ! $target->isStaff();
    }

    /**
     * Respondent accounts only: the ones getRoleAttribute() calls ROLE_USER.
     * Survey statistics use it so entries typed in from staff accounts (BPS
     * Batam/Provinsi, superadmin, Mitra, Kominfo; mostly tests) are not counted
     * as companies. Same flags as getRoleAttribute(), so the retired `is_admin`
     * is ignored.
     */
    public function scopeRespondents(Builder $query): Builder
    {
        foreach (['is_superadmin', 'is_bps', 'is_bps_provinsi', 'is_kominfo_user', 'is_mitra'] as $column) {
            $query->where(fn (Builder $q) => $q->where($column, false)->orWhereNull($column));
        }

        return $query;
    }

    /**
     * Assign a single role, clearing every other role flag so the roles can
     * never overlap. Does not persist — call save() afterwards.
     */
    public function setRole(string $role): void
    {
        $definitions = self::roleDefinitions();

        if (! array_key_exists($role, $definitions)) {
            throw new \InvalidArgumentException("Unknown role: {$role}");
        }

        // Clear every role flag first — this is what guarantees exclusivity.
        foreach (self::ROLE_FLAG_COLUMNS as $column) {
            $this->{$column} = false;
        }

        // Set the flag(s) that back the chosen role (the basic user has none).
        foreach ($definitions[$role]['columns'] as $column) {
            $this->{$column} = true;
        }
    }

    /**
     * Human-friendly label for the user's current role.
     */
    public function roleLabel(): string
    {
        return self::roleDefinitions()[$this->role]['label'];
    }

    /**
     * Tailwind colour keyword for the role badge (red/blue/sky/purple/amber/gray).
     */
    public function roleBadge(): string
    {
        return self::roleDefinitions()[$this->role]['badge'];
    }

    /**
     * Count of users per canonical role, using the same priority resolution
     * as {@see self::getRoleAttribute()} so overlapping legacy rows are only
     * counted once.
     *
     * @return array<string, int>
     */
    public static function roleCounts(): array
    {
        $counts = array_fill_keys(array_keys(self::roleDefinitions()), 0);

        self::query()
            ->select(self::ROLE_FLAG_COLUMNS)
            ->get()
            ->each(function (self $user) use (&$counts) {
                $counts[$user->role]++;
            });

        return $counts;
    }
}
