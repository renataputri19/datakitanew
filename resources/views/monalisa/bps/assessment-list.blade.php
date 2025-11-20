@extends('layouts.monalisa-dashboard')

@section('title', 'Daftar Assessment - MONALISA BPS')
@section('description', 'Daftar semua assessment MONALISA untuk verifikasi')

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
            <h1 class="ud-page-title">Daftar Assessment</h1>
            <p class="ud-page-description">Kelola dan verifikasi semua assessment MONALISA</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="ud-card text-center">
            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                {{ $assessments->where('status', 'submitted')->count() }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Menunggu Verifikasi</div>
        </div>
        <div class="ud-card text-center">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                {{ $assessments->where('status', 'verified')->count() }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Terverifikasi</div>
        </div>
        <div class="ud-card text-center">
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                {{ $assessments->where('status', 'rejected')->count() }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Ditolak</div>
        </div>
        <div class="ud-card text-center">
            <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">
                {{ $assessments->where('status', 'draft')->count() }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Draft</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="ud-card mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Filter Assessment</h3>
        
        <form method="GET" action="{{ route('monalisa.bps.assessments') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status" id="status" class="monalisa-form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Domain Filter -->
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Domain
                </label>
                <select name="domain" id="domain" class="monalisa-form-select">
                    <option value="">Semua Domain</option>
                    @foreach($domains as $domain)
                    <option value="{{ $domain->id }}" {{ request('domain') == $domain->id ? 'selected' : '' }}>
                        Domain {{ $domain->domain_number }}: {{ $domain->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Cari Indikator
                </label>
                <input type="text" name="search" id="search" 
                       value="{{ request('search') }}" 
                       placeholder="Nama atau kode indikator..."
                       class="monalisa-form-input">
            </div>

            <!-- Submit -->
            <div class="flex items-end">
                <button type="submit" class="monalisa-btn monalisa-btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
            </div>
        </form>

        @if(request()->hasAny(['status', 'domain', 'search']))
        <div class="mt-4 flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {{ $assessments->count() }} hasil
            </span>
            <a href="{{ route('monalisa.bps.assessments') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                Reset Filter
            </a>
        </div>
        @endif
    </div>

    <!-- Assessment Table -->
    <div class="monalisa-table-wrapper has-mobile-cards">
        <!-- Mobile Card Layout -->
        <div class="monalisa-mobile-cards">
            @forelse($assessments as $assessment)
            <div class="monalisa-assessment-card">
                <!-- Card Header - Indicator -->
                <div class="monalisa-card-header">
                    <div class="monalisa-card-code">
                        <span class="monalisa-indikator-code">{{ $assessment->indikator->indikator_code }}</span>
                    </div>
                    <div class="monalisa-card-title">
                        {{ $assessment->indikator->name }}
                    </div>
                </div>

                <!-- Card Body - Details -->
                <div class="monalisa-card-body">
                    <!-- Domain -->
                    <div class="monalisa-card-row">
                        <div class="monalisa-card-label">Domain</div>
                        <div class="monalisa-card-value">
                            Domain {{ $assessment->indikator->aspek->domain->domain_number }}
                        </div>
                    </div>

                    <!-- Kominfo User -->
                    <div class="monalisa-card-row">
                        <div class="monalisa-card-label">Kominfo User</div>
                        <div class="monalisa-card-value">
                            {{ $assessment->kominfoSubmittedBy->name ?? ($assessment->kominfoCreatedBy->name ?? '-') }}
                        </div>
                    </div>

                    <!-- Maturity Levels -->
                    <div class="monalisa-card-row">
                        <div class="monalisa-card-label">Maturity Level</div>
                        <div class="monalisa-card-levels">
                            @if($assessment->kominfo_maturity_level)
                            <div class="monalisa-card-level-item">
                                <span class="monalisa-card-level-label text-blue-600 dark:text-blue-400">Kominfo:</span>
                                <span class="text-blue-600 dark:text-blue-400">Level {{ $assessment->kominfo_maturity_level }}</span>
                            </div>
                            @endif
                            @if($assessment->bps_maturity_level)
                            <div class="monalisa-card-level-item">
                                <span class="monalisa-card-level-label text-green-600 dark:text-green-400">BPS:</span>
                                <span class="text-green-600 dark:text-green-400">Level {{ $assessment->bps_maturity_level }}</span>
                            </div>
                            @endif
                            @if(!$assessment->kominfo_maturity_level && !$assessment->bps_maturity_level)
                            <span class="text-gray-400 dark:text-gray-600">-</span>
                            @endif
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="monalisa-card-row">
                        <div class="monalisa-card-label">Status</div>
                        <div class="monalisa-card-value">
                            <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                                {{ ucfirst($assessment->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Submitted Date -->
                    @if($assessment->kominfo_submitted_at)
                    <div class="monalisa-card-row">
                        <div class="monalisa-card-label">Submitted</div>
                        <div class="monalisa-card-value">
                            {{ $assessment->kominfo_submitted_at->format('d M Y') }}
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                ({{ $assessment->kominfo_submitted_at->diffForHumans() }})
                            </span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Card Footer - Action -->
                @if($assessment->status !== 'draft')
                <div class="monalisa-card-footer">
                    <a href="{{ route('monalisa.bps.assessment.show', $assessment->id) }}"
                       class="monalisa-btn monalisa-btn-primary">
                        @if($assessment->status === 'verified')
                            Lihat
                        @elseif($assessment->status === 'submitted')
                            Verifikasi
                        @else
                            Review
                        @endif
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="monalisa-card-empty">
                <svg class="monalisa-card-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="monalisa-card-empty-text">Tidak ada assessment ditemukan</p>
            </div>
            @endforelse
        </div>

        <!-- Desktop/Tablet Table Layout -->
        <div class="monalisa-table-container">
            <table class="monalisa-table">
                <thead>
                    <tr>
                        <th scope="col">
                            Indikator
                        </th>
                        <th scope="col" class="hide-mobile hide-tablet hide-laptop">
                            Domain
                        </th>
                        <th scope="col" class="hide-mobile hide-tablet hide-laptop">
                            Kominfo User
                        </th>
                        <th scope="col">
                            Level
                        </th>
                        <th scope="col">
                            Status
                        </th>
                        <th scope="col" class="hide-mobile hide-tablet hide-laptop">
                            Submitted
                        </th>
                        <th scope="col">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $assessment)
                    <tr>
                        <td class="monalisa-table-indikator-cell">
                            <div class="monalisa-table-indikator-wrapper">
                                <div class="monalisa-table-indikator-code-wrapper">
                                    <span class="monalisa-indikator-code">{{ $assessment->indikator->indikator_code }}</span>
                                </div>
                                <div class="monalisa-table-indikator-name">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $assessment->indikator->name }}
                                    </div>
                                    <!-- Show domain on mobile, tablet, and laptop (hide on xl+) -->
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 xl:hidden">
                                        Domain {{ $assessment->indikator->aspek->domain->domain_number }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-mobile hide-tablet hide-laptop">
                            <div class="monalisa-table-cell-content text-sm text-gray-600 dark:text-gray-400">
                                Domain {{ $assessment->indikator->aspek->domain->domain_number }}
                            </div>
                        </td>
                        <td class="hide-mobile hide-tablet hide-laptop">
                            <div class="monalisa-table-cell-content text-sm text-gray-900 dark:text-white">
                                {{ $assessment->kominfoSubmittedBy->name ?? ($assessment->kominfoCreatedBy->name ?? '-') }}
                            </div>
                        </td>
                        <td>
                            <div class="monalisa-table-levels">
                                @if($assessment->kominfo_maturity_level)
                                <span class="monalisa-table-level-item font-semibold text-blue-600 dark:text-blue-400">
                                    K: L{{ $assessment->kominfo_maturity_level }}
                                </span>
                                @endif
                                @if($assessment->bps_maturity_level)
                                <span class="monalisa-table-level-item font-semibold text-green-600 dark:text-green-400">
                                    BPS: L{{ $assessment->bps_maturity_level }}
                                </span>
                                @endif
                                @if(!$assessment->kominfo_maturity_level && !$assessment->bps_maturity_level)
                                <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="monalisa-table-status">
                            <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                                {{ ucfirst($assessment->status) }}
                            </span>
                        </td>
                        <td class="hide-mobile hide-tablet hide-laptop">
                            <div class="monalisa-table-date text-sm text-gray-600 dark:text-gray-400">
                                @if($assessment->kominfo_submitted_at)
                                    <span>{{ $assessment->kominfo_submitted_at->format('d M Y') }}</span>
                                    <span class="text-xs">{{ $assessment->kominfo_submitted_at->diffForHumans() }}</span>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="monalisa-table-actions">
                                @if($assessment->status !== 'draft')
                                <a href="{{ route('monalisa.bps.assessment.show', $assessment->id) }}"
                                   class="monalisa-btn monalisa-btn-primary btn-sm">
                                    @if($assessment->status === 'verified')
                                        Lihat
                                    @elseif($assessment->status === 'submitted')
                                        Verifikasi
                                    @else
                                        Review
                                    @endif
                                </a>
                                @else
                                <span class="text-gray-400 dark:text-gray-600 text-sm">Menunggu</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="ud-empty-state-compact">
                                <svg class="ud-empty-icon mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-600 dark:text-gray-400">Tidak ada assessment ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($assessments->hasPages())
        <div class="ud-pagination-wrapper mt-6">
            {{ $assessments->links() }}
        </div>
        @endif
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('monalisa.bps.dashboard') }}" 
           class="monalisa-btn monalisa-btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
@endsection

