<?php

namespace App\Http\Controllers;

use App\Models\TemporarySurveiSibstr;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TemporarySurveyController extends Controller
{
    /**
     * Display the temporary SIBSTR survey form.
     *
     * @return \Illuminate\View\View
     */
    public function showSurvey()
    {
        $jenisPerusahaanOptions = TemporarySurveiSibstr::getJenisPerusahaanOptions();

        return view('survey.temporary-sibstr', compact('jenisPerusahaanOptions'));
    }

    /**
     * Handle the survey form submission.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitSurvey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'perusahaan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_perusahaan' => 'required|in:industri,non-industri',
            'files' => 'required|array|min:1', // Files are required and must be an array with at least 1 file
            'files.*' => 'required|file|max:10240', // 10MB max per file
        ], [
            'files.required' => 'File kuesioner wajib diupload.',
            'files.min' => 'Minimal satu file kuesioner harus diupload.',
            'files.*.required' => 'File kuesioner tidak boleh kosong.',
            'files.*.file' => 'File yang diupload harus berupa file yang valid.',
            'files.*.max' => 'Ukuran file maksimal 10MB.',
            'perusahaan.required' => 'Nama perusahaan wajib diisi.',
        ]);

        // Custom file extension validation to handle .xls files properly
        if ($request->hasFile('files')) {
            $allowedExtensions = ['xlsx', 'xls', 'pdf', 'doc', 'docx'];
            foreach ($request->file('files') as $index => $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    $validator->errors()->add("files.{$index}", 'Format file harus berupa Excel (.xlsx, .xls), PDF, atau Word (.doc, .docx).');
                }
            }
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle company creation if not selected from dropdown
        $companyId = $request->company_id;
        if (!$companyId && $request->perusahaan && $request->alamat) {
            // Check if company already exists
            $existingCompany = Company::where('nama_perusahaan', $request->perusahaan)->first();

            if ($existingCompany) {
                $companyId = $existingCompany->id;
            } else {
                // Create new company
                $newCompany = Company::create([
                    'nama_perusahaan' => $request->perusahaan,
                    'alamat' => $request->alamat,
                ]);
                $companyId = $newCompany->id;
            }
        }

        $filePaths = [];

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $perusahaanSlug = Str::slug($request->perusahaan);
                $jenisSlug = $request->jenis_perusahaan;

                // Create filename: [perusahaan_name]_[industri/nonindustri]_[original_filename]
                $filename = $perusahaanSlug . '_' . $jenisSlug . '_' . $originalName;

                // Store in private storage
                $path = $file->storeAs('temporary-survey-files', $filename, 'local');
                $filePaths[] = $path;
            }
        }

        // Create survey submission
        TemporarySurveiSibstr::create([
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'company_id' => $companyId,
            'perusahaan' => $request->perusahaan,
            'alamat' => $request->alamat,
            'jenis_perusahaan' => $request->jenis_perusahaan,
            'file_paths' => $filePaths,
        ]);

        return redirect()->back()->with('success', 'Survei berhasil dikirim. Terima kasih atas partisipasi Anda!');
    }

    /**
     * Search companies for AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCompanies(Request $request)
    {
        $search = $request->get('search', '');
        $companies = Company::getForDropdown($search, 10);

        return response()->json([
            'companies' => $companies
        ]);
    }

    /**
     * Display the superadmin overview dashboard (stats + management hub).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function superadminDashboard(Request $request)
    {
        // SIBSTR submission statistics
        $stats = [
            'total' => TemporarySurveiSibstr::count(),
            'industri' => TemporarySurveiSibstr::where('jenis_perusahaan', 'industri')->count(),
            'non_industri' => TemporarySurveiSibstr::where('jenis_perusahaan', 'non-industri')->count(),
            'today' => TemporarySurveiSibstr::whereDate('created_at', today())->count(),
        ];

        // Platform-wide stats for the overview section
        $userStats = User::roleCounts();
        $userStats['total'] = array_sum($userStats);
        $roleDefinitions = User::roleDefinitions();
        $companyCount = Company::count();

        return view('survey.superadmin-dashboard', compact(
            'stats', 'userStats', 'roleDefinitions', 'companyCount'
        ));
    }

    /**
     * Display the SIBSTR submissions management page (filter + table).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function submissionsIndex(Request $request)
    {
        $query = TemporarySurveiSibstr::query();

        // Initialize filter variables
        $filters = [
            'search_company' => $request->get('search_company'),
            'search_name' => $request->get('search_name'),
            'company_type' => $request->get('company_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        // Apply filters
        if (!empty($filters['search_company'])) {
            $query->where('perusahaan', 'like', '%' . $filters['search_company'] . '%');
        }

        if (!empty($filters['search_name'])) {
            $query->where('nama', 'like', '%' . $filters['search_name'] . '%');
        }

        if (!empty($filters['company_type'])) {
            $query->where('jenis_perusahaan', $filters['company_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Get total count before pagination
        $totalCount = TemporarySurveiSibstr::count();
        $filteredCount = $query->count();

        // Apply ordering and pagination
        $submissions = $query->orderBy('created_at', 'desc')->paginate(10);

        // Preserve query parameters in pagination links
        $submissions->appends($request->query());

        return view('superadmin.submissions.index', compact(
            'submissions', 'filters', 'totalCount', 'filteredCount'
        ));
    }

    /**
     * Download a file from the survey submission.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadFile($filename)
    {
        $path = 'temporary-survey-files/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download(storage_path('app/' . $path));
    }

    /**
     * Show the form for creating a new submission.
     *
     * @return \Illuminate\View\View
     */
    public function createSubmission()
    {
        $companies = Company::orderBy('nama_perusahaan')->get();
        return view('superadmin.submissions.create', compact('companies'));
    }

    /**
     * Store a newly created submission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSubmission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'perusahaan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_perusahaan' => 'required|in:industri,non-industri',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        TemporarySurveiSibstr::create($request->only([
            'nama', 'jabatan', 'no_hp', 'email', 'company_id',
            'perusahaan', 'alamat', 'jenis_perusahaan'
        ]));

        return redirect()->route('superadmin.submissions.index')
                        ->with('success', 'Data submission berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified submission.
     *
     * @param \App\Models\TemporarySurveiSibstr $submission
     * @return \Illuminate\View\View
     */
    public function editSubmission(TemporarySurveiSibstr $submission)
    {
        $companies = Company::orderBy('nama_perusahaan')->get();
        return view('superadmin.submissions.edit', compact('submission', 'companies'));
    }

    /**
     * Update the specified submission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TemporarySurveiSibstr $submission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSubmission(Request $request, TemporarySurveiSibstr $submission)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'perusahaan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_perusahaan' => 'required|in:industri,non-industri',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $submission->update($request->only([
            'nama', 'jabatan', 'no_hp', 'email', 'company_id',
            'perusahaan', 'alamat', 'jenis_perusahaan'
        ]));

        return redirect()->route('superadmin.submissions.index')
                        ->with('success', 'Data submission berhasil diperbarui.');
    }

    /**
     * Remove the specified submission from storage.
     *
     * @param \App\Models\TemporarySurveiSibstr $submission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroySubmission(TemporarySurveiSibstr $submission)
    {
        // Delete associated files if they exist
        if ($submission->file_paths && is_array($submission->file_paths)) {
            foreach ($submission->file_paths as $filePath) {
                $fullPath = 'temporary-survey-files/' . basename($filePath);
                if (Storage::disk('local')->exists($fullPath)) {
                    Storage::disk('local')->delete($fullPath);
                }
            }
        }

        $submission->delete();

        return redirect()->route('superadmin.submissions.index')
                        ->with('success', 'Data submission berhasil dihapus.');
    }
}
