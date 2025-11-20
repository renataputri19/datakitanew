{{--
    DEPRECATED: This view is deprecated in favor of the unified dashboard.
    Both Kominfo and BPS users now use resources/views/monalisa/bps/dashboard.blade.php
    for transparency and to show comparison between Kominfo and BPS scores.

    This file is kept for reference only and should not be used.
    The KominfoController now returns the unified BPS dashboard view.
--}}

@extends('layouts.monalisa-dashboard')

@section('title', 'Dashboard MONALISA - Kominfo')
@section('description', 'Dashboard self-assessment MONALISA untuk Kominfo')

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
            <h1 class="ud-page-title">Dashboard MONALISA</h1>
            <p class="ud-page-description">Sistem Monitoring dan Evaluasi Statistik Sektoral - Self Assessment</p>
        </div>
    </div>

    <!-- Score Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="monalisa-score-card">
            <div class="monalisa-score-label">Total IPS Score</div>
            <div class="monalisa-score-value">{{ number_format($scores['total'], 2) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Indeks Pembangunan Statistik</div>
        </div>

        <div class="monalisa-score-card accent">
            <div class="monalisa-score-label">Completed Assessments</div>
            <div class="monalisa-score-value">{{ $assessments->where('status', '!=', 'draft')->count() }}</div>
            <div class="text-sm text-white/80 mt-2">dari {{ $domains->sum(fn($d) => $d->aspeks->sum(fn($a) => $a->indikators->count())) }} indikator</div>
        </div>

        <div class="monalisa-score-card">
            <div class="monalisa-score-label">Verified by BPS</div>
            <div class="monalisa-score-value">{{ $assessments->where('status', 'verified')->count() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">assessment terverifikasi</div>
        </div>
    </div>

    <!-- Domain Overview -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Domain Overview</h2>

        @foreach($domains as $domain)
        <div class="monalisa-domain-card">
            <div class="monalisa-domain-header">
                <div>
                    <div class="monalisa-domain-title">
                        Domain {{ $domain->domain_number }}: {{ $domain->name }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $domain->aspeks->count() }} Aspek, {{ $domain->indikators->count() }} Indikator
                    </div>
                </div>
                <div class="monalisa-domain-weight">{{ $domain->weight }}%</div>
            </div>

            @php
                $domainIndikators = $domain->indikators->pluck('id');
                $domainAssessments = $assessments->whereIn('indikator_id', $domainIndikators);
                $completedCount = $domainAssessments->where('status', '!=', 'draft')->count();
                $totalCount = $domainIndikators->count();
                $progress = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
            @endphp

            <div class="mb-3">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <span>Progress</span>
                    <span>{{ $completedCount }}/{{ $totalCount }} ({{ number_format($progress, 0) }}%)</span>
                </div>
                <div class="monalisa-progress">
                    <div class="monalisa-progress-bar" style="width: {{ $progress }}%"></div>
                </div>
            </div>


        </div>
        @endforeach
    </div>

    <!-- Recent Assessments -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Recent Assessments</h2>

        <div class="monalisa-table-wrapper has-mobile-cards">
            <!-- Mobile Card Layout -->
            <div class="monalisa-mobile-cards">
                @forelse($assessments->sortByDesc('updated_at')->take(10) as $assessment)
                <div class="monalisa-assessment-card">
                    <!-- Card Header - Indicator -->
                    <div class="monalisa-card-header">
                        <div class="monalisa-card-title">
                            {{ $assessment->indikator->name }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $assessment->indikator->aspek->domain->name }}
                        </div>
                    </div>

                    <!-- Card Body - Details -->
                    <div class="monalisa-card-body">
                        <!-- Maturity Level -->
                        <div class="monalisa-card-row">
                            <div class="monalisa-card-label">Maturity Level</div>
                            <div class="monalisa-card-value">
                                @if($assessment->kominfo_maturity_level)
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">Level {{ $assessment->kominfo_maturity_level }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
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
                    </div>

                    <!-- Card Footer - Action -->
                    <div class="monalisa-card-footer">
                        <a href="{{ route('monalisa.kominfo.assessment.show', $assessment->indikator_id) }}"
                           class="monalisa-btn monalisa-btn-primary">
                            {{ $assessment->status === 'draft' ? 'Continue' : 'View' }}
                        </a>
                    </div>
                </div>
                @empty
                <div class="monalisa-card-empty">
                    <svg class="monalisa-card-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="monalisa-card-empty-text">Belum ada assessment. Mulai dengan memilih domain di atas.</p>
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
                            <th scope="col">
                                Level
                            </th>
                            <th scope="col">
                                Status
                            </th>
                            <th scope="col">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessments->sortByDesc('updated_at')->take(10) as $assessment)
                        <tr>
                            <td class="monalisa-table-indikator-cell">
                                <div class="monalisa-table-indikator-wrapper">
                                    <div class="monalisa-table-indikator-name">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $assessment->indikator->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $assessment->indikator->aspek->domain->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="monalisa-table-cell-content text-sm text-gray-900 dark:text-white">
                                    @if($assessment->kominfo_maturity_level)
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">Level {{ $assessment->kominfo_maturity_level }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="monalisa-table-status">
                                <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                                    {{ ucfirst($assessment->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="monalisa-table-actions">
                                    <a href="{{ route('monalisa.kominfo.assessment.show', $assessment->indikator_id) }}"
                                       class="monalisa-btn monalisa-btn-primary btn-sm">
                                        {{ $assessment->status === 'draft' ? 'Continue' : 'View' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="ud-empty-state-compact">
                                    <svg class="ud-empty-icon mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">Belum ada assessment. Mulai dengan memilih domain di atas.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

