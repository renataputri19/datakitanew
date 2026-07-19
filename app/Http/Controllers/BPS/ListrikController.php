<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\ListrikSurveyResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BPS-side data pages for Survei Listrik — mirrors BPS\UbController
 * (list, detail, full-data PDF) and adds the soft delete used by all three
 * survey data pages.
 */
class ListrikController extends Controller
{
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

        return view('bps.listrik.index', compact('surveyResponses', 'stats', 'user'));
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
