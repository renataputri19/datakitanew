@extends('layouts.bps')

@section('title', 'Data Survei Konstruksi - BPS Dashboard')
@section('description', 'Daftar semua respons Survei Perusahaan Konstruksi')

@push('styles')
{{-- Reuses the same self-contained styles as the other BPS data pages, so the
     four listings look identical without depending on a fresh npm run build. --}}
<style>
.ub-bps-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; box-shadow:0 1px 2px 0 rgba(0,0,0,.05); overflow:hidden; }
.dark .ub-bps-card { background:#1f2937; border-color:#374151; }
.ub-stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.5rem; margin-bottom:2rem; }
.ub-stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.5rem; box-shadow:0 1px 2px 0 rgba(0,0,0,.05); }
.dark .ub-stat-card { background:#1f2937; border-color:#374151; }
.ub-stat-icon { width:3rem; height:3rem; border-radius:9999px; display:flex; align-items:center; justify-content:center; margin-right:1rem; flex-shrink:0; }
.ub-table { width:100%; border-collapse:collapse; }
.ub-table thead th { background:#f9fafb; padding:.75rem 1rem; text-align:left; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; border-bottom:1px solid #e5e7eb; }
.dark .ub-table thead th { background:#111827; color:#9ca3af; border-color:#374151; }
.ub-table tbody td { padding:1rem; border-bottom:1px solid #e5e7eb; color:#374151; }
.dark .ub-table tbody td { border-color:#374151; color:#d1d5db; }
.ub-table tbody tr:hover { background:#f9fafb; }
.dark .ub-table tbody tr:hover { background:#111827; }
.status-badge { display:inline-flex; align-items:center; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:500; }
.status-completed { background:#d1fae5; color:#065f46; }
.dark .status-completed { background:#064e3b; color:#6ee7b7; }
.status-in-progress { background:#fef3c7; color:#92400e; }
.dark .status-in-progress { background:#78350f; color:#fcd34d; }
.ub-filter-section { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.5rem; margin-bottom:1.5rem; }
.dark .ub-filter-section { background:#111827; border-color:#374151; }
.btn-filter { padding:.5rem 1rem; background:#3b82f6; color:#fff; border-radius:.5rem; font-size:.875rem; font-weight:500; border:none; cursor:pointer; transition:background .2s; }
.btn-filter:hover { background:#2563eb; }
.btn-reset { padding:.5rem 1rem; background:#6b7280; color:#fff; border-radius:.5rem; font-size:.875rem; font-weight:500; border:none; cursor:pointer; transition:background .2s; text-decoration:none; display:inline-flex; align-items:center; gap:.25rem; }
.btn-reset:hover { background:#4b5563; }
.btn-view { padding:.375rem .75rem; background:#3b82f6; color:#fff; border-radius:.375rem; font-size:.75rem; font-weight:500; text-decoration:none; display:inline-flex; align-items:center; gap:.25rem; transition:background .2s; }
.btn-view:hover { background:#2563eb; }
.pagination-container { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-top:1px solid #e5e7eb; background:#f9fafb; flex-wrap:wrap; gap:.75rem; }
.dark .pagination-container { border-color:#374151; background:#111827; }
.empty-state { text-align:center; padding:4rem 2rem; }
.k-period-pill { display:inline-flex; align-items:center; padding:.15rem .55rem; border-radius:.375rem; font-size:.7rem; font-weight:600; background:#fef3c7; color:#92400e; }
.dark .k-period-pill { background:#78350f; color:#fcd34d; }
/* The two instruments sit in one list, so the quarterly rows read differently. */
.k-period-pill.k-pill-tw { background:#dbeafe; color:#1e40af; }
.dark .k-period-pill.k-pill-tw { background:#1e3a8a; color:#bfdbfe; }
.k-money { font-variant-numeric:tabular-nums; white-space:nowrap; }
</style>
@endpush

@section('content')
@php
  use App\Support\SibstrFormat;
  use App\Support\KonstruksiBlokPath;
@endphp
<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex items-start justify-between gap-4 flex-wrap">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Data Survei Konstruksi</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Daftar respons Survei Perusahaan Konstruksi (SKTH) dari seluruh pengguna
      </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
        SURVEI KONSTRUKSI
      </span>
      <button type="button" class="btn-export-excel" onclick="bpsOpenExportModal()">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export Excel
      </button>
    </div>
  </div>

  {{-- Statistics Cards --}}
  <div class="ub-stats-grid">
    @foreach([
      ['label' => 'Total Respons',  'value' => $stats['total'],       'color' => '#3b82f6',
       'sub' => $stats['tahunan'] . ' SKTH · ' . $stats['triwulanan'] . ' SKTR'],
      ['label' => 'Selesai',        'value' => $stats['completed'],   'color' => '#22c55e'],
      ['label' => 'Dalam Proses',   'value' => $stats['in_progress'], 'color' => '#f59e0b'],
    ] as $card)
      <div class="ub-stat-card">
        <div class="flex items-center">
          <div class="ub-stat-icon" style="background:{{ $card['color'] }}1a;color:{{ $card['color'] }};">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $card['label'] }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($card['value'], 0, ',', '.') }}</p>
            @if(!empty($card['sub']))
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $card['sub'] }}</p>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Filters --}}
  <div class="ub-filter-section">
    <form method="GET" action="{{ route('bps.konstruksi.index') }}" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="lg:col-span-2">
          <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
          <input type="text" id="search" name="search" value="{{ request('search') }}"
                 placeholder="Nama perusahaan, NUS, NIB, lokasi, pengguna..."
                 class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
          <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
          <select id="period" name="period"
                  class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Semua Periode</option>
            @foreach($periodOptions as $key => $label)
              <option value="{{ $key }}" {{ request('period') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select id="status" name="status"
                  class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Semua Status</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
          </select>
        </div>
        <div>
          <label for="sort_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Urutkan</label>
          <select id="sort_by" name="sort_by"
                  class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="updated_at" {{ request('sort_by', 'updated_at') === 'updated_at' ? 'selected' : '' }}>Terakhir Diperbarui</option>
            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
            <option value="nama_perusahaan" {{ request('sort_by') === 'nama_perusahaan' ? 'selected' : '' }}>Nama Perusahaan</option>
          </select>
        </div>
      </div>
      <div class="flex gap-2 flex-wrap items-end">
        <div>
          <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tampilkan</label>
          <select id="per_page" name="per_page"
                  class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100">
            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 per halaman</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
          </select>
        </div>
        <button type="submit" class="btn-filter">Terapkan Filter</button>
        <a href="{{ route('bps.konstruksi.index') }}" class="btn-reset">Reset Filter</a>
      </div>
    </form>
  </div>

  {{-- Data Table --}}
  <div class="ub-bps-card">
    @if($surveyResponses->count() > 0)
      <div class="overflow-x-auto">
        <table class="ub-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Perusahaan</th>
              <th>Periode</th>
              <th>Pengguna</th>
              <th>Lokasi</th>
              <th>Proyek</th>
              <th>Nilai Konstruksi<br><span style="font-weight:400;text-transform:none;">(000 Rp) · SKTR = triwulan berjalan</span></th>
              <th>Kemajuan</th>
              <th>Status</th>
              <th>Terakhir Diperbarui</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($surveyResponses as $index => $resp)
              @php
                $pct      = $resp->completionPercent();
                $barColor = $pct === 100 ? '#22c55e' : ($pct >= 50 ? '#3b82f6' : '#fbbf24');
                $periode  = KonstruksiBlokPath::periodLabel($resp->tahun, $resp->triwulan);
                $isTw     = $resp->isTriwulanan();
              @endphp
              <tr>
                <td class="font-medium">{{ $surveyResponses->firstItem() + $index }}</td>
                <td>
                  <div class="font-medium text-gray-900 dark:text-white">{{ $resp->nama_perusahaan ?: '-' }}</div>
                  @if($resp->nus)
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NUS {{ $resp->nus }}</div>
                  @endif
                </td>
                <td>
                  <span class="k-period-pill {{ $isTw ? 'k-pill-tw' : '' }}">{{ $periode }}</span>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $resp->instrumen() }}</div>
                </td>
                <td>
                  <div class="text-sm">{{ $resp->user->name ?? '-' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ $resp->user->email ?? '' }}</div>
                </td>
                <td>
                  @if($resp->kabupaten_kota)<div class="text-sm">{{ $resp->kabupaten_kota }}</div>@endif
                  @if($resp->provinsi)<div class="text-xs text-gray-500 dark:text-gray-400">{{ $resp->provinsi }}</div>@endif
                  @if(!$resp->kabupaten_kota && !$resp->provinsi)<span class="text-gray-400">-</span>@endif
                </td>
                {{-- Period-aware: a quarterly row keeps its projects in a
                     different column, and reading the annual one would show 0. --}}
                <td class="text-sm">
                  {{-- Both instruments now record keberadaan proyek, so the
                       dash means the same thing on either row. --}}
                  @if(!$resp->proyekBlokApplies())
                    <span class="text-gray-400" title="Responden menyatakan tidak ada proyek konstruksi">—</span>
                  @else
                    {{ $resp->proyekCount() }}
                  @endif
                </td>
                <td class="text-sm k-money">{{ SibstrFormat::idr($resp->nilaiKonstruksiTotal()) }}</td>
                <td>
                  <div class="flex items-center gap-2" style="min-width:90px;">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                      <div class="h-2 rounded-full" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $pct }}%</span>
                  </div>
                </td>
                <td>
                  @if($resp->is_completed)
                    <span class="status-badge status-completed">Selesai</span>
                  @else
                    <span class="status-badge status-in-progress">Dalam Proses</span>
                  @endif
                </td>
                <td>
                  <div class="text-sm">{{ $resp->updated_at->setTimezone('Asia/Jakarta')->format('d M Y') }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ $resp->updated_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                </td>
                <td>
                  <div style="display:flex; gap:.375rem; flex-wrap:wrap;">
                    <a href="{{ route('bps.konstruksi.show', $resp->id) }}" class="btn-view">Lihat Detail</a>
                    <a href="{{ route('bps.konstruksi.download', $resp->id) }}" class="btn-view" style="background:#16a34a;" title="Download PDF">PDF</a>
                    <button type="button" class="btn-delete"
                            title="Hapus data survei konstruksi ini"
                            onclick="bpsConfirmDelete(
                                '{{ route('bps.konstruksi.destroy', $resp->id) }}',
                                @js($resp->nama_perusahaan ?: ($resp->user->name ?? 'Responden')),
                                @js('Survei Konstruksi ' . $periode . ' — ' . ($resp->user->email ?? '')),
                                @js('Survei Konstruksi ' . $periode)
                            )">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="pagination-container">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Menampilkan {{ $surveyResponses->firstItem() }}–{{ $surveyResponses->lastItem() }} dari {{ $surveyResponses->total() }} hasil
        </div>
        <div>{{ $surveyResponses->links() }}</div>
      </div>
    @else
      <div class="empty-state">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Data</h3>
        <p class="text-gray-600 dark:text-gray-400">
          @if(request()->hasAny(['search', 'status', 'period']))
            Tidak ada hasil yang sesuai dengan filter yang dipilih.
          @else
            Belum ada respons Survei Konstruksi yang tersedia.
          @endif
        </p>
      </div>
    @endif
  </div>
</div>

@include('bps.partials.delete-survey-modal')

{{--
  Export dialog. Konstruksi rows are period-scoped, so the period filter leads;
  the per-blok filters cover the four blok BPS chases up most (identity, tenaga
  kerja, the project grid and the material breakdown).
--}}
@include('bps.partials.export-excel-modal', [
    'exportAction'   => route('bps.konstruksi.export'),
    'exportTitle'    => 'Ekspor Data Survei Konstruksi',
    'exportSubtitle' => 'Satu baris per responden berisi identitas Blok I, kelengkapan tiap blok, dan angka pokok (nilai kontrak, nilai konstruksi diselesaikan, ringkasan Blok IX). Kosongkan filter untuk mengunduh semua data.',
    'exportFields'   => [
        [
            'type'    => 'select',
            'name'    => 'period',
            'label'   => 'Periode',
            'value'   => request('period'),
            'options' => ['' => 'Semua Periode'] + $periodOptions,
        ],
        [
            'type'    => 'select',
            'name'    => 'status',
            'label'   => 'Status Survei',
            'value'   => request('status'),
            'options' => ['' => 'Semua Status', 'completed' => 'Selesai', 'in_progress' => 'Dalam Proses'],
        ],
        {{-- Blok I–III exist in both instruments; the rest belong to one only,
             so their labels name which. --}}
        [
            'type'    => 'select',
            'name'    => 'blok1',
            'label'   => 'Kelengkapan Blok I (SKTH + SKTR)',
            'options' => ['' => 'Semua', 'complete' => 'Blok I lengkap', 'incomplete' => 'Blok I belum lengkap'],
        ],
        [
            'type'    => 'select',
            'name'    => 'blok2',
            'label'   => 'Kelengkapan Blok II (SKTH + SKTR)',
            'options' => ['' => 'Semua', 'complete' => 'Blok II lengkap', 'incomplete' => 'Blok II belum lengkap'],
        ],
        [
            'type'    => 'select',
            'name'    => 'blok3',
            'label'   => 'Kelengkapan Blok III (Modal / Realisasi Fisik)',
            'options' => ['' => 'Semua', 'complete' => 'Sudah lengkap', 'incomplete' => 'Belum lengkap'],
        ],
        [
            'type'    => 'select',
            'name'    => 'blok6',
            'label'   => 'SKTH: Blok VI (Pekerjaan Konstruksi)',
            'options' => ['' => 'Semua', 'complete' => 'Sudah lengkap', 'incomplete' => 'Belum lengkap'],
            'hint'    => 'Hanya berlaku pada periode Tahunan.',
        ],
        [
            'type'    => 'select',
            'name'    => 'blok7',
            'label'   => 'SKTH: Blok VII (Bahan/Material)',
            'options' => ['' => 'Semua', 'complete' => 'Sudah lengkap', 'incomplete' => 'Belum lengkap'],
            'hint'    => 'Hanya berlaku pada periode Tahunan.',
        ],
        [
            // Not "status" — that name is already the Status Survei filter.
            'type'    => 'select',
            'name'    => 'blok_status',
            'label'   => 'SKTR: Updating Status Perusahaan',
            'options' => ['' => 'Semua', 'complete' => 'Sudah lengkap', 'incomplete' => 'Belum lengkap'],
            'hint'    => 'Hanya berlaku pada periode Triwulanan.',
        ],
        [
            'type'    => 'select',
            'name'    => 'status_perusahaan',
            'label'   => 'SKTR: Status Perusahaan',
            'options' => ['' => 'Semua Status'] + \App\Models\KonstruksiSurveyResponse::STATUS_PERUSAHAAN,
            'hint'    => 'Hanya berlaku pada periode Triwulanan.',
        ],
        [
            'type'    => 'select',
            'name'    => 'keberadaan_proyek',
            'label'   => 'SKTR: Keberadaan Proyek',
            'options' => ['' => 'Semua'] + \App\Models\KonstruksiSurveyResponse::KEBERADAAN_PROYEK,
            'hint'    => 'Hanya berlaku pada periode Triwulanan.',
        ],
        [
            'type'    => 'select',
            'name'    => 'kualifikasi',
            'label'   => 'Kualifikasi Perusahaan',
            'options' => ['' => 'Semua Kualifikasi'] + \App\Models\KonstruksiSurveyResponse::KUALIFIKASI,
        ],
        [
            'type'    => 'select',
            'name'    => 'kabupaten_kota',
            'label'   => 'Kabupaten/Kota',
            'options' => ['' => 'Semua Kabupaten/Kota'] + $kabkotaOptions->mapWithKeys(fn ($k) => [$k => $k])->all(),
        ],
        ['type' => 'date', 'name' => 'date_from', 'label' => 'Diperbarui Dari'],
        ['type' => 'date', 'name' => 'date_to',   'label' => 'Diperbarui Sampai'],
        [
            'type'        => 'text',
            'name'        => 'search',
            'label'       => 'Pencarian',
            'value'       => request('search'),
            'placeholder' => 'Nama perusahaan, NUS, NIB, lokasi, pengguna...',
            'full'        => true,
        ],
    ],
])
@endsection
