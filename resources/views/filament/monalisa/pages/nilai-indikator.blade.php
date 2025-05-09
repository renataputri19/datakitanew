<x-filament::page>


    <div class="space-y-8">




        {{-- Spider Charts --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6 mb-6 bg-gray-100 dark:bg-gray-800">
            @foreach ($domains as $domain)
                <x-filament::card>
                    <h2 class="text-lg font-medium mb-4 text-black dark:text-white">{{ $domain['domain'] }}</h2>
                    <div class="text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-800"
                        id="chart-{{ $loop->index }}" style="height: 400px;">
                    </div>
                </x-filament::card>
            @endforeach
        </div>



    </div>

    {{-- Include ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            const domains = @json($domains);
            const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

            // Function to split long indicator names into arrays
            function splitIntoArray(name, wordsPerLine = 3) {
                const words = name.split(' ');
                const lines = [];
                for (let i = 0; i < words.length; i += wordsPerLine) {
                    lines.push(words.slice(i, i + wordsPerLine).join(' '));
                }
                return lines; // Return as an array for multiline labels
            }

            domains.forEach((domain, index) => {
                const categories = domain.indicators.map((indicator, i) => {
                    // Logic for domain "Statistik Nasional"
                    if (domain.domain === "Statistik Nasional") {
                        // Split all indicators into arrays of 3 words each
                        return splitIntoArray(indicator.name, 4);
                    } else {
                        // For other domains, do not split the first indicator; split the rest
                        return i === 0 ? indicator.name : splitIntoArray(indicator.name, 3);
                    }
                });

                const options = {
                    plotOptions: {
                        radar: {
                            polygons: {
                                strokeColor: '#e8e8e8',
                                fill: {
                                    colors: ['#f8f8f8', '#fff']
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Score',
                        data: domain.indicators.map(i => i.value)
                    }],
                    chart: {
                        height: 600, // Chart height
                        type: 'radar',
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            }
                        },
                        dropShadow: {
                            enabled: true,
                            blur: 1,
                            left: 1,
                            top: 1
                        },
                    },
                    stroke: {
                        width: 2,
                        curve: 'smooth'
                    },
                    fill: {
                        opacity: 0.3,
                        colors: ['#00E396']
                    },
                    markers: {
                        size: 5,
                        hover: {
                            size: 8
                        }
                    },
                    xaxis: {
                        categories: categories, // Updated categories logic
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontWeight: 800,
                                color: isDarkMode ? '#9CA3AF' :
                                    '#6B7280' // Tailwind color for text-gray-500
                            }
                        }
                    },

                    yaxis: {
                        min: 0,
                        max: 7, // Set the maximum to 7 for better spacing
                        tickAmount: 7, // Ensure there are 7 ticks (0-7)
                        labels: {
                            formatter: function(value) {
                                // Display values within 0-5 only
                                return value > 5 ? '' : value.toFixed(1);
                            },
                            style: {
                                fontSize: '11px', // Increase font size for y-axis labels
                                fontWeight: 600, // Optional: Make it bold for better visibility
                                color: '#6B7280' // Tailwind color for text-gray-500
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toFixed(1) + ' / 5.0';
                            }
                        }
                    },
                    theme: {
                        palette: 'palette1', // Optional: Adjust the chart palette if needed
                    }
                };

                const chart = new ApexCharts(document.querySelector(`#chart-${index}`), options);
                chart.render();
            });
        });
    </script>




    {{-- <style>
        .apexcharts-title-text {
            fill: var(--tw-text-gray-500);
        }

        .apexcharts-xaxis text {
            fill: var(--tw-text-gray-500);
        }

        .dark .apexcharts-title-text {
            fill: var(--tw-text-gray-400);
        }

        .dark .apexcharts-xaxis text {
            fill: var(--tw-text-gray-400);
        }
    </style> --}}





</x-filament::page>
