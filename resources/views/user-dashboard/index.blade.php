@extends('layouts.user-dashboard')

@section('title', 'Dashboard - DataKita')
@section('description', 'Dashboard pengguna DataKita untuk mengakses profil, berita, dan pengaturan')



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

    <!-- Welcome Section -->
    <div class="ud-welcome">
        <h1 class="ud-welcome-title">Selamat Datang, {{ $user->name }}!</h1>
        <p class="ud-welcome-description">Kelola profil Anda dan akses berita terbaru dari BPS Kota Batam</p>
        <div class="ud-welcome-actions">
            <a href="{{ route('dashboard.profile') }}" class="ud-btn ud-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Lihat Profil
            </a>
            <a href="{{ route('dashboard.settings') }}#profile-section" class="ud-btn ud-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Profil
            </a>
            <a href="{{ route('dashboard.news') }}" class="ud-btn ud-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                Baca Berita
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    {{-- <div class="ud-grid ud-grid-cols-2 ud-mb-5" data-aos="fade-up" data-aos-delay="100">
        <div class="ud-stat-card">
            <div class="ud-stat-number">{{ $stats['profile_completion'] }}%</div>
            <div class="ud-stat-label">Kelengkapan Profil</div>
            <div class="ud-stat-description">
                @if($stats['profile_completion'] < 100)
                    Lengkapi profil Anda
                @else
                    Profil sudah lengkap
                @endif
            </div>
        </div>
    </div> --}}

    <!-- Apps Section -->
    <div class="ud-card ud-mb-4">
        <div class="ud-section-header">
            <h2 class="ud-section-title">
                <div class="ud-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                Aplikasi
            </h2>
            <a href="{{ route('dashboard.apps') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                Lihat Semua →
            </a>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 ud-mt-1 ud-mb-2">Akses cepat ke fitur utama DataKita</p>

        <div class="apps-grid-compact">
            <!-- Basic User Apps - Available to All Users -->

            <!-- WhatsApp Bot (Encik Bot) -->
            <a href="https://wa.me/6281319992171" target="_blank" rel="noopener noreferrer" class="app-card-compact app-card-green">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8a7 7 0 0110 9l2 3-3-2a7 7 0 11-9-10z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h.01M15 15h.01M11 11c.5 1.5 1.5 2.5 3 3"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Encik Bot (WhatsApp)</h3>
                    <p class="app-card-description-compact">Konsultasikan kebutuhan data via WhatsApp</p>
                </div>
            </a>

            <!-- News App -->
            <a href="{{ route('dashboard.news') }}" class="app-card-compact app-card-blue">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Berita</h3>
                    <p class="app-card-description-compact">Baca berita terbaru</p>
                </div>
            </a>

            <!-- Videos App -->
            <a href="{{ route('dashboard.videos') }}" class="app-card-compact app-card-purple">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Video</h3>
                    <p class="app-card-description-compact">Tonton video informatif</p>
                </div>
            </a>

            <!-- Survey App -->
            <a href="{{ route('survey.sibstr.blok1') }}" class="app-card-compact app-card-green">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Survei SIBSTR</h3>
                    <p class="app-card-description-compact">Isi survei industri</p>
                </div>
            </a>

            <!-- Guestbook App -->
            <a href="https://perpustakaan.bps.go.id/digilib/guestbook" target="_blank" rel="noopener noreferrer" class="app-card-compact app-card-indigo">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Buku Tamu</h3>
                    <p class="app-card-description-compact">Isi buku tamu digital</p>
                </div>
            </a>

            <!-- Queue System App -->
            <a href="{{ route('antrian.index') }}" class="app-card-compact app-card-teal">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Antrian Tamu</h3>
                    <p class="app-card-description-compact">Sistem antrian layanan</p>
                </div>
            </a>

            <!-- Role-Based Apps -->

            @if($user->is_bps)
            <!-- BPS Admin Dashboard -->
            <a href="{{ route('bps.dashboard') }}" class="app-card-compact app-card-red">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Admin BPS</h3>
                    <p class="app-card-description-compact">Kelola berita dan video</p>
                </div>
            </a>
            @endif

            @if($user->is_bps || $user->is_kominfo_user)
            <!-- MONALISA Dashboard -->
            <a href="{{ $user->is_kominfo_user ? route('monalisa.kominfo.dashboard') : route('monalisa.bps.dashboard') }}" class="app-card-compact app-card-orange">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">MONALISA</h3>
                    <p class="app-card-description-compact">Monitoring statistik sektoral</p>
                </div>
            </a>
            @endif

            @if($user->is_superadmin)
            <!-- Superadmin Dashboard -->
            <a href="{{ route('superadmin.dashboard') }}" class="app-card-compact app-card-pink">
                <div class="app-card-icon-compact">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="app-card-content-compact">
                    <h3 class="app-card-title-compact">Superadmin</h3>
                    <p class="app-card-description-compact">Kelola data survei</p>
                </div>
            </a>
            @endif
        </div>
    </div>

    <!-- Content Grid: News and Videos -->
    <div class="ud-grid ud-grid-cols-2 ud-mt-4">
        <!-- Recent News -->
        <div class="ud-card">
            <div class="ud-section-header">
                <h2 class="ud-section-title">
                    <div class="ud-section-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    Berita Terbaru
                </h2>
                <a href="{{ route('dashboard.news') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                    Lihat Semua →
                </a>
            </div>

            @if($recentNews->count() > 0)
                <div class="space-y-4">
                    @foreach($recentNews as $news)
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2 line-clamp-2">{{ $news->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">{{ Str::limit(strip_tags($news->content ?? ''), 100) }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ $news->created_at->format('d M Y') }}
                                </span>
                                @if(!empty($news->source_url))
                                    <a href="{{ $news->source_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                        Baca →
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ud-empty-state-compact">
                    <svg class="ud-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <p>Belum ada berita tersedia</p>
                </div>
            @endif
        </div>

        <!-- Recent Videos -->
        <div class="ud-card">
            <div class="ud-section-header">
                <h2 class="ud-section-title">
                    <div class="ud-section-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    Video Terbaru
                </h2>
                <a href="{{ $bpsYoutubeUrl ?? 'https://www.youtube.com/@bpskotabatam1884' }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                    Lihat Semua →
                </a>
            </div>

            @if(isset($recentVideos) && $recentVideos->count() > 0)
                <div class="space-y-4">
                    @foreach($recentVideos as $video)
                        <div class="ud-video-card-compact">
                            <div class="ud-video-thumbnail">
                                @php
                                    $videoId = App\Helpers\YoutubeHelper::extractYoutubeId($video->url);
                                @endphp
                                @if($video->thumbnail)
                                    <img src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}">
                                @elseif($videoId)
                                    <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg"
                                         onerror="this.onerror=null; this.src='https://img.youtube.com/vi/{{ $videoId }}/hqdefault.jpg';"
                                         alt="{{ $video->title }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600">
                                        <svg class="h-10 w-10 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                <a href="{{ $video->url }}" target="_blank" class="ud-video-play-button">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </a>
                            </div>
                            <div class="ud-video-content">
                                <h4 class="ud-video-title">{{ $video->title }}</h4>
                                <p class="ud-video-date">{{ $video->formatted_date }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ud-empty-state-compact">
                    <svg class="ud-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <p>Belum ada video tersedia</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Apps Navigation Section -->
    <div class="mt-8 text-center">
        <a href="{{ route('dashboard.apps') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            Lihat Semua Aplikasi
        </a>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Akses berbagai aplikasi dan fitur DataKita
        </p>
    </div>
@endsection

@push('styles')
<style>
    /* Compact Apps Grid Layout */
    .apps-grid-compact {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    @media (max-width: 640px) {
        .apps-grid-compact {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Compact App Card Styles */
    .app-card-compact {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem;
        min-height: 130px;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        text-decoration: none;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .app-card-compact::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: currentColor;
        opacity: 0.45;
        transition: opacity 0.2s ease;
    }

    .app-card-compact:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .app-card-compact:hover::before {
        opacity: 1;
    }

    .app-card-icon-compact {
        flex-shrink: 0;
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: currentColor;
        opacity: 0.12;
    }

    .app-card-icon-compact svg {
        width: 1.75rem;
        height: 1.75rem;
        color: currentColor;
        opacity: 10;
    }

    .app-card-content-compact {
        flex: 1;
        min-width: 0;
    }

    .app-card-title-compact {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .app-card-description-compact {
        font-size: 0.8125rem;
        color: #6b7280;
        line-height: 1.4;
    }

    .app-card-compact:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }

    /* Color Variants */
    .app-card-blue { color: #2563eb; }
    .app-card-purple { color: #7c3aed; }
    .app-card-green { color: #059669; }
    .app-card-indigo { color: #4f46e5; }
    .app-card-teal { color: #0d9488; }
    .app-card-red { color: #dc2626; }
    .app-card-orange { color: #ea580c; }
    .app-card-pink { color: #db2777; }

    /* Dark Mode */
    .dark .app-card-compact {
        background: #1f2937;
        border-color: #374151;
    }

    .dark .app-card-title-compact {
        color: #f9fafb;
    }

    .dark .app-card-description-compact {
        color: #9ca3af;
    }

    .dark .app-card-compact:hover {
        background: #374151;
    }
</style>
@endpush