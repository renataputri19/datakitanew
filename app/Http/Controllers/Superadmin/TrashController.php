<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ListrikSurveyResponse;
use App\Models\SurveyResponse;
use App\Models\UbSurveyResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Superadmin recycle bin for survey submissions soft-deleted by BPS from the
 * /bps/{sibstr,ub,listrik} data pages.
 *
 * Restoring is not unconditional: each table keeps a "one live submission per
 * user per period" unique key (see migration 2026_07_19_000002). If the
 * responden already re-filled the survey after the deletion, that slot is
 * occupied and restoring would hit a duplicate-key error — so every row is
 * checked first and reported as terhalang rather than blowing up.
 */
class TrashController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'is_superadmin']);
    }

    /**
     * Per-survey config: model, label, and the columns forming the
     * "one live row per period" uniqueness rule.
     */
    private const TYPES = [
        'sibstr' => [
            'model'  => SurveyResponse::class,
            'label'  => 'Survei SIBSTR',
            'unique' => ['user_id', 'survey_type', 'tahun', 'triwulan'],
        ],
        'ub' => [
            'model'  => UbSurveyResponse::class,
            'label'  => 'Survei UB',
            'unique' => ['user_id', 'tahun'],
        ],
        'listrik' => [
            'model'  => ListrikSurveyResponse::class,
            'label'  => 'Survei Listrik',
            'unique' => ['user_id', 'tahun'],
        ],
    ];

    private function config(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    /** Trashed-rows query for a survey type (SIBSTR rows are type-scoped). */
    private function trashedQuery(string $type): Builder
    {
        $cfg   = $this->config($type);
        $query = $cfg['model']::onlyTrashed()->with('user');

        if ($type === 'sibstr') {
            $query->where('survey_type', 'sibstr');
        }

        return $query;
    }

    /**
     * Is the period slot this row belongs to still free?
     * Uses the default (non-trashed) scope, so it only sees live rows.
     */
    private function slotIsFree(string $type, Model $row): bool
    {
        $cfg   = $this->config($type);
        $query = $cfg['model']::query();

        foreach ($cfg['unique'] as $col) {
            $query->where($col, $row->{$col});
        }

        return !$query->exists();
    }

    /** Human-readable period for a row. */
    private function periodLabel(string $type, Model $row): string
    {
        if ($type !== 'sibstr') {
            return 'Tahun ' . ($row->tahun ?? '—');
        }

        return ((int) ($row->triwulan ?? 0)) === 0
            ? 'Tahunan ' . ($row->tahun ?? '—')
            : 'Triwulan ' . $row->triwulan . ' ' . ($row->tahun ?? '—');
    }

    public function index(Request $request)
    {
        $type   = $request->input('type', 'sibstr');
        $search = $request->input('search');
        $this->config($type);

        $counts = [];
        foreach (array_keys(self::TYPES) as $t) {
            $counts[$t] = $this->trashedQuery($t)->count();
        }

        $query = $this->trashedQuery($type);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $rows = $query->orderByDesc('deleted_at')
            ->paginate($request->input('per_page', 25))
            ->withQueryString();

        // Decorate each row with restorability so the view stays logic-free.
        $rows->getCollection()->transform(function ($row) use ($type) {
            $row->setAttribute('can_restore', $this->slotIsFree($type, $row));
            $row->setAttribute('period_label', $this->periodLabel($type, $row));

            return $row;
        });

        $blockedTotal = $this->trashedQuery($type)->get()
            ->filter(fn ($r) => !$this->slotIsFree($type, $r))
            ->count();

        return view('superadmin.trash.index', [
            'rows'         => $rows,
            'type'         => $type,
            'types'        => array_map(fn ($c) => $c['label'], self::TYPES),
            'counts'       => $counts,
            'search'       => $search,
            'blockedTotal' => $blockedTotal,
            'user'         => $request->user(),
        ]);
    }

    /**
     * Restore a single submission.
     */
    public function restore(Request $request, string $type, string $id)
    {
        $cfg = $this->config($type);
        $row = $cfg['model']::onlyTrashed()->findOrFail($id);

        $label  = $row->nama_perusahaan ?: ($row->user->name ?? 'Responden');
        $period = $this->periodLabel($type, $row);

        if (!$this->slotIsFree($type, $row)) {
            return redirect()
                ->route('superadmin.trash.index', ['type' => $type])
                ->with('error', "Tidak dapat memulihkan \"{$label}\" ({$period}): responden sudah mengisi ulang {$cfg['label']} untuk periode tersebut. Hapus dulu data yang aktif jika ingin memulihkan versi lama ini.");
        }

        $row->restore();

        return redirect()
            ->route('superadmin.trash.index', ['type' => $type])
            ->with('success', "Data {$cfg['label']} milik \"{$label}\" ({$period}) berhasil dipulihkan.");
    }

    /**
     * Restore every recoverable submission of one survey type. Rows whose
     * period slot is taken are skipped and reported rather than failing the
     * whole batch.
     */
    public function restoreAll(Request $request, string $type)
    {
        $cfg     = $this->config($type);
        $rows    = $this->trashedQuery($type)->get();
        $done    = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (!$this->slotIsFree($type, $row)) {
                $skipped++;
                continue;
            }
            $row->restore();
            $done++;
        }

        $redirect = redirect()->route('superadmin.trash.index', ['type' => $type]);

        if ($done === 0 && $skipped === 0) {
            return $redirect->with('error', "Tidak ada data {$cfg['label']} yang terhapus.");
        }

        if ($done === 0) {
            return $redirect->with('error', "Tidak ada data yang dapat dipulihkan: {$skipped} data terhalang karena respondennya sudah mengisi ulang periode yang sama.");
        }

        $msg = "{$done} data {$cfg['label']} berhasil dipulihkan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} data dilewati karena periodenya sudah terisi data baru.";
        }

        return $redirect->with('success', $msg);
    }
}
