@extends('layouts.bps')

@section('title', 'Detail Survei SIBSTR - BPS Dashboard')
@section('description', 'Detail respons survei SIBSTR dalam mode tampilan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-blok3a.css') }}">
<style>
/* ============================================
   BPS SIBSTR Detail View - Single Page Layout
   ============================================ */

/* View Mode Container */
.bps-detail-view {
    position: relative;
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 2rem;
    align-items: start;
}

/* Main Content Area */
.bps-detail-main {
    min-width: 0;
}

/* ---- Header Card ---- */
.bps-view-header {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 2px solid #3b82f6;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.bps-view-header h1 {
    color: #1e40af;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.bps-view-header .company-name {
    color: #1f2937;
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.bps-view-header .meta-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.bps-view-header .meta-item {
    display: flex;
    flex-direction: column;
}

.bps-view-header .meta-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.bps-view-header .meta-value {
    font-size: 0.875rem;
    color: #111827;
    font-weight: 500;
}

.bps-view-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.bps-view-badge.completed {
    background: #d1fae5;
    color: #065f46;
}

.bps-view-badge.in-progress {
    background: #fef3c7;
    color: #92400e;
}

/* ---- View Mode Indicator ---- */
.view-mode-indicator {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 0.5rem;
}

.view-mode-indicator p {
    margin: 0;
    color: #1e40af;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.view-mode-indicator svg {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

/* ---- Back Button ---- */
.bps-back-button {
    display: inline-flex !important;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #3b82f6 !important;
    color: white !important;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.bps-back-button:hover {
    background: #2563eb !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* ---- Block Section Cards ---- */
.block-section {
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
    margin-bottom: 2rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    scroll-margin-top: 1.5rem;
}

.block-section-header {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.block-section-header .block-number {
    background: rgba(255, 255, 255, 0.2);
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.block-section-header h2 {
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0;
    color: white;
    line-height: 1.3;
}

.block-section-header .block-subtitle {
    font-size: 0.8125rem;
    opacity: 0.85;
    font-weight: 400;
    margin-top: 0.125rem;
}

.block-section-body {
    padding: 0;
}

/* ---- Read-only Form Overrides ---- */
.block-section-body input,
.block-section-body textarea,
.block-section-body select {
    pointer-events: none !important;
    cursor: default !important;
    opacity: 0.9;
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
}

.block-section-body .radio-input,
.block-section-body input[type="radio"] {
    pointer-events: none !important;
}

/* Hide all survey action elements */
.block-section-body .form-actions,
.block-section-body #autosave-status,
.block-section-body .autosave-status,
.block-section-body .add-product-container,
.block-section-body .add-row-btn,
.block-section-body .delete-row-btn {
    display: none !important;
}

/* Override the survey header inside blocks */
.block-section-body .survey-header {
    display: none;
}

/* Keep the survey-container and survey-form visible inside blocks */
.block-section-body .survey-container {
    padding: 0;
    max-width: 100%;
}

.block-section-body .survey-form {
    margin: 0;
}

/* ---- Sticky TOC Sidebar ---- */
.bps-toc-sidebar {
    position: sticky;
    top: 1.5rem;
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.bps-toc-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    color: white;
    padding: 0.875rem 1.25rem;
    font-weight: 700;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bps-toc-header svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.bps-toc-list {
    list-style: none;
    margin: 0;
    padding: 0.5rem 0;
}

.bps-toc-item {
    margin: 0;
}

.bps-toc-link {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1.25rem;
    color: #4b5563;
    text-decoration: none;
    font-size: 0.8125rem;
    font-weight: 500;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.bps-toc-link:hover {
    background: #f0f9ff;
    color: #1e40af;
    border-left-color: #93c5fd;
}

.bps-toc-link.active {
    background: #eff6ff;
    color: #1e40af;
    font-weight: 600;
    border-left-color: #3b82f6;
}

.bps-toc-link .toc-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: #d1d5db;
    flex-shrink: 0;
    transition: all 0.2s;
}

.bps-toc-link.active .toc-dot {
    background: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

/* ---- Bottom Actions Bar ---- */
.bps-bottom-actions {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-top: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.bps-bottom-actions .page-summary {
    font-size: 0.8125rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bps-bottom-actions .page-summary svg {
    width: 1rem;
    height: 1rem;
    color: #9ca3af;
}

/* ---- Mobile TOC Toggle ---- */
.bps-toc-mobile-toggle {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 100;
    background: #1e40af;
    color: white;
    border: none;
    border-radius: 50%;
    width: 3.5rem;
    height: 3.5rem;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4);
    cursor: pointer;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.bps-toc-mobile-toggle:hover {
    background: #1e3a8a;
    transform: scale(1.05);
}

.bps-toc-mobile-toggle svg {
    width: 1.5rem;
    height: 1.5rem;
}

/* ---- Scroll to Top ---- */
.bps-scroll-top {
    position: fixed;
    bottom: 1.5rem;
    right: 6rem;
    z-index: 100;
    background: white;
    color: #1e40af;
    border: 2px solid #1e40af;
    border-radius: 50%;
    width: 3rem;
    height: 3rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.bps-scroll-top:hover {
    background: #1e40af;
    color: white;
}

.bps-scroll-top svg {
    width: 1.25rem;
    height: 1.25rem;
}

/* ---- Blok 3B empty state ---- */
.blok3b-empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
}

.blok3b-empty-state svg {
    width: 3rem;
    height: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.blok3b-empty-state p {
    font-size: 0.9375rem;
    margin: 0;
}

/* ---- Responsive ---- */
@media (max-width: 1024px) {
    .bps-detail-view {
        grid-template-columns: 1fr;
    }

    .bps-toc-sidebar {
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 280px;
        border-radius: 0;
        z-index: 200;
        box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
        overflow-y: auto;
    }

    .bps-toc-sidebar.mobile-open {
        display: block;
    }

    .bps-toc-mobile-toggle {
        display: flex;
    }

    .bps-toc-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 199;
    }

    .bps-toc-overlay.active {
        display: block;
    }
}

@media (max-width: 640px) {
    .bps-view-header {
        padding: 1rem;
    }

    .bps-view-header h1 {
        font-size: 1.25rem;
    }

    .block-section-header {
        padding: 0.875rem 1rem;
    }

    .block-section-header h2 {
        font-size: 1rem;
    }

    .bps-bottom-actions {
        flex-direction: column;
        align-items: stretch;
    }
}

/* ---- Print Styles ---- */
@media print {
    .bps-toc-sidebar,
    .bps-toc-mobile-toggle,
    .bps-scroll-top,
    .bps-back-button,
    .bps-bottom-actions,
    .view-mode-indicator {
        display: none !important;
    }

    .bps-detail-view {
        grid-template-columns: 1fr;
    }

    .block-section {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}

/* Table responsive inside blocks */
.block-section-body .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Blok 5 table inside blocks */
.block-section-body .survey-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 920px;
}
.block-section-body .survey-table th,
.block-section-body .survey-table td {
    border: 1px solid var(--gray-200, #e5e7eb);
    padding: 12px;
    text-align: center;
    vertical-align: middle;
}
.block-section-body .radio-group {
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}
.block-section-body .radio-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-radius: 999px;
    cursor: default;
    user-select: none;
    pointer-events: none;
}
.block-section-body .radio-pill input[type="radio"] {
    width: 18px;
    height: 18px;
}
.block-section-body .row-label {
    text-align: left;
    font-weight: 600;
}
.block-section-body .row-label .component-desc {
    display: block;
    margin-top: 6px;
    font-weight: 400;
    color: var(--gray-600, #6b7280);
    font-size: 0.8125rem;
    line-height: 1.4;
}
.block-section-body .sticky-col {
    position: sticky;
    left: 0;
    background: var(--card-bg, #fff);
    z-index: 1;
}
.block-section-body .survey-table th.prospect {
    background: #e0f2fe;
    color: #111827;
}
.block-section-body .survey-table td.prospect-col {
    background: #f7fbff;
}
</style>
@endpush

@section('content')
<!-- Mobile TOC Overlay -->
<div class="bps-toc-overlay" id="toc-overlay"></div>

<div class="bps-detail-view">
    <!-- ===== Main Content ===== -->
    <div class="bps-detail-main">
        <!-- Header Card -->
        <div class="bps-view-header">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1;">
                    <h1>Detail Survei SIBSTR</h1>
                    <div class="company-name">{{ $surveyResponse->nama_perusahaan ?: 'Nama Perusahaan Belum Diisi' }}</div>
                    <div class="meta-info">
                        <div class="meta-item">
                            <span class="meta-label">Pengguna</span>
                            <span class="meta-value">{{ $surveyResponse->user->name }}</span>
                            <span style="font-size: 0.75rem; color: #6b7280;">{{ $surveyResponse->user->email }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Terakhir Diperbarui</span>
                            <span class="meta-value">{{ $surveyResponse->updated_at->setTimezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</span>
                        </div>
                        @if($surveyResponse->kip)
                        <div class="meta-item">
                            <span class="meta-label">KIP</span>
                            <span class="meta-value">{{ $surveyResponse->kip }}</span>
                        </div>
                        @endif
                        @if($surveyResponse->idsbr)
                        <div class="meta-item">
                            <span class="meta-label">IDSBR</span>
                            <span class="meta-value">{{ $surveyResponse->idsbr }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                    @php
                        $isTahunanRecord = (((int)($surveyResponse->triwulan ?? 0)) === 0);
                        $isFinishedRecord = $isTahunanRecord
                            ? ($surveyResponse->annual_survey_status === 'FINISH_SURVEY')
                            : (bool) $surveyResponse->is_completed;
                    @endphp
                    @if($isFinishedRecord)
                        <span class="bps-view-badge completed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $isTahunanRecord ? 'FINISH_SURVEY' : 'Selesai' }}
                        </span>
                    @else
                        <span class="bps-view-badge in-progress">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Dalam Proses
                        </span>
                    @endif
                    <a href="{{ route('bps.sibstr.index') }}" class="bps-back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <!-- View Mode Indicator -->
        <div class="view-mode-indicator" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <p style="display:flex; align-items:center; gap:.5rem; margin:0;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Mode Tampilan Read-Only 
            </p>
            <a href="{{ route('bps.sibstr.download', $surveyResponse->id) }}" class="bps-back-button" style="display:inline-flex; align-items:center; gap:.5rem; background:#1e40af; color:#fff; padding:.5rem .75rem; border-radius:.5rem; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 3a1 1 0 011 1v10.586l3.293-3.293a1 1 0 111.414 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 111.414-1.414L11 14.586V4a1 1 0 011-1z" />
                    <path d="M5 18a1 1 0 011-1h12a1 1 0 011 1v1a2 2 0 01-2 2H7a2 2 0 01-2-2v-1z" />
                </svg>
                Download PDF
            </a>
        </div>

        <!-- ===== BLOK I: Keterangan Umum ===== -->
        @if(($showBlocks['blok1'] ?? true))
        <div class="block-section" id="section-blok1">
            <div class="block-section-header">
                <div class="block-number">I</div>
                <div>
                    <h2>Blok I: Keterangan Umum</h2>
                    <div class="block-subtitle">General Information</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok1')
            </div>
        </div>
        @endif

        <!-- ===== BLOK II: Pendahuluan ===== -->
        @if(($showBlocks['blok2'] ?? true))
        <div class="block-section" id="section-blok2">
            <div class="block-section-header">
                <div class="block-number">II</div>
                <div>
                    <h2>Blok II: Pendahuluan</h2>
                    <div class="block-subtitle">Keterangan Perusahaan</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok2')
            </div>
        </div>
        @endif

        <!-- ===== BLOK IIIA: Kondisi Perekonomian ===== -->
        @if(!empty($showBlocks['blok3a']))
        <div class="block-section" id="section-blok3a">
            <div class="block-section-header">
                <div class="block-number">IIIA</div>
                <div>
                    <h2>Blok IIIA: Produksi</h2>
                    <div class="block-subtitle">Kondisi Perekonomian (Pelaku Usaha)</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok3a')
            </div>
        </div>
        @endif

        <!-- ===== BLOK IIIB ===== -->
        @if(!empty($showBlocks['blok3bIndustri']) || !empty($showBlocks['blok3bNonIndustri']))
        <div class="block-section" id="section-blok3b">
            <div class="block-section-header">
                <div class="block-number">IIIB</div>
                <div>
                    <h2>Blok IIIB: Pendapatan & Pengeluaran @if(!empty($showBlocks['blok3bIndustri'])) (Industri) @elseif(!empty($showBlocks['blok3bNonIndustri'])) (Non-Industri) @endif</h2>
                    <div class="block-subtitle">Pendapatan, Persediaan, dan Pengeluaran</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok3b', [
                    'showIndustri' => !empty($showBlocks['blok3bIndustri']),
                    'showNonIndustri' => !empty($showBlocks['blok3bNonIndustri'])
                ])
            </div>
        </div>
        @endif

        <!-- ===== BLOK IIIC: Bahan Baku & Bahan Penolong ===== -->
        @if(!empty($showBlocks['blok3c']))
        <div class="block-section" id="section-blok3c">
            <div class="block-section-header">
                <div class="block-number">IIIC</div>
                <div>
                    <h2>Blok IIIC: Bahan Baku &amp; Bahan Penolong</h2>
                    <div class="block-subtitle">Ringkasan Data Bahan Baku, Flow Table, dan Prospek Usaha</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok3c')
            </div>
        </div>
        @endif

        <!-- ===== BLOK IV: Fenomena dan Catatan ===== -->
        @if(!empty($showBlocks['blok4']))
        <div class="block-section" id="section-blok4">
            <div class="block-section-header">
                <div class="block-number">IV</div>
                <div>
                    <h2>Blok IV: Fenomena dan Catatan</h2>
                    <div class="block-subtitle">Barang Modal</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok4')
            </div>
        </div>
        @endif

        <!-- ===== BLOK V: Kondisi dan Prospek Usaha ===== -->
        @if(!empty($showBlocks['blok5']))
        <div class="block-section" id="section-blok5">
            <div class="block-section-header">
                <div class="block-number">V</div>
                <div>
                    <h2>Blok V: Kondisi dan Prospek Usaha</h2>
                    <div class="block-subtitle">Tenaga Kerja</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok5')
            </div>
        </div>
        @endif

        <!-- ===== BLOK VI: Catatan ===== -->
        @if(!empty($showBlocks['blok6']))
        <div class="block-section" id="section-blok6">
            <div class="block-section-header">
                <div class="block-number">VI</div>
                <div>
                    <h2>Blok VI: Catatan</h2>
                    <div class="block-subtitle">Catatan Tambahan</div>
                </div>
            </div>
            <div class="block-section-body">
                @include('bps.sibstr.partials.blok6')
            </div>
        </div>
        @endif

        <!-- Bottom Actions -->
        <div class="bps-bottom-actions">
            @php
                $shown = 0;
                $shown += !empty($showBlocks['blok1']) ? 1 : 0;
                $shown += !empty($showBlocks['blok2']) ? 1 : 0;
                $shown += !empty($showBlocks['blok3a']) ? 1 : 0;
                $shown += (!empty($showBlocks['blok3bIndustri']) || !empty($showBlocks['blok3bNonIndustri'])) ? 1 : 0;
                $shown += !empty($showBlocks['blok3c']) ? 1 : 0;
                $shown += !empty($showBlocks['blok4']) ? 1 : 0;
                $shown += !empty($showBlocks['blok5']) ? 1 : 0;
                $shown += !empty($showBlocks['blok6']) ? 1 : 0;
            @endphp
            <div class="page-summary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Menampilkan {{ $shown }} blok survei yang relevan
            </div>
            <a href="{{ route('bps.sibstr.index') }}" class="bps-back-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar SIBSTR
            </a>
        </div>
    </div>

    <!-- ===== TOC Sidebar ===== -->
    <nav class="bps-toc-sidebar" id="toc-sidebar">
        <div class="bps-toc-header">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            Daftar Isi
        </div>
        <ul class="bps-toc-list">
            @if(!empty($showBlocks['blok1']))
            <li class="bps-toc-item">
                <a href="#section-blok1" class="bps-toc-link active" data-section="section-blok1">
                    <span class="toc-dot"></span>
                    Blok I: Keterangan Umum
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok2']))
            <li class="bps-toc-item">
                <a href="#section-blok2" class="bps-toc-link" data-section="section-blok2">
                    <span class="toc-dot"></span>
                    Blok II: Pendahuluan
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok3a']))
            <li class="bps-toc-item">
                <a href="#section-blok3a" class="bps-toc-link" data-section="section-blok3a">
                    <span class="toc-dot"></span>
                    Blok IIIA: Produksi
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok3bIndustri']) || !empty($showBlocks['blok3bNonIndustri']))
            <li class="bps-toc-item">
                <a href="#section-blok3b" class="bps-toc-link" data-section="section-blok3b">
                    <span class="toc-dot"></span>
                    Blok IIIB: Pendapatan @if(!empty($showBlocks['blok3bIndustri'])) (Industri) @elseif(!empty($showBlocks['blok3bNonIndustri'])) (Non-Industri) @endif
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok3c']))
            <li class="bps-toc-item">
                <a href="#section-blok3c" class="bps-toc-link" data-section="section-blok3c">
                    <span class="toc-dot"></span>
                    Blok IIIC: Bahan Baku
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok4']))
            <li class="bps-toc-item">
                <a href="#section-blok4" class="bps-toc-link" data-section="section-blok4">
                    <span class="toc-dot"></span>
                    Blok IV: Fenomena
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok5']))
            <li class="bps-toc-item">
                <a href="#section-blok5" class="bps-toc-link" data-section="section-blok5">
                    <span class="toc-dot"></span>
                    Blok V: Kondisi Usaha
                </a>
            </li>
            @endif
            @if(!empty($showBlocks['blok6']))
            <li class="bps-toc-item">
                <a href="#section-blok6" class="bps-toc-link" data-section="section-blok6">
                    <span class="toc-dot"></span>
                    Blok VI: Catatan
                </a>
            </li>
            @endif
        </ul>
    </nav>
</div>

<!-- Mobile TOC Toggle Button -->
<button class="bps-toc-mobile-toggle" id="toc-mobile-toggle" title="Daftar Isi">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
    </svg>
</button>

<!-- Scroll to Top Button -->
<button class="bps-scroll-top" id="scroll-top-btn" title="Kembali ke atas">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
    </svg>
</button>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==============================
    // 1. Disable ALL form interactivity
    // ==============================
    window.surveyRoutes = null;

    // Disable all forms
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    });

    // Disable all inputs
    document.querySelectorAll('.block-section-body input, .block-section-body textarea, .block-section-body select').forEach(function(el) {
        el.setAttribute('readonly', 'readonly');
        el.setAttribute('disabled', 'disabled');
        el.style.pointerEvents = 'none';
        el.style.cursor = 'default';
    });

    // Disable all buttons inside block sections
    document.querySelectorAll('.block-section-body button').forEach(function(btn) {
        btn.setAttribute('disabled', 'disabled');
        btn.style.pointerEvents = 'none';
        btn.style.display = 'none';
    });

    // ==============================
    // 2. Scroll Spy for TOC
    // ==============================
    var tocLinks = document.querySelectorAll('.bps-toc-link');
    var sections = document.querySelectorAll('.block-section');

    function updateActiveTocLink() {
        var scrollPos = window.scrollY + 100;
        var currentSection = '';

        sections.forEach(function(section) {
            if (section.offsetTop <= scrollPos) {
                currentSection = section.id;
            }
        });

        tocLinks.forEach(function(link) {
            link.classList.remove('active');
            if (link.getAttribute('data-section') === currentSection) {
                link.classList.add('active');
            }
        });
    }

    var scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateActiveTocLink, 50);
    });

    // TOC smooth scroll
    tocLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            var target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Close mobile TOC
                var sidebar = document.getElementById('toc-sidebar');
                var overlay = document.getElementById('toc-overlay');
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        });
    });

    // ==============================
    // 3. Mobile TOC Toggle
    // ==============================
    var tocToggle = document.getElementById('toc-mobile-toggle');
    var tocSidebar = document.getElementById('toc-sidebar');
    var tocOverlay = document.getElementById('toc-overlay');

    if (tocToggle) {
        tocToggle.addEventListener('click', function() {
            tocSidebar.classList.toggle('mobile-open');
            tocOverlay.classList.toggle('active');
        });
    }

    if (tocOverlay) {
        tocOverlay.addEventListener('click', function() {
            tocSidebar.classList.remove('mobile-open');
            tocOverlay.classList.remove('active');
        });
    }

    // ==============================
    // 4. Scroll to Top Button
    // ==============================
    var scrollTopBtn = document.getElementById('scroll-top-btn');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 600) {
            scrollTopBtn.style.display = 'flex';
        } else {
            scrollTopBtn.style.display = 'none';
        }
    });

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ==============================
    // 5. Show "Belum diisi" for empty fields
    // ==============================
    document.querySelectorAll('.block-section-body input[type="text"], .block-section-body input[type="email"], .block-section-body input[type="url"], .block-section-body input[type="number"], .block-section-body input[type="tel"], .block-section-body textarea').forEach(function(input) {
        if (input.type === 'hidden') return;
        if (!input.value || input.value.trim() === '') {
            input.placeholder = 'Belum diisi';
            input.style.color = '#9ca3af';
            input.style.fontStyle = 'italic';
        }
    });

    // Initial TOC state
    updateActiveTocLink();
});
</script>
@endpush
