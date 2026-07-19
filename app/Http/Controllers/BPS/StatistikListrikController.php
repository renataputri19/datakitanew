<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\ListrikSurveyResponse;
use Illuminate\Http\Request;

/**
 * Statistik Listrik — BPS-only dashboard over Survei Listrik data.
 * Same architecture as the SIBSTR statistik dashboard: the controller
 * flattens every respondent into one embedded JSON payload and the page
 * filters/aggregates client-side.
 */
class StatistikListrikController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    public function index(Request $request)
    {
        $rows = ListrikSurveyResponse::with('user:id,name,email')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('user_id')
            ->values();

        $monthKeys = ListrikSurveyResponse::availableMonthKeys();

        $monthMeta = [];
        foreach ($monthKeys as $key) {
            [$y, $m] = explode('_', $key);
            $monthMeta[] = ['key' => $key, 'year' => (int) $y, 'month' => (int) $m];
        }

        $payload = [
            'months'      => $monthMeta,
            'years'       => array_values(array_unique(array_column($monthMeta, 'year'))),
            'categories'  => ListrikSurveyResponse::CATEGORIES,
            'generatedAt' => now()->locale('id')->translatedFormat('j F Y H.i'),
            'rows'        => $rows->map(fn ($r) => $this->buildRow($r))->all(),
        ];

        return view('bps.statistik.listrik', [
            'user'    => $request->user(),
            'payload' => $payload,
        ]);
    }

    private function buildRow(ListrikSurveyResponse $r): array
    {
        // Normalise the stored grid (list of wilayah rows per month; legacy
        // object months are wrapped) into: monthly totals per category, plus
        // per-wilayah splits for the wilayah breakdown card.
        $raw = is_array($r->data_listrik) ? $r->data_listrik : [];
        $monthly = [];
        $wilayahMonthly = [];
        foreach (ListrikSurveyResponse::availableMonthKeys() as $ym) {
            $rows = ListrikSurveyResponse::normalizeMonthRows($raw[$ym] ?? null);
            foreach (array_keys(ListrikSurveyResponse::CATEGORIES) as $cat) {
                foreach (['kwh', 'rp'] as $f) {
                    $monthly[$ym][$cat][$f] = null;
                }
            }
            foreach ($rows as $row) {
                $label = ListrikSurveyResponse::wilayahLabel($row['w'] ?? null);
                foreach (array_keys(ListrikSurveyResponse::CATEGORIES) as $cat) {
                    foreach (['kwh', 'rp'] as $f) {
                        $v = $row[$cat][$f] ?? null;
                        if (!is_numeric($v)) {
                            continue;
                        }
                        $monthly[$ym][$cat][$f] = ($monthly[$ym][$cat][$f] ?? 0) + (float) $v;
                        $wilayahMonthly[$ym][$label][$cat][$f] = ($wilayahMonthly[$ym][$label][$cat][$f] ?? 0) + (float) $v;
                    }
                }
            }
        }

        return [
            'id'              => $r->id,
            'uid'             => $r->user_id,
            'perusahaan'      => $r->nama_perusahaan ?: ($r->user->name ?? 'Tanpa nama'),
            'kabupaten'       => $r->kabupaten_kota,
            'jenisPembangkit' => $r->jenis_pembangkit,
            'dayaKw'          => $r->daya_terpasang_kw !== null ? (float) $r->daya_terpasang_kw : null,
            'selesai'         => (bool) $r->is_completed,
            'updatedAt'       => optional($r->updated_at)->locale('id')->translatedFormat('j M Y'),
            'catatan'         => $r->catatan,
            'monthly'         => $monthly,
            'wilayahMonthly'  => $wilayahMonthly,
        ];
    }
}
