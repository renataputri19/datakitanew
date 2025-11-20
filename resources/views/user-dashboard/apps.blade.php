@extends('layouts.user-dashboard')

@section('title', 'Aplikasi - DataKita')
@section('description', 'Akses berbagai aplikasi dan fitur DataKita berdasarkan peran Anda')

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
            <h1 class="ud-page-title">Aplikasi DataKita</h1>
            <p class="ud-page-description">Akses berbagai aplikasi dan fitur yang tersedia untuk Anda</p>
        </div>
    </div>

    <!-- Apps Grid -->
<div class="apps-grid">
        
        <!-- Basic User Apps - Available to All Users -->
        
        <!-- WhatsApp Bot (Encik Bot) -->
        <a href="https://wa.me/6281319992171" target="_blank" rel="noopener noreferrer" class="app-card app-card-green">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8a7 7 0 0110 9l2 3-3-2a7 7 0 11-9-10z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h.01M15 15h.01M11 11c.5 1.5 1.5 2.5 3 3"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Encik Bot (WhatsApp)</h3>
                <p class="app-card-description">Konsultasikan kebutuhan data Anda via WhatsApp</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- News App -->
        <a href="{{ route('dashboard.news') }}" class="app-card app-card-blue">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Berita</h3>
                <p class="app-card-description">Baca berita terbaru dari BPS Kota Batam</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Videos App -->
        <a href="{{ route('dashboard.videos') }}" class="app-card app-card-purple">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Video</h3>
                <p class="app-card-description">Tonton video informatif dari BPS</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Survey App -->
        <a href="{{ route('temporary.survey.sibstr') }}" class="app-card app-card-green">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Survei SIBSTR</h3>
                <p class="app-card-description">Isi survei industri besar dan sedang triwulanan</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Guestbook App -->
        <a href="https://perpustakaan.bps.go.id/digilib/guestbook" target="_blank" rel="noopener noreferrer" class="app-card app-card-indigo">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Buku Tamu</h3>
                <p class="app-card-description">Isi buku tamu digital perpustakaan BPS</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
            </div>
        </a>

        <!-- Queue System App -->
        <a href="{{ route('antrian.index') }}" class="app-card app-card-teal">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Antrian Tamu</h3>
                <p class="app-card-description">Sistem antrian untuk layanan tamu BPS</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Role-Based Apps -->
        
        @if($user->is_bps)
        <!-- BPS Admin Dashboard -->
        <a href="{{ route('bps.dashboard') }}" class="app-card app-card-red">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Dashboard Admin BPS</h3>
                <p class="app-card-description">Kelola berita dan video BPS Kota Batam</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>
        @endif

        @if($user->is_bps || $user->is_kominfo_user)
        <!-- MONALISA Dashboard -->
        <a href="{{ $user->is_kominfo_user ? route('monalisa.kominfo.dashboard') : route('monalisa.bps.dashboard') }}" class="app-card app-card-orange">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Dashboard MONALISA</h3>
                <p class="app-card-description">Monitoring dan evaluasi statistik sektoral</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>
        @endif

        @if($user->is_superadmin)
        <!-- Superadmin Dashboard -->
        <a href="{{ route('superadmin.dashboard') }}" class="app-card app-card-pink">
            <div class="app-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div class="app-card-content">
                <h3 class="app-card-title">Dashboard Superadmin</h3>
                <p class="app-card-description">Kelola data survei dan sistem</p>
            </div>
            <div class="app-card-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>
        @endif

    </div>

    <!-- Back to Dashboard Button -->
<div class="mt-8">
        <a href="{{ route('dashboard') }}" class="ud-btn ud-btn-secondary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* Apps Grid Layout */
    .apps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.75rem;
        margin-top: 1rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 640px) {
        .apps-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    /* App Card Styles */
    .app-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        min-height: 120px;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        text-decoration: none;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .app-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: currentColor;
        opacity: 0.35;
        transition: opacity 0.2s ease;
    }

    .app-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .app-card:hover::before {
        opacity: 1;
    }

    .app-card:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }

    .app-card-icon {
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

    .app-card-icon svg {
        width: 1.75rem;
        height: 1.75rem;
        color: currentColor;
        opacity: 10;
    }

    .app-card-content {
        flex: 1;
        min-width: 0;
    }

    .app-card-title {
        font-size: 1.0625rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .app-card-description {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.4;
    }

    .app-card-arrow {
        flex-shrink: 0;
        width: 1.5rem;
        height: 1.5rem;
        opacity: 0.5;
        transition: all 0.2s ease;
    }

    .app-card:hover .app-card-arrow {
        opacity: 1;
        transform: translateX(4px);
    }

    .app-card-arrow svg {
        width: 100%;
        height: 100%;
        color: currentColor;
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
    .dark .app-card {
        background: #1f2937;
        border-color: #374151;
    }

    .dark .app-card-title {
        color: #f9fafb;
    }

    .dark .app-card-description {
        color: #9ca3af;
    }

    .dark .app-card:hover {
        background: #374151;
    }
</style>
@endpush

