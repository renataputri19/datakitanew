<?php

namespace App\Http\Controllers\BPS;

use App\Exports\KonstruksiBlok1Export;
use App\Http\Controllers\BPS\Concerns\ExportsSurveyMonitoring;
use App\Http\Controllers\Controller;
use App\Models\KonstruksiSurveyResponse;
use App\Support\KonstruksiBlokPath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * BPS-side data pages for Survei Konstruksi — mirrors BPS\ListrikController
 * (list, export, detail, full-data PDF, soft delete).
 *
 * Konstruksi rows carry a period like SIBSTR does, so the list and the export
 * both offer a period filter the other surveys do not need. Only the annual
 * questionnaire exists today; the filter is already period-shaped so the
 * quarterly one slots in without another migration of this page.
 */
class KonstruksiController extends Controller
{
    use ExportsSurveyMonitoring;

    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    /** Apply the free-text search shared by the list and the export dialog. */
    private function applySearch($query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('nama_perusahaan', 'like', "%{$search}%")
              ->orWhere('kabupaten_kota', 'like', "%{$search}%")
              ->orWhere('nus', 'like', "%{$search}%")
              ->orWhere('nib', 'like', "%{$search}%")
              ->orWhereHas('user', function ($uq) use ($search) {
                  $uq->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Narrow to one period. The dialog sends "2025-tahunan" / "2026-2" so a
     * single dropdown covers both instruments.
     */
    private function applyPeriod($query, ?string $period): void
    {
        if ($period === null || $period === '' || !str_contains($period, '-')) {
            return;
        }

        [$tahun, $tw] = explode('-', $period, 2);
        $query->where('tahun', (int) $tahun)
              ->where('triwulan', $tw === 'tahunan' ? 0 : (int) $tw);
    }

    private function applyStatus($query, ?string $status): void
    {
        if ($status === 'completed') {
            $query->where('is_completed', true);
        } elseif ($status === 'in_progress') {
            $query->where('is_completed', false);
        }
    }

    /** Periods that actually have rows, newest first, for the dropdowns. */
    private function periodOptions(): array
    {
        return KonstruksiSurveyResponse::query()
            ->select('tahun', 'triwulan')
            ->distinct()
            ->orderByDesc('tahun')
            ->orderBy('triwulan')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->tahun . '-' . ($r->triwulan === 0 ? 'tahunan' : $r->triwulan)
                    => KonstruksiBlokPath::periodLabel($r->tahun, $r->triwulan),
            ])
            ->all();
    }

    public function index(Request $request)
    {
        $user    = Auth::user();
        $sortBy  = $request->input('sort_by', 'updated_at');
        $perPage = $request->input('per_page', 25);

        $query = KonstruksiSurveyResponse::with('user');
        $this->applySearch($query, $request->input('search'));
        $this->applyStatus($query, $request->input('status'));
        $this->applyPeriod($query, $request->input('period'));

        $allowed = ['updated_at', 'created_at', 'nama_perusahaan'];
        $query->orderBy(in_array($sortBy, $allowed, true) ? $sortBy : 'updated_at', 'desc');

        $surveyResponses = $query->paginate($perPage)->withQueryString();

        // Split by instrument: BPS monitors the annual and quarterly rounds
        // separately, and a single combined count hides which one is lagging.
        $stats = [
            'total'       => KonstruksiSurveyResponse::count(),
            'completed'   => KonstruksiSurveyResponse::where('is_completed', true)->count(),
            'in_progress' => KonstruksiSurveyResponse::where('is_completed', false)->count(),
            'tahunan'     => KonstruksiSurveyResponse::where('triwulan', KonstruksiSurveyResponse::TAHUNAN)->count(),
            'triwulanan'  => KonstruksiSurveyResponse::where('triwulan', '!=', KonstruksiSurveyResponse::TAHUNAN)->count(),
        ];

        // Dropdown choices come from the data itself, so BPS only ever sees
        // regions and qualifications that exist.
        $kabkotaOptions = KonstruksiSurveyResponse::query()
            ->whereNotNull('kabupaten_kota')
            ->where('kabupaten_kota', '!=', '')
            ->distinct()
            ->orderBy('kabupaten_kota')
            ->pluck('kabupaten_kota');

        return view('bps.konstruksi.index', compact(
            'surveyResponses', 'stats', 'user', 'kabkotaOptions'
        ) + ['periodOptions' => $this->periodOptions()]);
    }

    /**
     * Export the Konstruksi submissions as a monitoring spreadsheet.
     *
     * The dialog filters independently of the page's own filters so BPS can
     * pull a different slice than the one on screen; every filter accepts ""
     * for "semua".
     */
    public function export(Request $request)
    {
        $writer = $this->exportFormat($request->input('format'));

        $query = KonstruksiSurveyResponse::with('user');

        $this->applyStatus($query, $request->input('status'));
        $this->applyPeriod($query, $request->input('period'));
        $this->applySearch($query, $request->input('search'));

        // Per-blok completeness. These read the stored flags rather than the
        // model predicates because the filter has to run in SQL; the flags are
        // rewritten on every autosave and on finish, so they track the answers.
        //
        // blok1–blok3 exist in both instruments; blok6/blok7 are SKTH-only and
        // blok_status is SKTR-only, so those narrow to one instrument by
        // construction — a SKTR row never has blok6_completed set.
        $blokFilters = [
            'blok1'       => 'blok1_completed',
            'blok2'       => 'blok2_completed',
            'blok3'       => 'blok3_completed',
            'blok6'       => 'blok6_completed',
            'blok7'       => 'blok7_completed',
            'blok_status' => 'status_completed',
        ];

        foreach ($blokFilters as $input => $column) {
            $choice = $request->input($input);

            if ($choice === 'complete') {
                $query->where($column, true);
            } elseif ($choice === 'incomplete') {
                $query->where(fn ($q) => $q->where($column, false)->orWhereNull($column));
            }
        }

        // Quarterly preamble answers — useful for chasing non-response.
        foreach (['status_perusahaan', 'keberadaan_proyek'] as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        if (($kabkota = $request->input('kabupaten_kota')) !== null && $kabkota !== '') {
            $query->where('kabupaten_kota', $kabkota);
        }

        if (($kualifikasi = $request->input('kualifikasi')) !== null && $kualifikasi !== '') {
            $query->where('kualifikasi', $kualifikasi);
        }

        $this->applyUpdatedAtRange($query, $request->input('date_from'), $request->input('date_to'));

        $records = $query->orderBy('kabupaten_kota')->orderBy('nama_perusahaan')->get();

        $filename = 'Data_Survei_Konstruksi_' . $this->exportStamp() . $this->exportExtension($writer);

        return Excel::download(new KonstruksiBlok1Export($records), $filename, $writer);
    }

    /**
     * Everything the detail page and the full-data PDF both need.
     *
     * The two instruments answer different blocks, so the annual figures and
     * the quarterly ones are both prepared here and the view uses whichever
     * belongs to its period — cheaper than two near-identical assemblers.
     */
    private function detailData(KonstruksiSurveyResponse $response): array
    {
        return [
            'response'      => $response,
            'user'          => $response->user,
            // Annual (SKTH)
            'proyekRows'    => $response->proyekRows(),
            'proyekTotal'   => $response->totalProyek(),
            'bahanRows'     => $response->bahanRows(),
            'ringkasan'     => $response->ringkasan(),
            // Quarterly (SKTR)
            'proyekTwRows'  => $response->proyekTwRows(),
            'proyekTwTotal' => $response->totalProyekTw(),
            'quarterLabels' => $response->isTriwulanan() ? $response->quarterLabels() : [],
            'periode'       => KonstruksiBlokPath::periodLabel($response->tahun, $response->triwulan),
            'completedAt'   => $response->last_saved_at
                ? $response->last_saved_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB'
                : '—',
        ];
    }

    public function show($id)
    {
        $response = KonstruksiSurveyResponse::with('user')->findOrFail($id);

        $view = $response->isTriwulanan() ? 'bps.konstruksi.show-tw' : 'bps.konstruksi.show';

        return view($view, $this->detailData($response) + [
            'user'      => Auth::user(),
            'responden' => $response->user,
            'navBlocks' => KonstruksiBlokPath::rows($response, [
                'year'   => $response->tahun,
                'period' => $response->triwulan === 0 ? 'tahunan' : (string) $response->triwulan,
            ], ''),
        ]);
    }

    /** Full-data PDF for one response — the whole questionnaire, not a receipt. */
    public function download($id)
    {
        $response = KonstruksiSurveyResponse::with('user')->findOrFail($id);

        $view = $response->isTriwulanan() ? 'bps.konstruksi.pdf-tw' : 'bps.konstruksi.pdf';

        $pdf = Pdf::loadView($view, $this->detailData($response))
            // Both project grids are far wider than portrait allows (17 columns
            // annual, 16 quarterly).
            ->setPaper('A4', 'landscape')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        $name    = preg_replace('/[^a-zA-Z0-9_]/', '_', $response->nama_perusahaan ?: 'survei');
        $periode = $response->isTriwulanan()
            ? $response->tahun . '_TW' . $response->triwulan
            : (string) $response->tahun;

        return $pdf->download("Data_Survei_Konstruksi_{$periode}_{$name}.pdf");
    }

    /**
     * Soft-delete a single Konstruksi submission. Scoped to this table only —
     * the same user's SIBSTR, UB and Listrik submissions are untouched.
     */
    public function destroy($id)
    {
        $response = KonstruksiSurveyResponse::findOrFail($id);
        $label    = $response->nama_perusahaan ?: ($response->user->name ?? 'Responden');
        $periode  = KonstruksiBlokPath::periodLabel($response->tahun, $response->triwulan);

        $response->delete();

        return redirect()->route('bps.konstruksi.index')
            ->with('success', "Data Survei Konstruksi milik \"{$label}\" ({$periode}) berhasil dihapus.");
    }
}
