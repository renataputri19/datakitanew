<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Who downloaded which survey Excel, and when — the audit trail written by
 * the BPS Data Survei exports. Read-only: rows cannot be edited or deleted
 * from the app.
 *
 * Access is enforced by the ['auth', 'is_superadmin'] route group.
 */
class ExportLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'survey'    => ['nullable', Rule::in(array_keys(ExportLog::SURVEYS))],
            'search'    => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to'   => ['nullable', 'date_format:Y-m-d'],
        ]);

        $survey   = $validated['survey'] ?? null;
        $search   = trim((string) ($validated['search'] ?? ''));
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo   = $validated['date_to'] ?? null;

        $query = ExportLog::query()->latest('created_at');

        if ($survey) {
            $query->where('survey', $survey);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
            });
        }

        // Dates are picked in WIB; timestamps are stored in UTC.
        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom, 'Asia/Jakarta')->startOfDay()->utc());
        }
        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo, 'Asia/Jakarta')->endOfDay()->utc());
        }

        $since = now()->subDays(30);
        $recent = fn () => ExportLog::where('created_at', '>=', $since);

        $summary = [
            'total'   => ExportLog::count(),
            'last30'  => $recent()->count(),
            'users30' => $recent()->distinct()->count('user_email'),
            'rows30'  => (int) $recent()->sum('row_count'),
        ];

        $perSurvey = ExportLog::selectRaw('survey, COUNT(*) as n')->groupBy('survey')->pluck('n', 'survey');

        return view('superadmin.export-logs.index', [
            'logs'        => $query->paginate(25)->withQueryString(),
            'summary'     => $summary,
            'perSurvey'   => $perSurvey,
            'surveys'     => ExportLog::SURVEYS,
            'definitions' => User::roleDefinitions(),
            'survey'      => $survey,
            'search'      => $search,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
        ]);
    }
}
