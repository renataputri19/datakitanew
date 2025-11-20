@extends('layouts.monalisa-dashboard')

@php
    $isBpsUser = auth()->user()->is_bps;
    $isKominfoUser = auth()->user()->is_kominfo_user;
@endphp

@section('title', 'Analisis Detail Indikator - MONALISA' . ($isBpsUser ? ' BPS' : ''))
@section('description', 'Analisis mendalam tingkat indikator untuk identifikasi area perbaikan spesifik')

@push('styles')
<style>
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box; /* ensure padding doesn't cause overflow */
    }

    .dark .chart-container {
        background: #1f2937;
    }

    .comparison-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 48px;
    }

    @media (min-width: 1024px) {
        .comparison-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .score-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
    }

    .dark .score-section-header {
        border-bottom-color: #374151;
    }

    .score-section-header svg {
        width: 24px;
        height: 24px;
    }

    .score-section-header.kominfo {
        color: #3b82f6;
    }

    .score-section-header.bps {
        color: #10b981;
    }

    .score-section-title {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .aspek-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
    }

    .dark .aspek-header {
        border-bottom-color: #374151;
    }

    .aspek-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1f2937;
    }

    .dark .aspek-title {
        color: #f3f4f6;
    }

    .aspek-stats {
        display: flex;
        gap: 12px;
        font-size: 0.875rem;
        flex-wrap: wrap;
    }

    .stat-badge {
        padding: 4px 12px;
        border-radius: 6px;
        font-weight: 600;
    }

    .stat-badge.assessed {
        background: #dbeafe;
        color: #1e40af;
    }

    .dark .stat-badge.assessed {
        background: #1e3a8a;
        color: #93c5fd;
    }

    .stat-badge.verified {
        background: #d1fae5;
        color: #065f46;
    }

    .dark .stat-badge.verified {
        background: #064e3b;
        color: #6ee7b7;
    }

    .stat-badge.avg-score {
        background: #fef3c7;
        color: #92400e;
    }

    .dark .stat-badge.avg-score {
        background: #78350f;
        color: #fde68a;
    }

    .domain-section {
        margin-bottom: 48px;
    }

    .domain-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 3px solid #3b82f6;
    }

    .dark .domain-title {
        color: #f3f4f6;
        border-bottom-color: #60a5fa;
    }

    .indicator-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .dark .indicator-legend {
        background: #111827;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .dark .legend-item {
        color: #9ca3af;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    /* IPS Rating Colors */
    .legend-dot.memuaskan {
        background: #10b981; /* Green - Satisfactory */
    }

    .legend-dot.sangat-baik {
        background: #3b82f6; /* Blue - Very Good */
    }

    .legend-dot.baik {
        background: #f59e0b; /* Amber - Good */
    }

    .legend-dot.cukup {
        background: #f97316; /* Orange - Fair */
    }

    .legend-dot.kurang {
        background: #ef4444; /* Red - Poor */
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        margin-bottom: 24px;
    }

    .back-button:hover {
        background: #e5e7eb;
        transform: translateX(-4px);
    }

    .dark .back-button {
        background: #374151;
        color: #e5e7eb;
    }

    .dark .back-button:hover {
        background: #4b5563;
    }

    .back-button svg {
        width: 20px;
        height: 20px;
    }

    /* Ensure ApexCharts scales to container width and avoids overflow */
    .chart-min {
        min-height: 400px;
        width: 100%;
        max-width: 100%;
    }

    @media (max-width: 1024px) {
        .chart-min { min-height: 320px; }
    }

    @media (max-width: 640px) {
        .chart-min { min-height: 280px; }
    }

    /* ApexCharts SVG/container should never exceed parent width */
    .apexcharts-canvas,
    .apexcharts-svg {
        width: 100% !important;
        max-width: 100% !important;
    }
</style>
@endpush

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

    <!-- Back Button -->
    {{-- <a href="{{ route('monalisa.' . ($isBpsUser ? 'bps' : 'kominfo') . '.charts') }}" class="back-button" data-aos="fade-right">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Visualisasi
    </a> --}}

    <!-- Page Header -->
    <div class="ud-page-header">
        <div class="ud-page-header-content">
            <h1 class="ud-page-title">Analisis Detail Indikator</h1>
            <p class="ud-page-description">
                Analisis mendalam pada tingkat indikator untuk mengidentifikasi area spesifik yang memerlukan perbaikan. Perbandingan skor Kominfo (Self-Assessment) dan BPS (Verified) ditampilkan berdampingan untuk transparansi.
            </p>
        </div>
    </div>

    <!-- IPS Rating Legend -->
    <div class="indicator-legend" style="margin-bottom: 24px;">
        <div style="font-weight: 700; width: 100%; margin-bottom: 8px; color: #1f2937;" class="dark:text-gray-200">
            Skala IPS (Indeks Pembangunan Statistik):
        </div>
        <div class="legend-item">
            <span class="legend-dot memuaskan"></span>
            <span><strong>Memuaskan</strong> (4.2 - 5.0)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot sangat-baik"></span>
            <span><strong>Sangat Baik</strong> (3.5 - &lt;4.2)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot baik"></span>
            <span><strong>Baik</strong> (2.6 - &lt;3.5)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot cukup"></span>
            <span><strong>Cukup</strong> (1.8 - &lt;2.6)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot kurang"></span>
            <span><strong>Kurang</strong> (&lt;1.8)</span>
        </div>
    </div>

    @foreach($chartData['domains'] as $domain)
    <div class="domain-section">
        <h2 class="domain-title">Domain {{ $domain['domain_number'] }}: {{ $domain['name'] }}</h2>

        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ $domain['total_indikators'] }} indikator total |
            {{ $domain['assessed_indikators'] }} dinilai Kominfo |
            {{ $domain['verified_indikators'] }} terverifikasi BPS
        </div>

        <!-- Domain-Level Radar Charts (All Indicators as axes) -->
        <div class="comparison-grid">
            <!-- Kominfo Domain Chart -->
            <div class="chart-container">
                <div class="score-section-header kominfo">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h3 class="score-section-title">Skor Kominfo (Self-Assessment)</h3>
                </div>
                <div id="kominfodomainChart{{ $domain['id'] }}" class="chart-min"></div>
                @if($domain['kominfo_avg_score'] > 0)
                <div class="text-center mt-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                    Rata-rata Domain: {{ number_format($domain['kominfo_avg_score'], 2) }}
                </div>
                @endif
            </div>

            <!-- BPS Domain Chart -->
            <div class="chart-container">
                <div class="score-section-header bps">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="score-section-title">Skor BPS (Verified)</h3>
                </div>
                <div id="bpsDomainChart{{ $domain['id'] }}" class="chart-min"></div>
                @if($domain['bps_avg_score'] > 0)
                <div class="text-center mt-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                    Rata-rata Domain: {{ number_format($domain['bps_avg_score'], 2) }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e7eb' : '#374151';
    const gridColor = isDark ? '#374151' : '#e5e7eb';
    const chartData = @json($chartData);

    // Responsive breakpoints for ApexCharts
    const responsiveBreakpoints = [
        {
            breakpoint: 1024,
            options: {
                chart: { height: 360 },
                xaxis: { labels: { style: { fontSize: '9px' } } },
                markers: { size: 3 }
            }
        },
        {
            breakpoint: 768,
            options: {
                chart: { height: 320 },
                xaxis: { labels: { style: { fontSize: '8px' } } },
                markers: { size: 3 }
            }
        },
        {
            breakpoint: 480,
            options: {
                chart: { height: 280 },
                xaxis: { labels: { style: { fontSize: '7px' } } },
                markers: { size: 2 }
            }
        }
    ];

    // IPS Rating System Helper Function
    function getIPSPredikat(score) {
        if (score >= 4.2) return 'Memuaskan';
        if (score >= 3.5) return 'Sangat Baik';
        if (score >= 2.6) return 'Baik';
        if (score >= 1.8) return 'Cukup';
        return 'Kurang';
    }

    function getIPSColor(score) {
        if (score >= 4.2) return '#10b981'; // Green - Memuaskan
        if (score >= 3.5) return '#3b82f6'; // Blue - Sangat Baik
        if (score >= 2.6) return '#f59e0b'; // Amber - Baik
        if (score >= 1.8) return '#f97316'; // Orange - Cukup
        return '#ef4444'; // Red - Kurang
    }

    // Render Charts for each domain
    chartData.domains.forEach(domain => {
        // Kominfo Domain Chart - All Indicators
        if (domain.indikators && domain.indikators.length > 0) {
            const kominfodomainOptions = {
                series: [{
                    name: 'Skor Kominfo',
                    data: domain.indikators.map(ind => ind.kominfo_score || 0)
                }],
                chart: {
                    height: 400,
                    width: '100%',
                    type: 'radar',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                colors: ['#3b82f6'],
                xaxis: {
                    categories: domain.indikators.map(ind => ind.code),
                    labels: {
                        style: {
                            colors: Array(domain.indikators.length).fill(textColor),
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 5,
                    tickAmount: 5,
                    labels: {
                        style: { colors: textColor },
                        formatter: function(val) { return val.toFixed(0); }
                    }
                },
                plotOptions: {
                    radar: {
                        polygons: {
                            strokeColors: gridColor,
                            strokeWidth: 1,
                            fill: {
                                colors: [isDark ? '#1f2937' : '#ffffff', isDark ? '#111827' : '#f9fafb']
                            }
                        }
                    }
                },
                markers: {
                    size: 4,
                    colors: ['#3b82f6'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val, opts) {
                            const indikator = domain.indikators[opts.dataPointIndex];
                            if (val === 0) {
                                return `${indikator.name}: Belum Dinilai`;
                            }
                            const predikat = getIPSPredikat(val);
                            return `${indikator.name}: ${val} (${predikat})`;
                        }
                    }
                },
                legend: { show: false },
                responsive: responsiveBreakpoints
            };

            const kominfodomainChart = new ApexCharts(
                document.querySelector(`#kominfodomainChart${domain.id}`),
                kominfodomainOptions
            );
            kominfodomainChart.render();
        }

        // BPS Domain Chart - All Indicators
        if (domain.indikators && domain.indikators.length > 0) {
            const bpsDomainOptions = {
                series: [{
                    name: 'Skor BPS',
                    data: domain.indikators.map(ind => ind.bps_score || 0)
                }],
                chart: {
                    height: 400,
                    width: '100%',
                    type: 'radar',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                colors: ['#10b981'],
                xaxis: {
                    categories: domain.indikators.map(ind => ind.code),
                    labels: {
                        style: {
                            colors: Array(domain.indikators.length).fill(textColor),
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 5,
                    tickAmount: 5,
                    labels: {
                        style: { colors: textColor },
                        formatter: function(val) { return val.toFixed(0); }
                    }
                },
                plotOptions: {
                    radar: {
                        polygons: {
                            strokeColors: gridColor,
                            strokeWidth: 1,
                            fill: {
                                colors: [isDark ? '#1f2937' : '#ffffff', isDark ? '#111827' : '#f9fafb']
                            }
                        }
                    }
                },
                markers: {
                    size: 4,
                    colors: ['#10b981'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val, opts) {
                            const indikator = domain.indikators[opts.dataPointIndex];
                            if (val === 0) {
                                return `${indikator.name}: Belum Terverifikasi`;
                            }
                            const predikat = getIPSPredikat(val);
                            return `${indikator.name}: ${val} (${predikat})`;
                        }
                    }
                },
                legend: { show: false },
                responsive: responsiveBreakpoints
            };

            const bpsDomainChart = new ApexCharts(
                document.querySelector(`#bpsDomainChart${domain.id}`),
                bpsDomainOptions
            );
            bpsDomainChart.render();
        }
    });
});
</script>
@endpush

