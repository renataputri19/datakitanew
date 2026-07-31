@extends('layouts.app')

@section('title', 'SIBSTR - Blok IIIA-2')

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
    .field-error { display: none; color: #dc2626; font-size: 0.75rem; margin-top: 0.3rem; }
    .field-error.visible { display: block; }
    .input-invalid { border-color: #ef4444 !important; box-shadow: 0 0 0 2px #fee2e2 !important; }

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
    @include('survey.partials.edit-mode-banner', ['exitUrl' => route('survey.sibstr.entry')])
    @endif

    {{-- ── Survey Header ──────────────────────────────────── --}}
    <div class="survey-header" data-aos="fade-up">
        <h1 class="survey-title">SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)</h1>
        <h2 class="survey-subtitle">BLOK IIIA-2. BAHAN BAKU DAN BAHAN PENOLONG</h2>
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

        {{-- Section 304 Header --}}
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

        {{-- Section 304 Body --}}
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
                Pratinjau Excel
                <span style="font-size:0.75rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">(Ringkasan Baca-Saja — perbarui otomatis saat Anda mengetik)</span>
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

        {{-- ── Form actions ──────────────────────────────────── --}}
        <div class="form-actions" style="margin-top:2rem;">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok3a" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15,18 9,12 15,6"></polyline></svg>
                    Kembali ke Blok IIIA
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
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:    '{{ route("survey.sibstr.blok3a2.autosave",      ["year" => $tahun, "period" => $period]) }}',
    saveAll:     '{{ route("survey.sibstr.blok3a2.save",          ["year" => $tahun, "period" => $period]) }}',
    status:      '{{ route("survey.sibstr.blok3a2.status",        ["year" => $tahun, "period" => $period]) }}',
    backToBlok3a:'{{ route("survey.sibstr.blok3a",                ["year" => $tahun, "period" => $period]) }}',
    nextBlok: @php
        $p3a2Next = (isset($kbliPrefix) && $kbliPrefix >= 10 && $kbliPrefix <= 33)
            ? route('survey.sibstr.blok3b.industri',   ['year' => $tahun, 'period' => $period])
            : route('survey.sibstr.blok3b.nonindustri',['year' => $tahun, 'period' => $period]);
        echo "'{$p3a2Next}'";
    @endphp,
    blok3b_industri:    '{{ route("survey.sibstr.blok3b.industri",    ["year" => $tahun, "period" => $period]) }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri", ["year" => $tahun, "period" => $period]) }}'
};
@endif

window.surveyData = {
    materials: @json($surveyResponse->blok3a2_materials ?? [])
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok3a2.js') }}"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 700, easing: 'ease-in-out', once: true });</script>
@endpush
@endsection
