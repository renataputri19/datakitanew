<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * One survey Excel/CSV export from a BPS Data Survei page. Written by
 * {@see \App\Http\Controllers\BPS\Concerns\ExportsSurveyMonitoring::logExport()}
 * and read by superadmins at /superadmin/log-unduhan.
 *
 * Append-only: rows are never updated, so there is no updated_at.
 */
class ExportLog extends Model
{
    use HasUuid;

    public const UPDATED_AT = null;

    /** Survey key => label, in sidebar order. */
    public const SURVEYS = [
        'sibstr'     => 'SIBSTR',
        'ub'         => 'UB',
        'listrik'    => 'Listrik',
        'konstruksi' => 'Konstruksi',
    ];

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'survey',
        'format',
        'filename',
        'row_count',
        'filters',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'filters'    => 'array',
        'row_count'  => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an export by the request's user.
     *
     * Filters are copied from the query string (the export dialog submits
     * GET), keeping only short scalar values: enough to see what was pulled
     * without letting a crafted URL bloat the table.
     */
    public static function record(Request $request, string $survey, string $filename, string $format, int $rows): self
    {
        $user = $request->user();

        $filters = collect($request->query())
            ->except(['format', '_token'])
            ->filter(fn ($value) => is_scalar($value) && (string) $value !== '')
            ->map(fn ($value) => Str::limit((string) $value, 100, ''))
            ->take(20)
            ->all();

        return self::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->name ?? '-',
            'user_email' => $user?->email ?? '-',
            'user_role'  => $user?->role ?? 'guest',
            'survey'     => $survey,
            'format'     => $format,
            'filename'   => $filename,
            'row_count'  => $rows,
            'filters'    => $filters ?: null,
            'ip_address' => self::clientIp($request),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);
    }

    /**
     * The visitor's address. Behind Cloudflare -> SafeLine, request()->ip()
     * resolves to a Cloudflare edge node, so prefer the header Cloudflare
     * sets — it is supplementary here; the account is the accountable party.
     */
    private static function clientIp(Request $request): ?string
    {
        $cf = $request->header('CF-Connecting-IP');

        if (is_string($cf) && filter_var($cf, FILTER_VALIDATE_IP)) {
            return $cf;
        }

        return $request->ip();
    }

    public function surveyLabel(): string
    {
        return self::SURVEYS[$this->survey] ?? strtoupper($this->survey);
    }
}
