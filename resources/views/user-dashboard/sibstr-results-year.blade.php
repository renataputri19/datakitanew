@extends('layouts.user-dashboard')

@section('title', 'SIBSTR ' . $tahun . ' - Dashboard Pengguna')

@section('dashboard-content')
  <!-- Mobile/Tablet Menu Button -->
  <div class="lg:hidden mb-4">
    <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
            type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
      Menu
    </button>
  </div>

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4" aria-label="Breadcrumb">
    <a href="{{ route('dashboard.surveys.sibstr.results') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
      Survei SIBSTR
    </a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $tahun }}</span>
  </nav>

  <!-- Page Header -->
  <div class="ud-page-header">
    <div class="ud-page-header-content">
      <h1 class="ud-page-title">SIBSTR {{ $tahun }}</h1>
      <p class="ud-page-description">Ringkasan dan status pengisian survei{{ $tahun >= 2026 ? ' per triwulan' : '' }}</p>
    </div>
  </div>

  {{-- ── RINGKASAN (Blok I dari data terbaru / tahunan) ──────────────────── --}}
  @if($ringkasanResponse)
    <div class="ud-section-header mt-4">
      <h2 class="ud-section-title">
        <div class="ud-section-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        Ringkasan Perusahaan
      </h2>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mt-2">
      <!-- Status card -->
      <div class="ud-card">
        <div class="ud-card-header">
          <h3 class="ud-card-title">Status</h3>
        </div>
        <dl class="mt-3 space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <dt class="text-gray-600 dark:text-gray-400">Periode</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100">
              {{ $ringkasanResponse->triwulan == 0
                    ? 'Tahunan'
                    : \App\Models\SurveyResponse::triwulanLabel($ringkasanResponse->triwulan) }}
            </dd>
          </div>
          <div class="flex items-center justify-between">
            <dt class="text-gray-600 dark:text-gray-400">Status</dt>
            <dd>
              @if($ringkasanResponse->is_completed)
                <span class="inline-flex items-center gap-1 text-green-700 dark:text-green-400 font-medium">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Selesai
                </span>
              @else
                <span class="text-yellow-700 dark:text-yellow-400 font-medium">Draft</span>
              @endif
            </dd>
          </div>
          <div class="flex items-center justify-between">
            <dt class="text-gray-600 dark:text-gray-400">Terakhir disimpan</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100 text-xs">
              {{ $ringkasanResponse->last_saved_at ? $ringkasanResponse->last_saved_at->format('d M Y, H:i') : '-' }}
            </dd>
          </div>
        </dl>

        @if($annualResponse && $annualResponse->is_completed)
          <div class="mt-4 flex flex-col gap-2">
            <a href="{{ route('dashboard.surveys.sibstr.download-certificate') . '?tahun=' . $tahun }}"
               class="ud-btn w-full justify-center text-sm bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Unduh Bukti
            </a>
            <a href="{{ route('survey.sibstr.edit.blok1', ['year' => $tahun, 'period' => 'tahunan']) }}"
               class="ud-btn ud-btn-primary w-full justify-center text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit Survei Tahunan
            </a>
          </div>
        @endif
      </div>

      <!-- Company info (Blok I) -->
      <div class="ud-card lg:col-span-2">
        <div class="ud-card-header">
          <h3 class="ud-card-title">Identitas Perusahaan (Blok I)</h3>
        </div>
        <dl class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <dt class="text-gray-600 dark:text-gray-400">Nama Perusahaan</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $ringkasanResponse->nama_perusahaan ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-gray-600 dark:text-gray-400">Alamat Pabrik</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $ringkasanResponse->alamat_pabrik ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-gray-600 dark:text-gray-400">Kabupaten / Kota</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $ringkasanResponse->kabupaten_kota ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-gray-600 dark:text-gray-400">Email</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $ringkasanResponse->email ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-gray-600 dark:text-gray-400">KBLI Utama</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $ringkasanResponse->kbli_utama ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-gray-600 dark:text-gray-400">NIB</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $ringkasanResponse->nib ?? '-' }}</dd>
          </div>
        </dl>
      </div>
    </div>
  @endif

  {{-- ── TRIWULAN CARDS ─────────────────────────────────────────────────── --}}
  @if($tahun >= 2026)

  @if(!$tahunanFullyComplete)
  {{-- Blocking banner: Tahunan 2025 not yet completed through Block 6 --}}
  <div class="mt-6 rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-4 flex flex-col sm:flex-row sm:items-start gap-3">
    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div class="flex-1">
      <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Ada Pembaruan di Survei Tahunan 2025</p>
      <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
        Formulir tahunan 2025 telah diperbarui dengan tambahan data baru. Lengkapi bagian yang belum terisi — survei triwulanan {{ $tahun }} akan terbuka setelah semua data tahunan selesai diisi.
      </p>
      <a href="{{ route('survey.sibstr.edit.blok1', ['year' => 2025, 'period' => 'tahunan']) }}"
         class="inline-flex items-center gap-1.5 mt-3 text-sm font-medium text-amber-800 dark:text-amber-300 underline hover:no-underline">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Lengkapi Sekarang
      </a>
    </div>
  </div>
  @endif

  <div class="ud-section-header mt-8">
    <h2 class="ud-section-title">
      <div class="ud-section-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
      </div>
      Data Triwulanan {{ $tahun }}
    </h2>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-2">
    @foreach($triwulanCards as $card)
      @php
        $tw        = $card['triwulan'];
        $resp      = $card['response'];
        $completed = $card['is_completed'];
        $inProgress= $card['is_in_progress'];
        $available = $card['is_available'];
        $locked    = $card['is_locked'];
        $tahunanLocked = !$tahunanFullyComplete && !$completed;

        // URL for new/in-progress entry (fill flow)
        $surveyUrl = route('survey.sibstr.blok1', ['year' => $tahun, 'period' => $tw]);
        // URL for editing a completed entry (edit flow — bypasses checkSibstrCompletion redirect)
        $editUrl   = route('survey.sibstr.edit.blok1', ['year' => $tahun, 'period' => $tw]);
      @endphp

      <div class="ud-card flex flex-col {{ $locked ? 'opacity-50' : '' }}">
        <!-- TW header -->
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Triwulan {{ ['I','II','III','IV'][$tw-1] }}</span>
          @if($completed)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
              </svg>
              Selesai
            </span>
          @elseif($inProgress)
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
              Draft
            </span>
          @elseif($available)
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
              Tersedia
            </span>
          @else
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500">
              Belum tersedia
            </span>
          @endif
        </div>

        <!-- TW period label -->
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ $card['label'] }}</p>

        @if($resp)
          <!-- Last saved -->
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Terakhir: {{ $resp->last_saved_at ? $resp->last_saved_at->format('d M Y') : '-' }}
          </p>
        @endif

        <!-- Actions -->
        <div class="mt-auto flex flex-col gap-2">
          @if($completed)
            <!-- Edit + Bukti -->
            <a href="{{ $editUrl }}"
               class="ud-btn ud-btn-primary w-full justify-center text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit
            </a>
            <a href="{{ route('dashboard.surveys.sibstr.download-certificate') . '?tahun=' . $tahun . '&triwulan=' . $tw }}"
               class="ud-btn w-full justify-center text-sm border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Bukti
            </a>
          @elseif($inProgress && !$tahunanLocked)
            <a href="{{ $surveyUrl }}"
               class="ud-btn ud-btn-primary w-full justify-center text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
              Lanjutkan
            </a>
          @elseif($available && !$tahunanLocked)
            <a href="{{ $surveyUrl }}"
               class="ud-btn ud-btn-primary w-full justify-center text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Mulai Isi
            </a>
          @elseif($tahunanLocked && ($available || $inProgress))
            <button disabled class="ud-btn w-full justify-center text-sm cursor-not-allowed opacity-50 border border-amber-300 text-amber-700 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 dark:text-amber-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              Selesaikan Tahunan Dulu
            </button>
          @else
            <button disabled class="ud-btn w-full justify-center text-sm cursor-not-allowed opacity-50 border border-gray-300 text-gray-500 bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              Belum tersedia
            </button>
          @endif
        </div>
      </div>
    @endforeach
  </div>
  @else
  {{-- For years before 2026, triwulanan is not available --}}
  <div class="ud-card mt-8">
    <p class="text-sm text-gray-500 dark:text-gray-400 italic">
      Pelaporan Triwulanan hanya tersedia mulai tahun 2026. Untuk tahun {{ $tahun }}, hanya tersedia Survei Tahunan.
    </p>
  </div>
  @endif

  {{-- ── ANNUAL LEGACY ROW (2025 style) ─────────────────────────────────── --}}
  @if($annualResponse)
    <div class="ud-section-header mt-8">
      <h2 class="ud-section-title">
        <div class="ud-section-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </div>
        Survei Tahunan {{ $tahun }}
      </h2>
    </div>

    <div class="ud-card mt-2">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
            Formulir Tahunan (Blok I–VI)
          </p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Status:
            @if($annualResponse->is_completed)
              <span class="text-green-600 dark:text-green-400 font-medium">Selesai</span>
              · Disimpan {{ $annualResponse->last_saved_at?->format('d M Y, H:i') ?? '-' }}
            @else
              <span class="text-yellow-600 dark:text-yellow-400 font-medium">Draft</span>
            @endif
          </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
          @if($annualResponse->is_completed)
            <a href="{{ route('dashboard.surveys.sibstr.download-certificate') . '?tahun=' . $tahun }}"
               class="ud-btn border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700 text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Unduh Bukti
            </a>
          @endif
          <a href="{{ route('survey.sibstr.edit.blok1', ['year' => $tahun, 'period' => 'tahunan']) }}"
             class="ud-btn ud-btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Survei
          </a>
        </div>
      </div>
    </div>
  @endif

  {{-- Notes (Blok VI) --}}
  @if($ringkasanResponse && !empty($ringkasanResponse->blok6_data['catatan']))
    <div class="ud-card mt-4">
      <div class="ud-card-header">
        <h3 class="ud-card-title">Catatan (Blok VI)</h3>
      </div>
      <p class="mt-2 text-sm text-gray-900 dark:text-gray-100">
        {{ $ringkasanResponse->blok6_data['catatan'] }}
      </p>
    </div>
  @endif

@endsection
