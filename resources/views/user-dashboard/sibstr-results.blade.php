@extends('layouts.user-dashboard')

@section('title', 'Hasil Survei SIBSTR - Dashboard Pengguna')

@section('dashboard-content')
  <!-- Mobile/Tablet Menu Button -->
  <div class="lg:hidden mb-4">
    <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
      Menu
    </button>
  </div>

  <!-- Page Header -->
  <div class="ud-page-header">
    <div class="ud-page-header-content">
      <h1 class="ud-page-title">Hasil Survei SIBSTR</h1>
      <p class="ud-page-description">Ringkasan hasil survei untuk akun Anda</p>
    </div>
  </div>

  <!-- Section Header with Action -->
  <div class="ud-section-header">
    <h2 class="ud-section-title">
      <div class="ud-section-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9 9 0 1020.945 13H11V3.055z"></path>
        </svg>
      </div>
      Ringkasan Hasil SIBSTR
    </h2>
    @if($isCompleted)
      <div class="flex items-center gap-3">
        <a href="{{ route('dashboard.surveys.sibstr.download-certificate') }}" class="ud-btn bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
          </svg>
          Unduh Bukti Penyelesaian
        </a>
        <a href="{{ route('survey.sibstr.edit.blok1') }}" class="ud-btn ud-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path>
            </svg>
            Edit Survei
        </a>
      </div>
    @endif
  </div>

  @if(!$isCompleted)
    <div class="ud-card">
      <div class="flex flex-col md:flex-row items-start gap-6">
        <!-- Illustration / Icon -->
        <div class="hidden md:flex">
          <div class="w-16 h-16 rounded-xl flex items-center justify-center bg-yellow-100 text-yellow-600 shadow-sm dark:bg-yellow-900/30 dark:text-yellow-400">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14A10 10 0 014.93 4.93z" />
            </svg>
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="ud-card-title">Survei belum selesai</h3>
              <p class="ud-card-subtitle">Selesaikan survei untuk membuka ringkasan hasil dan insight penting.</p>
            </div>
          </div>

          <!-- Value props to encourage completion -->
          <ul class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span>Ringkasan indikator utama</span>
            </li>
            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span>Status kelengkapan per blok</span>
            </li>
            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span>Insight dan rekomendasi awal</span>
            </li>
            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span>Bisa disimpan dan dilanjutkan kapan saja</span>
            </li>
          </ul>

          <!-- Primary CTA -->
          <div class="mt-6 flex items-center flex-wrap gap-4">
            <a href="{{ route('survey.sibstr.blok1') }}" class="ud-btn ud-btn-primary">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              Mulai / Lanjutkan Survei
            </a>
            <span class="text-xs text-gray-500 dark:text-gray-400">Perkiraan waktu ± 10–15 menit</span>
          </div>
        </div>
      </div>
    </div>
  @else
    <!-- Status + Summary Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Status Card -->
      <div class="ud-card">
        <div class="ud-card-header">
          <h3 class="ud-card-title">Status Survei</h3>
          <div class="ud-card-subtitle">Informasi penyelesaian dan waktu simpan</div>
        </div>
        <dl class="mt-3 space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <dt class="text-gray-600 dark:text-gray-400">Status</dt>
            <dd class="font-medium text-green-700 dark:text-green-400">Selesai</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt class="text-gray-600 dark:text-gray-400">Terakhir disimpan</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $completedAt ? $completedAt->format('d M Y, H:i') : '-' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Company Summary (Blok I) -->
      <div class="ud-card lg:col-span-2">
        <div class="ud-card-header">
          <h3 class="ud-card-title">Ringkasan Perusahaan (Blok I)</h3>
          <div class="ud-card-subtitle">Informasi identitas perusahaan</div>
        </div>

        <dl class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">Nama Perusahaan</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $response->nama_perusahaan ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">Alamat Pabrik</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $response->alamat_pabrik ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">Email</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $response->email ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">KBLI Utama</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $response->kbli_utama ?? '-' }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Notes (Blok VI) -->
    <div class="ud-card mt-6">
      <div class="ud-card-header">
        <h3 class="ud-card-title">Catatan (Blok VI)</h3>
        <div class="ud-card-subtitle">Keterangan tambahan dari responden</div>
      </div>
      <p class="mt-2 text-sm text-gray-900 dark:text-gray-100">{{ $response->catatan ?? '-' }}</p>
    </div>
  @endif
@endsection