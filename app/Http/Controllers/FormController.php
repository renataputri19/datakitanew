<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FormController extends Controller
{
    /**
     * Display the institution form.
     *
     * @return \Illuminate\View\View
     */
    public function showInstitutionForm()
    {
        return view('form.institution');
    }

    /**
     * Handle the institution form submission.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitInstitutionForm(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'jenis' => 'required|string|in:pemerintah,swasta,pendidikan,organisasi,lainnya',
            'nama_institusi' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'nomor' => 'nullable|string|max:20',
        ], [
            'jenis.required' => 'Jenis institusi harus dipilih',
            'jenis.in' => 'Jenis institusi tidak valid',
            'nama_institusi.required' => 'Nama institusi harus diisi',
            'nama_institusi.max' => 'Nama institusi maksimal 255 karakter',
            'alamat.max' => 'Alamat maksimal 500 karakter',
            'nomor.max' => 'Nomor maksimal 20 karakter',
        ]);

        // Here you can process the form data
        // For example: save to database, send email, etc.
        
        // Log the submission for demonstration
        \Log::info('Institution form submitted', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Form berhasil dikirim!',
            'data' => $validated
        ]);
    }
}