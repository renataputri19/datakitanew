<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SibstrController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    /**
     * Display all SIBSTR survey responses
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $search = $request->input('search');
        $status = $request->input('status');
        $sortBy = $request->input('sort_by', 'updated_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 25);

        // Base query for SIBSTR responses
        $baseQuery = SurveyResponse::with('user')
            ->where('survey_type', 'sibstr');

        // Apply search filter
        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('kip', 'like', "%{$search}%")
                  ->orWhere('idsbr', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($status !== null && $status !== '') {
            if ($status === 'completed') {
                $baseQuery->where('is_completed', true);
            } elseif ($status === 'in_progress') {
                $baseQuery->where('is_completed', false);
            }
        }

        // Compute latest record id per user within filtered set to deduplicate
        $latestIds = (clone $baseQuery)
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        // Final query restricted to latest IDs per user
        $query = SurveyResponse::with('user')
            ->whereIn('id', $latestIds);

        // Apply sorting
        $allowedSortColumns = ['updated_at', 'created_at', 'nama_perusahaan', 'is_completed'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // Paginate results
        $surveyResponses = $query->paginate($perPage)->withQueryString();

        // Get statistics based on latest record per user
        $allLatestIds = SurveyResponse::where('survey_type', 'sibstr')
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $stats = [
            'total' => $allLatestIds->count(),
            'completed' => SurveyResponse::whereIn('id', $allLatestIds)->where('is_completed', true)->count(),
            'in_progress' => SurveyResponse::whereIn('id', $allLatestIds)->where('is_completed', false)->count(),
        ];

        return view('bps.sibstr.index', compact('surveyResponses', 'stats', 'user'));
    }

    /**
     * Display a specific SIBSTR survey response
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Auth::user();

        // Resolve the requested record, then present unified latest data for that user
        $requested = SurveyResponse::with('user')
            ->where('survey_type', 'sibstr')
            ->findOrFail($id);

        $surveyResponse = SurveyResponse::unifiedForUser($requested->user_id, 'sibstr') ?? $requested;

        // Prepare all data that might be needed by the survey block views
        $bpsRiData = [
            'penghubung' => 'Tim Statistik Industri',
            'telepon' => '021-3810291 ext. 5310–5313',
            'fax' => '021-3863816, 021-3857046',
            'email' => 'ibs@bps.go.id',
            'alamat' => 'Jl. Dr. Sutomo No. 8, Jakarta 10710',
        ];

        $jenisKawasanOptions = SurveyResponse::getJenisKawasanOptions();
        
        // Pass kondisi_perusahaan and jaringan_unit_kegiatan for conditional display
        $kondisiPerusahaan = $surveyResponse->kondisi_perusahaan;
        $jaringanUnitKegiatan = $surveyResponse->jaringan_unit_kegiatan;
        
        // Pass KBLI prefix for block 4 navigation
        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        return view('bps.sibstr.show', compact(
            'surveyResponse', 
            'user', 
            'bpsRiData', 
            'jenisKawasanOptions',
            'kondisiPerusahaan',
            'jaringanUnitKegiatan',
            'kbliPrefix'
        ));
    }
}
