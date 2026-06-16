@extends('layouts.bps')

@section('title', 'Detail Survei UB – BPS Dashboard')
@section('description', 'Detail respons Survei UB dalam mode tampilan')

@push('styles')
<style>
/* ── Header ── */
.ub-view-header {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 2px solid #3b82f6;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.dark .ub-view-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #172554 100%);
    border-color: #3b82f6;
}
/* ── Section card ── */
.ub-section-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.dark .ub-section-card { background: #1f2937; border-color: #374151; }
.ub-section-title {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #3b82f6;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dbeafe;
}
.dark .ub-section-title { color: #93c5fd; border-color: #1e3a5f; }
/* ── Field row ── */
.ub-field-row {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 0.5rem 1rem;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.875rem;
}
.dark .ub-field-row { border-color: #374151; }
.ub-field-row:last-child { border-bottom: none; }
.ub-field-label { color: #6b7280; font-weight: 500; min-width: 0; }
.dark .ub-field-label { color: #9ca3af; }
.ub-field-value { color: #111827; font-weight: 500; word-break: break-word; }
.dark .ub-field-value { color: #f9fafb; }
.ub-field-empty { color: #9ca3af; font-style: italic; font-weight: 400; }
@media (max-width: 640px) {
    .ub-field-row { grid-template-columns: 1fr; gap: 0.15rem; }
}
/* ── Status badges ── */
.badge-done { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #d1fae5; color: #065f46; }
.dark .badge-done { background: #064e3b; color: #6ee7b7; }
.badge-progress { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #fef3c7; color: #92400e; }
.dark .badge-progress { background: #78350f; color: #fcd34d; }
.badge-blok-done { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 999px; background: #d1fae5; color: #065f46; font-weight: 600; }
.dark .badge-blok-done { background: #064e3b; color: #6ee7b7; }
.badge-blok-pending { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 999px; background: #f3f4f6; color: #6b7280; font-weight: 600; }
.dark .badge-blok-pending { background: #374151; color: #9ca3af; }
.ub-block-panel { display: none; }
.ub-block-panel.active { display: block; }
/* ── Currency field ── */
.ub-currency-table { width: 100%; border-collapse: collapse; }
.ub-currency-table td { padding: 0.4rem 0.5rem; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6; }
.dark .ub-currency-table td { border-color: #374151; }
.ub-currency-table tr:last-child td { border-bottom: none; font-weight: 700; }
.ub-currency-table .rp { text-align: right; font-variant-numeric: tabular-nums; }
/* ── Progress bar ── */
.blok-check-svg { width: 0.75rem; height: 0.75rem; }
</style>
@endpush

@section('content')
@php
$yn  = [1 => 'Ya', 2 => 'Tidak'];
$kawasanMap = [1=>'Kawasan Ekonomi Khusus (KEK)',2=>'Kawasan Industri (KI)',3=>'Stasiun',4=>'Bandara',5=>'Pelabuhan',6=>'Terminal',7=>'Rest area jalan tol',8=>'Kawasan sentra ekonomi perdesaan/kelurahan',9=>'Kawasan usaha lainnya',10=>'Di luar kawasan'];
$nibAlasanMap = [1=>'Dalam proses pembuatan NIB',2=>'Pengurusan NIB rumit',3=>'Tidak memerlukan NIB',4=>'Tidak tahu tentang NIB',5=>'Lainnya'];
$sbuMap = [1=>'Perseroan (PT/NV/PT Persero/PT Tbk/Perseroan Daerah/Perseroan Perorangan)',2=>'Yayasan',3=>'Koperasi',4=>'Dana Pensiun',5=>'Perum/Perumda',6=>'BUM Desa',7=>'Persekutuan Komanditer (CV)',8=>'Persekutuan Firma (Fa)',9=>'Persekutuan Perdata (Maatschap)',10=>'Kantor Perwakilan Luar Negeri',11=>'Badan Usaha Luar Negeri',12=>'Badan Usaha Lainnya (BLU, PTN-BH dll)',13=>'Bukan Badan Usaha'];
$jkMap  = [1=>'Laki-laki', 2=>'Perempuan'];
$jaringanMap = [1=>'Perusahaan Tunggal',2=>'Kantor Pusat (memiliki cabang)',3=>'Cabang/Unit dari Kantor Pusat dalam negeri',4=>'Perwakilan dari Kantor Pusat luar negeri',5=>'Pabrik/Unit Produksi',6=>'Unit Pembantu/Penunjang'];
$lokasiMap = [1=>'Apotek',2=>'Swalayan',3=>'Los Pasar',4=>'Toko, ruko, dan sejenisnya',5=>'Kedai, stan, tenda',6=>'Bar',7=>'Kelab malam, diskotek',8=>'Kafe',9=>'Restoran, warung makan',10=>'Keliling',11=>'Daring (online)'];
$klasAkomodasiMap = [1=>'Hotel Bintang 1',2=>'Hotel Bintang 2',3=>'Hotel Bintang 3',4=>'Hotel Bintang 4',5=>'Hotel Bintang 5',6=>'Lainnya (hotel nonbintang, vila, dll)'];
$sertHalalMap = [1=>'Ya, oleh BPJPH', 2=>'Ya, bukan oleh BPJPH', 3=>'Belum/tidak', 4=>'Dalam proses'];
$izinEdarMap  = [1=>'Ya, oleh BPOM', 2=>'Ya, bukan oleh BPOM', 3=>'Tidak'];
$mbgMap = [1=>'Ya, sebagai SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)',2=>'Ya, sebagai supplier',3=>'Ya, sebagai penerima manfaat MBG (Sekolah, Puskesmas, Posyandu)',4=>'Ya, peran lainnya',5=>'Tidak terlibat MBG'];
$prlMap = [1=>'Ya, seluruh produk', 2=>'Ya, sebagian produk', 3=>'Tidak'];
$rangeAsetMap = [1=>'s.d. Rp 500 juta',2=>'Lebih dari Rp 500 juta s.d. Rp 1 miliar',3=>'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',4=>'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',5=>'Lebih dari Rp 10 miliar'];
$koperasiJenisMap = [1=>'Open Loop (dapat melayani nonanggota)', 2=>'Close Loop (hanya melayani anggota)'];

$rp = fn($v) => ($v !== null && $v !== '') ? 'Rp '.number_format((float)$v, 0, ',', '.') : '<span class="ub-field-empty">Belum diisi</span>';
$val = fn($v, $map) => ($v && isset($map[$v])) ? $map[$v] : ($v ? $v : '<span class="ub-field-empty">Belum diisi</span>');
$str = fn($v) => ($v !== null && $v !== '') ? e($v) : '<span class="ub-field-empty">Belum diisi</span>';
@endphp

<div class="space-y-4">
    {{-- Back button + header row --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <a href="{{ route('bps.ub.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        <span class="text-sm text-gray-500 dark:text-gray-400">SE2026-L.UB &mdash; ID #{{ $response->id }}</span>
    </div>

    {{-- Header card --}}
    <div class="ub-view-header">
        <div class="flex items-start justify-between gap-4 flex-wrap mb-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-700 dark:text-blue-300 mb-1">Survei Unit Bisnis / Usaha Baru</p>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $response->nama_perusahaan ?: 'Nama Perusahaan Belum Diisi' }}
                </h1>
                @if($response->nama_komersial && $response->nama_komersial !== $response->nama_perusahaan)
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 italic">{{ $response->nama_komersial }}</p>
                @endif
            </div>
            @if($response->is_completed)
                <span class="badge-done">
                    <svg class="blok-check-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Selesai
                </span>
            @else
                <span class="badge-progress">
                    <svg class="blok-check-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Dalam Proses ({{ $response->completionPercent() }}%)
                </span>
            @endif
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div>
                <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 mb-0.5">Pengguna</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $response->user->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $response->user->email }}</p>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 mb-0.5">Lokasi</p>
                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $response->kabupaten_kota ?: '-' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $response->provinsi ?: '' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 mb-0.5">Terakhir Disimpan</p>
                <p class="font-medium text-gray-800 dark:text-gray-200">
                    {{ $response->last_saved_at ? $response->last_saved_at->setTimezone('Asia/Jakarta')->format('d M Y') : '-' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $response->last_saved_at ? $response->last_saved_at->setTimezone('Asia/Jakarta')->format('H:i') . ' WIB' : '' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 mb-0.5">Dibuat</p>
                <p class="font-medium text-gray-800 dark:text-gray-200">
                    {{ $response->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $response->created_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
            </div>
        </div>
        {{-- Block completion pills --}}
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach(['blok1a' => 'I-A', 'blok1b' => 'I-B', 'blok1c' => 'I-C', 'blok1d' => 'I-D', 'blok2' => 'II', 'blok3' => 'III'] as $key => $label)
            @php $field = $key . '_completed'; @endphp
            @if($response->$field)
                <span class="badge-blok-done">
                    <svg class="blok-check-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Blok {{ $label }}
                </span>
            @else
                <span class="badge-blok-pending">Blok {{ $label }}</span>
            @endif
            @endforeach
        </div>
    </div>

@php
$navItems = [
    ['key' => 'panel-1a', 'label' => 'Blok I-A', 'sub' => 'Identitas & Lokasi',       'done' => (bool)$response->blok1a_completed, 'num' => 1],
    ['key' => 'panel-1b', 'label' => 'Blok I-B', 'sub' => 'Kegiatan & Digital',       'done' => (bool)$response->blok1b_completed, 'num' => 2],
    ['key' => 'panel-1c', 'label' => 'Blok I-C', 'sub' => 'Sertifikasi & Kemitraan',  'done' => (bool)$response->blok1c_completed, 'num' => 3],
    ['key' => 'panel-1d', 'label' => 'Blok I-D', 'sub' => 'Pekerja & Keuangan',       'done' => (bool)$response->blok1d_completed, 'num' => 4],
    ['key' => 'panel-2',  'label' => 'Blok II',  'sub' => 'Catatan',                  'done' => (bool)$response->blok2_completed,  'num' => 5],
    ['key' => 'panel-3',  'label' => 'Blok III', 'sub' => 'Keterangan Petugas',       'done' => (bool)$response->blok3_completed,  'num' => 6],
];
$_completedCount = count(array_filter($navItems, fn($b) => $b['done']));
$_totalCount     = count($navItems);
@endphp

    {{-- Main flex layout: left sidebar + content (mirrors survey UB layout) --}}
    <div class="flex gap-5 items-start">

        {{-- ── Left sidebar (lg+) — identical structure to survey.ub.partials.sidebar ── --}}
        <aside class="hidden lg:flex flex-col w-48 flex-shrink-0 self-start sticky top-4">
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-3 py-2.5 bg-blue-50 dark:bg-blue-950/30 border-b border-blue-100 dark:border-blue-800">
              <p class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Navigasi Blok</p>
              <p class="text-[9px] text-blue-500 dark:text-blue-400 mt-0.5">SE2026-L.UB</p>
            </div>
            <nav class="p-1.5 space-y-0.5">
              @foreach($navItems as $blk)
              <a href="javascript:void(0)"
                 data-ub-bps-nav-key="{{ $blk['key'] }}"
                 onclick="ubBpsShowPanel('{{ $blk['key'] }}')"
                 class="flex items-center gap-2 w-full px-2.5 py-2 rounded-xl transition
                        {{ $blk['key'] === 'panel-1a'
                           ? 'bg-blue-600 text-white shadow-sm'
                           : ($blk['done']
                              ? 'text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/30'
                              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700') }}">
                <div class="ub-nav-bubble w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold
                            {{ $blk['done'] ? ($blk['key'] === 'panel-1a' ? 'bg-white/30 text-white' : 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400') : ($blk['key'] === 'panel-1a' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500') }}">
                  @if($blk['done'])
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  @else
                    {{ $blk['num'] }}
                  @endif
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-[11px] font-bold leading-tight">{{ $blk['label'] }}</p>
                  <p class="text-[9px] truncate leading-tight mt-0.5 opacity-75">{{ $blk['sub'] }}</p>
                </div>
              </a>
              @endforeach
            </nav>
            <div class="border-t border-gray-100 dark:border-gray-700 px-3 py-2">
              <a href="{{ route('bps.ub.index') }}"
                 class="flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Daftar Survei UB
              </a>
            </div>
          </div>
        </aside>

        {{-- Content panels --}}
        <div class="flex-1 min-w-0">

            {{-- ════════════════════════════════════════
                 BLOK I-A: Identitas & Lokasi
            ════════════════════════════════════════ --}}
            <div id="panel-1a" class="ub-block-panel active">
                <div class="ub-section-card">
                    <p class="ub-section-title">1–4. Lokasi Perusahaan</p>
                    <div class="ub-field-row"><span class="ub-field-label">Provinsi</span><span class="ub-field-value">{!! $str($response->provinsi) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Kabupaten/Kota</span><span class="ub-field-value">{!! $str($response->kabupaten_kota) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Kecamatan</span><span class="ub-field-value">{!! $str($response->kecamatan) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Kelurahan/Desa</span><span class="ub-field-value">{!! $str($response->kelurahan_desa) !!}</span></div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">5. Nama dan Alamat Perusahaan</p>
                    <div class="ub-field-row"><span class="ub-field-label">Nama Perusahaan</span><span class="ub-field-value">{!! $str($response->nama_perusahaan) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Nama Komersial</span><span class="ub-field-value">{!! $str($response->nama_komersial) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Alamat</span><span class="ub-field-value">{!! $str($response->alamat_perusahaan) !!}</span></div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">RT / RW</span>
                        <span class="ub-field-value">
                            @if($response->rt || $response->rw) RT {{ $response->rt ?: '-' }} / RW {{ $response->rw ?: '-' }}
                            @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    <div class="ub-field-row"><span class="ub-field-label">Kode Pos</span><span class="ub-field-value">{!! $str($response->kode_pos) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Nomor Telepon</span><span class="ub-field-value">{!! $str($response->nomor_telepon) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Nomor HP/WhatsApp</span><span class="ub-field-value">{!! $str($response->nomor_hp) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Email Perusahaan</span><span class="ub-field-value">{!! $str($response->email_perusahaan) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Website</span><span class="ub-field-value">{!! $str($response->homepage) !!}</span></div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jenis Kawasan (d)</span>
                        <span class="ub-field-value">{!! $val($response->jenis_kawasan, $kawasanMap) !!}</span>
                    </div>
                    <div class="ub-field-row"><span class="ub-field-label">Nama Kawasan (e)</span><span class="ub-field-value">{!! $str($response->nama_kawasan) !!}</span></div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">6. Nomor Induk Berusaha (NIB)</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Memiliki NIB?</span>
                        <span class="ub-field-value">{!! $val($response->has_nib, $yn) !!}</span>
                    </div>
                    @if($response->has_nib == 1)
                    <div class="ub-field-row"><span class="ub-field-label">Nomor NIB</span><span class="ub-field-value">{!! $str($response->nib) !!}</span></div>
                    @elseif($response->has_nib == 2)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Alasan tidak memiliki NIB</span>
                        <span class="ub-field-value">{!! $val($response->alasan_tidak_nib, $nibAlasanMap) !!}</span>
                    </div>
                    @endif
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">7. Status Badan Usaha</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Status Badan Usaha (a)</span>
                        <span class="ub-field-value">{!! $val($response->status_badan_usaha, $sbuMap) !!}</span>
                    </div>
                    @if($response->status_badan_usaha == 3)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Koperasi KDKMP? (b)</span>
                        <span class="ub-field-value">{!! $val($response->is_koperasi_kdkmp, $yn) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jenis Koperasi (c)</span>
                        <span class="ub-field-value">{!! $val($response->jenis_koperasi, $koperasiJenisMap) !!}</span>
                    </div>
                    @endif
                    <div class="ub-field-row">
                        <span class="ub-field-label">Laporan/Catatan Keuangan (d)</span>
                        <span class="ub-field-value">{!! $val($response->has_laporan_keuangan, $yn) !!}</span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">8. Pengusaha / Penanggung Jawab</p>
                    <div class="ub-field-row"><span class="ub-field-label">Nama (a)</span><span class="ub-field-value">{!! $str($response->nama_pengusaha) !!}</span></div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jenis Kelamin (b)</span>
                        <span class="ub-field-value">{!! $val($response->jenis_kelamin, $jkMap) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Umur (c)</span>
                        <span class="ub-field-value">
                            @if($response->umur) {{ $response->umur }} tahun @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    <div class="ub-field-row"><span class="ub-field-label">NIK (d)</span><span class="ub-field-value">{!! $str($response->nik) !!}</span></div>
                </div>
            </div>

            {{-- ════════════════════════════════════════
                 BLOK I-B: Kegiatan & Digital
            ════════════════════════════════════════ --}}
            <div id="panel-1b" class="ub-block-panel">
                <div class="ub-section-card">
                    <p class="ub-section-title">9. Kegiatan & Produk Utama</p>
                    <div class="ub-field-row"><span class="ub-field-label">Kegiatan Utama (a)</span><span class="ub-field-value">{!! $str($response->kegiatan_utama) !!}</span></div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Produksi barang di lokasi (b1)</span>
                        <span class="ub-field-value">{!! $val($response->produksi_di_lokasi, $yn) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Layanan makan/minum di tempat (b2)</span>
                        <span class="ub-field-value">{!! $val($response->layanan_makan_minum, $yn) !!}</span>
                    </div>
                    @if($response->produksi_di_lokasi == 2 && $response->layanan_makan_minum == 2 && $response->penjualan_barang !== null)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Penjualan barang (b3)</span>
                        <span class="ub-field-value">{!! $val($response->penjualan_barang, $yn) !!}</span>
                    </div>
                    @endif
                    @if($response->produksi_di_lokasi == 2 && $response->layanan_makan_minum == 2 && $response->penjualan_barang == 2 && $response->aktivitas_jasa_pertanian !== null)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Aktivitas jasa/pertanian (b4)</span>
                        <span class="ub-field-value">{!! $val($response->aktivitas_jasa_pertanian, $yn) !!}</span>
                    </div>
                    @endif
                    @if($response->lokasi_usaha !== null)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Lokasi usaha (c)</span>
                        <span class="ub-field-value">{!! $val($response->lokasi_usaha, $lokasiMap) !!}</span>
                    </div>
                    @endif
                    @if($response->input_produksi)
                    <div class="ub-field-row"><span class="ub-field-label">Input produksi (d)</span><span class="ub-field-value">{!! $str($response->input_produksi) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Proses produksi (e)</span><span class="ub-field-value">{!! $str($response->proses_produksi) !!}</span></div>
                    @endif
                    <div class="ub-field-row"><span class="ub-field-label">Produk Utama (f)</span><span class="ub-field-value">{!! $str($response->produk_utama) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Kode KBLI (g)</span><span class="ub-field-value">{!! $str($response->kode_kbli) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Kategori Lapangan Usaha (h)</span><span class="ub-field-value">{!! $str($response->kategori_lapangan_usaha) !!}</span></div>
                    @if($response->klasifikasi_akomodasi !== null)
                    <div class="ub-field-row"><span class="ub-field-label">Klasifikasi Akomodasi (i)</span><span class="ub-field-value">{!! $val($response->klasifikasi_akomodasi, $klasAkomodasiMap) !!}</span></div>
                    @endif
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">10–11. Jaringan Usaha</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jaringan Usaha (10)</span>
                        <span class="ub-field-value">{!! $val($response->jaringan_usaha, $jaringanMap) !!}</span>
                    </div>
                    @if($response->jaringan_usaha == 2)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jumlah Cabang (10b)</span>
                        <span class="ub-field-value">{{ $response->jumlah_cabang ?? '–' }}</span>
                    </div>
                    @endif
                    @if(in_array($response->jaringan_usaha, [3,4,5,6]))
                    <div class="ub-field-row"><span class="ub-field-label">Nama Kantor Pusat (11)</span><span class="ub-field-value">{!! $str($response->kp_nama) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Alamat Kantor Pusat</span><span class="ub-field-value">{!! $str($response->kp_alamat) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Negara Kantor Pusat</span><span class="ub-field-value">{!! $str($response->kp_negara) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Provinsi Kantor Pusat</span><span class="ub-field-value">{!! $str($response->kp_provinsi) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Kab/Kota Kantor Pusat</span><span class="ub-field-value">{!! $str($response->kp_kabkota) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Email Kantor Pusat</span><span class="ub-field-value">{!! $str($response->kp_email) !!}</span></div>
                    @endif
                </div>
                @if($response->jaringan_usaha != 6)
                <div class="ub-section-card">
                    <p class="ub-section-title">12. Penggunaan Internet dan Teknologi Digital</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Menggunakan internet dalam menjalankan usaha (12a)</span>
                        <span class="ub-field-value">{!! $val($response->uses_internet, $yn) !!}</span>
                    </div>
                    @if($response->uses_internet == 1)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Menerima pesanan barang/jasa (12b1)</span>
                        <span class="ub-field-value">{!! $response->internet_pesanan ? 'Ya' : 'Tidak' !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Produksi barang/jasa (12b2)</span>
                        <span class="ub-field-value">{!! $response->internet_produksi ? 'Ya' : 'Tidak' !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Distribusi barang/jasa (12b3)</span>
                        <span class="ub-field-value">{!! $response->internet_distribusi ? 'Ya' : 'Tidak' !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Membeli bahan baku online (12b4)</span>
                        <span class="ub-field-value">{!! $response->internet_beli_bahan_baku ? 'Ya' : 'Tidak' !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Promosi (12b5)</span>
                        <span class="ub-field-value">{!! $response->internet_promosi ? 'Ya' : 'Tidak' !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Lainnya (12b6)</span>
                        <span class="ub-field-value">{!! $response->internet_lainnya ? 'Ya' : 'Tidak' !!}</span>
                    </div>
                    @endif
                    <div class="ub-field-row">
                        <span class="ub-field-label">Memanfaatkan teknologi digital - AI, IoT, big data, dll (12c)</span>
                        <span class="ub-field-value">{!! $val($response->uses_teknologi_digital, $yn) !!}</span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">13. Ramah Lingkungan</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Memproduksi barang/jasa ramah lingkungan (13a)</span>
                        <span class="ub-field-value">{!! $val($response->produk_ramah_lingkungan, $prlMap) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Menggunakan input untuk perlindungan lingkungan (13b)</span>
                        <span class="ub-field-value">{!! $val($response->uses_input_lingkungan, $yn) !!}</span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">14. Produk Karya Seni, Sastra, Desain, Teknologi, atau Warisan Budaya</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Menggunakan produk karya seni/sastra/desain/teknologi/warisan budaya (14)</span>
                        <span class="ub-field-value">{!! $val($response->uses_karya_seni, $yn) !!}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- ════════════════════════════════════════
                 BLOK I-C: Sertifikasi & Kemitraan
            ════════════════════════════════════════ --}}
            <div id="panel-1c" class="ub-block-panel">
                <div class="ub-section-card">
                    <p class="ub-section-title">15. Sertifikat Halal (BPJPH)</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Apakah menghasilkan produk bersertifikat halal? (a)</span>
                        <span class="ub-field-value">{!! $val($response->sertifikat_halal, $sertHalalMap) !!}</span>
                    </div>
                    @if($response->sertifikat_halal == 1)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jumlah varian produk sudah bersertifikat halal BPJPH (b)</span>
                        <span class="ub-field-value">
                            @if($response->jumlah_produk_halal_bpjph !== null) {{ $response->jumlah_produk_halal_bpjph }} varian @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    @endif
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jumlah varian produk belum bersertifikat halal BPJPH (c)</span>
                        <span class="ub-field-value">
                            @if($response->jumlah_produk_belum_halal_bpjph !== null) {{ $response->jumlah_produk_belum_halal_bpjph }} varian @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">16. Izin Edar (BPOM)</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Apakah memiliki izin edar? (a)</span>
                        <span class="ub-field-value">{!! $val($response->izin_edar, $izinEdarMap) !!}</span>
                    </div>
                    @if($response->izin_edar == 1)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jumlah varian produk dengan izin edar BPOM (b)</span>
                        <span class="ub-field-value">
                            @if($response->jumlah_produk_izin_edar_bpom !== null) {{ $response->jumlah_produk_izin_edar_bpom }} varian @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    @endif
                    <div class="ub-field-row">
                        <span class="ub-field-label">Jumlah varian produk tanpa izin edar BPOM (c)</span>
                        <span class="ub-field-value">
                            @if($response->jumlah_produk_tanpa_izin_edar_bpom !== null) {{ $response->jumlah_produk_tanpa_izin_edar_bpom }} varian @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">17. Kemitraan KDKMP</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Bermitra dengan Koperasi Desa/Kelurahan Merah Putih (KDKMP)</span>
                        <span class="ub-field-value">{!! $val($response->bermitra_kdkmp, $yn) !!}</span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">18. Program Makan Bergizi Gratis (MBG)</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Keterlibatan dalam program MBG</span>
                        <span class="ub-field-value">{!! $val($response->terlibat_mbg, $mbgMap) !!}</span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">19. Penjualan/Pembelian kepada Bukan Penduduk Indonesia (1 Mei 2024 s.d. 31 Agustus 2026)</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Penjualan/pembelian Barang (19a)</span>
                        <span class="ub-field-value">{!! $val($response->ekspor_impor_barang, $yn) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Penjualan/pembelian Jasa (19b)</span>
                        <span class="ub-field-value">{!! $val($response->ekspor_impor_jasa, $yn) !!}</span>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════
                 BLOK I-D: Pekerja & Keuangan
            ════════════════════════════════════════ --}}
            <div id="panel-1d" class="ub-block-panel">
                <div class="ub-section-card">
                    <p class="ub-section-title">20. Jumlah Pekerja</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Laki-laki (a)</span>
                        <span class="ub-field-value">
                            @if($response->pekerja_laki !== null) {{ $response->pekerja_laki }} orang @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Perempuan (b)</span>
                        <span class="ub-field-value">
                            @if($response->pekerja_perempuan !== null) {{ $response->pekerja_perempuan }} orang @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label font-bold">Total (a+b) (c)</span>
                        <span class="ub-field-value font-bold">
                            @php $total = ($response->pekerja_laki ?? 0) + ($response->pekerja_perempuan ?? 0); @endphp
                            @if($response->pekerja_laki !== null || $response->pekerja_perempuan !== null)
                                {{ $total }} orang
                            @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">21. Tahun Mulai Beroperasi</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Tahun berapa perusahaan ini mulai beroperasi secara komersial?</span>
                        <span class="ub-field-value">
                            @if($response->tahun_beroperasi) {{ $response->tahun_beroperasi }} @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">22. Rincian Pengeluaran Tahun 2025 (Rp)</p>
                    <table class="ub-currency-table">
                        @php
                        $expendRows = [
                            'a' => ['label' => 'a. Total upah dan gaji, serta jaminan sosial pegawai', 'field' => 'pengeluaran_upah_gaji'],
                            'b' => ['label' => 'b. Biaya produksi (pemakaian bahan baku dan penolong)', 'field' => 'pengeluaran_biaya_produksi'],
                            'c' => ['label' => 'c. Biaya pembelian barang yang terjual (Khusus usaha perdagangan)', 'field' => 'pengeluaran_pembelian_barang'],
                            'd' => ['label' => 'd. Biaya operasional (air, listrik, gas, internet, pulsa, pemeliharaan, biaya angkutan)', 'field' => 'pengeluaran_operasional'],
                            'e' => ['label' => 'e. Biaya nonoperasional', 'field' => 'pengeluaran_nonoperasional'],
                        ];
                        $totalPengeluaran = 0;
                        foreach ($expendRows as $row) {
                            $totalPengeluaran += (float)($response->{$row['field']} ?? 0);
                        }
                        @endphp
                        @foreach($expendRows as $row)
                        <tr>
                            <td class="text-gray-600 dark:text-gray-400">{{ $row['label'] }}</td>
                            <td class="rp text-gray-900 dark:text-gray-100">
                                @if($response->{$row['field']} !== null)
                                    Rp {{ number_format((float)$response->{$row['field']}, 0, ',', '.') }}
                                @else <span class="ub-field-empty">–</span> @endif
                            </td>
                        </tr>
                        @endforeach
                        <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                            <td class="font-bold">f. Total pengeluaran (a+b+c+d+e)</td>
                            <td class="rp font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">23. Rincian Nilai Produksi/Penjualan/Pendapatan Tahun 2025</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Nilai produksi/penjualan/pendapatan barang dan jasa (a)</span>
                        <span class="ub-field-value">{!! $rp($response->nilai_produksi_barang_jasa) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Pendapatan lainnya yang dihasilkan (b)</span>
                        <span class="ub-field-value">{!! $rp($response->pendapatan_lainnya) !!}</span>
                    </div>
                    @php $totalPendapatan = (float)($response->nilai_produksi_barang_jasa ?? 0) + (float)($response->pendapatan_lainnya ?? 0); @endphp
                    <div class="ub-field-row">
                        <span class="ub-field-label font-bold">Total nilai produksi/penjualan/pendapatan (a+b) (c)</span>
                        <span class="ub-field-value font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Persentase pendapatan dari usaha online (d)</span>
                        <span class="ub-field-value">
                            @if($response->persen_pendapatan_online !== null) {{ $response->persen_pendapatan_online }}% @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">24. Nilai Aset pada 31 Desember 2025</p>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Nilai aset tanah dan bangunan (a)</span>
                        <span class="ub-field-value">{!! $rp($response->nilai_aset_tanah_bangunan) !!}</span>
                    </div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Nilai aset selain tanah dan bangunan (b)</span>
                        <span class="ub-field-value">{!! $rp($response->nilai_aset_lainnya) !!}</span>
                    </div>
                    @php $totalAset = (float)($response->nilai_aset_tanah_bangunan ?? 0) + (float)($response->nilai_aset_lainnya ?? 0); @endphp
                    <div class="ub-field-row">
                        <span class="ub-field-label font-bold">Nilai total aset (a+b) (c)</span>
                        <span class="ub-field-value font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalAset, 0, ',', '.') }}</span>
                    </div>
                    @if($response->range_total_aset !== null)
                    <div class="ub-field-row">
                        <span class="ub-field-label">Rentang nilai total aset (c1)</span>
                        <span class="ub-field-value">{!! $val($response->range_total_aset, $rangeAsetMap) !!}</span>
                    </div>
                    @endif
                    <div class="ub-field-row">
                        <span class="ub-field-label">Luas tanah yang dikuasai untuk kegiatan usaha pada 31 Desember 2025 (d)</span>
                        <span class="ub-field-value">
                            @if($response->luas_tanah !== null) {{ $response->luas_tanah }} m² @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                <div class="ub-section-card">
                    <p class="ub-section-title">25. Susunan Kepemilikan Modal pada 31 Desember 2025 (%)</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Total seluruh komponen harus = 100%</p>
                    @php
                    $modalRows = [
                        'a. Pribadi/Perorangan'                           => $response->modal_pribadi,
                        'b. Lembaga Nonprofit yang Melayani Rumah Tangga' => $response->modal_nonprofit,
                        'c. Korporasi Publik'                             => $response->modal_korporasi_publik,
                        'd. Korporasi Nonpublik'                          => $response->modal_korporasi_nonpublik,
                        'e. Pemerintah'                                   => $response->modal_pemerintah,
                        'f. Asing'                                        => $response->modal_asing,
                    ];
                    $totalModal = array_sum(array_filter(array_values($modalRows), fn($v) => $v !== null));
                    @endphp
                    @foreach($modalRows as $label => $pct)
                    <div class="ub-field-row">
                        <span class="ub-field-label">{{ $label }}</span>
                        <span class="ub-field-value">
                            @if($pct !== null) {{ $pct }}% @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                    @endforeach
                    <div class="ub-field-row">
                        <span class="ub-field-label font-bold">g. Total (a+b+c+d+e+f)</span>
                        <span class="ub-field-value font-bold {{ $totalModal == 100 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">{{ $totalModal }}%</span>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════
                 BLOK II: Catatan
            ════════════════════════════════════════ --}}
            <div id="panel-2" class="ub-block-panel">
                <div class="ub-section-card">
                    <p class="ub-section-title">Blok II — Catatan</p>
                    @if($response->catatan)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">{{ $response->catatan }}</div>
                    @else
                    <p class="text-gray-400 italic text-sm">Catatan belum diisi.</p>
                    @endif
                </div>
            </div>

            {{-- ════════════════════════════════════════
                 BLOK III: Keterangan Petugas
            ════════════════════════════════════════ --}}
            <div id="panel-3" class="ub-block-panel">
                @foreach([
                    ['prefix' => 'ppl', 'title' => 'Pencacah Lapangan (PPL)'],
                    ['prefix' => 'pml', 'title' => 'Pengawas/Pemeriksa Lapangan (PML)'],
                    ['prefix' => 'resp', 'title' => 'Responden'],
                ] as $person)
                @php $p = $person['prefix']; @endphp
                <div class="ub-section-card">
                    <p class="ub-section-title">{{ $person['title'] }}</p>
                    <div class="ub-field-row"><span class="ub-field-label">Nama</span><span class="ub-field-value">{!! $str($response->{$p.'_nama'}) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">NIP</span><span class="ub-field-value">{!! $str($response->{$p.'_nip'}) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Telepon</span><span class="ub-field-value">{!! $str($response->{$p.'_telepon'}) !!}</span></div>
                    <div class="ub-field-row"><span class="ub-field-label">Email</span><span class="ub-field-value">{!! $str($response->{$p.'_email'}) !!}</span></div>
                    <div class="ub-field-row">
                        <span class="ub-field-label">Tanggal</span>
                        <span class="ub-field-value">
                            @if($response->{$p.'_tanggal'}) {{ \Carbon\Carbon::parse($response->{$p.'_tanggal'})->format('d M Y') }} @else <span class="ub-field-empty">Belum diisi</span> @endif
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Mobile FAB & bottom-sheet (lg:hidden) — identical to survey.ub.partials.sidebar ── --}}
<div class="lg:hidden">
  <button id="ub-bps-fab" type="button" aria-label="Buka navigasi blok"
    class="fixed z-40 bottom-5 right-4 flex items-center gap-2.5 pl-3 pr-4 h-12 rounded-full bg-blue-600 text-white shadow-xl hover:bg-blue-700 active:scale-95 transition-transform">
    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
      </svg>
    </div>
    <div class="text-left leading-tight">
      <p class="text-[9px] font-semibold uppercase tracking-wider opacity-80">Navigasi Blok</p>
      <p id="ub-bps-fab-label" class="text-xs font-bold">Blok I-A</p>
    </div>
    <span id="ub-bps-fab-count" class="ml-0.5 flex-shrink-0 text-[10px] font-bold bg-white/25 rounded-full px-1.5 py-0.5 leading-none">
      {{ $_completedCount }}/{{ $_totalCount }}
    </span>
  </button>

  <div id="ub-bps-sheet"
    class="fixed bottom-20 right-4 z-50 w-72 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out origin-bottom-right"
    role="dialog" aria-modal="true" aria-label="Navigasi Blok">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
      <div>
        <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Navigasi Blok</p>
        <p class="text-[10px] text-blue-500 dark:text-blue-400 mt-0.5">SE2026-L.UB</p>
      </div>
      <button id="ub-bps-close" type="button" aria-label="Tutup"
        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <nav class="px-3 py-2 space-y-0.5 overflow-y-auto" style="max-height:55vh;">
      @foreach($navItems as $blk)
      <a href="javascript:void(0)"
         data-ub-bps-mob-nav-key="{{ $blk['key'] }}"
         onclick="ubBpsShowPanel('{{ $blk['key'] }}'); ubBpsCloseSheet();"
         class="flex items-center gap-3 w-full px-3 py-3 rounded-xl transition
                {{ $blk['key'] === 'panel-1a'
                   ? 'bg-blue-600 text-white shadow-sm'
                   : ($blk['done']
                      ? 'text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/30'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700') }}">
        <div class="ub-mob-bubble w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                    {{ $blk['done'] ? ($blk['key'] === 'panel-1a' ? 'bg-white/30 text-white' : 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400') : ($blk['key'] === 'panel-1a' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500') }}">
          @if($blk['done'])
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
          @else
            {{ $blk['num'] }}
          @endif
        </div>
        <div>
          <p class="text-sm font-bold leading-tight">{{ $blk['label'] }}</p>
          <p class="text-xs leading-tight mt-0.5 opacity-75">{{ $blk['sub'] }}</p>
        </div>
      </a>
      @endforeach
    </nav>
    <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3">
      <a href="{{ route('bps.ub.index') }}"
         class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Daftar Survei UB
      </a>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
  var PANELS   = ['panel-1a', 'panel-1b', 'panel-1c', 'panel-1d', 'panel-2', 'panel-3'];
  var NAV_META = @json($navItems);

  // ── Panel switching ─────────────────────────────────────────────────────
  window.ubBpsShowPanel = function (panelId) {
    if (PANELS.indexOf(panelId) === -1) return;

    // 1. Toggle content panels
    PANELS.forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.classList.toggle('active', id === panelId);
    });

    // 2. Update desktop sidebar items
    document.querySelectorAll('[data-ub-bps-nav-key]').forEach(function (a) {
      var key  = a.getAttribute('data-ub-bps-nav-key');
      var meta = NAV_META.find(function (m) { return m.key === key; });
      if (!meta) return;
      var isActive = key === panelId;
      var isDone   = meta.done;
      _applyNavState(a, a.querySelector('.ub-nav-bubble'), isActive, isDone, false);
    });

    // 3. Update mobile sheet items
    document.querySelectorAll('[data-ub-bps-mob-nav-key]').forEach(function (a) {
      var key  = a.getAttribute('data-ub-bps-mob-nav-key');
      var meta = NAV_META.find(function (m) { return m.key === key; });
      if (!meta) return;
      var isActive = key === panelId;
      var isDone   = meta.done;
      _applyNavState(a, a.querySelector('.ub-mob-bubble'), isActive, isDone, true);
    });

    // 4. Update FAB label
    var meta = NAV_META.find(function (m) { return m.key === panelId; });
    if (meta) {
      var lbl = document.getElementById('ub-bps-fab-label');
      if (lbl) lbl.textContent = meta.label;
    }
  };

  function _applyNavState(link, bubble, isActive, isDone, isMobile) {
    // Strip previous colour classes
    var cls = link.className;
    ['bg-blue-600','text-white','shadow-sm',
     'text-green-700','dark:text-green-300','hover:bg-green-50','dark:hover:bg-green-950/30',
     'text-gray-700','dark:text-gray-300','hover:bg-gray-50','dark:hover:bg-gray-700'
    ].forEach(function (c) { link.classList.remove(c); });
    if (bubble) {
      ['bg-white/30','bg-white/20','text-white',
       'bg-green-100','dark:bg-green-900/50','text-green-600','dark:text-green-400',
       'bg-gray-100','dark:bg-gray-700','text-gray-500'
      ].forEach(function (c) { bubble.classList.remove(c); });
    }
    if (isActive) {
      link.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
      if (bubble) bubble.classList.add('bg-white/30', 'text-white');
    } else if (isDone) {
      link.classList.add('text-green-700', 'dark:text-green-300', 'hover:bg-green-50', 'dark:hover:bg-green-950/30');
      if (bubble) bubble.classList.add('bg-green-100', 'dark:bg-green-900/50', 'text-green-600', 'dark:text-green-400');
    } else {
      link.classList.add('text-gray-700', 'dark:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
      if (bubble) bubble.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-500');
    }
  }

  // ── Mobile FAB open / close ──────────────────────────────────────────────────
  var fab      = document.getElementById('ub-bps-fab');
  var sheet    = document.getElementById('ub-bps-sheet');
  var closeBtn = document.getElementById('ub-bps-close');
  if (!fab || !sheet) return;
  var _open = false;

  function openSheet() {
    _open = true;
    sheet.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
    sheet.classList.add('opacity-100', 'scale-100');
  }

  window.ubBpsCloseSheet = function () {
    _open = false;
    sheet.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
    sheet.classList.remove('opacity-100', 'scale-100');
  };

  fab.addEventListener('click', function (e) {
    e.stopPropagation();
    _open ? window.ubBpsCloseSheet() : openSheet();
  });
  if (closeBtn) closeBtn.addEventListener('click', window.ubBpsCloseSheet);

  // Close on outside click / tap
  sheet.addEventListener('click', function (e) { e.stopPropagation(); });
  document.addEventListener('click', function () { if (_open) window.ubBpsCloseSheet(); });
})();
</script>
@endpush
