@extends('layouts.bps')

@section('title', 'Data Survei Listrik - BPS Dashboard')
@section('description', 'Daftar semua respons Survei Produksi & Nilai Produksi Listrik Bulanan')

@push('styles')
<style>
.ub-bps-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
    overflow: hidden;
}
.dark .ub-bps-card {
    background: #1f2937;
    border-color: #374151;
}
.ub-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.ub-stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
}
.dark .ub-stat-card {
    background: #1f2937;
    border-color: #374151;
}
.ub-stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}
.ub-table { width: 100%; border-collapse: collapse; }
.ub-table thead th {
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
.dark .ub-table thead th { background: #111827; color: #9ca3af; border-color: #374151; }
.ub-table tbody td { padding: 1rem; border-bottom: 1px solid #e5e7eb; color: #374151; }
.dark .ub-table tbody td { border-color: #374151; color: #d1d5db; }
.ub-table tbody tr:hover { background: #f9fafb; }
.dark .ub-table tbody tr:hover { background: #111827; }
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-completed { background: #d1fae5; color: #065f46; }
.dark .status-completed { background: #064e3b; color: #6ee7b7; }
.status-in-progress { background: #fef3c7; color: #92400e; }
.dark .status-in-progress { background: #78350f; color: #fcd34d; }
.ub-filter-section {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.dark .ub-filter-section { background: #111827; border-color: #374151; }
.btn-filter { padding: 0.5rem 1rem; background: #3b82f6; color: white; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; border: none; cursor: pointer; transition: background .2s; }
.btn-filter:hover { background: #2563eb; }
.btn-reset { padding: 0.5rem 1rem; background: #6b7280; color: white; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; border: none; cursor: pointer; transition: background .2s; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; }
.btn-reset:hover { background: #4b5563; }
.btn-view { padding: 0.375rem 0.75rem; background: #3b82f6; color: white; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; transition: background .2s; }
.btn-view:hover { background: #2563eb; }
.pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.dark .pagination-container { border-color: #374151; background: #111827; }
.empty-state { text-align: center; padding: 4rem 2rem; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Data Survei Listrik</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Daftar respons Survei Produksi &amp; Nilai Produksi Listrik Bulanan dari seluruh pengguna
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            SURVEI LISTRIK
        </span>
    </div>

    {{-- Statistics Cards --}}
    <div class="ub-stats-grid">
        <div class="ub-stat-card">
            <div class="flex items-center">
                <div class="ub-stat-icon" style="background:rgba(59,130,246,.1);color:#3b82f6;">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Responden</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="ub-stat-card">
            <div class="flex items-center">
                <div class="ub-stat-icon" style="background:rgba(34,197,94,.1);color:#22c55e;">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Selesai</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
        <div class="ub-stat-card">
            <div class="flex items-center">
                <div class="ub-stat-icon" style="background:rgba(251,191,36,.1);color:#fbbf24;">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Dalam Proses</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="ub-filter-section">
        <form method="GET" action="{{ route('bps.listrik.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Nama perusahaan, lokasi, pengguna..."
                           class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                    </select>
                </div>
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Urutkan</label>
                    <select id="sort_by" name="sort_by"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="updated_at" {{ request('sort_by', 'updated_at') === 'updated_at' ? 'selected' : '' }}>Terakhir Diperbarui</option>
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                        <option value="nama_perusahaan" {{ request('sort_by') === 'nama_perusahaan' ? 'selected' : '' }}>Nama Perusahaan</option>
                    </select>
                </div>
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tampilkan</label>
                    <select id="per_page" name="per_page"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 per halaman</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="submit" class="btn-filter">
                    <svg class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('bps.listrik.index') }}" class="btn-reset">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="ub-bps-card">
        @if($surveyResponses->count() > 0)
            <div class="overflow-x-auto">
                <table class="ub-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Perusahaan</th>
                            <th>Pengguna</th>
                            <th>Lokasi</th>
                            <th>Pembangkit</th>
                            <th>Kemajuan</th>
                            <th>Status</th>
                            <th>Terakhir Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveyResponses as $index => $resp)
                        <tr>
                            <td class="font-medium">{{ $surveyResponses->firstItem() + $index }}</td>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $resp->nama_perusahaan ?: '-' }}
                                </div>
                                @if($resp->nama_komersial && $resp->nama_komersial !== $resp->nama_perusahaan)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 italic">{{ $resp->nama_komersial }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $resp->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $resp->user->email ?? '' }}</div>
                            </td>
                            <td>
                                @if($resp->kabupaten_kota)
                                <div class="text-sm">{{ $resp->kabupaten_kota }}</div>
                                @endif
                                @if($resp->provinsi)
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $resp->provinsi }}</div>
                                @endif
                                @if(!$resp->kabupaten_kota && !$resp->provinsi)
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $resp->jenis_pembangkit ?: '-' }}</div>
                                @if($resp->daya_terpasang_kw)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format((float) $resp->daya_terpasang_kw, 2, ',', '.') }} kW
                                </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pct = $resp->completionPercent();
                                    $barColor = $pct === 100 ? '#22c55e' : ($pct >= 50 ? '#3b82f6' : '#fbbf24');
                                @endphp
                                <div class="flex items-center gap-2" style="min-width:90px;">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($resp->is_completed)
                                    <span class="status-badge status-completed">
                                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Selesai
                                    </span>
                                @else
                                    <span class="status-badge status-in-progress">
                                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Dalam Proses
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $resp->updated_at->setTimezone('Asia/Jakarta')->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $resp->updated_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.375rem; flex-wrap:wrap;">
                                    <a href="{{ route('bps.listrik.show', $resp->id) }}" class="btn-view">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Lihat Detail
                                    </a>
                                    <a href="{{ route('bps.listrik.download', $resp->id) }}"
                                       class="btn-view"
                                       style="background:#16a34a;"
                                       title="Download PDF">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                        </svg>
                                        PDF
                                    </a>
                                    <button type="button" class="btn-delete"
                                            title="Hapus data survei listrik ini"
                                            onclick="bpsConfirmDelete(
                                                '{{ route('bps.listrik.destroy', $resp->id) }}',
                                                @js($resp->nama_perusahaan ?: ($resp->user->name ?? 'Responden')),
                                                @js('Survei Listrik — ' . ($resp->user->email ?? '')),
                                                @js('Survei Listrik')
                                            )">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pagination-container">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan {{ $surveyResponses->firstItem() }}–{{ $surveyResponses->lastItem() }} dari {{ $surveyResponses->total() }} hasil
                </div>
                <div>{{ $surveyResponses->links() }}</div>
            </div>
        @else
            <div class="empty-state">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Data</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    @if(request()->hasAny(['search', 'status']))
                        Tidak ada hasil yang sesuai dengan filter yang dipilih.
                    @else
                        Belum ada respons Survei Listrik yang tersedia.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@include('bps.partials.delete-survey-modal')
@endsection
