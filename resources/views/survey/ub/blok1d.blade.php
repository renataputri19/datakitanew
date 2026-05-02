@extends('layouts.user-dashboard')

@section('title', 'Survei UB — Blok I-D: Pekerja & Keuangan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<style>
.ub-card{background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:1.75rem 2rem;margin-bottom:1.25rem;}
.dark .ub-card{background:#1f2937;border-color:#374151;}
.ub-section-title{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#3b82f6;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #dbeafe;}
.dark .ub-section-title{color:#93c5fd;border-color:#1e3a5f;}
.ub-label{display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.35rem;}
.dark .ub-label{color:#d1d5db;}
.ub-required{color:#ef4444;margin-left:.2rem;}
.ub-input{width:100%;border:1px solid #d1d5db;border-radius:.625rem;padding:.55rem .85rem;font-size:.875rem;color:#111827;background:#fff;transition:border-color .15s;}
.ub-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dark .ub-input{background:#111827;border-color:#4b5563;color:#f9fafb;}
.ub-input.error{border-color:#ef4444;}
.ub-input[readonly]{background:#f9fafb;color:#6b7280;cursor:not-allowed;}
.dark .ub-input[readonly]{background:#374151;color:#9ca3af;}
.ub-hint{font-size:.73rem;color:#6b7280;margin-top:.3rem;line-height:1.4;}
.dark .ub-hint{color:#9ca3af;}
.ub-radio-group{display:flex;flex-wrap:wrap;gap:.5rem .75rem;}
.ub-radio-label{display:inline-flex;align-items:center;gap:.45rem;padding:.4rem .85rem;border:1.5px solid #d1d5db;border-radius:.625rem;font-size:.8125rem;cursor:pointer;transition:all .15s;}
.ub-radio-label:has(input:checked){border-color:#3b82f6;background:#eff6ff;color:#1d4ed8;}
.dark .ub-radio-label{border-color:#4b5563;color:#d1d5db;}
.dark .ub-radio-label:has(input:checked){border-color:#3b82f6;background:#1e3a5f;color:#93c5fd;}
.ub-radio-label input{width:.875rem;height:.875rem;accent-color:#3b82f6;}
.ub-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.ub-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;}
@media(max-width:640px){.ub-grid-2,.ub-grid-3{grid-template-columns:1fr;}}
.ub-stepper{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ub-step.done{background:#dcfce7;color:#15803d;border:1.5px solid #bbf7d0;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.active{background:#dbeafe;color:#1d4ed8;border:1.5px solid #bfdbfe;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.pending{background:#f3f4f6;color:#9ca3af;border:1.5px solid #e5e7eb;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.dark .ub-step.done{background:#14532d;color:#86efac;border-color:#166534;}
.dark .ub-step.active{background:#1e3a5f;color:#93c5fd;border-color:#1d4ed8;}
.dark .ub-step.pending{background:#374151;color:#6b7280;border-color:#4b5563;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.rp-field{position:relative;}
.rp-prefix{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);font-size:.8rem;color:#6b7280;font-weight:600;pointer-events:none;}
.rp-field .ub-input{padding-left:2.5rem;}
.total-row{background:#f0fdf4;border-radius:.5rem;padding:.5rem .85rem;font-size:.875rem;font-weight:600;color:#15803d;}
.dark .total-row{background:#14532d;color:#86efac;}
.modal-percent{font-size:.75rem;color:#6b7280;margin-top:.5rem;}
.dark .modal-percent{color:#9ca3af;}
.ub-hint-btn{display:inline-flex;align-items:center;gap:.35rem;margin-top:.85rem;font-size:.75rem;font-weight:600;color:#3b82f6;background:none;border:none;cursor:pointer;padding:.3rem .6rem;border-radius:.5rem;transition:background .15s;}
.ub-hint-btn:hover{background:#eff6ff;}
.dark .ub-hint-btn{color:#93c5fd;}
.dark .ub-hint-btn:hover{background:#1e3a5f;}
.ub-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem;}
.ub-modal-box{background:#fff;border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,.18);max-width:500px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;animation:ubModalIn .18s ease;}
.dark .ub-modal-box{background:#1f2937;border:1px solid #374151;}
@keyframes ubModalIn{from{opacity:0;transform:translateY(-12px) scale(.97);}to{opacity:1;transform:none;}}
.ub-modal-header{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.25rem .75rem;border-bottom:1px solid #e5e7eb;}
.dark .ub-modal-header{border-color:#374151;}
.ub-modal-title{font-size:.875rem;font-weight:700;color:#111827;}
.dark .ub-modal-title{color:#f9fafb;}
.ub-modal-close{background:none;border:none;cursor:pointer;color:#6b7280;font-size:1.25rem;line-height:1;padding:.2rem .45rem;border-radius:.375rem;transition:background .15s;}
.ub-modal-close:hover{background:#f3f4f6;color:#111827;}
.dark .ub-modal-close:hover{background:#374151;color:#f9fafb;}
.ub-modal-body{padding:1rem 1.25rem 1.25rem;overflow-y:auto;font-size:.8125rem;color:#4b5563;line-height:1.65;}
.dark .ub-modal-body{color:#d1d5db;}
.ub-modal-body p{margin-bottom:.6rem;}
.ub-modal-body p:last-child{margin-bottom:0;}
.ub-modal-body strong{color:#111827;}
.dark .ub-modal-body strong{color:#f9fafb;}
</style>
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>Menu
  </button>
</div>

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Blok I-D — Pekerja &amp; Keuangan</h1>
    <p class="ud-page-description">SE2026-L.UB · Pertanyaan 20–25: Tenaga kerja, tahun beroperasi, pengeluaran, pendapatan, aset, kepemilikan modal</p>
  </div>
  <a href="{{ route('survey.ub.blok1c') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

<div class="flex gap-5 items-start mt-4">
@include('survey.ub.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="globalErr" class="hidden mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/30 dark:border-red-700 dark:text-red-300"></div>

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<form id="survey-form" method="POST" action="{{ ($editMode ?? false) ? route('survey.ub.edit.blok1d.save') : route('survey.ub.blok1d.save') }}" novalidate>
@csrf

{{-- Q20: Tenaga Kerja --}}
<div class="ub-card">
  <p class="ub-section-title">20. Jumlah Pekerja</p>
  <p class="ub-hint mb-3">1. Isikan pekerja per 31 Desember 2025, atau 2. Jika tidak tersedia, isikan rata-rata pekerja per bulan di tahun 2025.</p>
  <div class="ub-grid-3">
    <div>
      <label class="ub-label">a. Laki-laki <span class="ub-required">*</span></label>
      <div class="flex items-center gap-2">
        <input name="pekerja_laki" id="pLaki" type="number" min="0" class="ub-input" style="max-width:120px;" value="{{ old('pekerja_laki',$response->pekerja_laki) }}" placeholder="0">
        <span class="text-sm text-gray-500">orang</span>
      </div>
      <div class="ub-err-msg" data-field="pekerja_laki"></div>
    </div>
    <div>
      <label class="ub-label">b. Perempuan <span class="ub-required">*</span></label>
      <div class="flex items-center gap-2">
        <input name="pekerja_perempuan" id="pPerempuan" type="number" min="0" class="ub-input" style="max-width:120px;" value="{{ old('pekerja_perempuan',$response->pekerja_perempuan) }}" placeholder="0">
        <span class="text-sm text-gray-500">orang</span>
      </div>
      <div class="ub-err-msg" data-field="pekerja_perempuan"></div>
    </div>
    <div>
      <label class="ub-label">c. Total (a+b)</label>
      <div class="flex items-center gap-2">
        <input id="totalPekerja" class="ub-input" style="max-width:120px;" readonly placeholder="0">
        <span class="text-sm text-gray-500">orang</span>
      </div>
    </div>
  </div>
</div>

{{-- Q21: Tahun Beroperasi --}}
<div class="ub-card">
  <p class="ub-section-title">21. Tahun Mulai Beroperasi</p>
  <label class="ub-label">Tahun berapa perusahaan ini mulai beroperasi secara komersial? <span class="ub-required">*</span></label>
  <input name="tahun_beroperasi" type="number" min="1900" max="2026" class="ub-input" style="max-width:120px;" value="{{ old('tahun_beroperasi',$response->tahun_beroperasi) }}" placeholder="2000">
  <div class="ub-err-msg" data-field="tahun_beroperasi"></div>
</div>

{{-- Q22: Pengeluaran --}}
<div class="ub-card">
  <p class="ub-section-title">22. Rincian Pengeluaran Tahun 2025 <span class="ub-required">*</span></p>
  <div class="space-y-3">
    @php
      $expendFields = [
        'pengeluaran_upah_gaji'       => 'a. Total upah dan gaji, serta jaminan sosial pegawai',
        'pengeluaran_biaya_produksi'  => 'b. Biaya produksi (pemakaian bahan baku dan penolong)',
        'pengeluaran_pembelian_barang'=> 'c. Biaya pembelian barang yang terjual (Khusus usaha perdagangan)',
        'pengeluaran_operasional'     => 'd. Biaya operasional (air, listrik, gas, internet, pulsa, pemeliharaan, biaya angkutan)',
        'pengeluaran_nonoperasional'  => 'e. Biaya nonoperasional',
      ];
    @endphp
    @foreach($expendFields as $field => $label)
    <div>
      <label class="ub-label">{{ $label }} <span class="ub-required">*</span></label>
      <div class="rp-field" style="max-width:340px;">
        <span class="rp-prefix">Rp</span>
        <input name="{{ $field }}" id="{{ $field }}" type="text" inputmode="decimal" class="ub-input expend-input" value="{{ old($field, $response->$field ? number_format($response->$field, 0, ',', '.') : '') }}" placeholder="0">
      </div>
      <div class="ub-err-msg" data-field="{{ $field }}"></div>
    </div>
    @endforeach
    <div class="total-row flex items-center justify-between" style="max-width:340px;">
      <span>f. Total pengeluaran (a+b+c+d+e)</span>
      <span id="totalPengeluaran">Rp 0</span>
    </div>
  </div>
  <button type="button" class="ub-hint-btn" data-open-modal="modal-petunjuk-pengeluaran">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Lihat Petunjuk Pengisian
  </button>
</div>

{{-- Q23: Nilai Produksi --}}
<div class="ub-card">
  <p class="ub-section-title">23. Rincian Nilai Produksi/Penjualan/Pendapatan Tahun 2025 <span class="ub-required">*</span></p>
  <div class="space-y-3">
    <div>
      <label class="ub-label">a. Nilai produksi/penjualan/pendapatan barang dan jasa <span class="ub-required">*</span></label>
      <div class="rp-field" style="max-width:340px;">
        <span class="rp-prefix">Rp</span>
        <input name="nilai_produksi_barang_jasa" id="nilaiProduksi" type="text" inputmode="decimal" class="ub-input income-input" value="{{ old('nilai_produksi_barang_jasa', $response->nilai_produksi_barang_jasa ? number_format($response->nilai_produksi_barang_jasa, 0, ',', '.') : '') }}" placeholder="0">
      </div>
      <div class="ub-err-msg" data-field="nilai_produksi_barang_jasa"></div>
    </div>
    <div>
      <label class="ub-label">b. Pendapatan lainnya yang dihasilkan</label>
      <div class="rp-field" style="max-width:340px;">
        <span class="rp-prefix">Rp</span>
        <input name="pendapatan_lainnya" id="pendapatanLain" type="text" inputmode="decimal" class="ub-input income-input" value="{{ old('pendapatan_lainnya', $response->pendapatan_lainnya ? number_format($response->pendapatan_lainnya, 0, ',', '.') : '') }}" placeholder="0">
      </div>
    </div>
    <div class="total-row flex items-center justify-between" style="max-width:340px;">
      <span>c. Total nilai produksi/penjualan/pendapatan (a+b)</span>
      <span id="totalPendapatan">Rp 0</span>
    </div>
    <div>
      <label class="ub-label">d. Persentase pendapatan dari usaha online</label>
      <div class="flex items-center gap-2" style="max-width:160px;">
        <input name="persen_pendapatan_online" type="number" min="0" max="100" step="0.01" class="ub-input" value="{{ old('persen_pendapatan_online',$response->persen_pendapatan_online) }}" placeholder="0">
        <span class="text-sm text-gray-500 font-semibold">%</span>
      </div>
    </div>
  </div>
  <button type="button" class="ub-hint-btn" data-open-modal="modal-petunjuk-pendapatan">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Lihat Petunjuk Pengisian
  </button>
</div>

{{-- Q24: Aset --}}
<div class="ub-card">
  <p class="ub-section-title">24. Nilai Aset pada 31 Desember 2025 <span class="ub-required">*</span></p>
  <div class="space-y-3">
    <div>
      <label class="ub-label">a. Nilai aset tanah dan bangunan <span class="ub-required">*</span></label>
      <div class="rp-field" style="max-width:340px;">
        <span class="rp-prefix">Rp</span>
        <input name="nilai_aset_tanah_bangunan" id="asetTanah" type="text" inputmode="decimal" class="ub-input aset-input" value="{{ old('nilai_aset_tanah_bangunan', $response->nilai_aset_tanah_bangunan ? number_format($response->nilai_aset_tanah_bangunan, 0, ',', '.') : '') }}" placeholder="0">
      </div>
      <div class="ub-err-msg" data-field="nilai_aset_tanah_bangunan"></div>
    </div>
    <div>
      <label class="ub-label">b. Nilai aset selain tanah dan bangunan <span class="ub-required">*</span></label>
      <div class="rp-field" style="max-width:340px;">
        <span class="rp-prefix">Rp</span>
        <input name="nilai_aset_lainnya" id="asetLain" type="text" inputmode="decimal" class="ub-input aset-input" value="{{ old('nilai_aset_lainnya', $response->nilai_aset_lainnya ? number_format($response->nilai_aset_lainnya, 0, ',', '.') : '') }}" placeholder="0">
      </div>
      <div class="ub-err-msg" data-field="nilai_aset_lainnya"></div>
    </div>
    <div class="total-row flex items-center justify-between" style="max-width:340px;">
      <span>c. Nilai total aset (a+b)</span>
      <span id="totalAset">Rp 0</span>
    </div>
    <div>
      <label class="ub-label">c1. Jika tidak dapat mengisikan nilai nominal, pilih nilai total aset dalam rentang:</label>
      <div class="ub-radio-group mt-1">
        @foreach([1=>'s.d. Rp 500 juta',2=>'Lebih dari Rp 500 juta s.d. Rp 1 miliar',3=>'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',4=>'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',5=>'Lebih dari Rp 10 miliar'] as $val=>$lbl)
        <label class="ub-radio-label"><input type="radio" name="range_total_aset" value="{{ $val }}" {{ old('range_total_aset',$response->range_total_aset)==$val?'checked':'' }}> {{ $val }}. {{ $lbl }}</label>
        @endforeach
      </div>
    </div>
    <div>
      <label class="ub-label">d. Luas tanah yang dikuasai dan digunakan untuk kegiatan usaha pada 31 Desember 2025 <span class="ub-required">*</span></label>
      <div class="flex items-center gap-2" style="max-width:200px;">
        <input name="luas_tanah" type="number" min="0" step="0.01" class="ub-input" value="{{ old('luas_tanah',$response->luas_tanah) }}" placeholder="0">
        <span class="text-sm text-gray-500 font-semibold">m²</span>
      </div>
      <div class="ub-err-msg" data-field="luas_tanah"></div>
    </div>
  </div>
</div>

{{-- Q25: Kepemilikan Modal --}}
<div class="ub-card">
  <p class="ub-section-title">25. Susunan Kepemilikan Modal pada 31 Desember 2025 <span class="ub-required">*</span></p>
  <p class="ub-hint mb-3">Total seluruh komponen harus = 100%</p>
  <div class="ub-grid-2">
    @php
      $modalFields = [
        'modal_pribadi'             => 'a. Pribadi/Perorangan',
        'modal_nonprofit'           => 'b. Lembaga Nonprofit yang Melayani Rumah Tangga',
        'modal_korporasi_publik'    => 'c. Korporasi Publik',
        'modal_korporasi_nonpublik' => 'd. Korporasi Nonpublik',
        'modal_pemerintah'          => 'e. Pemerintah',
        'modal_asing'               => 'f. Asing',
      ];
    @endphp
    @foreach($modalFields as $field => $label)
    <div>
      <label class="ub-label">{{ $label }}</label>
      <div class="flex items-center gap-2" style="max-width:140px;">
        <input name="{{ $field }}" type="number" min="0" max="100" step="0.01" class="ub-input modal-input" value="{{ old($field,$response->$field) }}" placeholder="0">
        <span class="text-sm text-gray-500 font-semibold">%</span>
      </div>
    </div>
    @endforeach
  </div>
  <div class="mt-3 flex items-center gap-3">
    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">g. Total (a+b+c+d+e+f):</span>
    <span id="totalModal" class="text-sm font-bold text-blue-600 dark:text-blue-400">0%</span>
    <span id="modalAlert" class="hidden text-xs text-red-600 dark:text-red-400">⚠ Total harus 100%</span>
  </div>
</div>

<div class="flex items-center justify-between mt-6 mb-8">
  <a href="{{ route('survey.ub.blok1c') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali ke Blok I-C
  </a>
  <button type="submit" id="submitBtn" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
    Simpan &amp; Lanjut
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
</div>
</form>
</div>
</div>

{{-- Petunjuk Modal: Pengeluaran (Q22) --}}
<div id="modal-petunjuk-pengeluaran" class="ub-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="mpe-title" style="display:none;">
  <div class="ub-modal-box">
    <div class="ub-modal-header">
      <span id="mpe-title" class="ub-modal-title">Petunjuk Pengisian — Rincian Pengeluaran</span>
      <button type="button" class="ub-modal-close" data-close-modal aria-label="Tutup">&#215;</button>
    </div>
    <div class="ub-modal-body">
      <p><strong>a. Upah dan gaji:</strong> Termasuk komisi, tips, bonus, cuti. Tidak termasuk upah/gaji yang dikapitalisasi.</p>
      <p><strong>b. Biaya produksi:</strong> Nilai barang dan jasa sebagai bahan baku. Tidak termasuk aset tetap atau perubahan persediaan.</p>
      <p><strong>c. Pembelian barang terjual:</strong> Khusus usaha perdagangan — nilai pembelian barang perdagangan yang terjual.</p>
      <p><strong>d. Biaya operasional:</strong> Listrik, bahan bakar, air, pemeliharaan, angkutan, sewa operasi, lisensi software &lt;1 tahun.</p>
      <p><strong>e. Biaya nonoperasional:</strong> Bunga, pajak, administrasi, hukum, donasi, restrukturisasi, biaya lain-lain.</p>
    </div>
  </div>
</div>

{{-- Petunjuk Modal: Pendapatan (Q23) --}}
<div id="modal-petunjuk-pendapatan" class="ub-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="mpp-title" style="display:none;">
  <div class="ub-modal-box">
    <div class="ub-modal-header">
      <span id="mpp-title" class="ub-modal-title">Petunjuk Pengisian — Nilai Produksi/Pendapatan</span>
      <button type="button" class="ub-modal-close" data-close-modal aria-label="Tutup">&#215;</button>
    </div>
    <div class="ub-modal-body">
      <p><strong>a. Nilai produksi:</strong> Termasuk barang yang dijual (diproduksi sendiri/tidak), ekspor (FOB), pendapatan jasa perbaikan, kontrak/subkontrak, royalti, sewa operasi. Tidak termasuk penjualan aset.</p>
      <p><strong>b. Pendapatan lainnya:</strong> Sewa/royalti sumber daya alam, pendapatan bunga, dividen, subsidi pemerintah, donasi.</p>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  // Format number with dots
  function parseRp(val){ return parseFloat((val||'').replace(/\./g,'').replace(',','.')) || 0; }
  function formatRp(n){ return 'Rp ' + n.toLocaleString('id-ID'); }

  // Auto-format currency inputs
  function setupCurrencyInput(el){
    el.addEventListener('blur', function(){
      const n = parseRp(this.value);
      this.value = n > 0 ? n.toLocaleString('id-ID') : '';
    });
    el.addEventListener('focus', function(){
      const n = parseRp(this.value);
      this.value = n > 0 ? String(n) : '';
    });
  }
  document.querySelectorAll('.expend-input,.income-input,.aset-input').forEach(setupCurrencyInput);

  // Pekerja total
  function updatePekerja(){
    const l = parseInt(document.getElementById('pLaki').value)||0;
    const p = parseInt(document.getElementById('pPerempuan').value)||0;
    document.getElementById('totalPekerja').value = l + p;
  }
  ['pLaki','pPerempuan'].forEach(id => document.getElementById(id)?.addEventListener('input', updatePekerja));
  updatePekerja();

  // Pengeluaran total
  function updatePengeluaran(){
    const ids = ['pengeluaran_upah_gaji','pengeluaran_biaya_produksi','pengeluaran_pembelian_barang','pengeluaran_operasional','pengeluaran_nonoperasional'];
    const total = ids.reduce((sum, id) => sum + parseRp(document.getElementById(id)?.value), 0);
    document.getElementById('totalPengeluaran').textContent = formatRp(total);
  }
  document.querySelectorAll('.expend-input').forEach(el => el.addEventListener('input', updatePengeluaran));
  updatePengeluaran();

  // Pendapatan total
  function updatePendapatan(){
    const a = parseRp(document.getElementById('nilaiProduksi')?.value);
    const b = parseRp(document.getElementById('pendapatanLain')?.value);
    document.getElementById('totalPendapatan').textContent = formatRp(a + b);
  }
  document.querySelectorAll('.income-input').forEach(el => el.addEventListener('input', updatePendapatan));
  updatePendapatan();

  // Aset total
  function updateAset(){
    const a = parseRp(document.getElementById('asetTanah')?.value);
    const b = parseRp(document.getElementById('asetLain')?.value);
    document.getElementById('totalAset').textContent = formatRp(a + b);
  }
  document.querySelectorAll('.aset-input').forEach(el => el.addEventListener('input', updateAset));
  updateAset();

  // Modal total
  function updateModal(){
    const total = Array.from(document.querySelectorAll('.modal-input')).reduce((sum, el) => sum + (parseFloat(el.value)||0), 0);
    const t = document.getElementById('totalModal');
    const a = document.getElementById('modalAlert');
    t.textContent = total.toFixed(2) + '%';
    const ok = Math.abs(total - 100) < 0.01;
    t.className = 'text-sm font-bold ' + (ok ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400');
    a.classList.toggle('hidden', ok || total === 0);
  }
  document.querySelectorAll('.modal-input').forEach(el => el.addEventListener('input', updateModal));
  updateModal();

})();

  // Info modal open/close
  (function(){
    function openModal(id){var el=document.getElementById(id);if(el){el.style.display='flex';document.body.style.overflow='hidden';}}
    function closeAll(){document.querySelectorAll('.ub-modal-overlay').forEach(function(m){m.style.display='none';});document.body.style.overflow='';}
    document.addEventListener('click',function(e){
      var btn=e.target.closest('[data-open-modal]');
      if(btn){openModal(btn.dataset.openModal);return;}
      var close=e.target.closest('[data-close-modal]');
      if(close){var o=close.closest('.ub-modal-overlay');if(o){o.style.display='none';document.body.style.overflow='';}return;}
      if(e.target.classList&&e.target.classList.contains('ub-modal-overlay')){e.target.style.display='none';document.body.style.overflow='';}
    });
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closeAll();});
  })();
</script>
<script>
window.surveyRoutes = {
    autoSave: '{{ route("survey.ub.blok1d.autosave") }}',
    saveAll:  '{{ ($editMode ?? false) ? route("survey.ub.edit.blok1d.save") : route("survey.ub.blok1d.save") }}',
    status:   '{{ route("survey.ub.blok1d.status") }}',
    nextBlok: '{{ route("survey.ub.blok2") }}',
    showGuidanceNearSubmit: false
};
</script>
<script src="{{ asset('js/survey-ub-blok1d.js') }}"></script>
<script src="{{ asset('js/survey.js') }}"></script>
@endpush
@endsection
