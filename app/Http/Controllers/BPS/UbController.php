<?php

namespace App\Http\Controllers\BPS;

use App\Exports\UbBlok1Export;
use App\Http\Controllers\BPS\Concerns\ExportsSurveyMonitoring;
use App\Http\Controllers\Controller;
use App\Models\UbSurveyResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class UbController extends Controller
{
    use ExportsSurveyMonitoring;

    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    /**
     * Display all UB survey responses.
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $search  = $request->input('search');
        $status  = $request->input('status');
        $sortBy  = $request->input('sort_by', 'updated_at');
        $perPage = $request->input('per_page', 25);

        $query = UbSurveyResponse::with('user');

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
            'total'       => UbSurveyResponse::count(),
            'completed'   => UbSurveyResponse::where('is_completed', true)->count(),
            'in_progress' => UbSurveyResponse::where('is_completed', false)->count(),
        ];

        // Choices for the export dialog's dropdowns, taken from the data itself
        // so BPS only ever sees regions and years that exist.
        $kabkotaOptions = UbSurveyResponse::query()
            ->whereNotNull('kabupaten_kota')
            ->where('kabupaten_kota', '!=', '')
            ->distinct()
            ->orderBy('kabupaten_kota')
            ->pluck('kabupaten_kota');

        $tahunOptions = UbSurveyResponse::query()
            ->whereNotNull('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun');

        return view('bps.ub.index', compact(
            'surveyResponses', 'stats', 'user', 'kabkotaOptions', 'tahunOptions'
        ));
    }

    /**
     * Export the UB submissions as a monitoring spreadsheet.
     *
     * The dialog filters independently of the page's own filters so BPS can
     * pull a different slice than the one on screen; every filter accepts ""
     * for "semua".
     */
    public function export(Request $request)
    {
        $status   = $request->input('status');       // '' | completed | in_progress
        $blok1    = $request->input('blok1');        // '' | complete | incomplete
        $tahun    = $request->input('tahun');
        $kabkota  = $request->input('kabupaten_kota');
        $search   = $request->input('search');
        $writer   = $this->exportFormat($request->input('format'));

        $query = UbSurveyResponse::with('user');

        if ($status === 'completed') {
            $query->where('is_completed', true);
        } elseif ($status === 'in_progress') {
            $query->where('is_completed', false);
        }

        // Blok 1 spans four sub-blocks; "lengkap" means all four are done.
        $blok1Flags = ['blok1a_completed', 'blok1b_completed', 'blok1c_completed', 'blok1d_completed'];
        if ($blok1 === 'complete') {
            foreach ($blok1Flags as $flag) {
                $query->where($flag, true);
            }
        } elseif ($blok1 === 'incomplete') {
            $query->where(function ($q) use ($blok1Flags) {
                foreach ($blok1Flags as $flag) {
                    $q->orWhere($flag, false)->orWhereNull($flag);
                }
            });
        }

        if ($tahun !== null && $tahun !== '') {
            $query->where('tahun', (int) $tahun);
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

        $filename = 'Data_Survei_UB_' . $this->exportStamp() . $this->exportExtension($writer);

        return Excel::download(new UbBlok1Export($records), $filename, $writer);
    }

    /**
     * Display a specific UB survey response.
     */
    public function show($id)
    {
        $user     = Auth::user();
        $response = UbSurveyResponse::with('user')->findOrFail($id);

        return view('bps.ub.show', compact('response', 'user'));
    }

    /**
     * Download the full-data UB PDF (all inputted fields) for a given response.
     * Used by BPS and, via delegation, by Mitra users.
     */
    public function download($id)
    {
        $response = UbSurveyResponse::with('user')->findOrFail($id);

        $completedAt = $response->last_saved_at
            ? $response->last_saved_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB'
            : '—';

        $pdf = Pdf::loadView('bps.ub.pdf', [
                'response'    => $response,
                'user'        => $response->user,
                'completedAt' => $completedAt,
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        $filename = 'SE2026-L.UB_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $response->nama_perusahaan ?? 'survei') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Soft-delete a single UB submission. Scoped to this table only — the same
     * user's SIBSTR and Listrik submissions are untouched.
     */
    public function destroy($id)
    {
        $response = UbSurveyResponse::findOrFail($id);
        $label    = $response->nama_perusahaan ?: ($response->user->name ?? 'Responden');

        $response->delete();

        return redirect()->route('bps.ub.index')
            ->with('success', "Data Survei UB milik \"{$label}\" berhasil dihapus.");
    }
}
