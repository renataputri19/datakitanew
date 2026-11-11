@extends('layouts.superadmin')

@section('title', 'Log Unduhan Excel - Superadmin - DataKita')
@section('description', 'Riwayat unduhan Excel data survei oleh pengguna BPS')

@php
    // Readable names for the export dialog's query-string filters.
    $filterLabels = [
        'year' => 'Tahun', 'tahun' => 'Tahun', 'type' => 'Jenis', 'triwulan' => 'Triwulan',
        'period' => 'Periode', 'status' => 'Status', 'search' => 'Cari', 'blok1' => 'Blok 1',
        'grid' => 'Grid', 'jenis_pembangkit' => 'Pembangkit', 'kabupaten_kota' => 'Kab/Kota',
        'kualifikasi' => 'Kualifikasi', 'date_from' => 'Dari', 'date_to' => 'Sampai',
    ];
    $surveyChip = [
        'sibstr'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'ub'         => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
        'listrik'    => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'konstruksi' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    ];
    $hasFilter = $survey || $search !== '' || $dateFrom || $dateTo;
@endphp

@section('dashboard-content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <nav class="text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard Superadmin</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 dark:text-gray-300">Log Unduhan Excel</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Log Unduhan Excel</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Setiap unduhan Excel/CSV dari halaman Data Survei BPS (SIBSTR, UB, Listrik, Konstruksi) tercatat di sini: siapa, kapan, data apa, dan berapa baris.</p>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['last30'], 0, ',', '.') }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Unduhan 30 hari terakhir</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['users30'], 0, ',', '.') }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Pengguna yang mengunduh (30 hari)</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['rows30'], 0, ',', '.') }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Baris data terunduh (30 hari)</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['total'], 0, ',', '.') }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Total unduhan tercatat</div>
        </div>
    </div>

    {{-- Survey tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('superadmin.export-logs.index', array_filter(['search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium border {{ ! $survey ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            Semua <span class="opacity-75">({{ number_format($summary['total'], 0, ',', '.') }})</span>
        </a>
        @foreach($surveys as $key => $label)
            <a href="{{ route('superadmin.export-logs.index', array_filter(['survey' => $key, 'search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium border {{ $survey === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                {{ $label }} <span class="opacity-75">({{ number_format($perSurvey[$key] ?? 0, 0, ',', '.') }})</span>
            </a>
        @endforeach
    </div>

    {{-- Search + date range --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="{{ route('superadmin.export-logs.index') }}" class="flex flex-col lg:flex-row gap-3">
            @if($survey)
                <input type="hidden" name="survey" value="{{ $survey }}">
            @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..."
                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="flex items-center gap-2">
                <label for="date_from" class="text-sm text-gray-500 dark:text-gray-400">Dari</label>
                <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}"
                       class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <label for="date_to" class="text-sm text-gray-500 dark:text-gray-400">s.d.</label>
                <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}"
                       class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">Terapkan</button>
                @if($hasFilter)
                    <a href="{{ route('superadmin.export-logs.index') }}" class="px-5 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors">Reset</a>
                @endif
            </div>
        </form>
        @if($errors->any())
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
        @endif
    </div>

    {{-- Log table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu (WIB)</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Survei</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Baris</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Filter</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">File &amp; IP</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        @php $roleDef = $definitions[$log->user_role] ?? null; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors align-top">
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                {{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->setTimezone('Asia/Jakarta')->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->user_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $log->user_email }}</div>
                                <div class="mt-1">
                                    @include('superadmin.partials.role-badge', ['badge' => $roleDef['badge'] ?? 'gray', 'label' => $roleDef['label'] ?? $log->user_role])
                                    @if($log->user_id === null)
                                        <span class="ml-1 text-[11px] text-gray-400">(akun dihapus)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $surveyChip[$log->survey] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">{{ $log->surveyLabel() }}</span>
                                <span class="ml-1 text-xs font-mono uppercase text-gray-500 dark:text-gray-400">{{ $log->format }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-right font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ number_format($log->row_count, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                @if(empty($log->filters))
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Semua data (tanpa filter)</span>
                                @else
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($log->filters as $key => $value)
                                            <span class="inline-flex px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-[11px] text-gray-700 dark:text-gray-300">{{ $filterLabels[$key] ?? $key }}: {{ $value }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <div class="font-mono break-all text-gray-700 dark:text-gray-300">{{ $log->filename }}</div>
                                <div class="mt-0.5" title="{{ $log->user_agent }}">IP {{ $log->ip_address ?? '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ $hasFilter ? 'Tidak ada unduhan yang cocok dengan filter.' : 'Belum ada unduhan Excel yang tercatat.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
