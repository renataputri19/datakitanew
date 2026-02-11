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
      <a href="{{ route('survey.sibstr.blok1') }}" class="ud-btn ud-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path>
        </svg>
        Edit Survei
      </a>
    @endif
  </div>

  @if(!$isCompleted)
    <div class="ud-alert ud-alert-warning">
      <svg class="ud-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <div>
        <div class="ud-empty-title">Survei belum selesai</div>
        <div class="ud-empty-description">Silakan mulai atau lanjutkan survei untuk melihat ringkasan hasil.</div>
        <div class="mt-4">
          <a href="{{ route('survey.sibstr.blok1') }}" class="ud-btn ud-btn-primary">Mulai / Lanjutkan Survei</a>
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