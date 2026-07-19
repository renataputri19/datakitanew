<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
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
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN      = 'admin';   // Admin BPS — the /bps management area
    public const ROLE_KOMINFO    = 'kominfo';
    public const ROLE_MITRA      = 'mitra';
    public const ROLE_USER       = 'user';

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
     * @return array<string, array<string, string|null>>
     */
    public static function roleDefinitions(): array
    {
        return [
            self::ROLE_SUPERADMIN => [
                'label'       => 'Super Admin',
                'short'       => 'Superadmin',
                'description' => 'Akses penuh: kelola pengguna, data submission SIBSTR, dan perusahaan.',
                'area'        => '/superadmin',
                'column'      => 'is_superadmin',
                'badge'       => 'red',
            ],
            self::ROLE_ADMIN => [
                'label'       => 'Admin BPS',
                'short'       => 'Admin BPS',
                'description' => 'Kelola berita, video, data survei (SIBSTR/UB), dan pengguna di area BPS.',
                'area'        => '/bps',
                'column'      => 'is_bps',
                'badge'       => 'blue',
            ],
            self::ROLE_KOMINFO => [
                'label'       => 'Kominfo (OPD)',
                'short'       => 'Kominfo',
                'description' => 'Mengisi self-assessment MONALISA untuk instansi/OPD.',
                'area'        => '/monalisa/kominfo',
                'column'      => 'is_kominfo_user',
                'badge'       => 'purple',
            ],
            self::ROLE_MITRA => [
                'label'       => 'Mitra Survei',
                'short'       => 'Mitra',
                'description' => 'Input dan edit data survei lapangan (SIBSTR/UB).',
                'area'        => '/survei',
                'column'      => 'is_mitra',
                'badge'       => 'amber',
            ],
            self::ROLE_USER => [
                'label'       => 'Pengguna',
                'short'       => 'Pengguna',
                'description' => 'Akses dashboard publik dan layanan umum DataKita.',
                'area'        => '/dashboard',
                'column'      => null,
                'badge'       => 'gray',
            ],
        ];
    }

    /**
     * The single canonical role of this user, resolved from the flag columns
     * by priority (highest privilege wins for any legacy overlapping data).
     */
    public function getRoleAttribute(): string
    {
        if ($this->is_superadmin)   return self::ROLE_SUPERADMIN;
        if ($this->is_bps)          return self::ROLE_ADMIN;
        if ($this->is_kominfo_user) return self::ROLE_KOMINFO;
        if ($this->is_mitra)        return self::ROLE_MITRA;

        return self::ROLE_USER;
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

        // Set the one flag that backs the chosen role (the basic user has none).
        $column = $definitions[$role]['column'];
        if ($column !== null) {
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
     * Tailwind colour keyword for the role badge (red/blue/purple/amber/gray).
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
