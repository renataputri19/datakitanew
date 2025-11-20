{{--
    DEPRECATED: This view is deprecated in favor of the unified charts view.
    Both Kominfo and BPS users now use resources/views/monalisa/bps/charts.blade.php
    for transparency and to show comparison between Kominfo and BPS scores.

    This file is kept for reference only and should not be used.
    The KominfoController now returns the unified BPS charts view.
--}}

@extends('layouts.monalisa-dashboard')

@section('title', 'Visualisasi Data - MONALISA Kominfo')
@section('description', 'Visualisasi progress dan skor assessment MONALISA')

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
            <h1 class="ud-page-title">Visualisasi Data Assessment</h1>
            <p class="ud-page-description">Grafik dan analisis progress self-assessment MONALISA Anda</p>
        </div>
    </div>

    <!-- Overall Progress -->
    @php
        $totalIndikators = 0;
        $assessedIndikators = 0;
        foreach ($chartData['domains'] as $domain) {
            $totalIndikators += $domain['total_indikators'];
            $assessedIndikators += $domain['assessed_indikators'];
        }
        $overallProgress = $totalIndikators > 0 ? ($assessedIndikators / $totalIndikators) * 100 : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Progress Keseluruhan</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($overallProgress, 1) }}%</div>
                </div>
            </div>
        </div>

        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Indikator Dinilai</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $assessedIndikators }}/{{ $totalIndikators }}</div>
                </div>
            </div>
        </div>

        <div class="ud-card">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Domain</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($chartData['domains']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Radar Chart - Overall Score by Domain -->
    <div class="chart-container mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Skor per Domain</h2>
        <div id="domainRadarChart"></div>
    </div>

    <!-- Domain Details -->
    @foreach($chartData['domains'] as $domain)
    <div class="chart-container mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Domain {{ $domain['domain_number'] }}: {{ $domain['name'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $domain['assessed_indikators'] }}/{{ $domain['total_indikators'] }} indikator dinilai
                </p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-600 dark:text-gray-400">Rata-rata Skor</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $domain['assessed_indikators'] > 0 ? number_format($domain['score'], 2) : 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Aspek Bar Chart -->
        <div id="aspekChart{{ $domain['id'] }}"></div>

        <!-- Aspek Details with Spider Charts -->
        @foreach($domain['aspeks'] as $aspek)
        @if($aspek['assessed_indikators'] > 0)
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">
                Aspek {{ $aspek['aspek_number'] }}: {{ $aspek['name'] }}
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
                    ({{ $aspek['assessed_indikators'] }}/{{ $aspek['total_indikators'] }} indikator)
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
        <a href="{{ route('monalisa.kominfo.dashboard') }}" 
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

    // Domain Radar Chart
    const domainData = @json($chartData['domains']);
    
    const domainRadarOptions = {
        series: [{
            name: 'Skor Self-Assessment',
            data: domainData.map(d => d.assessed_indikators > 0 ? d.score.toFixed(2) : 0)
        }],
        chart: {
            height: 400,
            type: 'radar',
            toolbar: {
                show: false
            },
            background: 'transparent'
        },
        colors: ['#3b82f6'],
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
            colors: ['#3b82f6'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: {
                formatter: function(val, opts) {
                    const domain = domainData[opts.dataPointIndex];
                    return `Skor: ${val} (${domain.assessed_indikators}/${domain.total_indikators} indikator)`;
                }
            }
        }
    };

    const domainRadarChart = new ApexCharts(document.querySelector("#domainRadarChart"), domainRadarOptions);
    domainRadarChart.render();

    // Aspek Bar Charts for each domain
    domainData.forEach(domain => {
        const aspekOptions = {
            series: [{
                name: 'Skor Aspek',
                data: domain.aspeks.map(a => a.assessed_indikators > 0 ? a.score.toFixed(2) : 0)
            }],
            chart: {
                height: 300,
                type: 'bar',
                toolbar: {
                    show: false
                },
                background: 'transparent'
            },
            colors: ['#8b5cf6'],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: false,
                    columnWidth: '60%',
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '12px',
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
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                y: {
                    formatter: function(val, opts) {
                        const aspek = domain.aspeks[opts.dataPointIndex];
                        return `Skor: ${val} (${aspek.assessed_indikators}/${aspek.total_indikators} indikator)`;
                    }
                }
            }
        };

        const aspekChart = new ApexCharts(document.querySelector(`#aspekChart${domain.id}`), aspekOptions);
        aspekChart.render();

        // Aspek Radar Charts - showing indicators within each aspek
        domain.aspeks.forEach(aspek => {
            if (aspek.assessed_indikators > 0) {
                const indikatorData = aspek.indikators.filter(ind => ind.score !== null);

                if (indikatorData.length > 0) {
                    const aspekRadarOptions = {
                        series: [{
                            name: 'Skor Indikator',
                            data: indikatorData.map(ind => ind.score)
                        }],
                        chart: {
                            height: 350,
                            type: 'radar',
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#8b5cf6'],
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
                            colors: ['#8b5cf6'],
                            strokeColors: '#fff',
                            strokeWidth: 2,
                        },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                            y: {
                                formatter: function(val, opts) {
                                    const indikator = indikatorData[opts.dataPointIndex];
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

