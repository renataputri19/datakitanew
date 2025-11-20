@extends('layouts.monalisa-dashboard')

@section('title', 'Domain ' . $domain->domain_number . ' - MONALISA Kominfo')
@section('description', 'Detail domain ' . $domain->name . ' untuk self-assessment MONALISA')

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
            <p class="ud-page-description">{{ $domain->description ?? 'Lengkapi assessment untuk semua indikator dalam domain ini' }}</p>
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

    <!-- Progress Overview -->
    @php
        $totalIndikators = $domain->indikators->count();
        $completedAssessments = $assessments->where('status', '!=', 'draft')->count();
        $progress = $totalIndikators > 0 ? ($completedAssessments / $totalIndikators) * 100 : 0;
    @endphp
    
    <div class="ud-card mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Progress Assessment</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $completedAssessments }} dari {{ $totalIndikators }} indikator telah diselesaikan
                </p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($progress, 0) }}%</div>
            </div>
        </div>
        <div class="monalisa-progress">
            <div class="monalisa-progress-bar" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <!-- Aspeks and Indikators -->
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
                $hasAssessment = $assessment && $assessment->status !== 'draft';
            @endphp
            
            <div class="monalisa-indikator-item">
                <div class="flex items-center gap-3 flex-1">
                    <span class="monalisa-indikator-code">{{ $indikator->indikator_code }}</span>
                    <span class="monalisa-indikator-name">{{ $indikator->name }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    @if($hasAssessment)
            <span class="monalisa-badge monalisa-badge-{{ $assessment->status }}">
                {{ ucfirst($assessment->status) }}
            </span>
            
            <div class="text-sm">
                @if($assessment->kominfo_maturity_level)
                <span class="font-semibold text-blue-600 dark:text-blue-400">
                    K: L{{ $assessment->kominfo_maturity_level }}
                </span>
                @endif
                @if($assessment->bps_maturity_level)
                <span class="font-semibold text-green-600 dark:text-green-400 ml-2">
                    BPS: L{{ $assessment->bps_maturity_level }}
                </span>
                @endif
            </div>
        @else
            <span class="monalisa-badge monalisa-badge-draft">Belum Dinilai</span>
        @endif
                    
                    <a href="{{ route('monalisa.kominfo.assessment.show', $indikator->id) }}" 
                       class="monalisa-btn monalisa-btn-primary btn-sm">
                        @if($hasAssessment)
                            @if($assessment->status === 'verified')
                                Lihat
                            @else
                                Edit
                            @endif
                        @else
                            Mulai
                        @endif
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Navigation -->
    <div class="monalisa-bottom-nav">
        <a href="{{ route('monalisa.kominfo.dashboard') }}"
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
        <a href="{{ route('monalisa.kominfo.domain', $nextDomain->id) }}"
           class="monalisa-btn monalisa-btn-primary">
            Domain {{ $nextDomain->domain_number }} Selanjutnya
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
        @endif
    </div>
@endsection

@push('scripts')
<script>
// Show success toast after redirect from assessment submission
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        // Use DataKita-styled toast via MONALISA notification system
        if (window.MonalisaNotifications && typeof window.MonalisaNotifications.showToast === 'function') {
            window.MonalisaNotifications.showToast('Berhasil', @json(session('success')), 'success');
        }
    @endif
});
</script>
@endpush

