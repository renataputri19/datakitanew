<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\UbSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UbController extends Controller
{
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

        return view('bps.ub.index', compact('surveyResponses', 'stats', 'user'));
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
}
