<?php

namespace App\Http\Controllers\BPS;

use App\Exports\ListrikBlok1Export;
use App\Http\Controllers\BPS\Concerns\ExportsSurveyMonitoring;
use App\Http\Controllers\Controller;
use App\Models\ListrikSurveyResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * BPS-side data pages for Survei Listrik — mirrors BPS\UbController
 * (list, detail, full-data PDF) and adds the soft delete used by all three
 * survey data pages.
 */
class ListrikController extends Controller
{
    use ExportsSurveyMonitoring;

    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    /**
     * Display all Listrik survey responses.
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $search  = $request->input('search');
        $status  = $request->input('status');
        $sortBy  = $request->input('sort_by', 'updated_at');
        $perPage = $request->input('per_page', 25);

        $query = ListrikSurveyResponse::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('nama_komersial', 'like', "%{$search}%")
                  ->orWhere('kabupaten_kota', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== null && $status !== '') {
            if ($status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($status === 'in_progress') {
                $query->where('is_completed', false);
            }
        }

        $allowed = ['updated_at', 'created_at', 'nama_perusahaan'];
        $query->orderBy(in_array($sortBy, $allowed) ? $sortBy : 'updated_at', 'desc');

        $surveyResponses = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total'       => ListrikSurveyResponse::count(),
            'completed'   => ListrikSurveyResponse::where('is_completed', true)->count(),
            'in_progress' => ListrikSurveyResponse::where('is_completed', false)->count(),
        ];

        // Choices for the export dialog's dropdowns, taken from the data itself
        // so BPS only ever sees regions and plant types that exist.
        $kabkotaOptions = ListrikSurveyResponse::query()
            ->whereNotNull('kabupaten_kota')
            ->where('kabupaten_kota', '!=', '')
            ->distinct()
            ->orderBy('kabupaten_kota')
            ->pluck('kabupaten_kota');

        $pembangkitOptions = ListrikSurveyResponse::query()
            ->whereNotNull('jenis_pembangkit')
            ->where('jenis_pembangkit', '!=', '')
            ->distinct()
            ->orderBy('jenis_pembangkit')
            ->pluck('jenis_pembangkit');

        return view('bps.listrik.index', compact(
            'surveyResponses', 'stats', 'user', 'kabkotaOptions', 'pembangkitOptions'
        ));
    }

    /**
     * Export the Listrik submissions as a monitoring spreadsheet.
     *
     * The dialog filters independently of the page's own filters so BPS can
     * pull a different slice than the one on screen; every filter accepts ""
     * for "semua".
     */
    public function export(Request $request)
    {
        $status     = $request->input('status');       // '' | completed | in_progress
        $blok1      = $request->input('blok1');        // '' | complete | incomplete
        $grid       = $request->input('grid');         // '' | complete | incomplete
        $pembangkit = $request->input('jenis_pembangkit');
        $kabkota    = $request->input('kabupaten_kota');
        $search     = $request->input('search');
        $writer     = $this->exportFormat($request->input('format'));

        $query = ListrikSurveyResponse::with('user');

        if ($status === 'completed') {
            $query->where('is_completed', true);
        } elseif ($status === 'in_progress') {
            $query->where('is_completed', false);
        }

        foreach ([['blok1_completed', $blok1], ['blok2_completed', $grid]] as [$column, $choice]) {
            if ($choice === 'complete') {
                $query->where($column, true);
            } elseif ($choice === 'incomplete') {
                $query->where(fn ($q) => $q->where($column, false)->orWhereNull($column));
            }
        }

        if ($pembangkit !== null && $pembangkit !== '') {
            $query->where('jenis_pembangkit', $pembangkit);
        }

        if ($kabkota !== null && $kabkota !== '') {
            $query->where('kabupaten_kota', $kabkota);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('nama_komersial', 'like', "%{$search}%")
                  ->orWhere('kabupaten_kota', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $this->applyUpdatedAtRange($query, $request->input('date_from'), $request->input('date_to'));

        $records = $query->orderBy('kabupaten_kota')->orderBy('nama_perusahaan')->get();

        $filename = 'Data_Survei_Listrik_' . $this->exportStamp() . $this->exportExtension($writer);

        return Excel::download(new ListrikBlok1Export($records), $filename, $writer);
    }

    /**
     * Display a specific Listrik survey response, Blok II grouped per quarter.
     */
    public function show($id)
    {
        $user     = Auth::user();
        $response = ListrikSurveyResponse::with('user')->findOrFail($id);
        $quarters = $response->quarterlyBreakdown();

        return view('bps.listrik.show', compact('response', 'user', 'quarters'));
    }

    /**
     * Download the full-data Listrik PDF for a given response.
     */
    public function download($id)
    {
        $response = ListrikSurveyResponse::with('user')->findOrFail($id);

        $completedAt = $response->last_saved_at
            ? $response->last_saved_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB'
            : '—';

        $pdf = Pdf::loadView('bps.listrik.pdf', [
                'response'    => $response,
                'user'        => $response->user,
                'completedAt' => $completedAt,
                'quarters'    => $response->quarterlyBreakdown(),
            ])
            ->setPaper('A4', 'landscape')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        $filename = 'Data_Survei_Listrik_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $response->nama_perusahaan ?? 'survei') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Soft-delete a single Listrik submission. Scoped to this table only —
     * the same user's UB and SIBSTR submissions are untouched.
     */
    public function destroy($id)
    {
        $response = ListrikSurveyResponse::findOrFail($id);
        $label    = $response->nama_perusahaan ?: ($response->user->name ?? 'Responden');

        $response->delete();

        return redirect()->route('bps.listrik.index')
            ->with('success', "Data Survei Listrik milik \"{$label}\" berhasil dihapus.");
    }
}
