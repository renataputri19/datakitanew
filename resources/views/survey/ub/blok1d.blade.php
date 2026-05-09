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
.ptab{border-color:transparent;color:#6b7280;}
.ptab:hover{color:#374151;background:#f9fafb;}
.dark .ptab{color:#9ca3af;}
.dark .ptab:hover{color:#e5e7eb;background:rgba(55,65,81,.4);}
.ptab-active{border-color:#3b82f6 !important;color:#1d4ed8 !important;background:#eff6ff !important;}
.dark .ptab-active{color:#93c5fd !important;background:rgba(30,58,95,.5) !important;}
.ptab2{border-color:transparent;color:#6b7280;}
.ptab2:hover{color:#374151;background:#f9fafb;}
.dark .ptab2{color:#9ca3af;}
.dark .ptab2:hover{color:#e5e7eb;background:rgba(55,65,81,.4);}
.ptab2-active{border-color:#3b82f6 !important;color:#1d4ed8 !important;background:#eff6ff !important;}
.dark .ptab2-active{color:#93c5fd !important;background:rgba(30,58,95,.5) !important;}
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
  {{-- Petunjuk Q20 --}}
  <div class="mb-3" id="petunjuk20Wrap">
    <button type="button" id="petunjuk20Toggle"
      class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
      <svg id="petunjuk20Chevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
      </svg>
      <span id="petunjuk20Label">Lihat petunjuk pengisian pekerja</span>
    </button>
    <div id="petunjuk20Panel" class="hidden mt-2 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
      <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Cara pengisian jumlah pekerja:</p>
      <div class="space-y-2">
        <div class="flex gap-2.5">
          <span class="shrink-0 w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
          <p><strong>Isikan pekerja per 31 Desember 2025</strong> — jumlah pekerja aktif pada tanggal tersebut.</p>
        </div>
        <div class="flex gap-2.5">
          <span class="shrink-0 w-5 h-5 rounded-full bg-gray-400 dark:bg-gray-600 text-white text-xs font-bold flex items-center justify-center">2</span>
          <p>Jika data per 31 Desember 2025 <strong>tidak tersedia</strong>, isikan <strong>rata-rata pekerja per bulan</strong> selama tahun 2025.</p>
        </div>
      </div>
    </div>
  </div>
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
  {{-- Petunjuk tabbed panel --}}
  <div class="mt-4" id="petunjukWrap">
    <button type="button" id="petunjukToggle"
      class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
      <svg id="petunjukChevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
      </svg>
      <span id="petunjukToggleLabel">Lihat petunjuk pengisian pengeluaran</span>
    </button>

    <div id="petunjukPanel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">

      {{-- Tab row --}}
      <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50" id="petunjukTabs">
        <button type="button" data-tab="a" class="ptab ptab-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">a. Upah &amp; Gaji</button>
        <button type="button" data-tab="b" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">b. Biaya Produksi</button>
        <button type="button" data-tab="c" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">c. Barang Terjual</button>
        <button type="button" data-tab="d" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">d. Operasional</button>
        <button type="button" data-tab="e" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">e. Nonoperasional</button>
      </div>

      {{-- Tab panels --}}
      <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

        <div data-panel="a" class="ppanel">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">a. Total upah dan gaji, serta jaminan sosial pegawai</p>
          <div class="space-y-3">
            <div>
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">✓ Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Upah dan gaji pegawai/karyawan yang telah dikeluarkan ringkasan pembayarannya <em>(group certificate)</em></li>
                <li>Komisi dan tips untuk pegawai/karyawan</li>
                <li>Bonus</li>
                <li>Pembayaran cuti tahunan dan jenis cuti lainnya</li>
              </ul>
            </div>
            <div>
              <p class="font-semibold text-red-600 dark:text-red-400 mb-1">✗ Tidak Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Upah dan gaji yang dikapitalisasi</li>
                <li>Pembayaran untuk konsultan dan kontraktor yang berusaha sendiri (bukan karyawan perusahaan), yang dibayarkan dengan komisi</li>
              </ul>
            </div>
          </div>
        </div>

        <div data-panel="b" class="ppanel hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">b. Biaya produksi (pemakaian bahan baku dan penolong)</p>
          <div class="space-y-3">
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-2.5">
              <p class="font-semibold text-amber-700 dark:text-amber-300 mb-1">Catatan:</p>
              <p class="mb-1">Mencakup seluruh nilai barang dan jasa yang digunakan sebagai bahan baku dalam proses produksi, tidak termasuk aset tetap.</p>
              <p class="mb-1">Pada usaha pertanian, ongkos/biaya yang dicatat adalah biaya dari barang/jasa yang benar-benar <strong>DIBELI/DIBAYARKAN</strong> dan <strong>TELAH DIGUNAKAN</strong> (tidak termasuk yang disimpan, diberikan ke pihak lain, dsb) untuk usaha tanaman pangan, hortikultura, perkebunan, peternakan, kehutanan, dan perikanan selama periode tahun 2025.</p>
              <p>Biaya produksi meliputi: benih, bibit, pupuk, pestisida, vaksin, vitamin, obat-obatan, pakan, umpan, wadah, tali, es, garam, konsumsi awak kapal, dll.</p>
            </div>
            <div>
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">✓ Termasuk:</p>
              <ul class="list-disc list-inside pl-1">
                <li>Pembelian bahan yang digunakan dalam proses produksi dan pengemasan</li>
              </ul>
            </div>
            <div>
              <p class="font-semibold text-red-600 dark:text-red-400 mb-1">✗ Tidak Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Pembelian barang yang dikapitalisasi</li>
                <li>Perubahan persediaan</li>
                <li>Pembelian barang jadi untuk dijual kembali</li>
              </ul>
            </div>
          </div>
        </div>

        <div data-panel="c" class="ppanel hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">c. Biaya pembelian barang yang terjual</p>
          <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-2.5 mb-2">
            <p class="font-semibold text-amber-700 dark:text-amber-300 mb-1">Catatan:</p>
            <p>Mencakup seluruh nilai pembelian barang perdagangan yang terjual.</p>
            <p class="mt-1 text-gray-600 dark:text-gray-400"><em>Contoh: nilai pembelian beras yang terjual, nilai pembelian tepung yang terjual, dsb.</em></p>
          </div>
          <p class="text-gray-500 dark:text-gray-400 italic">Khusus untuk usaha perdagangan.</p>
        </div>

        <div data-panel="d" class="ppanel hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">d. Biaya operasional (air, listrik, gas, internet, pulsa, pemeliharaan, biaya angkutan)</p>
          <div class="space-y-3">
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-2.5">
              <p class="font-semibold text-amber-700 dark:text-amber-300 mb-1">Catatan:</p>
              <p>Mencakup biaya-biaya yang tidak secara langsung dalam proses produksi seperti air, listrik, gas, pemeliharaan, serta biaya angkutan.</p>
            </div>
            <div>
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">✓ Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Pengeluaran listrik, bahan bakar dan air</li>
                <li>Pembelian bahan perkantoran umum</li>
                <li>Pembelian komponen dan bahan bakar untuk kendaraan bermotor</li>
                <li>Pembayaran ke pihak lain untuk kargo, delivery, dan jasa angkutan</li>
                <li>Pembayaran sewa operasi (dengan atau tanpa operator)</li>
                <li>Biaya lisensi software komputer yang berumur kurang dari satu tahun (termasuk biaya instalasi oleh provider eksternal)</li>
                <li>Biaya bank selain bunga</li>
              </ul>
            </div>
            <div>
              <p class="font-semibold text-red-600 dark:text-red-400 mb-1">✗ Tidak Termasuk:</p>
              <ul class="list-disc list-inside pl-1">
                <li>Pemeliharaan besar</li>
              </ul>
            </div>
          </div>
        </div>

        <div data-panel="e" class="ppanel hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">e. Biaya nonoperasional</p>
          <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-2.5">
            <p class="font-semibold text-amber-700 dark:text-amber-300 mb-1">Catatan:</p>
            <p>Mencakup biaya bunga, biaya pajak, biaya administrasi, biaya hukum, biaya donasi, biaya restrukturisasi, biaya kerugian di awal, dan biaya lain-lain yang tidak terkait dengan kegiatan operasional.</p>
            <p class="mt-1 text-gray-500 dark:text-gray-400"><em>Seperti: biaya perjalanan yang tidak terkait dengan bisnis.</em></p>
          </div>
        </div>

      </div>
    </div>
  </div>
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
  {{-- Petunjuk Q23 tabbed panel --}}
  <div class="mt-4" id="petunjukPendapatanWrap">
    <button type="button" id="petunjukPendapatanToggle"
      class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
      <svg id="petunjukPendapatanChevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
      </svg>
      <span id="petunjukPendapatanLabel">Lihat petunjuk pengisian pendapatan</span>
    </button>

    <div id="petunjukPendapatanPanel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">

      {{-- Tab row --}}
      <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50">
        <button type="button" data-ptab2="a" class="ptab2 ptab2-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">a. Nilai Produksi/Penjualan</button>
        <button type="button" data-ptab2="b" class="ptab2 shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">b. Pendapatan Lainnya</button>
      </div>

      {{-- Tab panels --}}
      <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

        <div data-ppanel2="a" class="ppanel2">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">a. Nilai produksi/penjualan/pendapatan barang dan jasa</p>
          <div class="space-y-3">
            <div>
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">✓ Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Barang yang dijual baik yang diproduksi sendiri maupun tidak</li>
                <li>Penjualan ekspor <em>(FOB — Free On Board)</em></li>
                <li>Penjualan atau transfer ke rekan bisnis/organisasi atau cabang di luar negeri</li>
                <li>Pendapatan yang diperoleh dari pengangkutan barang yang tidak dijual perusahaan</li>
                <li>Pendapatan jasa perbaikan dan layanan</li>
                <li>Pendapatan dari kontrak, subkontrak, dan komisi</li>
                <li>Pendapatan manajemen dari perusahaan/organisasi terkait maupun tidak</li>
                <li>Pendapatan dari jasa pemasangan</li>
                <li>Pendapatan dari jasa berlangganan dan keanggotaan</li>
                <li>Pendapatan dari jasa iklan</li>
                <li>Pendapatan royalti (hak cipta, hak paten, waralaba, dll)</li>
                <li>Pendapatan dari sewa operasi</li>
              </ul>
            </div>
            <div>
              <p class="font-semibold text-red-600 dark:text-red-400 mb-1">✗ Tidak Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Penjualan aset</li>
                <li>Royalti dari penggunaan lahan di bawah pengaturan sewa mineral</li>
              </ul>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-2.5">
              <p class="font-semibold text-amber-700 dark:text-amber-300 mb-1">Catatan (usaha pertanian):</p>
              <p class="mb-1">Nilai produksi yang dicatat yaitu nilai produksi yang dipanen, dikonsumsi sendiri (termasuk yang digunakan untuk bibit), diolah sendiri, dijual, disimpan, digunakan sebagai upah/gaji pekerja, diberikan kepada pihak lain, hilang, pertambahan bobot ternak, serta nilai pembesaran tanaman kehutanan selama periode tahun 2025 sesuai dengan jenis dan satuan produksi pada masing-masing subsektor yang diusahakan.</p>
              <p>Nilai produksi yang dicakup termasuk nilai produksi ikutan yang dijual.</p>
            </div>
          </div>
        </div>

        <div data-ppanel2="b" class="ppanel2 hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">b. Pendapatan lainnya yang dihasilkan</p>
          <div class="space-y-3">
            <div>
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">✓ Termasuk:</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Sewa/royalti sumber daya alam</li>
                <li>Pendapatan bunga</li>
                <li>Pendapatan dividen</li>
                <li>Pendanaan dari Pemerintah (subsidi, skema magang dan pelatihan)</li>
                <li>Donasi</li>
              </ul>
            </div>
            <div>
              <p class="font-semibold text-red-600 dark:text-red-400 mb-1">✗ Tidak Termasuk:</p>
              <ul class="list-disc list-inside pl-1">
                <li>Pendanaan yang disediakan khusus untuk barang modal tertentu</li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
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

  // Petunjuk Q20 simple toggle
  (function(){
    const toggle = document.getElementById('petunjuk20Toggle');
    const panel  = document.getElementById('petunjuk20Panel');
    const chevron= document.getElementById('petunjuk20Chevron');
    const label  = document.getElementById('petunjuk20Label');
    if (!toggle) return;
    toggle.addEventListener('click', function() {
      const open = !panel.classList.contains('hidden');
      panel.classList.toggle('hidden', open);
      chevron.style.transform = open ? '' : 'rotate(90deg)';
      label.textContent = open ? 'Lihat petunjuk pengisian pekerja' : 'Sembunyikan petunjuk';
    });
  })();

  // Petunjuk tabbed panel
  (function(){
    const toggle = document.getElementById('petunjukToggle');
    const panel  = document.getElementById('petunjukPanel');
    const chevron= document.getElementById('petunjukChevron');
    const label  = document.getElementById('petunjukToggleLabel');
    if(!toggle) return;
    toggle.addEventListener('click', function(){
      const open = !panel.classList.contains('hidden');
      panel.classList.toggle('hidden', open);
      chevron.style.transform = open ? '' : 'rotate(90deg)';
      label.textContent = open ? 'Lihat petunjuk pengisian pengeluaran' : 'Sembunyikan petunjuk pengisian';
    });
    document.querySelectorAll('.ptab').forEach(function(btn){
      btn.addEventListener('click', function(){
        const t = this.dataset.tab;
        document.querySelectorAll('.ptab').forEach(b => b.classList.remove('ptab-active'));
        this.classList.add('ptab-active');
        document.querySelectorAll('.ppanel').forEach(p => p.classList.add('hidden'));
        document.querySelector('[data-panel="' + t + '"]').classList.remove('hidden');
      });
    });
  })();

  // Petunjuk Q23 tabbed panel
  (function(){
    const toggle = document.getElementById('petunjukPendapatanToggle');
    const panel  = document.getElementById('petunjukPendapatanPanel');
    const chevron= document.getElementById('petunjukPendapatanChevron');
    const label  = document.getElementById('petunjukPendapatanLabel');
    if(!toggle) return;
    toggle.addEventListener('click', function(){
      const open = !panel.classList.contains('hidden');
      panel.classList.toggle('hidden', open);
      chevron.style.transform = open ? '' : 'rotate(90deg)';
      label.textContent = open ? 'Lihat petunjuk pengisian pendapatan' : 'Sembunyikan petunjuk pengisian';
    });
    document.querySelectorAll('.ptab2').forEach(function(btn){
      btn.addEventListener('click', function(){
        const t = this.dataset.ptab2;
        document.querySelectorAll('.ptab2').forEach(b => b.classList.remove('ptab2-active'));
        this.classList.add('ptab2-active');
        document.querySelectorAll('.ppanel2').forEach(p => p.classList.add('hidden'));
        document.querySelector('[data-ppanel2="' + t + '"]').classList.remove('hidden');
      });
    });
  })();

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
