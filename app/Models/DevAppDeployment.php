<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One deploy attempt of a {@see DevApp}, recorded so the portal can show
 * history and build logs without the developer needing Dokploy access.
 */
class DevAppDeployment extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_QUEUED  = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'dev_app_id',
        'triggered_by_user_id',
        'dokploy_deployment_id',
        'status',
        'log',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function devApp(): BelongsTo
    {
        return $this->belongsTo(DevApp::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'green',
            self::STATUS_RUNNING => 'blue',
            self::STATUS_FAILED  => 'red',
            default              => 'gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_QUEUED  => 'Antre',
            self::STATUS_RUNNING => 'Berjalan',
            self::STATUS_SUCCESS => 'Berhasil',
            self::STATUS_FAILED  => 'Gagal',
            default              => ucfirst((string) $this->status),
        };
    }
}
