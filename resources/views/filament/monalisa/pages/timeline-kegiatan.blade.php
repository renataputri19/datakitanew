<x-filament::page>
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
        {{-- <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Rancangan Timeline EPSS 2025</h1> --}}

        <div class="overflow-auto">
            <table class="table-auto border-collapse border border-gray-300 dark:border-gray-600 w-full">
                <thead>
                    <tr>
                        <th class="border border-gray-300 dark:border-gray-600 p-2 bg-gray-200 dark:bg-gray-700">Kategori</th>
                        <th class="border border-gray-300 dark:border-gray-600 p-2 bg-gray-200 dark:bg-gray-700">Kegiatan</th>
                        @foreach (range(1, 12) as $month)
                            <th class="border border-gray-300 dark:border-gray-600 p-2 bg-gray-200 dark:bg-gray-700">
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $categoryGroup)
                        <!-- Category Row -->
                        <tr>
                            <td colspan="14" class="border border-gray-300 dark:border-gray-600 p-2 bg-gray-100 dark:bg-gray-800 font-bold">
                                {{ $categoryGroup['category'] }}
                            </td>
                        </tr>
        
                        <!-- Tasks Rows -->
                        @foreach ($categoryGroup['tasks'] as $task)
                            <tr>
                                <td></td> <!-- Empty for category -->
                                <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $task['task'] }}</td>
                                @foreach (range(1, 12) as $month)
                                    <td class="border border-gray-300 dark:border-gray-600 p-2">
                                        @php
                                            $startDate = \Carbon\Carbon::parse($task['start_date']);
                                            $endDate = \Carbon\Carbon::parse($task['end_date']);
                                        @endphp
                                        @if ($startDate->month <= $month && $endDate->month >= $month)
                                            <div class="bg-green-500 h-4 w-full rounded"></div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>



    <style>
        table th,
        table td {
            text-align: center;
        }

        .bg-green-500 {
            background-color: #38a169;
            /* Tailwind green */
        }
    </style>

</x-filament::page>
