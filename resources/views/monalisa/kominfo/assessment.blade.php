@extends('layouts.monalisa-dashboard')

@section('title', 'Assessment - ' . $indikator->name)
@section('description', 'Form self-assessment untuk indikator ' . $indikator->indikator_code)

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
                    {{ $indikator->indikator_code }}
                </span>
                @if($assessment && $assessment->status !== 'draft')
                <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                    {{ ucfirst($assessment->status) }}
                </span>
                @endif
            </div>
            <h1 class="ud-page-title">{{ $indikator->name }}</h1>
            <p class="ud-page-description">
                {{ $indikator->aspek->domain->name }} → Aspek {{ $indikator->aspek->aspek_number }}: {{ $indikator->aspek->name }}
            </p>
        </div>
    </div>

    @if($assessment && $assessment->status === 'rejected')
    <!-- Rejected Assessment Alert -->
    <div class="ud-alert ud-alert-danger mb-6">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <strong>Assessment Ditolak oleh BPS</strong>
            <p class="text-sm mt-1">Silakan lakukan revisi berdasarkan feedback BPS di bawah ini, kemudian submit ulang.</p>
        </div>
    </div>

    <!-- BPS Rejection Feedback -->
    <div class="ud-card mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            Feedback Penolakan dari BPS
        </h3>
        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                Ditolak oleh: <strong>{{ $assessment->bpsUser?->name ?? 'BPS' }}</strong> pada {{ $assessment->bps_updated_at?->format('d M Y H:i') }}
            </div>
            <div class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $assessment->bps_audit_comment }}</div>
        </div>
    </div>

    <!-- BPS Comment History (for rejected assessments) -->
    @if($assessment->bpsCommentHistory && $assessment->bpsCommentHistory->count() > 1)
    <div class="ud-card mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Riwayat Feedback BPS Sebelumnya
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Feedback dan komentar BPS dari verifikasi sebelumnya
        </p>

        <div class="space-y-4">
            @foreach($assessment->bpsCommentHistory->skip(1) as $history)
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
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Feedback:</div>
                    <div class="text-gray-900 dark:text-white leading-relaxed">
                        {{ $history->comment }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    @if($assessment && $assessment->status === 'verified')
    <!-- Verified Assessment View (Read-only) -->
    <div class="ud-alert ud-alert-success mb-6">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <strong>Assessment Terverifikasi</strong>
            <p class="text-sm mt-1">Assessment ini telah diverifikasi oleh BPS pada {{ $assessment->bps_verified_at?->format('d M Y H:i') }}</p>
        </div>
    </div>

    <!-- Audit Trail Information -->
    @if($assessment->kominfo_updated_at || $assessment->bps_updated_at)
    <div class="ud-card mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Perubahan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($assessment->kominfo_updated_at)
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Terakhir Diperbarui oleh Kominfo</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $assessment->kominfoUpdatedBy?->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $assessment->kominfo_updated_at->format('d M Y H:i') }}</div>
            </div>
            @endif
            @if($assessment->bps_updated_at)
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Terakhir Diverifikasi oleh BPS</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $assessment->bpsUpdatedBy?->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $assessment->bps_updated_at->format('d M Y H:i') }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="ud-card">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Self-Assessment Anda</h3>
            <div class="space-y-3">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Maturity Level</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">Level {{ $assessment->kominfo_maturity_level }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Justifikasi</div>
                    <div class="text-gray-900 dark:text-white">{{ $assessment->kominfo_justification }}</div>
                </div>
                @if($assessment->kominfoScoredBy)
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Dinilai oleh</div>
                    <div class="text-gray-900 dark:text-white">{{ $assessment->kominfoScoredBy->name }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="ud-card">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Verifikasi BPS</h3>
            <div class="space-y-3">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">BPS Maturity Level</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">Level {{ $assessment->bps_maturity_level }}</div>
                </div>
                @if($assessment->bps_audit_comment)
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Komentar Audit</div>
                    <div class="text-gray-900 dark:text-white">{{ $assessment->bps_audit_comment }}</div>
                </div>
                @endif
                @if($assessment->bpsScoredBy)
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Diverifikasi oleh</div>
                    <div class="text-gray-900 dark:text-white">{{ $assessment->bpsScoredBy->name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- BPS Comment History (for verified assessments) -->
    @if($assessment->bpsCommentHistory && $assessment->bpsCommentHistory->count() > 0)
    <div class="ud-card mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Riwayat Feedback BPS
        </h3>
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
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Feedback:</div>
                    <div class="text-gray-900 dark:text-white leading-relaxed">
                        {{ $history->comment }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @else
    <!-- Assessment Form -->
    <form id="kominfoAssessmentForm" action="{{ route('monalisa.kominfo.assessment.submit', $assessment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="ud-card">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Self-Assessment</h2>
            
            <!-- Maturity Level Selector -->
            <div class="monalisa-form-group">
                <label for="maturity_level" class="monalisa-form-label">
                    Maturity Level <span class="text-red-500">*</span>
                </label>
                <select name="maturity_level" id="maturity_level" 
                        class="monalisa-form-select @error('maturity_level') error @enderror" 
                        required>
                    <option value="">Pilih Level Kematangan</option>
                    <option value="1" {{ old('maturity_level', $assessment?->kominfo_maturity_level) == 1 ? 'selected' : '' }}>
                        Level 1 - Rintisan
                    </option>
                    <option value="2" {{ old('maturity_level', $assessment?->kominfo_maturity_level) == 2 ? 'selected' : '' }}>
                        Level 2 - Terkelola
                    </option>
                    <option value="3" {{ old('maturity_level', $assessment?->kominfo_maturity_level) == 3 ? 'selected' : '' }}>
                        Level 3 - Terdefinisi
                    </option>
                    <option value="4" {{ old('maturity_level', $assessment?->kominfo_maturity_level) == 4 ? 'selected' : '' }}>
                        Level 4 - Terukur
                    </option>
                    <option value="5" {{ old('maturity_level', $assessment?->kominfo_maturity_level) == 5 ? 'selected' : '' }}>
                        Level 5 - Optimum
                    </option>
                </select>
                @error('maturity_level')
                <span class="monalisa-form-error">{{ $message }}</span>
                @enderror
                <span class="monalisa-form-help">Pilih level kematangan yang sesuai dengan kondisi saat ini</span>
            </div>

            <!-- Justification -->
            <div class="monalisa-form-group">
                <label for="justification" class="monalisa-form-label">
                    Justifikasi/Penjelasan <span class="text-red-500">*</span>
                </label>
                <textarea name="justification" id="justification" 
                          class="monalisa-form-textarea @error('justification') error @enderror" 
                          rows="6" 
                          required 
                          placeholder="Jelaskan alasan pemilihan level kematangan, bukti pendukung, dan kondisi aktual...">{{ old('justification', $assessment?->kominfo_justification) }}</textarea>
                @error('justification')
                <span class="monalisa-form-error">{{ $message }}</span>
                @enderror
                <span class="monalisa-form-help">Minimal 50 karakter. Jelaskan secara detail kondisi dan bukti yang mendukung penilaian Anda.</span>
            </div>

            <!-- Document Upload Section -->
            <div class="monalisa-form-group">
                <label class="monalisa-form-label">
                    Dokumen Pendukung
                </label>

                <!-- Upload Area -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                    <input type="file" id="documentUpload" class="hidden"
                           accept=".pdf"
                           multiple
                           data-assessment-id="{{ $assessment->id }}">
                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <button type="button" onclick="document.getElementById('documentUpload').click()"
                            class="monalisa-btn monalisa-btn-secondary mb-2">
                        Pilih File
                    </button>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        atau drag & drop file di sini
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                        Hanya PDF - Maksimal 10MB per file
                    </p>
                </div>

                <!-- Uploaded Documents List -->
                @if($assessment && $assessment->documents->count() > 0)
                <div class="mt-4">
                    <h4 id="documentsCountLabel" class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Dokumen yang Sudah Diupload ({{ $assessment->documents->count() }})
                    </h4>
                    <div class="space-y-2" id="documentsList">
                        @foreach($assessment->documents as $document)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 transition-colors" data-document-id="{{ $document->id }}">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="flex-shrink-0">
                                    @if(in_array($document->file_type, ['pdf']))
                                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                    </svg>
                                    @elseif(in_array($document->file_type, ['doc', 'docx']))
                                    <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                    </svg>
                                    @elseif(in_array($document->file_type, ['xls', 'xlsx']))
                                    <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                    </svg>
                                    @else
                                    <svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                    </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="monalisa-file-name font-medium text-gray-900 dark:text-white">
                                        {{ $document->original_filename }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format($document->file_size / 1024, 2) }} KB
                                        @if($document->description)
                                        • {{ $document->description }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <a href="{{ route('monalisa.kominfo.document.download', $document->id) }}"
                                   class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                   title="Download">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                                @if($assessment->canBeEditedByKominfo())
                                <button type="button"
                                        onclick="replaceDocument('{{ $document->id }}')"
                                        class="p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                                        title="Ganti File">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                                <button type="button"
                                        onclick="deleteDocument('{{ $document->id }}')"
                                        class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                        title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div id="noDocumentsPlaceholder" class="mt-4 text-center py-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Belum ada dokumen yang diupload</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-between">
            <a href="{{ route('monalisa.kominfo.domain', $indikator->aspek->domain_id) }}"
               class="monalisa-btn monalisa-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            <div class="flex gap-3">
                <button type="submit" name="action" value="draft" class="monalisa-btn monalisa-btn-secondary">
                    Simpan Draft
                </button>
                @if($assessment->status === 'submitted')
                <button type="submit" name="action" value="update" class="monalisa-btn monalisa-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Update Assessment
                </button>
                @elseif($assessment->status === 'rejected')
                <button type="submit" name="action" value="submit" class="monalisa-btn monalisa-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Resubmit Setelah Revisi
                </button>
                @else
                <button type="submit" name="action" value="submit" class="monalisa-btn monalisa-btn-primary">
                    Submit untuk Verifikasi
                </button>
                @endif
            </div>
        </div>
    </form>
    @endif

    <!-- Documents Section (for verified assessments) -->
    @if($assessment && $assessment->status === 'verified' && $assessment->documents->count() > 0)
    <div class="ud-card mt-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Dokumen Pendukung</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($assessment->documents as $document)
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="monalisa-file-name font-medium text-gray-900 dark:text-white">{{ $document->original_filename }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $document->formatted_size }}</div>
                </div>
            </div>
            <a href="{{ route('monalisa.kominfo.document.download', $document->id) }}" 
               class="monalisa-btn monalisa-btn-primary btn-sm">
                Download
            </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="monalisa-modal" role="dialog" aria-labelledby="deleteModalTitle" aria-modal="true" style="display: none !important; opacity: 0 !important; visibility: hidden !important;">
        <div class="monalisa-modal-backdrop"></div>
        <div class="monalisa-modal-container">
            <div class="monalisa-modal-content monalisa-modal-danger">
                <!-- Modal Header -->
                <div class="monalisa-modal-header">
                    <div class="monalisa-modal-icon monalisa-modal-icon-danger">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <div class="monalisa-modal-title-wrapper">
                        <h3 id="deleteModalTitle" class="monalisa-modal-title">Hapus Dokumen</h3>
                        <p class="monalisa-modal-subtitle">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="monalisa-modal-body">
                    <p class="monalisa-modal-text">Apakah Anda yakin ingin menghapus dokumen ini?</p>
                    <div class="monalisa-modal-file-info">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span id="deleteFileName" class="monalisa-modal-filename"></span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="monalisa-modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="monalisa-modal-btn monalisa-modal-btn-cancel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="monalisa-modal-btn monalisa-modal-btn-danger">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Hapus Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replace File Confirmation Modal -->
    <div id="replaceModal" class="monalisa-modal" role="dialog" aria-labelledby="replaceModalTitle" aria-modal="true" style="display: none !important; opacity: 0 !important; visibility: hidden !important;">
        <div class="monalisa-modal-backdrop"></div>
        <div class="monalisa-modal-container">
            <div class="monalisa-modal-content monalisa-modal-warning">
                <!-- Modal Header -->
                <div class="monalisa-modal-header">
                    <div class="monalisa-modal-icon monalisa-modal-icon-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <div class="monalisa-modal-title-wrapper">
                        <h3 id="replaceModalTitle" class="monalisa-modal-title">Ganti File</h3>
                        <p class="monalisa-modal-subtitle">File lama akan digantikan dengan file baru</p>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="monalisa-modal-body">
                    <p class="monalisa-modal-text">Anda akan mengganti dokumen berikut:</p>
                    <div class="monalisa-modal-file-info">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span id="replaceFileName" class="monalisa-modal-filename"></span>
                    </div>
                    <div class="monalisa-modal-info-box">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm">
                            <strong>Catatan:</strong> Setelah konfirmasi, Anda akan diminta memilih file baru. File lama akan dihapus dan digantikan dengan file yang Anda pilih.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="monalisa-modal-footer">
                    <button type="button" onclick="closeReplaceModal()" class="monalisa-modal-btn monalisa-modal-btn-cancel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </button>
                    <button type="button" id="confirmReplaceBtn" class="monalisa-modal-btn monalisa-modal-btn-warning">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Lanjutkan Ganti File
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Modal management
let deleteDocumentId = null;
let replaceDocumentId = null;
let justificationValid = false;

// Utility: update documents count label
function updateDocumentsCount() {
    const list = document.getElementById('documentsList');
    const countLabel = document.getElementById('documentsCountLabel');
    const count = list ? list.children.length : 0;
    if (countLabel) {
        countLabel.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Dokumen yang Sudah Diupload (${count})`;
    }
    const placeholder = document.getElementById('noDocumentsPlaceholder');
    if (placeholder) {
        placeholder.style.display = count > 0 ? 'none' : '';
    }
}

// Utility: create document item DOM
function createDocumentItem(doc, canEdit = true) {
    const div = document.createElement('div');
    div.className = 'flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 transition-colors';
    div.setAttribute('data-document-id', doc.id);

    const iconColor = (doc.file_type === 'pdf') ? 'text-red-500' : (doc.file_type === 'doc' || doc.file_type === 'docx') ? 'text-blue-500' : (doc.file_type === 'xls' || doc.file_type === 'xlsx') ? 'text-green-500' : 'text-gray-500';
    const sizeKb = (doc.file_size / 1024).toFixed(2);

    div.innerHTML = `
        <div class="flex items-center gap-3 flex-1">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="monalisa-file-name font-medium text-gray-900 dark:text-white">${doc.original_filename}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">${sizeKb} KB${doc.description ? ' • ' + doc.description : ''}</div>
            </div>
        </div>
        <div class="flex items-center gap-2 ml-4">
            <a href="/monalisa/kominfo/document/${doc.id}/download" class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Download">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </a>
            ${canEdit ? `
            <button type="button" onclick="replaceDocument('${doc.id}')" class="p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors" title="Ganti File">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <button type="button" onclick="deleteDocument('${doc.id}')" class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
            ` : ''}
        </div>
    `;
    return div;
}

// Delete Modal Functions
function openDeleteModal(documentId, fileName) {
    deleteDocumentId = documentId;
    document.getElementById('deleteFileName').textContent = fileName;
    const modal = document.getElementById('deleteModal');

    // Remove inline styles to allow CSS classes to work
    modal.style.display = '';
    modal.style.opacity = '';
    modal.style.visibility = '';

    modal.classList.add('monalisa-modal-active');
    document.body.style.overflow = 'hidden';

    // Trigger animation
    setTimeout(() => {
        modal.querySelector('.monalisa-modal-content').classList.add('monalisa-modal-show');
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.querySelector('.monalisa-modal-content').classList.remove('monalisa-modal-show');

    setTimeout(() => {
        modal.classList.remove('monalisa-modal-active');
        document.body.style.overflow = '';
        deleteDocumentId = null;

        // Re-apply inline styles to ensure modal is hidden
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
    }, 300);
}

// Replace Modal Functions
function openReplaceModal(documentId, fileName) {
    replaceDocumentId = documentId;
    document.getElementById('replaceFileName').textContent = fileName;
    const modal = document.getElementById('replaceModal');

    // Remove inline styles to allow CSS classes to work
    modal.style.display = '';
    modal.style.opacity = '';
    modal.style.visibility = '';

    modal.classList.add('monalisa-modal-active');
    document.body.style.overflow = 'hidden';

    // Trigger animation
    setTimeout(() => {
        modal.querySelector('.monalisa-modal-content').classList.add('monalisa-modal-show');
    }, 10);
}

function closeReplaceModal() {
    const modal = document.getElementById('replaceModal');
    modal.querySelector('.monalisa-modal-content').classList.remove('monalisa-modal-show');

    setTimeout(() => {
        modal.classList.remove('monalisa-modal-active');
        document.body.style.overflow = '';
        replaceDocumentId = null;

        // Re-apply inline styles to ensure modal is hidden
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
    }, 300);
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeReplaceModal();
    }
});

// Close modals on backdrop click
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target.classList.contains('monalisa-modal-backdrop')) {
        closeDeleteModal();
    }
});

document.getElementById('replaceModal')?.addEventListener('click', function(e) {
    if (e.target.classList.contains('monalisa-modal-backdrop')) {
        closeReplaceModal();
    }
});

// Confirm delete button handler
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (deleteDocumentId) {
        performDelete(deleteDocumentId);
        closeDeleteModal();
    }
});

// Confirm replace button handler
document.getElementById('confirmReplaceBtn')?.addEventListener('click', function() {
    if (replaceDocumentId) {
        closeReplaceModal();
        performReplace(replaceDocumentId);
    }
});

// File upload handling + Justification validation
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('documentUpload');
    const assessmentId = fileInput?.dataset.assessmentId;
    const form = document.getElementById('kominfoAssessmentForm');
    const justification = document.getElementById('justification');
    const submitButtons = form ? form.querySelectorAll('button[type="submit"]') : [];
    const minJustificationChars = 50;

    function setSubmitEnabled(enabled) {
        submitButtons.forEach(btn => btn.disabled = !enabled);
    }

    function showJustificationError(msg) {
        let err = document.getElementById('justificationError');
        if (!err) {
            err = document.createElement('span');
            err.id = 'justificationError';
            err.className = 'monalisa-form-error';
            justification.parentElement.appendChild(err);
        }
        err.textContent = msg;
        err.style.display = msg ? '' : 'none';
    }

    function updateJustificationHelper(count) {
        let helper = justification.parentElement.querySelector('.monalisa-form-help');
        if (helper) {
            const base = 'Minimal 50 karakter. Jelaskan secara detail kondisi dan bukti yang mendukung penilaian Anda.';
            helper.textContent = `${base} (${count}/${minJustificationChars})`;
        }
    }

    function validateJustification() {
        const val = justification?.value?.trim() || '';
        const count = val.length;
        updateJustificationHelper(count);
        if (count < minJustificationChars) {
            showJustificationError(`Minimal ${minJustificationChars} karakter. Saat ini: ${count}.`);
            justificationValid = false;
        } else {
            showJustificationError('');
            justificationValid = true;
        }
        setSubmitEnabled(justificationValid);
        return justificationValid;
    }

    if (justification) {
        // initialize state
        validateJustification();
        justification.addEventListener('input', validateJustification);
        justification.addEventListener('blur', validateJustification);
    }

    if (fileInput) {
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        });

        // Handle drag and drop
        const dropZone = fileInput.closest('.border-dashed');
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('border-blue-500', 'dark:border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/10');
            });

            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('border-blue-500', 'dark:border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/10');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('border-blue-500', 'dark:border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/10');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    uploadFiles(files);
                }
            });
        }
    }

    // Validate on submit to prevent accidental submission
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateJustification()) {
                e.preventDefault();
                justification?.focus();
                showNotification('Lengkapi Justifikasi/Penjelasan minimal 50 karakter sebelum submit.', 'error');
            }
        });
    }

    function uploadFiles(files) {
        Array.from(files).forEach(file => {
            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showNotification('File ' + file.name + ' terlalu besar. Maksimal 10MB.', 'error');
                return;
            }

            // Validate file type
            const allowedTypes = ['application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Hanya file PDF yang diperbolehkan: ' + file.name, 'error');
                return;
            }

            uploadFile(file);
        });

        // Reset input
        fileInput.value = '';
    }

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('description', '');

        // Show loading notification
        showNotification('Mengupload ' + file.name + '...', 'info');

        fetch(`/monalisa/kominfo/assessment/${assessmentId}/upload`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('File berhasil diupload!', 'success');
                // Append to list without reloading
                let list = document.getElementById('documentsList');
                if (!list) {
                    // Create list container if not present
                    const container = document.createElement('div');
                    container.className = 'mt-4';
                    const h4 = document.createElement('h4');
                    h4.id = 'documentsCountLabel';
                    h4.className = 'font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2';
                    h4.innerHTML = `
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Dokumen yang Sudah Diupload (0)`;
                    container.appendChild(h4);
                    list = document.createElement('div');
                    list.id = 'documentsList';
                    list.className = 'space-y-2';
                    container.appendChild(list);
                    // Replace placeholder
                    const placeholder = document.getElementById('noDocumentsPlaceholder');
                    if (placeholder && placeholder.parentElement) {
                        placeholder.parentElement.replaceChild(container, placeholder);
                    } else {
                        document.querySelector('.monalisa-form-group')?.appendChild(container);
                    }
                }
                const item = createDocumentItem(data.document, true);
                list.appendChild(item);
                updateDocumentsCount();
            } else {
                showNotification('Gagal mengupload file.', 'error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showNotification('Terjadi kesalahan saat mengupload file.', 'error');
        });
    }
});

// Replace document function - opens confirmation modal
function replaceDocument(documentId) {
    // Get the filename from the document element
    const docElement = document.querySelector(`[data-document-id="${documentId}"]`);
    const fileName = docElement?.querySelector('.font-medium')?.textContent || 'dokumen ini';

    openReplaceModal(documentId, fileName);
}

// Perform actual replace - opens file picker after confirmation
function performReplace(documentId) {
    // Create a hidden file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.pdf';
    fileInput.style.display = 'none';

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showNotification('File terlalu besar. Maksimal 10MB.', 'error');
            return;
        }

        // Validate file type
        const allowedTypes = ['application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Hanya file PDF yang diperbolehkan.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('description', '');

        showNotification('Mengganti file...', 'info');

        fetch(`/monalisa/kominfo/document/${documentId}/replace`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('File berhasil diganti!', 'success');
                // Update DOM item without reload
                const docElement = document.querySelector(`[data-document-id="${documentId}"]`);
                if (docElement) {
                    const nameEl = docElement.querySelector('.font-medium');
                    // Use robust selectors to handle Tailwind dark variant without CSS escaping issues
                    // Prefer the light mode classes; fall back to matching the dark variant token in class attribute
                    const infoEl = docElement.querySelector('.text-sm.text-gray-500')
                        || docElement.querySelector('[class~="dark:text-gray-400"]');
                    if (nameEl) nameEl.textContent = data.document.original_filename;
                    if (infoEl) {
                        const sizeKb = (data.document.file_size / 1024).toFixed(2);
                        infoEl.textContent = `${sizeKb} KB${data.document.description ? ' • ' + data.document.description : ''}`;
                    }
                }
                updateDocumentsCount();
            } else {
                showNotification(data.message || 'Gagal mengganti file.', 'error');
            }
        })
        .catch(error => {
            console.error('Replace error:', error);
            showNotification('Terjadi kesalahan saat mengganti file.', 'error');
        });
    });

    // Trigger file selection
    document.body.appendChild(fileInput);
    fileInput.click();

    // Clean up after selection
    setTimeout(() => {
        document.body.removeChild(fileInput);
    }, 1000);
}

// Delete document function - opens modal
function deleteDocument(documentId) {
    // Get the filename from the document element
    const docElement = document.querySelector(`[data-document-id="${documentId}"]`);
    const fileName = docElement?.querySelector('.font-medium')?.textContent || 'dokumen ini';

    openDeleteModal(documentId, fileName);
}

// Perform actual delete
function performDelete(documentId) {
    showNotification('Menghapus dokumen...', 'info');

    fetch(`/monalisa/kominfo/document/${documentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Dokumen berhasil dihapus!', 'success');
            // Remove document from list with animation
            const docElement = document.querySelector(`[data-document-id="${documentId}"]`);
            if (docElement) {
                docElement.style.transition = 'opacity 0.3s, transform 0.3s';
                docElement.style.opacity = '0';
                docElement.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    docElement.remove();
                    updateDocumentsCount();
                }, 300);
            } else {
                updateDocumentsCount();
            }
        } else {
            showNotification(data.message || 'Gagal menghapus dokumen.', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showNotification('Terjadi kesalahan saat menghapus dokumen.', 'error');
    });
}

// Simple notification function - unified with DataKita toast
function showNotification(message, type = 'info') {
    // Prefer the global MONALISA notification toast for consistency
    if (window.MonalisaNotifications && typeof window.MonalisaNotifications.showToast === 'function') {
        const title = type === 'success' ? 'Berhasil' :
                      type === 'error' ? 'Kesalahan' :
                      type === 'warning' ? 'Peringatan' : 'Info';
        window.MonalisaNotifications.showToast(title, message, type);
        return;
    }

    // Fallback: minimal inline notification (only if toast is unavailable)
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'info' ? 'bg-blue-500 text-white' :
        'bg-gray-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => { notification.style.transform = 'translateX(0)'; }, 10);
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/monalisa-bps-assessment.css') }}">
@endpush

