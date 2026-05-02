@extends('layouts.user-dashboard')

@section('title', 'Hasil Survei SIBSTR - Mitra')
@section('description', 'Daftar respons survei SIBSTR dari seluruh pengguna')

@push('styles')
<style>
.sibstr-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
}
.dark .sibstr-card {
    background: #1f2937;
    border-color: #374151;
}
.sibstr-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.dark .stat-card {
    background: #1f2937;
    border-color: #374151;
}
.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}
.sibstr-table {
    width: 100%;
    border-collapse: collapse;
}
.sibstr-table thead th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
}
.dark .sibstr-table thead th {
    background: #111827;
    color: #9ca3af;
    border-color: #374151;
}
.sibstr-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}
.dark .sibstr-table tbody td {
    border-color: #374151;
    color: #d1d5db;
}
.sibstr-table tbody tr:hover {
    background: #f9fafb;
}
.dark .sibstr-table tbody tr:hover {
    background: #111827;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-completed {
    background: #d1fae5;
    color: #065f46;
}
.dark .status-completed {
    background: #064e3b;
    color: #6ee7b7;
}
.status-in-progress {
    background: #fef3c7;
    color: #92400e;
}
.dark .status-in-progress {
    background: #78350f;
    color: #fcd34d;
}
.filter-section {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.dark .filter-section {
    background: #111827;
    border-color: #374151;
}
.btn-filter {
    padding: 0.5rem 1rem;
    background: #3b82f6;
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-filter:hover { background: #2563eb; }
.btn-reset {
    padding: 0.5rem 1rem;
    background: #6b7280;
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-reset:hover { background: #4b5563; }
.btn-view {
    padding: 0.375rem 0.75rem;
    background: #3b82f6;
    color: white;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s;
}
.btn-view:hover { background: #2563eb; }
.pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}
.dark .pagination-container {
    border-color: #374151;
    background: #111827;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-state-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    color: #9ca3af;
}
</style>
@endpush

@section('dashboard-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hasil Survei SIBSTR</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Daftar respons survei SIBSTR dari seluruh pengguna &mdash;
                <span class="font-medium">{{ $isTahunan ? 'Tahunan' : 'Triwulanan' }} {{ $year }}</span>
            </p>
        </div>
        <!-- Year selector quick links -->
        <div class="flex items-center gap-2 flex-wrap">
            @foreach([2025, 2026] as $yr)
                <a href="{{ route('survey.sibstr.entry', array_merge(request()->except(['year','page']), ['year' => $yr, 'type' => $type])) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-medium border transition-all
                          {{ $year === $yr ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-400' }}">
                    {{ $yr }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Tahunan / Triwulanan Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-1" aria-label="Tabs">
            <a href="{{ route('survey.sibstr.entry', array_merge(request()->except(['type','triwulan','page']), ['type' => 'tahunan'])) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 transition-colors
                      {{ $isTahunan
                          ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400'
                          : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Tahunan
                @if($isTahunan)
                    <span class="bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $stats['total'] }}</span>
                @endif
            </a>
            <a href="{{ route('survey.sibstr.entry', array_merge(request()->except(['type','triwulan','page']), ['type' => 'triwulanan'])) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 transition-colors
                      {{ !$isTahunan
                          ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400'
                          : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Triwulanan
                @if(!$isTahunan)
                    <span class="bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $stats['total'] }}</span>
                @endif
            </a>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="sibstr-stats-grid">
        <div class="stat-card">
            <div class="flex items-center">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Survei</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $isTahunan ? 'FINISH_SURVEY' : 'Selesai' }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center">
                <div class="stat-icon" style="background: rgba(251, 191, 36, 0.1); color: #fbbf24;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Dalam Proses</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('survey.sibstr.entry') }}" class="space-y-4">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="year" value="{{ $year }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Pencarian
                    </label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari nama perusahaan, KIP, IDSBR..."
                           class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status
                    </label>
                    <select id="status"
                            name="status"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                            {{ $isTahunan ? 'FINISH_SURVEY' : 'Selesai' }}
                        </option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                    </select>
                </div>

                @if(!$isTahunan)
                <div>
                    <label for="triwulan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Triwulan
                    </label>
                    <select id="triwulan"
                            name="triwulan"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Triwulan</option>
                        <option value="1" {{ request('triwulan') == '1' ? 'selected' : '' }}>Triwulan I (Jan–Mar)</option>
                        <option value="2" {{ request('triwulan') == '2' ? 'selected' : '' }}>Triwulan II (Apr–Jun)</option>
                        <option value="3" {{ request('triwulan') == '3' ? 'selected' : '' }}>Triwulan III (Jul–Sep)</option>
                        <option value="4" {{ request('triwulan') == '4' ? 'selected' : '' }}>Triwulan IV (Okt–Des)</option>
                    </select>
                </div>
                @endif

                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Urutkan Berdasarkan
                    </label>
                    <select id="sort_by"
                            name="sort_by"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="updated_at" {{ request('sort_by', 'updated_at') === 'updated_at' ? 'selected' : '' }}>Terakhir Diperbarui</option>
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                        <option value="nama_perusahaan" {{ request('sort_by') === 'nama_perusahaan' ? 'selected' : '' }}>Nama Perusahaan</option>
                    </select>
                </div>

                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tampilkan
                    </label>
                    <select id="per_page"
                            name="per_page"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 per halaman</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('survey.sibstr.entry', ['type' => $type, 'year' => $year]) }}" class="btn-reset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Survey Responses Table -->
    <div class="sibstr-card">
        @if($surveyResponses->count() > 0)
            <div class="overflow-x-auto">
                <table class="sibstr-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Perusahaan</th>
                            <th>Pengguna</th>
                            <th>KIP/IDSBR</th>
                            @if(!$isTahunan)
                            <th>Periode</th>
                            @endif
                            <th>Status</th>
                            <th>Terakhir Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveyResponses as $index => $response)
                        @php
                            $isFinished = $isTahunan
                                ? ($response->annual_survey_status === 'FINISH_SURVEY')
                                : $response->is_completed;
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $surveyResponses->firstItem() + $index }}</td>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $response->nama_perusahaan ?: '-' }}
                                </div>
                                @if($response->kabupaten_kota)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $response->kabupaten_kota }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $response->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $response->user->email }}</div>
                            </td>
                            <td>
                                @if($response->kip)
                                <div class="text-xs"><span class="font-medium">KIP:</span> {{ $response->kip }}</div>
                                @endif
                                @if($response->idsbr)
                                <div class="text-xs"><span class="font-medium">IDSBR:</span> {{ $response->idsbr }}</div>
                                @endif
                                @if(!$response->kip && !$response->idsbr)
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            @if(!$isTahunan)
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    TW {{ $response->triwulan }}
                                </span>
                            </td>
                            @endif
                            <td>
                                @if($isFinished)
                                    <span class="status-badge status-completed">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ $isTahunan ? 'FINISH_SURVEY' : 'Selesai' }}
                                    </span>
                                @else
                                    <span class="status-badge status-in-progress">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Dalam Proses
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $response->updated_at->setTimezone('Asia/Jakarta')->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $response->updated_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.375rem; flex-wrap:wrap;">
                                    <a href="{{ route('survey.mitra.sibstr.show', $response->id) }}" class="btn-view">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat Detail
                                    </a>
                                    <a href="{{ route('survey.mitra.sibstr.download', $response->id) }}"
                                       class="btn-view"
                                       style="background:#16a34a;"
                                       title="Download PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                        </svg>
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan {{ $surveyResponses->firstItem() }} sampai {{ $surveyResponses->lastItem() }} dari {{ $surveyResponses->total() }} hasil
                </div>
                <div>
                    {{ $surveyResponses->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Data</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    @if(request()->hasAny(['search', 'status', 'triwulan']))
                        Tidak ada hasil yang sesuai dengan filter yang dipilih.
                    @else
                        Belum ada respons survei SIBSTR {{ $isTahunan ? 'Tahunan' : 'Triwulanan' }} {{ $year }} yang tersedia.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
