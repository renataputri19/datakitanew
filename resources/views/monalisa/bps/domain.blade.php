@extends('layouts.monalisa-dashboard')

@section('title', 'Domain ' . $domain->domain_number . ' - MONALISA BPS')
@section('description', 'Detail domain ' . $domain->name . ' untuk verifikasi assessment MONALISA')

@section('monalisa-content')
    <!-- Mobile/Tablet Menu Button -->
    <div class="lg:hidden mb-4">
        <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="monalisa-sidebar" aria-expanded="false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            Menu
        </button>
    </div>

    <!-- Page Header -->
    <div class="ud-page-header">
        <div class="ud-page-header-content">
            <h1 class="ud-page-title">Domain {{ $domain->domain_number }}: {{ $domain->name }}</h1>
            <p class="ud-page-description">{{ $domain->description ?? 'Verifikasi assessment untuk semua indikator dalam domain ini' }}</p>
            <div class="mt-4 flex items-center gap-4">
                <div class="flex items-center gap-2 text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="font-semibold">Bobot: {{ $domain->weight }}%</span>
                </div>
                <div class="flex items-center gap-2 text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="font-semibold">{{ $domain->aspeks->count() }} Aspek, {{ $domain->indikators->count() }} Indikator</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Domain Summary Card -->
    @php
        $totalAspeks = $domain->aspeks->count();
        $totalIndikators = $domain->indikators->count();
        $verifiedCount = $assessments->where('status', 'verified')->count();
        $submittedCount = $assessments->where('status', 'submitted')->count();
        $draftCount = $assessments->where('status', 'draft')->count();
        $rejectedCount = $assessments->where('status', 'rejected')->count();
    @endphp

    <section class="ud-card monalisa-card mb-6" role="article" aria-labelledby="domainCardTitle" aria-describedby="domainCardDesc" tabindex="0">
        <header class="ud-card-header">
            <h2 id="domainCardTitle" class="ud-card-title">Ringkasan Domain {{ $domain->domain_number }}</h2>
            <p id="domainCardDesc" class="ud-card-subtitle">Informasi utama dan status verifikasi untuk domain ini</p>
        </header>
        <div class="monalisa-card-meta mb-6">
            <div class="monalisa-card-meta-item" aria-label="Bobot domain">
                <div class="monalisa-card-meta-label">Bobot</div>
                <div class="monalisa-card-meta-value">{{ $domain->weight }}%</div>
            </div>
            <div class="monalisa-card-meta-item" aria-label="Jumlah aspek dan indikator">
                <div class="monalisa-card-meta-label">Struktur</div>
                <div class="monalisa-card-meta-value">{{ $totalAspeks }} Aspek • {{ $totalIndikators }} Indikator</div>
            </div>
            <div class="monalisa-card-meta-item" aria-label="Status verifikasi">
                <div class="monalisa-card-meta-label">Status</div>
                <div class="monalisa-card-meta-value">{{ $verifiedCount }} Verif • {{ $submittedCount }} Menunggu</div>
            </div>
            <div class="monalisa-card-meta-item" aria-label="Draft dan ditolak">
                <div class="monalisa-card-meta-label">Draft & Ditolak</div>
                <div class="monalisa-card-meta-value">{{ $draftCount }} Draft • {{ $rejectedCount }} Ditolak</div>
            </div>
        </div>
        <div class="monalisa-card-actions" role="group" aria-label="Aksi ringkas">
            <a href="{{ route('monalisa.bps.dashboard') }}" class="monalisa-btn monalisa-btn-secondary btn-sm" aria-label="Kembali ke dashboard MONALISA BPS">
                Kembali ke Dashboard
            </a>
            @php
                $nextDomain = \App\Models\MonalisaDomain::where('order', '>', $domain->order)->orderBy('order')->first();
            @endphp
            @if($nextDomain)
            <a href="{{ route('monalisa.bps.domain', $nextDomain->id) }}" class="monalisa-btn monalisa-btn-primary btn-sm" aria-label="Buka domain berikutnya">
                Domain {{ $nextDomain->domain_number }} Selanjutnya
            </a>
            @endif
            <a href="#aspeks" class="monalisa-btn monalisa-btn-primary btn-sm" aria-label="Loncat ke daftar aspek">
                Lihat Daftar Aspek
            </a>
        </div>
    </section>

    <!-- Verification Status Overview -->
    @php
        $totalIndikators = $domain->indikators->count();
        $verifiedCount = $assessments->where('status', 'verified')->count();
        $submittedCount = $assessments->where('status', 'submitted')->count();
        $draftCount = $assessments->where('status', 'draft')->count();
        $rejectedCount = $assessments->where('status', 'rejected')->count();
    @endphp
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="ud-stat-card ud-text-center" aria-label="Jumlah terverifikasi">
            <div class="ud-stat-number text-green-600 dark:text-green-400">{{ $verifiedCount }}</div>
            <div class="ud-stat-label">Terverifikasi</div>
        </div>
        <div class="ud-stat-card ud-text-center" aria-label="Jumlah menunggu verifikasi">
            <div class="ud-stat-number text-yellow-600 dark:text-yellow-400">{{ $submittedCount }}</div>
            <div class="ud-stat-label">Menunggu</div>
        </div>
        <div class="ud-stat-card ud-text-center" aria-label="Jumlah draft">
            <div class="ud-stat-number text-gray-600 dark:text-gray-400">{{ $draftCount }}</div>
            <div class="ud-stat-label">Draft</div>
        </div>
        <div class="ud-stat-card ud-text-center" aria-label="Jumlah ditolak">
            <div class="ud-stat-number text-red-600 dark:text-red-400">{{ $rejectedCount }}</div>
            <div class="ud-stat-label">Ditolak</div>
        </div>
    </div>

    <!-- Aspeks and Indikators -->
    <div id="aspeks"></div>
    @foreach($domain->aspeks as $aspek)
    <div class="monalisa-aspek-card">
        <div class="monalisa-aspek-header">
            <h3 class="monalisa-aspek-title">Aspek {{ $aspek->aspek_number }}: {{ $aspek->name }}</h3>
            <span class="monalisa-aspek-number">{{ $aspek->indikators->count() }} Indikator</span>
        </div>
        
        @if($aspek->description)
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $aspek->description }}</p>
        @endif

        <div class="space-y-3">
            @foreach($aspek->indikators as $indikator)
            @php
                $assessment = $assessments->firstWhere('indikator_id', $indikator->id);
                $hasSubmission = $assessment && $assessment->status !== 'draft';
            @endphp
            
            <div class="monalisa-indikator-item">
                <div class="flex items-center gap-3 flex-1">
                    <span class="monalisa-indikator-code">{{ $indikator->indikator_code }}</span>
                    <span class="monalisa-indikator-name">{{ $indikator->name }}</span>
                </div>
                
                <!-- Mobile: status + scores (row 1), button (row 2); Desktop: inline -->
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    @if($hasSubmission)
                        <div class="flex items-center gap-2">
                            <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                                {{ ucfirst($assessment->status) }}
                            </span>
                            <div class="flex items-center gap-2 text-sm">
                                @if($assessment->kominfo_maturity_level)
                                <span class="font-semibold text-blue-600 dark:text-blue-400">
                                    K: L{{ $assessment->kominfo_maturity_level }}
                                </span>
                                @endif
                                @if($assessment->bps_maturity_level)
                                <span class="font-semibold text-green-600 dark:text-green-400">
                                    BPS: L{{ $assessment->bps_maturity_level }}
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <a href="{{ route('monalisa.bps.assessment.show', $assessment->id) }}" 
                           class="monalisa-btn monalisa-btn-primary btn-sm mt-2 md:mt-0">
                            @if($assessment->status === 'verified')
                                Lihat
                            @elseif($assessment->status === 'submitted')
                                Verifikasi
                            @else
                                Review
                            @endif
                        </a>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="monalisa-badge monalisa-badge-draft">Belum Ada Submission</span>
                        </div>
                        <button class="monalisa-btn monalisa-btn-secondary btn-sm mt-2 md:mt-0" disabled>
                            Menunggu
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Navigation -->
    <div class="monalisa-bottom-nav">
        <a href="{{ route('monalisa.bps.dashboard') }}"
           class="monalisa-btn monalisa-btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>

        @php
            $nextDomain = \App\Models\MonalisaDomain::where('order', '>', $domain->order)->orderBy('order')->first();
        @endphp

        @if($nextDomain)
        <a href="{{ route('monalisa.bps.domain', $nextDomain->id) }}"
           class="monalisa-btn monalisa-btn-primary">
            Domain {{ $nextDomain->domain_number }} Selanjutnya
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
        @endif
    </div>
@endsection

