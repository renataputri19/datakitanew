@extends('layouts.user-dashboard')

@section('title', 'Survei SIBSTR - Dashboard Pengguna')

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

  <!-- Page Header -->
  <div class="ud-page-header">
    <div class="ud-page-header-content">
      <h1 class="ud-page-title">Survei SIBSTR</h1>
      <p class="ud-page-description">Pilih tahun untuk melihat detail dan mengisi data survei</p>
    </div>
  </div>

  <!-- Section Header -->
  <div class="ud-section-header">
    <h2 class="ud-section-title">
      <div class="ud-section-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      Pilih Tahun
    </h2>
  </div>

  <!-- Year Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
    @forelse($allYears as $yr)
      @php
        $summary = $yearSummaries[$yr];
        $isCurrent = ($yr === $currentYear);
      @endphp

      <a href="{{ route('dashboard.surveys.sibstr.results.year', $yr) }}"
         class="ud-card group block hover:border-blue-500 border-2 transition-all
                {{ $isCurrent ? 'border-blue-400 dark:border-blue-500' : 'border-transparent' }}">

        <!-- Year badge -->
        <div class="flex items-center justify-between mb-3">
          <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            {{ $yr }}
          </span>
          @if($isCurrent)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
              Tahun ini
            </span>
          @endif
        </div>

        @if($summary['has_annual'])
          <!-- Legacy / Annual entry -->
          <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            <span class="inline-flex items-center gap-1">
              @if($summary['annual_done'])
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium text-green-700 dark:text-green-400">Survei Tahunan Selesai</span>
              @else
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                </svg>
                <span class="font-medium text-yellow-700 dark:text-yellow-400">Survei Tahunan — Draft</span>
              @endif
            </span>
          </div>
        @endif

        @if($summary['tw_total'] > 0 || $summary['tw_available'] > 0)
          <!-- Quarterly progress -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-medium text-gray-700 dark:text-gray-300">
              {{ $summary['tw_completed'] }} / {{ max($summary['tw_total'], $summary['tw_available']) }} triwulan selesai
            </span>
          </div>

          <!-- Mini progress bar -->
          @if($summary['tw_available'] > 0)
            <div class="mt-2 w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full transition-all"
                   style="width: {{ $summary['tw_available'] > 0 ? ($summary['tw_completed'] / $summary['tw_available'] * 100) : 0 }}%">
              </div>
            </div>
          @endif
        @elseif(!$summary['has_any'])
          <p class="text-sm text-gray-500 dark:text-gray-400 italic">Belum ada data</p>
        @endif

        <!-- Arrow -->
        <div class="mt-3 flex justify-end">
          <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </a>
    @empty
      <div class="col-span-full ud-card text-center py-10">
        <p class="text-gray-500 dark:text-gray-400 mb-4">Belum ada data survei. Mulai survei pertama Anda.</p>
        <a href="{{ route('survey.sibstr.entry') }}" class="ud-btn ud-btn-primary">
          Mulai Survei
        </a>
      </div>
    @endforelse
  </div>
@endsection