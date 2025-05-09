<x-filament::page>



    {{-- Summary Cards --}}
    {{-- <div class="space-y-6"> --}}
    {{-- Top Stats Row --}}
    {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-filament::card
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex flex-col h-full p-2">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Domains</span>
                        <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 dark:text-orange-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ count($domains) }}</div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active domains</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex flex-col h-full p-2">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Aspek</span>
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ $totalAspeks }}</div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Aspeks Tracked</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex flex-col h-full p-2">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Indicators</span>
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd"
                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ collect($domains)->sum(fn($domain) => count($domain['indicators'])) }}
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total metrics tracked</p>
                    </div>
                </div>
            </x-filament::card>
        </div> --}}

    {{-- Total Score Card --}}
    {{-- <div class="mt-8">
            <x-filament::card
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex flex-col p-2">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Score</span>
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ number_format($scores['totalScore'], 2) }}
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Weighted Score Across All Domains</p>
                    </div>
                </div>
            </x-filament::card>
        </div> --}}

    {{-- Domains Section --}}
    {{-- <div class="mt-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Nilai EPSS Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($scores['domains'] as $domain)
                    <x-filament::card
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="p-2">
                            <div class="font-bold text-lg text-gray-900 dark:text-white mb-4">{{ $domain['domain'] }}
                            </div>
                            <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ number_format($domain['score'], 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-6">Weighted Score</div>
                            <div class="space-y-3">
                                @foreach ($domain['aspeks'] as $aspek => $score)
                                    <div
                                        class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                        <span class="text-gray-600 dark:text-gray-300">{{ $aspek }}</span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-white">{{ number_format($score, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-filament::card>
                @endforeach
            </div>
        </div>
    </div> --}}











    <div class="space-y-8">





        {{-- Total Score Card --}}
        <div class="mt-6">
            <x-filament::card class="relative overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Total Nilai EPSS
                    </h3>
                    <div class="p-2.5 bg-emerald-100/50 dark:bg-emerald-900/50 rounded-full">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Score Gauge --}}
                    <div class="flex flex-col items-center justify-center">
                        <div class="relative w-32 h-32">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="64" cy="64" r="56" class="text-gray-200 dark:text-gray-700"
                                    stroke="currentColor" stroke-width="12" fill="none" />
                                <circle cx="64" cy="64" r="56"
                                    class="{{ $scores['totalScore'] >= 4 ? 'text-primary-600 dark:text-primary-400' : ($scores['totalScore'] >= 3 ? 'text-primary-600 dark:text-primary-400' : 'text-primary-600 dark:text-primary-400') }} text-primary-600 dark:text-primary-400"
                                    stroke="currentColor" stroke-width="12" stroke-linecap="round" fill="none"
                                    stroke-dasharray="{{ ($scores['totalScore'] / 5) * 351.858 }}, 351.858" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span
                                    class="text-3xl font-bold {{ $scores['totalScore'] >= 4 ? 'text-primary-600 dark:text-primary-400' : ($scores['totalScore'] >= 3 ? 'text-primary-600 dark:text-primary-400' : 'text-primary-600 dark:text-primary-400') }} text-primary-600 dark:text-primary-400">
                                    {{ number_format($scores['totalScore'], 2) }}
                                </span>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">dari 5.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- Score Details --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Domains Card --}}
                            <div class="bg-gray-100 dark:bg-gray-600 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-200">Total Domain
                                    </div>
                                    <div class="p-2 bg-primary-100/50 dark:bg-primary-900/50 rounded-full">
                                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ count($scores['domains']) }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-200 mt-1">Domain yang dinilai
                                    </div>
                                </div>
                                {{-- <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                        Main evaluation areas
                                    </div>
                                </div> --}}
                            </div>

                            {{-- Aspects Card --}}
                            <div class="bg-gray-100 dark:bg-gray-600 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-200">Total Aspek
                                    </div>
                                    <div class="p-2 bg-success-100/50 dark:bg-success-900/50 rounded-full">
                                        <svg class="h-5 w-5 text-success-600 dark:text-success-400" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ collect($scores['domains'])->sum(fn($domain) => count($domain['aspeks'])) }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-200 mt-1">Aspek yang dinilai</div>
                                </div>
                                {{-- <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-xs font-medium text-success-600 dark:text-success-400">
                                        Key measurement components
                                    </div>
                                </div> --}}
                            </div>

                            {{-- Indicators Card --}}
                            <div class="bg-gray-100 dark:bg-gray-600 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-200">Total Indikator
                                    </div>
                                    <div class="p-2 bg-warning-100/50 dark:bg-warning-900/50 rounded-full">
                                        <svg class="h-5 w-5 text-warning-600 dark:text-warning-400" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd"
                                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ collect($domains)->sum(fn($domain) => count($domain['indicators'])) }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-200 mt-1">Indikator kinerja yang dinilai</div>
                                </div>
                                {{-- <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-xs font-medium text-warning-600 dark:text-warning-400">
                                        Detailed measurement points
                                    </div>
                                </div> --}}
                            </div>
                        </div>

                        {{-- <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white mb-3">Score Distribution</div>
                            <div class="space-y-3">
                                @foreach ($scores['domains'] as $domain)
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span
                                                class="text-gray-600 dark:text-gray-300">{{ $domain['domain'] }}</span>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white">{{ number_format($domain['score'], 2) }}</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-1.5 {{ $domain['score'] >= 4 ? 'bg-emerald-500' : ($domain['score'] >= 3 ? 'bg-green-500' : 'bg-yellow-500') }} rounded-full"
                                                style="width: {{ ($domain['score'] / 5) * 100 }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div> --}}
                    </div>
                </div>
            </x-filament::card>
        </div>




        {{-- <div class="mt-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Domain Analysis</h1>
                <span class="px-3 py-1 text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400 rounded-full">
                    Max Score: 5.00
                </span>
            </div>
        
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($scores['domains'] as $domain)
                    <x-filament::card class="relative overflow-hidden transition-all duration-200 hover:shadow-lg"> --}}

                        {{-- Domain Header --}}

                        {{-- <div class="flex items-center justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $domain['domain'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Domain Score</p>
                            </div>
                            <div @class([
                                'px-3 py-1 text-sm font-semibold rounded-full',
                                'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-400' => $domain['score'] >= 4,
                                'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-400' => $domain['score'] >= 3 && $domain['score'] < 4,
                                'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-400' => $domain['score'] < 3,
                            ])>
                                {{ number_format($domain['score'], 2) }}
                            </div>
                        </div> --}}
        
                        {{-- Progress Bar --}}

                        {{-- <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full mb-6">
                            <div class="h-1.5 rounded-full transition-all duration-300 ease-in-out" 
                                 @class([
                                     'bg-success-500' => $domain['score'] >= 4,
                                     'bg-warning-500' => $domain['score'] >= 3 && $domain['score'] < 4,
                                     'bg-danger-500' => $domain['score'] < 3,
                                 ])
                                 style="width: {{ ($domain['score'] / 5) * 100 }}%">
                            </div>
                        </div> --}}
        
                        {{-- Aspects List --}}

                        {{-- <div class="space-y-3">
                            @foreach ($domain['aspeks'] as $aspek => $score)
                                <div class="group">
                                    <div class="flex justify-between items-center text-sm mb-1">
                                        <span class="text-gray-700 dark:text-gray-300">{{ $aspek }}</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($score, 2) }}</span>
                                    </div>
                                    <div class="h-1 w-full bg-gray-100 dark:bg-gray-800 rounded-full">
                                        <div class="h-1 bg-primary-500/50 group-hover:bg-primary-500 transition-all duration-200 rounded-full"
                                             style="width: {{ ($score / 5) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach --}}
        
                            {{-- Total Score --}}

                            {{-- <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white">Total Score</span>
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-gray-900 dark:text-white">
                                            {{ number_format($domain['aspeks']->sum(), 2) }}
                                        </span>
                                        <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500"
                                                 style="width: {{ ($domain['aspeks']->sum() / 5) * 100 }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-filament::card>
                @endforeach
            </div>
        </div> --}}




















        {{-- Disini --}}


        {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-filament::card class="relative overflow-hidden transition-all duration-200 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Total Domains</div>
                    <div class="p-2.5 bg-primary-100/50 dark:bg-primary-900/50 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600 dark:text-primary-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ count($domains) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Active domains</div>
                </div>
            </x-filament::card>

            <x-filament::card class="relative overflow-hidden transition-all duration-200 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Total Aspek</div>
                    <div class="p-2.5 bg-success-100/50 dark:bg-success-900/50 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success-600 dark:text-success-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAspeks }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Total Aspeks Tracked</div>
                </div>
            </x-filament::card>

            <x-filament::card class="relative overflow-hidden transition-all duration-200 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Total Indicators</div>
                    <div class="p-2.5 bg-warning-100/50 dark:bg-warning-900/50 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning-600 dark:text-warning-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ collect($domains)->sum(fn($domain) => count($domain['indicators'])) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Total metrics tracked</div>
                </div>
            </x-filament::card>
        </div> --}}

        {{-- Total Score Card --}}
        {{-- <div class="mt-6">
            <x-filament::card class="relative overflow-hidden transition-all duration-200 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Total Score</div>
                    <div class="p-2.5 bg-success-100/50 dark:bg-success-900/50 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success-600 dark:text-success-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <div class="relative inline-block w-20 h-20">
                        <svg class="w-full h-full" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#E5E7EB"
                                stroke-width="4" />
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#10B981"
                                stroke-width="4" stroke-dasharray="100"
                                stroke-dashoffset="{{ 100 - ($scores['totalScore'] / 5) * 100 }}"
                                transform="rotate(-90 18 18)" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($scores['totalScore'], 2) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Total Weighted Score (Max: 5)
                    </div>
                </div>


            </x-filament::card>
        </div> --}}

        <div class="mt-6 mb-6 pt-2">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-6 mb-6">Evaluasi Domain dan Aspek</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                @foreach ($scores['domains'] as $domain)
                    <x-filament::card class="transition-all duration-200 hover:shadow-lg">
                        <div class="font-bold text-lg text-gray-900 dark:text-white">{{ $domain['domain'] }}</div>
                        <div class="text-3xl font-bold mt-4 text-gray-900 dark:text-white">
                            {{ number_format($domain['score'], 2) }}
                        </div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Weighted Score</div>
                        <ul class="mt-6 space-y-3">
                            @foreach ($domain['aspeks'] as $aspek => $score)
                                <li
                                    class="flex justify-between items-center py-2  border-b border-gray-100 dark:border-gray-800 last:border-0">
                                    <span class="text-gray-700 dark:text-gray-200">{{ $aspek }}</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ number_format($score, 2) }}</span>
                                </li>
                            @endforeach
                            <!-- Total of all aspek scores -->
                            <li
                                class="flex justify-between items-center py-2 font-semibold text-gray-900 dark:text-white">
                                <span>Total Skor Aspek</span>
                                <div class="flex items-center gap-2">
                                    <span>{{ number_format($domain['aspeks']->sum(), 2) }}</span>
                                    <div class="w-24 h-2 bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden">
                                        <div class="h-full bg-green-500"
                                            style="width: {{ ($domain['aspeks']->sum() / 5) * 100 }}%;"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Maks: 5</span>
                                </div>
                            </li>



                        </ul>
                    </x-filament::card>
                @endforeach
            </div>

        </div>








        {{-- Summary Cards --}}
        {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Domains</div>
                    <div class="p-2 bg-primary-50 dark:bg-primary-900 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-bold">{{ count($domains) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active domains</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Aspek</div>
                    <div class="p-2 bg-success-50 dark:bg-success-900 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-bold">
                        {{ $totalAspeks }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-4">Total Aspeks Tracked</div>
                </div>


            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Indicators</div>
                    <div class="p-2 bg-warning-50 dark:bg-warning-900 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-bold">
                        {{ collect($domains)->sum(fn($domain) => count($domain['indicators'])) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-4">Total metrics tracked</div>
                </div>
            </x-filament::card>
        </div>


        <x-filament::card>
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Score</div>
                <div class="p-2 bg-success-50 dark:bg-success-900 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success-500" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold">
                    {{ number_format($scores['totalScore'], 2) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Weighted Score Across All Domains
                </div>
            </div>
        </x-filament::card>



        <x-filament::page>
            <h1 class="text-2xl font-bold mb-6">Nilai EPSS Dashboard</h1>

            @foreach ($scores['domains'] as $domain)
                <x-filament::card>
                    <div class="font-bold text-lg">{{ $domain['domain'] }}</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ number_format($domain['score'], 2) }}
                    </div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Weighted Score</div>
                    <ul class="mt-4">
                        @foreach ($domain['aspeks'] as $aspek => $score)
                            <li class="flex justify-between">
                                <span>{{ $aspek }}</span>
                                <span>{{ number_format($score, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-filament::card>
            @endforeach

        </x-filament::page> --}}




    </div>




</x-filament::page>
