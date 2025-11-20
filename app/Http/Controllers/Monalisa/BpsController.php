<?php

namespace App\Http\Controllers\Monalisa;

use App\Http\Controllers\Controller;
use App\Models\MonalisaDomain;
use App\Models\MonalisaAssessment;
use App\Models\MonalisaDocument;
use App\Models\MonalisaDocumentComment;
use App\Models\MonalisaNotification;
use App\Models\MonalisaBpsCommentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BpsController extends Controller
{
    /**
     * Display the BPS dashboard.
     * Now unified to work for both BPS and Kominfo users for transparency.
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Get all domains with their structure
        $domains = MonalisaDomain::with(['aspeks.indikators'])->orderBy('order')->get();

        // Get assessments for display based on user type
        if ($user->is_bps) {
            // BPS users see all submitted/verified/rejected assessments
            $assessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified', 'rejected'])
                ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy', 'documents'])
                ->get();
        } else {
            // Kominfo users see ALL assessments (organization-wide)
            $assessments = MonalisaAssessment::with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy', 'documents'])
                ->get();
        }

        // Get ALL organization-wide assessments for score calculation (both Kominfo and BPS scores)
        // This ensures IPS scores are organization-wide, not per-user
        // Include rejected assessments in the query but they won't have BPS scores yet
        $allAssessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified', 'rejected'])
            ->with(['indikator.aspek.domain'])
            ->get();

        // Calculate both Kominfo and BPS scores using organization-wide data
        $kominfoScores = $this->calculateScores($allAssessments, $domains, 'kominfo');
        $bpsScores = $this->calculateScores($allAssessments, $domains, 'bps');

        return view('monalisa.bps.dashboard', compact('domains', 'assessments', 'kominfoScores', 'bpsScores'));
    }

    /**
     * Show assessment for verification.
     */
    public function showAssessment($assessmentId)
    {
        $assessment = MonalisaAssessment::with([
            'indikator.aspek.domain',
            'kominfoCreatedBy',
            'kominfoSubmittedBy',
            'documents.comments.user',
            'bpsCommentHistory.bpsUser'
        ])->findOrFail($assessmentId);

        return view('monalisa.bps.assessment', compact('assessment'));
    }

    /**
     * Verify and score assessment.
     */
    public function verifyAssessment(Request $request, $assessmentId)
    {
        // Validate the request with proper field names and detailed messages
        $validated = $request->validate([
            'bps_maturity_level' => 'required|integer|min:1|max:5',
            'bps_audit_comment' => 'required|string|min:50',
        ], [
            'bps_maturity_level.required' => 'BPS Maturity Level wajib dipilih.',
            'bps_maturity_level.integer' => 'BPS Maturity Level harus berupa angka.',
            'bps_maturity_level.min' => 'BPS Maturity Level minimal 1.',
            'bps_maturity_level.max' => 'BPS Maturity Level maksimal 5.',
            'bps_audit_comment.required' => 'Komentar Audit wajib diisi.',
            'bps_audit_comment.min' => 'Komentar Audit minimal 50 karakter.',
        ]);

        $assessment = MonalisaAssessment::with('indikator')->findOrFail($assessmentId);

        // Check if this is an update to existing verification
        $isUpdate = $assessment->status === 'verified';

        $assessment->update([
            'bps_user_id' => auth()->id(),
            'bps_maturity_level' => $validated['bps_maturity_level'],
            'bps_audit_comment' => $validated['bps_audit_comment'],
            'bps_verified_at' => now(),
            'status' => 'verified',
        ]);

        // Track audit trail
        $assessment->trackBpsUpdate(auth()->id());

        // Save comment to history
        MonalisaBpsCommentHistory::create([
            'assessment_id' => $assessment->id,
            'bps_user_id' => auth()->id(),
            'comment' => $validated['bps_audit_comment'],
            'action_type' => $isUpdate ? 'score_updated' : 'verified',
            'bps_maturity_level' => $validated['bps_maturity_level'],
        ]);

        // Create notification
        if ($isUpdate) {
            MonalisaNotification::createForBpsScoreUpdate($assessment, auth()->id());
        } else {
            MonalisaNotification::createForVerification($assessment, auth()->id());
        }

        return response()->json([
            'success' => true,
            'message' => 'Assessment berhasil diverifikasi.',
            'redirect' => route('monalisa.bps.domain', $assessment->indikator->aspek->domain_id),
        ]);
    }

    /**
     * Reject assessment and return to Kominfo for revision.
     */
    public function rejectAssessment(Request $request, $assessmentId)
    {
        // Validate the request - rejection requires audit comment explaining why
        $validated = $request->validate([
            'bps_audit_comment' => 'required|string|min:50',
        ], [
            'bps_audit_comment.required' => 'Komentar penolakan wajib diisi.',
            'bps_audit_comment.min' => 'Komentar penolakan minimal 50 karakter untuk memberikan feedback yang jelas kepada Kominfo.',
        ]);

        $assessment = MonalisaAssessment::with('indikator')->findOrFail($assessmentId);

        // Update assessment with rejection
        $assessment->update([
            'bps_user_id' => auth()->id(),
            'bps_audit_comment' => $validated['bps_audit_comment'],
            'bps_verified_at' => null, // Clear verification timestamp
            'bps_maturity_level' => null, // Clear BPS score on rejection
            'status' => 'rejected',
        ]);

        // Track audit trail
        $assessment->trackBpsUpdate(auth()->id());

        // Save rejection comment to history
        MonalisaBpsCommentHistory::create([
            'assessment_id' => $assessment->id,
            'bps_user_id' => auth()->id(),
            'comment' => $validated['bps_audit_comment'],
            'action_type' => 'rejected',
            'bps_maturity_level' => null, // No score for rejections
        ]);

        // Create notification for all Kominfo users
        MonalisaNotification::createForRejection($assessment, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Assessment ditolak. Kominfo dapat melakukan revisi dan submit ulang.',
            'redirect' => route('monalisa.bps.domain', $assessment->indikator->aspek->domain_id),
        ]);
    }

    /**
     * Add comment to document.
     */
    public function addDocumentComment(Request $request, $documentId)
    {
        $request->validate([
            'comment' => 'required|string|min:5',
            'status' => 'nullable|in:pass,fail,needs_revision,info',
        ]);

        $document = MonalisaDocument::findOrFail($documentId);

        $comment = MonalisaDocumentComment::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'status' => $request->status,
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment,
        ]);
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
     * Calculate scores from assessments.
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
        
        // Get all assessments for this domain (including rejected for BPS to review)
        $indikatorIds = $domain->indikators->pluck('id');
        $assessments = MonalisaAssessment::whereIn('indikator_id', $indikatorIds)
            ->whereIn('status', ['submitted', 'verified', 'rejected'])
            ->with(['indikator', 'kominfoCreatedBy', 'kominfoSubmittedBy', 'documents'])
            ->get()
            ->keyBy('indikator_id');
        
        return view('monalisa.bps.domain', compact('domain', 'assessments'));
    }

    /**
     * Get list of all assessments for review.
     */
    public function assessmentList(Request $request)
    {
        $query = MonalisaAssessment::with([
            'indikator.aspek.domain',
            'kominfoCreatedBy',
            'kominfoSubmittedBy',
            'bpsUser'
        ]);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            // Default: show submitted, verified, and rejected assessments
            $query->whereIn('status', ['submitted', 'verified', 'rejected']);
        }

        // Filter by domain
        if ($request->has('domain_id') && $request->domain_id) {
            $query->whereHas('indikator.aspek', function ($q) use ($request) {
                $q->where('domain_id', $request->domain_id);
            });
        }

        $assessments = $query->orderBy('kominfo_submitted_at', 'desc')->paginate(20);
        $domains = MonalisaDomain::orderBy('order')->get();

        return view('monalisa.bps.assessment-list', compact('assessments', 'domains'));
    }

    /**
     * Show charts/visualization page.
     * Now unified to work for both BPS and Kominfo users for transparency.
     */
    public function showCharts()
    {
        $user = auth()->user();

        // Get all domains with their structure
        $domains = MonalisaDomain::with(['aspeks.indikators'])->orderBy('order')->get();

        // Get assessments for display based on user type
        if ($user->is_bps) {
            // BPS users see all submitted/verified/rejected assessments
            $assessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified', 'rejected'])
                ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
                ->get();
        } else {
            // Kominfo users see ALL assessments (organization-wide)
            $assessments = MonalisaAssessment::with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
                ->get();
        }

        // Get ALL organization-wide assessments for chart data calculation
        // This ensures IPS scores in charts are organization-wide, not per-user
        // Include rejected assessments in the query but they won't have BPS scores yet
        $allAssessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified', 'rejected'])
            ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
            ->get();

        // Prepare chart data using organization-wide assessments (both Kominfo and BPS scores)
        $chartData = $this->prepareChartData($domains, $allAssessments);

        return view('monalisa.bps.charts', compact('domains', 'assessments', 'chartData'));
    }

    /**
     * Prepare data for charts.
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
     * Now unified to work for both BPS and Kominfo users for transparency.
     */
    public function showIndicatorAnalysis()
    {
        $user = auth()->user();

        // Get all domains with their structure
        $domains = MonalisaDomain::with(['aspeks.indikators'])->orderBy('order')->get();

        // Get assessments for display based on user type
        if ($user->is_bps) {
            // BPS users see all submitted/verified/rejected assessments
            $assessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified', 'rejected'])
                ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
                ->get();
        } else {
            // Kominfo users see ALL assessments (organization-wide)
            $assessments = MonalisaAssessment::with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
                ->get();
        }

        // Get ALL organization-wide assessments for chart data calculation
        // This ensures IPS scores in charts are organization-wide, not per-user
        // Include rejected assessments in the query but they won't have BPS scores yet
        $allAssessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified', 'rejected'])
            ->with(['indikator.aspek.domain', 'kominfoCreatedBy', 'kominfoSubmittedBy'])
            ->get();

        // Prepare indicator-level chart data using organization-wide assessments
        $chartData = $this->prepareIndicatorChartData($domains, $allAssessments);

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

