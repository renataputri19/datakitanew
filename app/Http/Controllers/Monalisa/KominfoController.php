<?php

namespace App\Http\Controllers\Monalisa;

use App\Http\Controllers\Controller;
use App\Models\MonalisaDomain;
use App\Models\MonalisaIndikator;
use App\Models\MonalisaAssessment;
use App\Models\MonalisaDocument;
use App\Models\MonalisaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KominfoController extends Controller
{
    /**
     * Display the Kominfo dashboard.
     * Now uses unified view showing both Kominfo and BPS scores for transparency.
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Get all domains with their structure
        $domains = MonalisaDomain::with(['aspeks.indikators'])->orderBy('order')->get();

        // Get ALL assessments (organization-wide - all Kominfo users see the same data)
        $assessments = MonalisaAssessment::with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy', 'documents'])
            ->get();

        // Get ALL organization-wide assessments for score calculation (both Kominfo and BPS scores)
        // This ensures IPS scores are organization-wide, not per-user
        $allAssessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified'])
            ->with(['indikator.aspek.domain'])
            ->get();

        // Calculate both Kominfo and BPS scores using organization-wide data
        $kominfoScores = $this->calculateScores($allAssessments, $domains, 'kominfo');
        $bpsScores = $this->calculateScores($allAssessments, $domains, 'bps');

        // Use the unified BPS dashboard view which shows both scores
        return view('monalisa.bps.dashboard', compact('domains', 'assessments', 'kominfoScores', 'bpsScores'));
    }

    /**
     * Show assessment form for a specific indikator.
     */
    public function showAssessment($indikatorId)
    {
        $indikator = MonalisaIndikator::with(['aspek.domain'])->findOrFail($indikatorId);
        $user = auth()->user();

        // Get or create assessment (organization-wide)
        $assessment = MonalisaAssessment::getOrCreateForIndicator($indikatorId, $user->id);

        // Load documents and BPS comment history
        $assessment->load('documents.comments', 'bpsCommentHistory.bpsUser');

        return view('monalisa.kominfo.assessment', compact('indikator', 'assessment'));
    }

    /**
     * Save or update assessment.
     */
    public function saveAssessment(Request $request, $indikatorId)
    {
        $request->validate([
            'maturity_level' => 'required|integer|min:1|max:5',
            'justification' => 'required|string|min:10',
        ]);

        $user = auth()->user();
        $assessment = MonalisaAssessment::getOrCreateForIndicator($indikatorId, $user->id);

        $assessment->update([
            'kominfo_maturity_level' => $request->maturity_level,
            'kominfo_justification' => $request->justification,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assessment saved successfully',
        ]);
    }

    /**
     * Submit assessment for BPS verification.
     */
    public function submitAssessment(Request $request, $assessmentId)
    {
        $request->validate([
            'maturity_level' => 'required|integer|min:1|max:5',
            'justification' => 'required|string|min:50',
            'action' => 'required|in:draft,submit,update',
        ]);

        $assessment = MonalisaAssessment::with('indikator.aspek')
            ->findOrFail($assessmentId);

        // Check if this is an update to an existing submission
        $isUpdate = $assessment->status === 'submitted' && $request->action === 'update';
        $wasVerified = $assessment->status === 'verified';
        $wasRejected = $assessment->status === 'rejected';

        // Update assessment data
        $assessment->update([
            'kominfo_maturity_level' => $request->maturity_level,
            'kominfo_justification' => $request->justification,
            'status' => $request->action === 'submit' || $request->action === 'update' ? 'submitted' : 'draft',
            'kominfo_submitted_at' => $request->action === 'submit' || $request->action === 'update' ? now() : null,
            'kominfo_submitted_by' => $request->action === 'submit' || $request->action === 'update' ? auth()->id() : null,
        ]);

        // Track audit trail
        $assessment->trackKominfoUpdate(auth()->id());

        // Create notifications
        if ($request->action === 'submit' && !$wasRejected) {
            // New submission - notify BPS users
            MonalisaNotification::createForSubmission($assessment, auth()->id());
            $message = 'Assessment berhasil disubmit untuk verifikasi BPS.';
        } elseif ($request->action === 'submit' && $wasRejected) {
            // Resubmission after rejection - notify BPS users
            MonalisaNotification::createForResubmission($assessment, auth()->id());
            $message = 'Assessment berhasil diresubmit setelah revisi. BPS akan melakukan verifikasi ulang.';
        } elseif ($isUpdate || $wasVerified) {
            // Update to existing submission - notify BPS users
            MonalisaNotification::createForUpdate($assessment, auth()->id());
            $message = 'Assessment berhasil diperbarui. BPS akan menerima notifikasi tentang perubahan ini.';
        } else {
            $message = 'Assessment berhasil disimpan sebagai draft.';
        }

        // Get domain_id from the assessment's indikator
        $domainId = $assessment->indikator->aspek->domain_id;

        return redirect()->route('monalisa.kominfo.domain', $domainId)->with('success', $message);
    }

    /**
     * Upload document for assessment.
     */
    public function uploadDocument(Request $request, $assessmentId)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $assessment = MonalisaAssessment::findOrFail($assessmentId);

            // Check if assessment can be edited (not verified)
            if (!$assessment->canBeEditedByKominfo()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot upload document for verified assessment.',
                ], 403);
            }

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Create filename: [indikator_code]_[timestamp]_[original_name]
            $indikatorCode = $assessment->indikator
                ? str_replace('.', '_', $assessment->indikator->indikator_code)
                : 'unknown';
            $timestamp = now()->format('YmdHis');
            $filename = $indikatorCode . '_' . $timestamp . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;

            // Store in private storage
            $path = $file->storeAs('monalisa-documents', $filename, 'local');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan file ke server. Periksa konfigurasi storage.',
                ], 500);
            }

            // Create document record
            $document = MonalisaDocument::create([
                'assessment_id' => $assessment->id,
                'uploaded_by' => auth()->id(),
                'original_filename' => $originalName,
                'stored_filename' => $filename,
                'file_path' => $path,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => $document,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('uploadDocument error: ' . $e->getMessage(), [
                'assessment_id' => $assessmentId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupload dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Replace/update uploaded document.
     */
    public function replaceDocument(Request $request, $documentId)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $document = MonalisaDocument::findOrFail($documentId);

            // Check if assessment can be edited (not verified)
            if (!$document->assessment->canBeEditedByKominfo()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot replace document for verified assessment.',
                ], 403);
            }

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Create filename: [indikator_code]_[timestamp]_[original_name]
            $indikatorCode = $document->assessment->indikator
                ? str_replace('.', '_', $document->assessment->indikator->indikator_code)
                : 'unknown';
            $timestamp = now()->format('YmdHis');
            $filename = $indikatorCode . '_' . $timestamp . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;

            // Delete old file from storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            // Store new file in private storage
            $path = $file->storeAs('monalisa-documents', $filename, 'local');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan file ke server. Periksa konfigurasi storage.',
                ], 500);
            }

            // Update document record
            $document->update([
                'original_filename' => $originalName,
                'stored_filename' => $filename,
                'file_path' => $path,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'description' => $request->description ?? $document->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document replaced successfully',
                'document' => $document->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('replaceDocument error: ' . $e->getMessage(), [
                'document_id' => $documentId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengganti dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete uploaded document.
     */
    public function deleteDocument($documentId)
    {
        try {
            $document = MonalisaDocument::findOrFail($documentId);

            // Check if assessment can be edited (not verified)
            if (!$document->assessment->canBeEditedByKominfo()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete document for verified assessment.',
                ], 403);
            }

            // Delete file from storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('deleteDocument error: ' . $e->getMessage(), [
                'document_id' => $documentId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download document.
     */
    public function downloadDocument($documentId)
    {
        $document = MonalisaDocument::findOrFail($documentId);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return response()->download(
            storage_path('app/' . $document->file_path),
            $document->original_filename
        );
    }

    /**
     * View document inline in browser.
     */
    public function viewDocument($documentId)
    {
        $document = MonalisaDocument::findOrFail($documentId);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return response()->file(
            storage_path('app/' . $document->file_path),
            ['Content-Disposition' => 'inline; filename="' . $document->original_filename . '"']
        );
    }

    /**
     * Calculate scores from assessments.
     * Updated to support both Kominfo and BPS score types.
     */
    private function calculateScores($assessments, $domains, $type = 'kominfo')
    {
        $scores = [
            'total' => 0,
            'domains' => [],
        ];

        $levelField = $type === 'kominfo' ? 'kominfo_maturity_level' : 'bps_maturity_level';

        foreach ($domains as $domain) {
            $domainScore = 0;
            $aspekScores = [];

            foreach ($domain->aspeks as $aspek) {
                $aspekScore = 0;
                $indikatorCount = 0;

                foreach ($aspek->indikators as $indikator) {
                    $assessment = $assessments->firstWhere('indikator_id', $indikator->id);
                    if ($assessment && $assessment->$levelField) {
                        $aspekScore += $assessment->$levelField;
                        $indikatorCount++;
                    }
                }

                if ($indikatorCount > 0) {
                    $avgAspekScore = $aspekScore / $indikatorCount;
                    $aspekScores[] = $avgAspekScore * ($aspek->weight / 100);
                }
            }

            if (count($aspekScores) > 0) {
                $domainScore = array_sum($aspekScores);
                $scores['domains'][$domain->id] = [
                    'score' => $domainScore,
                    'weighted_score' => $domainScore * ($domain->weight / 100),
                ];
                $scores['total'] += $domainScore * ($domain->weight / 100);
            }
        }

        return $scores;
    }

    /**
     * Show domain-specific page.
     */
    public function showDomain($domainId)
    {
        $domain = MonalisaDomain::with(['aspeks.indikators'])->findOrFail($domainId);
        $user = auth()->user();

        // Get assessments for this domain (organization-wide)
        $indikatorIds = $domain->indikators->pluck('id');
        $assessments = MonalisaAssessment::whereIn('indikator_id', $indikatorIds)
            ->with(['indikator', 'documents'])
            ->get()
            ->keyBy('indikator_id');

        return view('monalisa.kominfo.domain', compact('domain', 'assessments'));
    }

    /**
     * Show charts/visualization page.
     * Now uses unified view showing both Kominfo and BPS scores for transparency.
     */
    public function showCharts()
    {
        $user = auth()->user();

        // Get all domains with their structure
        $domains = MonalisaDomain::with(['aspeks.indikators'])->orderBy('order')->get();

        // Get ALL assessments (organization-wide - all Kominfo users see the same data)
        $assessments = MonalisaAssessment::with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
            ->get();

        // Get ALL organization-wide assessments for chart data calculation
        // This ensures IPS scores in charts are organization-wide, not per-user
        $allAssessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified'])
            ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
            ->get();

        // Prepare chart data using organization-wide assessments (both Kominfo and BPS scores)
        $chartData = $this->prepareChartData($domains, $allAssessments);

        // Use the unified BPS charts view which shows comparison
        return view('monalisa.bps.charts', compact('domains', 'assessments', 'chartData'));
    }

    /**
     * Prepare data for charts.
     * Updated to support both Kominfo and BPS scores for transparency.
     */
    private function prepareChartData($domains, $assessments)
    {
        $data = [
            'domains' => [],
        ];

        foreach ($domains as $domain) {
            $domainData = [
                'id' => $domain->id,
                'name' => $domain->name,
                'domain_number' => $domain->domain_number,
                'weight' => $domain->weight,
                'kominfo_score' => 0,
                'bps_score' => 0,
                'total_indikators' => 0,
                'assessed_indikators' => 0,
                'verified_indikators' => 0,
                'aspeks' => [],
            ];

            foreach ($domain->aspeks as $aspek) {
                $aspekData = [
                    'id' => $aspek->id,
                    'name' => $aspek->name,
                    'aspek_number' => $aspek->aspek_number,
                    'weight' => $aspek->weight,
                    'kominfo_score' => 0,
                    'bps_score' => 0,
                    'total_indikators' => 0,
                    'assessed_indikators' => 0,
                    'verified_indikators' => 0,
                    'indikators' => [],
                ];

                foreach ($aspek->indikators as $indikator) {
                    $assessment = $assessments->firstWhere('indikator_id', $indikator->id);
                    $hasKominfoScore = $assessment && $assessment->kominfo_maturity_level;
                    $hasBpsScore = $assessment && $assessment->bps_maturity_level;

                    $indikatorData = [
                        'id' => $indikator->id,
                        'name' => $indikator->name,
                        'code' => $indikator->indikator_code,
                        'kominfo_score' => $hasKominfoScore ? $assessment->kominfo_maturity_level : null,
                        'bps_score' => $hasBpsScore ? $assessment->bps_maturity_level : null,
                        'status' => $assessment ? $assessment->status : 'not_started',
                    ];

                    $aspekData['indikators'][] = $indikatorData;
                    $aspekData['total_indikators']++;
                    $domainData['total_indikators']++;

                    if ($hasKominfoScore) {
                        $aspekData['kominfo_score'] += $assessment->kominfo_maturity_level;
                        $aspekData['assessed_indikators']++;
                        $domainData['assessed_indikators']++;
                    }

                    if ($hasBpsScore) {
                        $aspekData['bps_score'] += $assessment->bps_maturity_level;
                        $aspekData['verified_indikators']++;
                        $domainData['verified_indikators']++;
                    }
                }

                // Calculate average scores for aspek
                if ($aspekData['assessed_indikators'] > 0) {
                    $aspekData['kominfo_score'] = $aspekData['kominfo_score'] / $aspekData['assessed_indikators'];
                }
                if ($aspekData['verified_indikators'] > 0) {
                    $aspekData['bps_score'] = $aspekData['bps_score'] / $aspekData['verified_indikators'];
                }

                $domainData['aspeks'][] = $aspekData;
            }

            // Calculate average scores for domain
            $totalKominfoScore = 0;
            $totalBpsScore = 0;
            $assessedAspeks = 0;
            $verifiedAspeks = 0;

            foreach ($domainData['aspeks'] as $aspek) {
                if ($aspek['assessed_indikators'] > 0) {
                    $totalKominfoScore += $aspek['kominfo_score'];
                    $assessedAspeks++;
                }
                if ($aspek['verified_indikators'] > 0) {
                    $totalBpsScore += $aspek['bps_score'];
                    $verifiedAspeks++;
                }
            }

            if ($assessedAspeks > 0) {
                $domainData['kominfo_score'] = $totalKominfoScore / $assessedAspeks;
            }
            if ($verifiedAspeks > 0) {
                $domainData['bps_score'] = $totalBpsScore / $verifiedAspeks;
            }

            $data['domains'][] = $domainData;
        }

        return $data;
    }

    /**
     * Show indicator-level analysis page.
     * Provides detailed drill-down visualization at the indicator level.
     */
    public function showIndicatorAnalysis()
    {
        $user = auth()->user();

        // Get all domains with their structure
        $domains = MonalisaDomain::with(['aspeks.indikators'])->orderBy('order')->get();

        // Get ALL assessments (organization-wide - all Kominfo users see the same data)
        $assessments = MonalisaAssessment::with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
            ->get();

        // Get ALL organization-wide assessments for chart data calculation
        // This ensures IPS scores in charts are organization-wide, not per-user
        $allAssessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified'])
            ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
            ->get();

        // Prepare indicator-level chart data using organization-wide assessments
        $chartData = $this->prepareIndicatorChartData($domains, $allAssessments);

        // Use the unified BPS indicator analysis view
        return view('monalisa.bps.indicator-analysis', compact('domains', 'assessments', 'chartData'));
    }

    /**
     * Prepare indicator-level data for charts.
     * Flattens all indicators within each domain for domain-level radar charts.
     */
    private function prepareIndicatorChartData($domains, $assessments)
    {
        $data = [
            'domains' => [],
        ];

        foreach ($domains as $domain) {
            $domainData = [
                'id' => $domain->id,
                'name' => $domain->name,
                'domain_number' => $domain->domain_number,
                'kominfo_avg_score' => 0,
                'bps_avg_score' => 0,
                'total_indikators' => 0,
                'assessed_indikators' => 0,
                'verified_indikators' => 0,
                'indikators' => [],
            ];

            $domainKominfoScoreSum = 0;
            $domainBpsScoreSum = 0;
            $domainKominfoCount = 0;
            $domainBpsCount = 0;

            // Flatten all indicators across all aspeks in this domain
            foreach ($domain->aspeks as $aspek) {
                foreach ($aspek->indikators as $indikator) {
                    $assessment = $assessments->firstWhere('indikator_id', $indikator->id);
                    $hasKominfoScore = $assessment && $assessment->kominfo_maturity_level;
                    $hasBpsScore = $assessment && $assessment->bps_maturity_level;

                    $indikatorData = [
                        'id' => $indikator->id,
                        'name' => $indikator->name,
                        'code' => $indikator->indikator_code,
                        'kominfo_score' => $hasKominfoScore ? $assessment->kominfo_maturity_level : null,
                        'bps_score' => $hasBpsScore ? $assessment->bps_maturity_level : null,
                        'status' => $assessment ? $assessment->status : 'not_started',
                        'has_assessment' => $assessment !== null,
                    ];

                    $domainData['indikators'][] = $indikatorData;
                    $domainData['total_indikators']++;

                    if ($hasKominfoScore) {
                        $domainKominfoScoreSum += $assessment->kominfo_maturity_level;
                        $domainKominfoCount++;
                        $domainData['assessed_indikators']++;
                    }

                    if ($hasBpsScore) {
                        $domainBpsScoreSum += $assessment->bps_maturity_level;
                        $domainBpsCount++;
                        $domainData['verified_indikators']++;
                    }
                }
            }

            // Calculate domain-level average scores
            if ($domainKominfoCount > 0) {
                $domainData['kominfo_avg_score'] = $domainKominfoScoreSum / $domainKominfoCount;
            }
            if ($domainBpsCount > 0) {
                $domainData['bps_avg_score'] = $domainBpsScoreSum / $domainBpsCount;
            }

            $data['domains'][] = $domainData;
        }

        return $data;
    }
}

