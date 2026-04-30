@extends('layouts.monalisa-dashboard')

@section('title', 'Verifikasi Assessment - ' . $assessment->indikator->name)
@section('description', 'Form verifikasi assessment untuk indikator ' . $assessment->indikator->indikator_code)

@section('monalisa-content')
    <!-- Mobile/Tablet Menu Button -->
    <div class="lg:hidden mb-4">
        <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="monalisa-sidebar" aria-expanded="false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            Menu
        </button>
    </div>

    <!-- Page Header -->
    <div class="ud-page-header">
        <div class="ud-page-header-content">
            <div class="flex items-center gap-3 mb-2">
                <span class="bg-white/20 text-white px-3 py-1 rounded-lg font-mono font-semibold">
                    {{ $assessment->indikator->indikator_code }}
                </span>
                <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                    {{ ucfirst($assessment->status) }}
                </span>
            </div>
            <h1 class="ud-page-title">{{ $assessment->indikator->name }}</h1>
            <p class="ud-page-description">
                {{ $assessment->indikator->aspek->domain->name }} → Aspek {{ $assessment->indikator->aspek->aspek_number }}: {{ $assessment->indikator->aspek->name }}
            </p>
        </div>
    </div>

    <!-- Audit Trail Information -->
    @if($assessment->kominfo_updated_at || $assessment->bps_updated_at)
    <div class="ud-card mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Perubahan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($assessment->kominfo_updated_at)
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Terakhir Diperbarui oleh Kominfo</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $assessment->kominfoUpdatedBy?->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $assessment->kominfo_updated_at->format('d M Y H:i') }}</div>
                @if($assessment->kominfoScoredBy && $assessment->kominfoScoredBy->id !== $assessment->kominfoUpdatedBy?->id)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Dinilai oleh: {{ $assessment->kominfoScoredBy->name }}</div>
                @endif
            </div>
            @endif
            @if($assessment->bps_updated_at)
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Terakhir Diverifikasi oleh BPS</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $assessment->bpsUpdatedBy?->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $assessment->bps_updated_at->format('d M Y H:i') }}</div>
                @if($assessment->bpsScoredBy && $assessment->bpsScoredBy->id !== $assessment->bpsUpdatedBy?->id)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Dinilai oleh: {{ $assessment->bpsScoredBy->name }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Kominfo Self-Assessment -->
    <div class="ud-card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Self-Assessment Kominfo</h2>
            @if($assessment->kominfo_submitted_at)
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Submitted: {{ $assessment->kominfo_submitted_at->format('d M Y H:i') }}
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Submitted By</div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ $assessment->kominfoSubmittedBy->name ?? ($assessment->kominfoCreatedBy->name ?? 'N/A') }}
                </div>
                @if($assessment->kominfoSubmittedBy ?? $assessment->kominfoCreatedBy)
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ ($assessment->kominfoSubmittedBy ?? $assessment->kominfoCreatedBy)->email }}
                </div>
                @endif
            </div>

            <div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Maturity Level</div>
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    Level {{ $assessment->kominfo_maturity_level }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Documents</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $assessment->documents->count() }} file(s)
                </div>
            </div>
        </div>

        <div>
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Justifikasi:</div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white">
                {{ $assessment->kominfo_justification }}
            </div>
        </div>
    </div>

    <!-- Supporting Documents -->
    @if($assessment->documents->count() > 0)
    <div class="ud-card mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Dokumen Pendukung</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($assessment->documents as $document)
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 flex-1">
                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <div class="monalisa-file-name font-medium text-gray-900 dark:text-white">
                                {{ $document->original_filename }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $document->formatted_size }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 ml-2">
                        <a href="{{ route('monalisa.bps.document.view', $document->id) }}"
                           target="_blank"
                           class="monalisa-btn monalisa-btn-secondary btn-sm"
                           title="Lihat Dokumen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        <a href="{{ route('monalisa.bps.document.download', $document->id) }}"
                           class="monalisa-btn monalisa-btn-primary btn-sm"
                           title="Download">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                @if($document->comments->count() > 0)
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Comments:</div>
                    @foreach($document->comments as $comment)
                    <div class="text-sm mb-2 last:mb-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                            @if($comment->status)
                            <span class="monalisa-badge monalisa-badge-{{ $comment->status }} text-xs">
                                {{ ucfirst($comment->status) }}
                            </span>
                            @endif
                        </div>
                        <div class="text-gray-700 dark:text-gray-300">{!! nl2br(e($comment->comment)) !!}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- BPS Comment History -->
    @if($assessment->bpsCommentHistory && $assessment->bpsCommentHistory->count() > 0)
    <div class="ud-card mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Riwayat Komentar BPS
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Semua feedback dan komentar dari BPS untuk assessment ini
        </p>

        <div class="space-y-4">
            @foreach($assessment->bpsCommentHistory as $history)
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4
                        {{ $history->action_type === 'verified' ? 'bg-green-50 dark:bg-green-900/10' : '' }}
                        {{ $history->action_type === 'rejected' ? 'bg-red-50 dark:bg-red-900/10' : '' }}
                        {{ $history->action_type === 'score_updated' ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">

                <!-- Header -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold">
                                {{ substr($history->bpsUser->name ?? 'BPS', 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $history->bpsUser->name ?? 'BPS User' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $history->created_at->format('d M Y, H:i') }} WIB
                            </div>
                        </div>
                    </div>
                    <span class="monalisa-badge {{ $history->action_type_badge_class }}">
                        {{ $history->action_type_display }}
                    </span>
                </div>

                <!-- Maturity Level (if applicable) -->
                @if($history->bps_maturity_level)
                <div class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-600">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">BPS Maturity Level</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        Level {{ $history->bps_maturity_level }}
                    </div>
                </div>
                @endif

                <!-- Comment -->
                <div>
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Komentar:</div>
                    <div class="text-gray-900 dark:text-white leading-relaxed">
                        {!! nl2br(e($history->comment)) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- BPS Verification Form -->
    @if($assessment->status !== 'verified')
    <form id="bpsAssessmentForm" action="{{ route('monalisa.bps.assessment.verify', $assessment->id) }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="ud-card">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Verifikasi BPS</h2>
            
            <!-- BPS Maturity Level -->
            <div class="monalisa-form-group">
                <label for="bps_maturity_level" class="monalisa-form-label">
                    BPS Maturity Level <span class="text-red-500">*</span>
                </label>
                <select name="bps_maturity_level" id="bps_maturity_level" 
                        class="monalisa-form-select @error('bps_maturity_level') error @enderror" 
                        required>
                    <option value="">Pilih Level Kematangan</option>
                    <option value="1" {{ old('bps_maturity_level', $assessment->bps_maturity_level) == 1 ? 'selected' : '' }}>
                        Level 1 - Rintisan
                    </option>
                    <option value="2" {{ old('bps_maturity_level', $assessment->bps_maturity_level) == 2 ? 'selected' : '' }}>
                        Level 2 - Terkelola
                    </option>
                    <option value="3" {{ old('bps_maturity_level', $assessment->bps_maturity_level) == 3 ? 'selected' : '' }}>
                        Level 3 - Terdefinisi
                    </option>
                    <option value="4" {{ old('bps_maturity_level', $assessment->bps_maturity_level) == 4 ? 'selected' : '' }}>
                        Level 4 - Terukur
                    </option>
                    <option value="5" {{ old('bps_maturity_level', $assessment->bps_maturity_level) == 5 ? 'selected' : '' }}>
                        Level 5 - Optimum
                    </option>
                </select>
                @error('bps_maturity_level')
                <span class="monalisa-form-error">{{ $message }}</span>
                @enderror
                <span class="monalisa-form-help">
                    Kominfo Self-Assessment: Level {{ $assessment->kominfo_maturity_level }}
                </span>
            </div>

            <!-- Audit Comment -->
            <div class="monalisa-form-group">
                <label for="bps_audit_comment" class="monalisa-form-label">
                    Komentar Audit <span class="text-red-500">*</span>
                </label>
                <textarea name="bps_audit_comment" id="bps_audit_comment" 
                          class="monalisa-form-textarea @error('bps_audit_comment') error @enderror" 
                          rows="6" 
                          required 
                          placeholder="Berikan komentar audit, temuan, rekomendasi, atau catatan verifikasi...">{{ old('bps_audit_comment', $assessment->bps_audit_comment) }}</textarea>
                @error('bps_audit_comment')
                <span class="monalisa-form-error">{{ $message }}</span>
                @enderror
                <span class="monalisa-form-help">Minimal 50 karakter. Jelaskan hasil verifikasi dan alasan penilaian BPS.</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="monalisa-action-bar flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <a href="{{ route('monalisa.bps.domain', $assessment->indikator->aspek->domain_id) }}" 
               class="monalisa-btn monalisa-btn-secondary btn-sm w-full md:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
            
            <div class="monalisa-action-group flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <button type="submit" name="action" value="reject" class="monalisa-btn monalisa-btn-danger btn-sm w-full md:w-auto">
                    Tolak Assessment
                </button>
                <button type="submit" name="action" value="verify" class="monalisa-btn monalisa-btn-success btn-sm w-full md:w-auto">
                    Verifikasi & Approve
                </button>
            </div>
        </div>
    </form>
    @else
    <!-- Verified Assessment View -->
    <div class="ud-card">
        <div class="ud-alert ud-alert-success mb-6">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <strong>Assessment Terverifikasi</strong>
                <p class="text-sm mt-1">
                    Diverifikasi oleh {{ $assessment->bpsUser->name ?? 'N/A' }} pada {{ $assessment->bps_verified_at?->format('d M Y H:i') }}
                </p>
            </div>
        </div>

        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Hasil Verifikasi BPS</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">BPS Maturity Level</div>
                <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                    Level {{ $assessment->bps_maturity_level }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Verified By</div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ $assessment->bpsUser->name ?? 'N/A' }}
                </div>
                @if($assessment->bpsUser)
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $assessment->bpsUser->email }}
                </div>
                @endif
            </div>
        </div>

        <div>
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Komentar Audit:</div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white">
                {{ $assessment->bps_audit_comment }}
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('monalisa.bps.domain', $assessment->indikator->aspek->domain_id) }}" 
               class="monalisa-btn monalisa-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Domain
            </a>
        </div>
    </div>
    @endif
@endsection

@push('styles')
<!-- Survey Validation CSS for error display -->
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<!-- Responsive tweaks for BPS Assessment page -->
<link rel="stylesheet" href="{{ asset('css/monalisa-bps-assessment.css') }}">
@endpush

@push('scripts')
<!-- BPS Assessment Form Handler -->
<script src="{{ asset('js/monalisa-bps-assessment.js') }}"></script>
@endpush
