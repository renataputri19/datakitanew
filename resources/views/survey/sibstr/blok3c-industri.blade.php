@extends('layouts.app')

@section('title', 'SIBSTR - Blok IIIC Industri')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-blok3a.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
    /* ── Section header ───────────────────────────── */
    .section-304-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
        border-radius: 0.75rem 0.75rem 0 0;
        margin-top: 1.5rem;
    }
    .section-304-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #1e40af; }
    .section-304-body {
        border: 1px solid #bfdbfe; border-top: none;
        border-radius: 0 0 0.75rem 0.75rem;
        padding: 1.25rem; background: #fff;
    }
    #materials-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .dark .section-304-header { background: linear-gradient(135deg, #1e3a5f 0%, #1e40af22 100%); border-color: #1d4ed8; }
    .dark .section-304-body { background: var(--gray-800, #1f2937); border-color: #1d4ed8; }

    /* ── Toggle button ────────────────────────────── */
    .section-toggle-btn {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.35rem 0.85rem;
        border-radius: 0.5rem; border: 1px solid #93c5fd;
        background: #fff; color: #1d4ed8;
        font-size: 0.8125rem; font-weight: 600; cursor: pointer;
        transition: background 0.15s, border-color 0.15s, box-shadow 0.15s;
        white-space: nowrap;
    }
    .section-toggle-btn:hover { background: #eff6ff; box-shadow: 0 0 0 2px #bfdbfe; }
    .section-toggle-btn .toggle-chevron { transition: transform 0.25s; flex-shrink: 0; }
    .section-toggle-btn.collapsed .toggle-chevron { transform: rotate(-90deg); }

    /* ── Footnotes ────────────────────────────────── */
    .footnotes-box {
        margin-top: 1rem; padding: 0.875rem 1rem;
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem;
        font-size: 0.8125rem; color: #166534; line-height: 1.8;
    }
    .dark .footnotes-box { background: #14532d22; border-color: #166534; color: #6ee7b7; }

    /* ── DN / LN boxes ────────────────────────────── */
    .dn-ln-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
    .dn-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.625rem; padding: 1rem; }
    .ln-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.625rem; padding: 1rem; }
    .dark .dn-box { background: #78350f22; border-color: #92400e; }
    .dark .ln-box { background: #1e3a5f22; border-color: #1d4ed8; }
    .box-label { font-size: 0.8125rem; font-weight: 700; margin-bottom: 0.75rem; padding-bottom: 0.4rem; border-bottom: 2px solid; }
    .dn-box .box-label { color: #92400e; border-color: #f59e0b; }
    .ln-box .box-label { color: #1e40af; border-color: #3b82f6; }

    /* ── Input validation ─────────────────────────── */
    div.field-error,
    span.field-error { display: none; color: #dc2626; font-size: 0.75rem; margin-top: 0.3rem; }
    div.field-error.visible,
    span.field-error.visible { display: block; }
    .input-invalid { border-color: #ef4444 !important; box-shadow: 0 0 0 2px #fee2e2 !important; }

    /* ── Radio inline & invalid ──────────────────────── */
    .radio-inline { flex-direction: row !important; gap: 1.5rem; flex-wrap: wrap; }
    .radio-group-invalid {
        border: 2px solid #ef4444 !important;
        border-radius: 0.375rem;
        padding: 0.4rem 0.75rem;
        background: #fef2f2;
    }
    .dark .radio-group-invalid { background: #450a0a22; }

    /* ── Delete button ────────────────────────────── */
    .btn-delete-material {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.3rem 0.7rem;
        border-radius: 0.4rem; border: 1px solid #fca5a5;
        background: #fee2e2; color: #b91c1c;
        font-size: 0.75rem; font-weight: 600; cursor: pointer;
        transition: all 0.15s;
    }
    .btn-delete-material:hover { background: #fecaca; border-color: #f87171; transform: scale(1.03); }

    /* ── Delete confirmation modal ────────────────── */
    .delete-overlay {
        display: none; position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);
        align-items: center; justify-content: center;
    }
    .delete-overlay.active { display: flex; }
    .delete-modal-card {
        background: #fff; border-radius: 1rem; padding: 2rem 2rem 1.75rem;
        max-width: 380px; width: 92%; text-align: center;
        box-shadow: 0 24px 64px rgba(0,0,0,0.25);
        animation: popIn 0.22s ease-out;
    }
    .dark .delete-modal-card { background: #1f2937; }
    @keyframes popIn {
        from { opacity:0; transform:scale(0.85) translateY(12px); }
        to   { opacity:1; transform:scale(1) translateY(0); }
    }
    .delete-modal-icon {
        width: 56px; height: 56px; background: #fee2e2; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;
    }
    .delete-modal-card h3 { font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem; }
    .dark .delete-modal-card h3 { color: #f9fafb; }
    .delete-modal-card p { color: #6b7280; font-size: 0.875rem; margin: 0 0 1.5rem; line-height: 1.6; }
    .delete-modal-actions { display: flex; gap: 0.75rem; justify-content: center; }
    .btn-cancel-del {
        padding: 0.55rem 1.4rem; border-radius: 0.5rem;
        border: 1px solid #d1d5db; background: #f9fafb; color: #374151;
        font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s;
    }
    .btn-cancel-del:hover { background: #f3f4f6; }
    .btn-confirm-del {
        padding: 0.55rem 1.4rem; border-radius: 0.5rem;
        border: none; background: #dc2626; color: #fff;
        font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s, transform 0.1s;
    }
    .btn-confirm-del:hover { background: #b91c1c; }
    .btn-confirm-del:active { transform: scale(0.97); }

    /* ── Preview scroll container ─────────────────── */
    .preview-scroll-wrap { position: relative; padding: 0 2rem; }
    .preview-scroll-arrow {
        position: absolute; top: 50%; transform: translateY(-50%);
        width: 34px; height: 34px; border-radius: 50%;
        border: 1px solid #d1d5db; background: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; z-index: 5; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        transition: background 0.15s, box-shadow 0.15s, opacity 0.2s;
        color: #374151; opacity: 1;
    }
    .preview-scroll-arrow:hover { background: #f3f4f6; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .preview-scroll-arrow.hidden-arrow { opacity: 0; pointer-events: none; }
    .preview-scroll-arrow.left-arrow { left: 0; }
    .preview-scroll-arrow.right-arrow { right: 0; }
    .dark .preview-scroll-arrow { background: #374151; border-color: #4b5563; color: #e5e7eb; }
    #blok3a2-preview-table { overflow-x: auto; scroll-behavior: smooth; }
    #blok3a2-preview-table::-webkit-scrollbar { height: 6px; }
    #blok3a2-preview-table::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    #blok3a2-preview-table::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }

    /* ── Material counter badge ───────────────────── */
    .material-counter {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.625rem; height: 1.625rem; border-radius: 9999px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff; font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }

    /* ── Card adding animation ────────────────────── */
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
        .dn-ln-grid { grid-template-columns: 1fr; }
        .preview-scroll-wrap { padding: 0 1.5rem; }
    }

    /* ── Rincian rows: horizontal grid on desktop, vertical on mobile ── */
    .rincian-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 0.5rem;
        align-items: end;
        margin-bottom: 0.5rem;
    }
    @media (max-width: 600px) {
        .rincian-row {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            padding: 0.6rem 3rem 0.6rem 0.6rem;
            margin-bottom: 0.5rem;
            background: #f9fafb;
            border: 1px solid #bfdbfe;
            border-radius: 0.5rem;
        }
        .rincian-row > div:last-child {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding-bottom: 0 !important;
        }
    }
</style>
@endpush

@section('content')

{{-- ══ Delete Confirmation Modal ══════════════════════════════ --}}
<div id="delete-confirm-overlay" class="delete-overlay" role="dialog" aria-modal="true" aria-labelledby="del-modal-title">
    <div class="delete-modal-card">
        <div class="delete-modal-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 id="del-modal-title">Hapus Bahan Ini?</h3>
        <p id="del-modal-desc">Bahan ini akan dihapus secara permanen dari daftar.</p>
        <div class="delete-modal-actions">
            <button type="button" id="delete-cancel-btn" class="btn-cancel-del">Batal</button>
            <button type="button" id="delete-confirm-btn" class="btn-confirm-del">Ya, Hapus</button>
        </div>
    </div>
</div>

<div class="survey-container">
    @if(!empty($isEditMode))
    @include('survey.partials.edit-mode-banner', ['exitUrl' => route('dashboard.surveys.sibstr.results')])
    @endif

    {{-- ── Survey Header ──────────────────────────────────── --}}
    <div class="survey-header" data-aos="fade-up">
        <h1 class="survey-title">SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)</h1>
        <h2 class="survey-subtitle">BLOK IIIC. BAHAN BAKU DAN BAHAN PENOLONG</h2>
        <p class="survey-description">Bahan baku dan bahan penolong yang digunakan dalam proses produksi</p>
        <p class="survey-instruction">
            <strong>Petunjuk:</strong> Isi semua bahan baku dan bahan penolong yang digunakan.
            Klik <em>Tambah Bahan</em> untuk menambah baris baru. Klik judul kartu untuk membuka/menutupnya.
        </p>

        @if(!empty($historicalResponses) && $historicalResponses->isNotEmpty() && !(isset($triwulan) && $triwulan === 1))
        <div style="margin-top:1rem;">
            <button type="button" onclick="openHistDrawer()"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;
                           border-radius:0.625rem;border:2px solid #fbbf24;background:rgba(254,243,199,0.85);
                           color:#92400e;font-size:0.8125rem;font-weight:700;cursor:pointer;
                           transition:background 0.15s,box-shadow 0.15s;box-shadow:0 1px 4px rgba(251,191,36,0.25);"
                    aria-label="Buka panel data historis">
                <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Lihat Data Historis
                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.25rem;height:1.25rem;
                             border-radius:9999px;background:#fbbf24;color:#7c2d12;font-size:0.7rem;font-weight:800;">
                    {{ $historicalResponses->count() }}
                </span>
            </button>
        </div>
        @endif
    </div>

    {{-- ── Autosave status bar ──────────────────────────────── --}}
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    {{-- ── Survey Form ──────────────────────────────────────── --}}
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="150">
        @csrf

        {{-- Section header (materials) --}}
        <div class="section-304-header" id="section-304-header">
            <h3>304. Bahan baku dan bahan penolong yang digunakan</h3>
            <button type="button" id="toggle-304" class="section-toggle-btn"
                    aria-expanded="true" aria-controls="section-304-body"
                    title="Tampilkan/sembunyikan">
                <svg class="toggle-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <span id="toggle-304-label">Tutup</span>
            </button>
        </div>

        {{-- Section body --}}
        <div id="section-304-body" class="section-304-body">

            {{-- Dynamic material cards (rendered by JS) --}}
            <div id="materials-container"></div>

            {{-- Footnotes --}}
            <div class="footnotes-box">
                <p style="margin:0 0 0.3rem;">Apabila bahan baku yang digunakan lebih dari 10 item, dapat menggunakan lembar tambahan dengan format yang sama.</p>
                <p style="margin:0 0 0.3rem;"><strong>*)</strong> Termasuk yang diimpor oleh importir umum atau pihak lain.</p>
                <p style="margin:0;"><strong>**)</strong> Jika negara asal impor lebih dari satu, tuliskan negara dengan nilai impor terbesar.</p>
            </div>

            {{-- Add button --}}
            <div class="add-product-container" style="margin-top:1.25rem;">
                <button type="button" id="add-material-btn" class="btn-add">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Bahan
                </button>
            </div>
        </div>

        {{-- ── Preview section ──────────────────────────────── --}}
        <div class="special-section" id="preview-section" style="margin-top:2rem;" data-aos="fade-up" data-aos-delay="100">
            <h3 class="special-title">
                Ringkasan Data Bahan Baku
                <span style="font-size:0.75rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">(Pratinjau - diperbarui secara otomatis)</span>
            </h3>

            {{-- Scroll wrapper with arrow controls --}}
            <div class="preview-scroll-wrap">
                <button type="button" id="preview-left" class="preview-scroll-arrow left-arrow hidden-arrow" aria-label="Geser kiri" title="Geser kiri">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>

                <div id="blok3a2-preview-table">
                    {{-- Initial server-rendered state --}}
                    @php $initMaterials = $surveyResponse->blok3a2_materials ?? []; @endphp
                    @if(count($initMaterials) > 0)
                        <p style="text-align:center;color:#6b7280;font-size:0.8125rem;padding:0.5rem 0;">
                            <em>Memuat pratinjau…</em>
                        </p>
                    @else
                        <div style="text-align:center;padding:1.5rem;color:#9ca3af;font-size:0.875rem;">
                            <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.5rem;display:block;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Tambahkan bahan baku untuk melihat pratinjau di sini.
                        </div>
                    @endif
                </div>

                <button type="button" id="preview-right" class="preview-scroll-arrow right-arrow" aria-label="Geser kanan" title="Geser kanan">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>

            <p style="margin-top:0.625rem;font-size:0.75rem;color:#9ca3af;">
                Pratinjau ini tidak dapat diedit langsung. Untuk mengubah, perbarui kartu di atas.
            </p>
        </div>

        {{-- ── 318. Nilai Aset ──────────────────────────────── --}}
        <div class="form-section" style="margin-top:2rem;" data-aos="fade-up" data-aos-delay="120">
            <div class="section-header">
                <h3 class="section-title">NILAI ASET</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">318.</span>
                        <span>Nilai aset pada 31 Desember 2025 (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318a_display">a. Tanah dan bangunan</label>
                            <input type="text" id="q318a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q318a]" required>
                            <input type="hidden" name="blok3b_industri[q318a]" id="q318a" value="{{ $surveyResponse->blok3b_industri_data['q318a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318b_display">b. Selain tanah dan bangunan</label>
                            <input type="text" id="q318b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q318b]" required>
                            <input type="hidden" name="blok3b_industri[q318b]" id="q318b" value="{{ $surveyResponse->blok3b_industri_data['q318b'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318c_display">c. Nilai total aset (otomatis jumlah a + b)</label>
                            <input type="text" id="q318c_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q318c]" id="q318c" value="{{ $surveyResponse->blok3b_industri_data['q318c'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318c_range">c1. Jika tidak dapat mengisikan nominal, pilih rentang</label>
                            <select id="q318c_range" name="blok3b_industri[q318c_range]" class="form-control">
                                <option value="">Pilih rentang</option>
                                <option value="1" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '1') ? 'selected' : '' }}>1 s.d. Rp 500 juta</option>
                                <option value="2" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '2') ? 'selected' : '' }}>Lebih dari Rp 500 juta s.d. Rp 1 miliar</option>
                                <option value="3" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '3') ? 'selected' : '' }}>Lebih dari Rp 1 miliar s.d. Rp 5 miliar</option>
                                <option value="4" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '4') ? 'selected' : '' }}>Lebih dari Rp 5 miliar s.d. Rp 10 miliar</option>
                                <option value="5" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '5') ? 'selected' : '' }}>Lebih dari Rp 10 miliar</option>
                            </select>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q318d_area">d. Luas tanah yang digunakan untuk usaha (m persegi)</label>
                            <input type="number" id="q318d_area" name="blok3b_industri[q318d_area]" class="form-control" min="0" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q318d_area'] ?? '' }}" placeholder="0" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                </div>
            </div>
        </div>

        {{-- ── 319. Kepemilikan Modal ───────────────────────── --}}
        <div class="form-section" style="margin-top:1.5rem;" data-aos="fade-up" data-aos-delay="140">
            <div class="section-header">
                <h3 class="section-title">KEPEMILIKAN MODAL</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">319.</span>
                        <span>Susunan kepemilikan modal pada 31 Desember 2025 (%)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319a">a. Pribadi/Perorangan</label>
                            <input type="number" id="q319a" name="blok3b_industri[q319a]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319a'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319b">b. Lembaga Nonprofit yang Melayani Rumah Tangga</label>
                            <input type="number" id="q319b" name="blok3b_industri[q319b]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319b'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319c">c. Korporasi Publik</label>
                            <input type="number" id="q319c" name="blok3b_industri[q319c]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319c'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319d">d. Korporasi Non Publik</label>
                            <input type="number" id="q319d" name="blok3b_industri[q319d]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319d'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319e">e. Pemerintah Pusat</label>
                            <input type="number" id="q319e" name="blok3b_industri[q319e]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319e'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319f">f. Pemerintah Daerah</label>
                            <input type="number" id="q319f" name="blok3b_industri[q319f]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319f'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319g">g. Perusahaan Swasta Nasional</label>
                            <input type="number" id="q319g" name="blok3b_industri[q319g]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319g'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319h">h. Asing</label>
                            <input type="number" id="q319h" name="blok3b_industri[q319h]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319h'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q319i_display">i. Total (otomatis) — harus 100%</label>
                            <input type="number" id="q319i_display" class="form-control readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q319i]" id="q319i" value="{{ $surveyResponse->blok3b_industri_data['q319i'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Total kepemilikan modal harus 100%.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 701-703. Prospek dan Kendala Usaha (tahunan only) ─── --}}
        @if(($triwulan ?? 0) == 0)
        <div class="form-section" id="section-prospek-kendala" style="margin-top:1.5rem;" data-aos="fade-up" data-aos-delay="160">
            <div class="section-header">
                <h3 class="section-title">PROSPEK DAN KENDALA USAHA/PERUSAHAAN</h3>
            </div>
            <div class="form-grid">

                {{-- Q701: Kendala/Kesulitan --}}
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">320.</span>
                        <span>Apakah selama tahun 2025 usaha/perusahaan mengalami kendala/kesulitan berikut?</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow" id="q701a-row">
                            <label class="form-sublabel">a. Permodalan</label>
                            <div class="radio-group" id="q701a-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q320]" id="q701a_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q320']) && $surveyResponse->blok3b_industri_data['q320'] == '1') ? 'checked' : '' }}>
                                    <label for="q701a_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q320]" id="q701a_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q320']) && $surveyResponse->blok3b_industri_data['q320'] == '2') ? 'checked' : '' }}>
                                    <label for="q701a_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q701a-error">Wajib dipilih salah satu.</div>
                        </div>
                        <div class="form-subrow" id="q701b-row">
                            <label class="form-sublabel">b. Bahan baku</label>
                            <div class="radio-group" id="q701b-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q321]" id="q701b_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q321']) && $surveyResponse->blok3b_industri_data['q321'] == '1') ? 'checked' : '' }}>
                                    <label for="q701b_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q321]" id="q701b_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q321']) && $surveyResponse->blok3b_industri_data['q321'] == '2') ? 'checked' : '' }}>
                                    <label for="q701b_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q701b-error">Wajib dipilih salah satu.</div>
                        </div>
                        <div class="form-subrow" id="q701c-row">
                            <label class="form-sublabel">c. Pemasaran</label>
                            <div class="radio-group" id="q701c-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q322]" id="q701c_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q322']) && $surveyResponse->blok3b_industri_data['q322'] == '1') ? 'checked' : '' }}>
                                    <label for="q701c_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q322]" id="q701c_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q322']) && $surveyResponse->blok3b_industri_data['q322'] == '2') ? 'checked' : '' }}>
                                    <label for="q701c_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q701c-error">Wajib dipilih salah satu.</div>
                        </div>
                        <div class="form-subrow" id="q701d-row">
                            <label class="form-sublabel">d. Iklim Usaha</label>
                            <div class="radio-group" id="q701d-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q323]" id="q701d_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q323']) && $surveyResponse->blok3b_industri_data['q323'] == '1') ? 'checked' : '' }}>
                                    <label for="q701d_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q323]" id="q701d_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q323']) && $surveyResponse->blok3b_industri_data['q323'] == '2') ? 'checked' : '' }}>
                                    <label for="q701d_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q701d-error">Wajib dipilih salah satu.</div>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                </div>

                {{-- Q702: Rencana Rekrut/Pengembangan 2026 --}}
                <div class="form-row" id="q702-row">
                    <label class="form-label">
                        <span class="question-number">321.</span>
                        <span>Apakah tahun 2026 usaha/perusahaan berencana merekrut pegawai atau mengembangkan/memperluas usaha?</span>
                    </label>
                    <div class="radio-group" id="q702-group">
                        <div class="radio-option">
                            <input type="radio" name="blok3b_industri[q324]" id="q702_ya" value="1" class="radio-input"
                                   {{ (isset($surveyResponse->blok3b_industri_data['q324']) && $surveyResponse->blok3b_industri_data['q324'] == '1') ? 'checked' : '' }}>
                            <label for="q702_ya" class="radio-label">Ya</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="blok3b_industri[q324]" id="q702_tidak" value="2" class="radio-input"
                                   {{ (isset($surveyResponse->blok3b_industri_data['q324']) && $surveyResponse->blok3b_industri_data['q324'] == '2') ? 'checked' : '' }}>
                            <label for="q702_tidak" class="radio-label">Tidak</label>
                        </div>
                    </div>
                    <div class="form-errors"><div class="field-error" id="q702-error">Wajib dipilih salah satu.</div></div>
                </div>

                {{-- Q703: Strategi Daya Saing --}}
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">322.</span>
                        <span>Strategi perusahaan untuk peningkatan daya saing?</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow" id="q703a-row">
                            <label class="form-sublabel">a. Inovasi (barang dan jasa)</label>
                            <div class="radio-group" id="q703a-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q325]" id="q703a_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q325']) && $surveyResponse->blok3b_industri_data['q325'] == '1') ? 'checked' : '' }}>
                                    <label for="q703a_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q325]" id="q703a_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q325']) && $surveyResponse->blok3b_industri_data['q325'] == '2') ? 'checked' : '' }}>
                                    <label for="q703a_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q703a-error">Wajib dipilih salah satu.</div>
                        </div>
                        <div class="form-subrow" id="q703b-row">
                            <label class="form-sublabel">b. Pengembangan Teknologi</label>
                            <div class="radio-group" id="q703b-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q326]" id="q703b_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q326']) && $surveyResponse->blok3b_industri_data['q326'] == '1') ? 'checked' : '' }}>
                                    <label for="q703b_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q326]" id="q703b_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q326']) && $surveyResponse->blok3b_industri_data['q326'] == '2') ? 'checked' : '' }}>
                                    <label for="q703b_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q703b-error">Wajib dipilih salah satu.</div>
                        </div>
                        <div class="form-subrow" id="q703c-row">
                            <label class="form-sublabel">c. Pemasaran (marketing)</label>
                            <div class="radio-group" id="q703c-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q327]" id="q703c_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q327']) && $surveyResponse->blok3b_industri_data['q327'] == '1') ? 'checked' : '' }}>
                                    <label for="q703c_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q327]" id="q703c_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q327']) && $surveyResponse->blok3b_industri_data['q327'] == '2') ? 'checked' : '' }}>
                                    <label for="q703c_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q703c-error">Wajib dipilih salah satu.</div>
                        </div>
                        <div class="form-subrow" id="q703d-row">
                            <label class="form-sublabel">d. Kemitraan (UMKM, pemerintah, dll)</label>
                            <div class="radio-group" id="q703d-group">
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q328]" id="q703d_ya" value="1" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q328']) && $surveyResponse->blok3b_industri_data['q328'] == '1') ? 'checked' : '' }}>
                                    <label for="q703d_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="blok3b_industri[q328]" id="q703d_tidak" value="2" class="radio-input"
                                           {{ (isset($surveyResponse->blok3b_industri_data['q328']) && $surveyResponse->blok3b_industri_data['q328'] == '2') ? 'checked' : '' }}>
                                    <label for="q703d_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                            <div class="field-error" id="q703d-error">Wajib dipilih salah satu.</div>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                </div>

            </div>
        </div>
        @endif

        {{-- ── Form actions ──────────────────────────────────────── --}}
        <div class="form-actions" style="margin-top:2rem;">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok3b" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15,18 9,12 15,6"></polyline></svg>
                    Kembali ke Blok IIIB
                </button>
                <button type="button" id="save-draft" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17,21 17,13 7,13 7,21"></polyline>
                        <polyline points="7,3 7,8 15,8"></polyline>
                    </svg>
                    Simpan Draft
                </button>
                <button type="button" id="save-complete" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9,18 15,12 9,6"></polyline></svg>
                    Simpan dan Lanjutkan
                </button>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span><span style="color:#ef4444;">*</span> = wajib diisi &nbsp;|&nbsp; Nilai harus bilangan bulat positif</span>
            </div>
        </div>
    </form>
</div>

@if(!empty($historicalResponses) && !(isset($triwulan) && $triwulan === 1))
@include('survey.sibstr.partials.historical-drawer', [
    'historicalResponses' => $historicalResponses,
    'blockKey'            => 'blok3a2',
])
@endif

@push('scripts')
<script>
// Auto-redirect triwulanan users — blok3c is tahunan-only
@if(($triwulan ?? 0) > 0)
window.location.replace('{{ route("survey.sibstr.blok5", ["year" => $tahun, "period" => $period]) }}');
@endif

@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:        '{{ route("survey.sibstr.blok3c.industri.autosave", ["year" => $tahun, "period" => $period]) }}',
    saveAll:         '{{ route("survey.sibstr.blok3c.industri.save",     ["year" => $tahun, "period" => $period]) }}',
    status:          '{{ route("survey.sibstr.blok3c.industri.status",   ["year" => $tahun, "period" => $period]) }}',
    backToBlok3b:    '{{ route("survey.sibstr.blok3b.industri",          ["year" => $tahun, "period" => $period]) }}',
    nextBlok:        '{{ ($triwulan ?? 0) > 0 ? route("survey.sibstr.blok5", ["year" => $tahun, "period" => $period]) : route("survey.sibstr.blok4", ["year" => $tahun, "period" => $period]) }}',
    blok3b_industri: '{{ route("survey.sibstr.blok3b.industri",          ["year" => $tahun, "period" => $period]) }}'
};
@endif

window.surveyData = {
    materials: @json($surveyResponse->blok3a2_materials ?? []),
    blok3b: @json($surveyResponse->blok3b_industri_data ?? [])
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok3a2.js') }}"></script>
<script src="{{ asset('js/survey-blok3c-industri.js') }}"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 700, easing: 'ease-in-out', once: true });</script>
@endpush
@endsection
