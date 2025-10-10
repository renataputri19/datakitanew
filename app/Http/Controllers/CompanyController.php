<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CompanyTemplateExport;
use App\Imports\CompanyImport;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'is_superadmin']);
    }

    /**
     * Display a listing of companies.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Company::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $companies = $query->orderBy('nama_perusahaan')
                          ->paginate(10)
                          ->withQueryString();

        return view('superadmin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('superadmin.companies.create');
    }

    /**
     * Store a newly created company in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required|string|max:255|unique:companies,nama_perusahaan',
            'alamat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Company::create($request->only(['nama_perusahaan', 'alamat']));

        return redirect()->route('superadmin.companies.index')
                        ->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified company.
     *
     * @param \App\Models\Company $company
     * @return \Illuminate\View\View
     */
    public function edit(Company $company)
    {
        return view('superadmin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Company $company
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required|string|max:255|unique:companies,nama_perusahaan,' . $company->id,
            'alamat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company->update($request->only(['nama_perusahaan', 'alamat']));

        return redirect()->route('superadmin.companies.index')
                        ->with('success', 'Perusahaan berhasil diperbarui.');
    }

    /**
     * Remove the specified company from storage.
     *
     * @param \App\Models\Company $company
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('superadmin.companies.index')
                        ->with('success', 'Perusahaan berhasil dihapus.');
    }

    /**
     * Download Excel template for company import.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate()
    {
        return Excel::download(new CompanyTemplateExport, 'template_perusahaan.xlsx');
    }

    /**
     * Import companies from Excel file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            Excel::import(new CompanyImport, $request->file('file'));
            
            return redirect()->route('superadmin.companies.index')
                            ->with('success', 'Data perusahaan berhasil diimpor.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Search companies for AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $companies = Company::getForDropdown($search, 10);

        return response()->json([
            'companies' => $companies
        ]);
    }
}
