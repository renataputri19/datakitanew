@extends('layouts.monalisa-dashboard')

@php
    $isBpsUser = auth()->user()->is_bps;
    $isKominfoUser = auth()->user()->is_kominfo_user;
@endphp

@section('title', 'Visualisasi Data - MONALISA' . ($isBpsUser ? ' BPS' : ''))
@section('description', 'Visualisasi perbandingan skor Kominfo dan BPS untuk transparansi')

@push('styles')
<style>
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .dark .chart-container {
        background: #1f2937;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #f3f4f6;
        border-radius: 8px;
    }
    
    .dark .legend-item {
        background: #374151;
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
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

    <!-- Page Header -->
    <div class="ud-page-header">
        <div class="ud-page-header-content">
            <h1 class="ud-page-title">Visualisasi Data {{ $isBpsUser ? 'Verifikasi' : 'Assessment' }}</h1>
            <p class="ud-page-description">
                Perbandingan skor self-assessment Kominfo dan verifikasi BPS untuk transparansi
            </p>
        </div>
    </div>

    <!-- Overall Statistics -->
    @php
        $totalIndikators = 0;
        $assessedIndikators = 0;
        $verifiedIndikators = 0;
        foreach ($chartData['domains'] as $domain) {
            $totalIndikators += $domain['total_indikators'];
            $assessedIndikators += $domain['assessed_indikators'];
            $verifiedIndikators += $domain['verified_indikators'];
        }
        $assessmentProgress = $totalIndikators > 0 ? ($assessedIndikators / $totalIndikators) * 100 : 0;
        $verificationProgress = $totalIndikators > 0 ? ($verifiedIndikators / $totalIndikators) * 100 : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Indikator</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalIndikators }}</div>
                </div>
            </div>
        </div>

        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Sudah Dinilai</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $assessedIndikators }}</div>
                </div>
            </div>
        </div>

        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Terverifikasi</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $verifiedIndikators }}</div>
                </div>
            </div>
        </div>

        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Menunggu</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $assessedIndikators - $verifiedIndikators }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex gap-4 mb-6">
        <div class="legend-item">
            <div class="legend-color" style="background-color: #3b82f6;"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Skor Kominfo (Self-Assessment)</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #10b981;"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Skor BPS (Verifikasi)</span>
        </div>
    </div>

    <!-- Comparison Radar Chart - Overall Score by Domain -->
    <div class="chart-container mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Perbandingan Skor per Domain</h2>
        <div id="domainComparisonRadarChart"></div>
    </div>

    <!-- Domain Details -->
    @foreach($chartData['domains'] as $domain)
    <div class="chart-container mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Domain {{ $domain['domain_number'] }}: {{ $domain['name'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $domain['verified_indikators'] }}/{{ $domain['assessed_indikators'] }} indikator terverifikasi
                </p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-600 dark:text-gray-400">Skor BPS</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ $domain['verified_indikators'] > 0 ? number_format($domain['bps_score'], 2) : 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Aspek Comparison Bar Chart -->
        <div id="aspekComparisonChart{{ $domain['id'] }}"></div>

        <!-- Score Difference Chart -->
        @if($domain['verified_indikators'] > 0)
        <div class="mt-6">
            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Selisih Skor per Aspek</h4>
            <div id="aspekDifferenceChart{{ $domain['id'] }}"></div>
        </div>
        @endif

        <!-- Aspek Details with Spider Charts -->
        @foreach($domain['aspeks'] as $aspek)
        @if($aspek['verified_indikators'] > 0)
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">
                Aspek {{ $aspek['aspek_number'] }}: {{ $aspek['name'] }}
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
                    ({{ $aspek['verified_indikators'] }}/{{ $aspek['total_indikators'] }} indikator terverifikasi)
                </span>
            </h4>
            <div id="aspekRadarChart{{ $aspek['id'] }}"></div>
        </div>
        @endif
        @endforeach
    </div>
    @endforeach

    <!-- Back Button -->
    <div class="flex justify-start mt-8">
        <a href="{{ route($isBpsUser ? 'monalisa.bps.dashboard' : 'monalisa.kominfo.dashboard') }}"
           class="monalisa-btn monalisa-btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e7eb' : '#374151';
    const gridColor = isDark ? '#374151' : '#e5e7eb';

    // Domain Comparison Radar Chart
    const domainData = @json($chartData['domains']);
    
    const domainRadarOptions = {
        series: [
            {
                name: 'Skor Kominfo',
                data: domainData.map(d => d.assessed_indikators > 0 ? d.kominfo_score.toFixed(2) : 0)
            },
            {
                name: 'Skor BPS',
                data: domainData.map(d => d.verified_indikators > 0 ? d.bps_score.toFixed(2) : 0)
            }
        ],
        chart: {
            height: 450,
            type: 'radar',
            toolbar: {
                show: false
            },
            background: 'transparent'
        },
        colors: ['#3b82f6', '#10b981'],
        xaxis: {
            categories: domainData.map(d => `Domain ${d.domain_number}`),
            labels: {
                style: {
                    colors: Array(domainData.length).fill(textColor),
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            min: 0,
            max: 5,
            tickAmount: 5,
            labels: {
                style: {
                    colors: textColor
                }
            }
        },
        plotOptions: {
            radar: {
                polygons: {
                    strokeColors: gridColor,
                    fill: {
                        colors: [isDark ? '#1f2937' : '#ffffff', isDark ? '#111827' : '#f9fafb']
                    }
                }
            }
        },
        markers: {
            size: 4,
            strokeWidth: 2,
        },
        legend: {
            labels: {
                colors: textColor
            }
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light'
        }
    };

    const domainRadarChart = new ApexCharts(document.querySelector("#domainComparisonRadarChart"), domainRadarOptions);
    domainRadarChart.render();

    // Aspek Comparison Bar Charts for each domain
    domainData.forEach(domain => {
        const aspekOptions = {
            series: [
                {
                    name: 'Skor Kominfo',
                    data: domain.aspeks.map(a => a.assessed_indikators > 0 ? a.kominfo_score.toFixed(2) : 0)
                },
                {
                    name: 'Skor BPS',
                    data: domain.aspeks.map(a => a.verified_indikators > 0 ? a.bps_score.toFixed(2) : 0)
                }
            ],
            chart: {
                height: 300,
                type: 'bar',
                toolbar: {
                    show: false
                },
                background: 'transparent'
            },
            colors: ['#3b82f6', '#10b981'],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: false,
                    columnWidth: '70%',
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '11px',
                    colors: [textColor]
                },
                formatter: function(val) {
                    return val > 0 ? val : '';
                }
            },
            xaxis: {
                categories: domain.aspeks.map(a => `Aspek ${a.aspek_number}`),
                labels: {
                    style: {
                        colors: Array(domain.aspeks.length).fill(textColor),
                        fontSize: '11px'
                    }
                }
            },
            yaxis: {
                min: 0,
                max: 5,
                tickAmount: 5,
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            grid: {
                borderColor: gridColor
            },
            legend: {
                labels: {
                    colors: textColor
                }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light'
            }
        };

        const aspekChart = new ApexCharts(document.querySelector(`#aspekComparisonChart${domain.id}`), aspekOptions);
        aspekChart.render();

        // Score Difference Chart - showing the gap between Kominfo and BPS scores
        if (domain.verified_indikators > 0) {
            const differenceData = domain.aspeks
                .filter(a => a.verified_indikators > 0)
                .map(a => ({
                    aspek: `Aspek ${a.aspek_number}`,
                    difference: parseFloat((a.bps_score - a.kominfo_score).toFixed(2)),
                    kominfo: a.kominfo_score,
                    bps: a.bps_score
                }));

            if (differenceData.length > 0) {
                const differenceOptions = {
                    series: [{
                        name: 'Selisih (BPS - Kominfo)',
                        data: differenceData.map(d => d.difference)
                    }],
                    chart: {
                        height: 250,
                        type: 'bar',
                        toolbar: {
                            show: false
                        },
                        background: 'transparent'
                    },
                    colors: differenceData.map(d => d.difference >= 0 ? '#10b981' : '#ef4444'),
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            horizontal: true,
                            distributed: true,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            colors: [textColor]
                        },
                        formatter: function(val) {
                            return val > 0 ? `+${val}` : val;
                        }
                    },
                    xaxis: {
                        categories: differenceData.map(d => d.aspek),
                        labels: {
                            style: {
                                colors: textColor,
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: Array(differenceData.length).fill(textColor),
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: gridColor,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: {
                            formatter: function(val, opts) {
                                const data = differenceData[opts.dataPointIndex];
                                return `Selisih: ${val > 0 ? '+' + val : val}<br>Kominfo: ${data.kominfo.toFixed(2)}<br>BPS: ${data.bps.toFixed(2)}`;
                            }
                        }
                    }
                };

                const differenceChart = new ApexCharts(
                    document.querySelector(`#aspekDifferenceChart${domain.id}`),
                    differenceOptions
                );
                differenceChart.render();
            }
        }

        // Aspek Radar Charts - showing indicators within each aspek with comparison
        domain.aspeks.forEach(aspek => {
            if (aspek.verified_indikators > 0) {
                const indikatorData = aspek.indikators.filter(ind => ind.bps_score !== null);

                if (indikatorData.length > 0) {
                    const aspekRadarOptions = {
                        series: [
                            {
                                name: 'Skor Kominfo',
                                data: indikatorData.map(ind => ind.kominfo_score || 0)
                            },
                            {
                                name: 'Skor BPS',
                                data: indikatorData.map(ind => ind.bps_score)
                            }
                        ],
                        chart: {
                            height: 350,
                            type: 'radar',
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#3b82f6', '#10b981'],
                        xaxis: {
                            categories: indikatorData.map(ind => ind.code),
                            labels: {
                                style: {
                                    colors: Array(indikatorData.length).fill(textColor),
                                    fontSize: '11px'
                                }
                            }
                        },
                        yaxis: {
                            min: 0,
                            max: 5,
                            tickAmount: 5,
                            labels: {
                                style: {
                                    colors: textColor
                                }
                            }
                        },
                        plotOptions: {
                            radar: {
                                polygons: {
                                    strokeColors: gridColor,
                                    fill: {
                                        colors: [isDark ? '#1f2937' : '#ffffff', isDark ? '#111827' : '#f9fafb']
                                    }
                                }
                            }
                        },
                        markers: {
                            size: 4,
                            strokeWidth: 2,
                        },
                        legend: {
                            labels: {
                                colors: textColor
                            }
                        },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                            y: {
                                formatter: function(val, opts) {
                                    const indikator = indikatorData[opts.dataPointIndex];
                                    const seriesName = opts.w.config.series[opts.seriesIndex].name;
                                    return `${indikator.name}: Level ${val}`;
                                }
                            }
                        }
                    };

                    const aspekRadarChart = new ApexCharts(
                        document.querySelector(`#aspekRadarChart${aspek.id}`),
                        aspekRadarOptions
                    );
                    aspekRadarChart.render();
                }
            }
        });
    });
});
</script>
@endpush

