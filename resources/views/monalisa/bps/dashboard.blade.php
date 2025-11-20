@extends('layouts.monalisa-dashboard')

@php
    $isBpsUser = auth()->user()->is_bps;
    $isKominfoUser = auth()->user()->is_kominfo_user;
@endphp

@section('title', 'Dashboard MONALISA' . ($isBpsUser ? ' - BPS Verifikasi' : ' - Kominfo'))
@section('description', 'Dashboard ' . ($isBpsUser ? 'verifikasi' : 'self-assessment') . ' MONALISA')

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
            <h1 class="ud-page-title">Dashboard MONALISA{{ $isBpsUser ? ' - BPS' : '' }}</h1>
            <p class="ud-page-description">
                Sistem Monitoring dan Evaluasi Statistik Sektoral -
                {{ $isBpsUser ? 'Audit & Verifikasi' : 'Self Assessment' }}
            </p>
        </div>
    </div>

    <!-- Score Toggle - Show comparison for transparency -->
    <div class="monalisa-score-toggle">
        <button data-score-toggle="kominfo" class="monalisa-btn monalisa-btn-primary active">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Skor Kominfo (Self-Assessment)
        </button>
        <button data-score-toggle="bps" class="monalisa-btn monalisa-btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Skor BPS (Verified)
        </button>
    </div>

    <!-- Score Overview - Kominfo -->
    <div data-score-type="kominfo" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="monalisa-score-card">
            <div class="monalisa-score-label">Total IPS Score (Kominfo)</div>
            <div class="monalisa-score-value">{{ number_format($kominfoScores['total'], 2) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Self-Assessment Score</div>
        </div>

        <div class="monalisa-score-card accent">
            <div class="monalisa-score-label">Submitted Assessments</div>
            <div class="monalisa-score-value">{{ $assessments->where('status', 'submitted')->count() }}</div>
            <div class="text-sm text-white/80 mt-2">menunggu verifikasi</div>
        </div>

        <div class="monalisa-score-card">
            <div class="monalisa-score-label">Verified Assessments</div>
            <div class="monalisa-score-value">{{ $assessments->where('status', 'verified')->count() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">sudah diverifikasi</div>
        </div>
    </div>

    <!-- Score Overview - BPS -->
    <div data-score-type="bps" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" style="display: none;">
        <div class="monalisa-score-card">
            <div class="monalisa-score-label">Total IPS Score (BPS)</div>
            <div class="monalisa-score-value">{{ number_format($bpsScores['total'], 2) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Verified Score</div>
        </div>

        <div class="monalisa-score-card accent">
            <div class="monalisa-score-label">Score Difference</div>
            <div class="monalisa-score-value">
                {{ number_format(abs($kominfoScores['total'] - $bpsScores['total']), 2) }}
            </div>
            <div class="text-sm text-white/80 mt-2">selisih dengan self-assessment</div>
        </div>

        <div class="monalisa-score-card">
            <div class="monalisa-score-label">Agreement Rate</div>
            @php
                $agreementRate = $assessments->where('status', 'verified')->filter(function($a) {
                    return $a->kominfo_maturity_level === $a->bps_maturity_level;
                })->count();
                $totalVerified = $assessments->where('status', 'verified')->count();
                $agreementPercent = $totalVerified > 0 ? ($agreementRate / $totalVerified) * 100 : 0;
            @endphp
            <div class="monalisa-score-value">{{ number_format($agreementPercent, 0) }}%</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">tingkat kesesuaian</div>
        </div>
    </div>

    <!-- Domain Overview -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Domain Overview</h2>

        @foreach($domains as $domain)
        @php
            $domainIndikators = $domain->indikators->pluck('id');
            $domainAssessments = $assessments->whereIn('indikator_id', $domainIndikators);
            $verifiedCount = $domainAssessments->where('status', 'verified')->count();
            $submittedCount = $domainAssessments->where('status', 'submitted')->count();
            $totalCount = $domainIndikators->count();
            $progressPercent = $totalCount > 0 ? ($verifiedCount / $totalCount) * 100 : 0;
        @endphp

        <div class="monalisa-domain-card">
            <!-- Domain Header -->
            <div class="monalisa-domain-header">
                <div class="monalisa-domain-title-wrapper">
                    <div class="monalisa-domain-title">
                        Domain {{ $domain->domain_number }}: {{ $domain->name }}
                    </div>
                    <div class="monalisa-domain-subtitle">
                        {{ $domain->aspeks->count() }} Aspek • {{ $domain->indikators->count() }} Indikator
                    </div>
                </div>
                <div class="monalisa-domain-weight">
                    Bobot: {{ $domain->weight }}%
                </div>
            </div>

            <!-- Domain Stats Grid -->
            <div class="monalisa-domain-stats">
                <div class="monalisa-domain-stat-item">
                    <div class="monalisa-domain-stat-label">Verified</div>
                    <div class="monalisa-domain-stat-value verified">{{ $verifiedCount }}</div>
                </div>
                <div class="monalisa-domain-stat-item">
                    <div class="monalisa-domain-stat-label">Pending</div>
                    <div class="monalisa-domain-stat-value pending">{{ $submittedCount }}</div>
                </div>
                <div class="monalisa-domain-stat-item">
                    <div class="monalisa-domain-stat-label">Total</div>
                    <div class="monalisa-domain-stat-value total">{{ $totalCount }}</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="monalisa-domain-progress-wrapper">
                <div class="monalisa-domain-progress-label">
                    <span>Progress Verifikasi</span>
                    <span>{{ number_format($progressPercent, 0) }}%</span>
                </div>
                <div class="monalisa-domain-progress-bar-container">
                    <div class="monalisa-domain-progress-bar" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="monalisa-domain-actions">
                <a href="{{ route('monalisa.bps.domain', $domain->id) }}"
                   class="monalisa-domain-action-btn monalisa-domain-action-btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Lihat Detail Domain
                </a>
                @if($submittedCount > 0)
                <a href="{{ route('monalisa.bps.assessments', ['domain' => $domain->id, 'status' => 'submitted']) }}"
                   class="monalisa-domain-action-btn monalisa-domain-action-btn-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Verifikasi ({{ $submittedCount }})
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pending Verifications -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Pending Verifications</h2>

        <div class="monalisa-table-wrapper has-mobile-cards">
            <!-- Mobile Card Layout -->
            <div class="monalisa-mobile-cards">
                @forelse($assessments->where('status', 'submitted')->sortByDesc('kominfo_submitted_at')->take(10) as $assessment)
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
                        <!-- Kominfo User -->
                        <div class="monalisa-card-row">
                            <div class="monalisa-card-label">Kominfo User</div>
                            <div class="monalisa-card-value">
                                {{ $assessment->kominfoUser->name ?? '-' }}
                            </div>
                        </div>

                        <!-- Self-Assessment Level -->
                        <div class="monalisa-card-row">
                            <div class="monalisa-card-label">Self-Assessment Level</div>
                            <div class="monalisa-card-value">
                                <span class="font-semibold text-blue-600 dark:text-blue-400">
                                    Level {{ $assessment->kominfo_maturity_level }}
                                </span>
                            </div>
                        </div>

                        <!-- Submitted -->
                        <div class="monalisa-card-row">
                            <div class="monalisa-card-label">Submitted</div>
                            <div class="monalisa-card-value text-gray-500 dark:text-gray-400">
                                {{ $assessment->kominfo_submitted_at?->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer - Action -->
                    <div class="monalisa-card-footer">
                        <a href="{{ route('monalisa.bps.assessment.show', $assessment->id) }}"
                           class="monalisa-btn monalisa-btn-primary">
                            Verify
                        </a>
                    </div>
                </div>
                @empty
                <div class="monalisa-card-empty">
                    <svg class="monalisa-card-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="monalisa-card-empty-text">Tidak ada assessment yang menunggu verifikasi.</p>
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
                            <th scope="col" class="hide-mobile">
                                Kominfo User
                            </th>
                            <th scope="col">
                                Level
                            </th>
                            <th scope="col" class="hide-mobile">
                                Submitted
                            </th>
                            <th scope="col">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessments->where('status', 'submitted')->sortByDesc('kominfo_submitted_at')->take(10) as $assessment)
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
                            <td class="hide-mobile">
                                <div class="monalisa-table-cell-content text-sm text-gray-900 dark:text-white">
                                    {{ $assessment->kominfoUser->name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="monalisa-table-cell-content text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    Level {{ $assessment->kominfo_maturity_level }}
                                </div>
                            </td>
                            <td class="hide-mobile">
                                <div class="monalisa-table-cell-content text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assessment->kominfo_submitted_at?->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                <div class="monalisa-table-actions">
                                    <a href="{{ route('monalisa.bps.assessment.show', $assessment->id) }}"
                                       class="monalisa-btn monalisa-btn-primary btn-sm">
                                        Verify
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="ud-empty-state-compact">
                                    <svg class="ud-empty-icon mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">Tidak ada assessment yang menunggu verifikasi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assessments->where('status', 'submitted')->count() > 10)
            <div class="mt-4 text-center px-4 pb-4">
                <a href="{{ route('monalisa.bps.assessments') }}" class="monalisa-btn monalisa-btn-secondary">
                    Lihat Semua Assessment
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Back to Dashboard Button -->
    <div class="mt-8 text-center">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
@endsection

@push('scripts')
<script>
// Score toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('[data-score-toggle]');
    const scoreContainers = document.querySelectorAll('[data-score-type]');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const scoreType = this.dataset.scoreToggle;

            // Update button states
            toggleButtons.forEach(btn => {
                btn.classList.remove('monalisa-btn-primary', 'active');
                btn.classList.add('monalisa-btn-secondary');
            });
            this.classList.remove('monalisa-btn-secondary');
            this.classList.add('monalisa-btn-primary', 'active');

            // Show/hide score containers
            scoreContainers.forEach(container => {
                if (container.dataset.scoreType === scoreType) {
                    container.style.display = 'grid';
                } else {
                    container.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush

