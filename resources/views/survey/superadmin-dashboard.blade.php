@extends('layouts.app')

@section('title', 'Dashboard Superadmin - SIBSTR Survey - DataKita')
@section('description', 'Dashboard Superadmin untuk mengelola data survei SIBSTR')

@push('styles')
<style>
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .dashboard-header {
        margin-bottom: 2rem;
    }
    
    .dashboard-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .dark .dashboard-title {
        color: #f9fafb;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    .dark .stat-card {
        background-color: #1f2937;
        border-color: #374151;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #3b82f6;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6b7280;
        font-weight: 500;
    }
    
    .dark .stat-label {
        color: #9ca3af;
    }
    
    .table-container {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    .dark .table-container {
        background-color: #1f2937;
        border-color: #374151;
    }
    
    .table-header {
        background-color: #f9fafb;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dark .table-header {
        background-color: #111827;
        border-color: #374151;
    }

    .table-header-left {
        flex: 1;
    }

    .table-header-right {
        flex-shrink: 0;
        margin-left: 1rem;
    }
    
    .table-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .dark .table-title {
        color: #f9fafb;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .dark .data-table th,
    .dark .data-table td {
        border-color: #374151;
    }
    
    .data-table th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .dark .data-table th {
        background-color: #111827;
        color: #d1d5db;
    }
    
    .data-table td {
        color: #1f2937;
    }
    
    .dark .data-table td {
        color: #f9fafb;
    }
    
    .data-table tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .dark .data-table tbody tr:hover {
        background-color: #111827;
    }
    
    .file-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        background-color: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        transition: background-color 0.15s ease-in-out;
    }
    
    .file-link:hover {
        background-color: #2563eb;
        color: white;
        text-decoration: none;
    }
    
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 0.25rem;
    }
    
    .badge-industri {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .badge-non-industri {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .dark .badge-industri {
        background-color: #1e3a8a;
        color: #bfdbfe;
    }
    
    .dark .badge-non-industri {
        background-color: #78350f;
        color: #fde68a;
    }
    
    .pagination-wrapper {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .dark .pagination-wrapper {
        border-color: #374151;
    }
    
    .no-data {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }
    
    .dark .no-data {
        color: #9ca3af;
    }

    /* Filter Section Styles */
    .filter-section {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .dark .filter-section {
        background-color: #1f2937;
        border-color: #374151;
    }

    .filter-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.25rem 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .dark .filter-header {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        border-color: #4b5563;
    }

    .filter-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .dark .filter-title {
        color: #f9fafb;
    }

    .filter-form {
        padding: 2rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .dark .filter-label {
        color: #d1d5db;
    }

    .filter-input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease-in-out;
        background: white;
    }

    .filter-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .dark .filter-input {
        background-color: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    .dark .filter-input:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        background-color: #1f2937;
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        padding-top: 1.25rem;
        border-top: 1px solid #e2e8f0;
    }

    .dark .filter-actions {
        border-color: #4b5563;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary {
        background: #f8fafc;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        color: #1f2937;
        border-color: #9ca3af;
    }

    .dark .btn-secondary {
        background: #374151;
        color: #d1d5db;
        border-color: #4b5563;
    }

    .dark .btn-secondary:hover {
        background: #4b5563;
        color: #f9fafb;
        border-color: #6b7280;
    }

    .filter-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .dark .filter-badges {
        background: #111827;
        border-color: #4b5563;
    }

    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.625rem;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .dark .filter-badge {
        background: #1e3a8a;
        color: #bfdbfe;
    }

    .filter-badge-remove {
        cursor: pointer;
        padding: 0.125rem;
        border-radius: 50%;
        transition: background-color 0.15s ease-in-out;
        color: inherit;
        text-decoration: none;
    }

    .filter-badge-remove:hover {
        background: rgba(0, 0, 0, 0.1);
        color: inherit;
    }

    .results-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .dark .results-info {
        background: #111827;
        border-color: #374151;
        color: #9ca3af;
    }

    /* Table Info Styles */
    .table-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .result-count {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
    }

    .dark .result-count {
        color: #9ca3af;
    }

    .filter-indicator {
        background: #3b82f6;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Enhanced filter styling */
    .filter-input::placeholder {
        color: #9ca3af;
        font-style: italic;
    }

    .dark .filter-input::placeholder {
        color: #6b7280;
    }

    .filter-input:hover {
        border-color: #9ca3af;
    }

    .dark .filter-input:hover {
        border-color: #6b7280;
    }

    /* Smooth transitions for all interactive elements */
    .filter-badge {
        transition: all 0.2s ease-in-out;
    }

    .filter-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }
        
        .data-table {
            font-size: 0.875rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.5rem;
        }

        .filter-header {
            padding: 1rem 1.5rem;
        }

        .filter-form {
            padding: 1.5rem;
        }

        .filter-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .filter-actions {
            justify-content: stretch;
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .filter-badges {
            padding: 1rem 1.5rem;
        }

        .table-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .results-info {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .filter-header {
            padding: 0.75rem 1rem;
        }

        .filter-title {
            font-size: 1rem;
        }

        .filter-form {
            padding: 1rem;
        }

        .filter-actions {
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            width: 100%;
        }

        .filter-badges {
            padding: 0.75rem 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header" data-aos="fade-up">
        <h1 class="dashboard-title">Dashboard Superadmin</h1>
        <p class="text-gray-600 dark:text-gray-400">
            Kelola data survei SIBSTR yang masuk dari perusahaan
        </p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4" data-aos="fade-up">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4" data-aos="fade-up">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['industri'] }}</div>
            <div class="stat-label">Industri</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['non_industri'] }}</div>
            <div class="stat-label">Non-Industri</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['today'] }}</div>
            <div class="stat-label">Hari Ini</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section" data-aos="fade-up" data-aos-delay="150">
        <div class="filter-header">
            <h3 class="filter-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                </svg>
                Filter & Pencarian Data
            </h3>
        </div>

        <form method="GET" action="{{ route('superadmin.dashboard') }}" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="search_company" class="filter-label">Cari Perusahaan</label>
                    <input type="text"
                           id="search_company"
                           name="search_company"
                           class="filter-input"
                           placeholder="Masukkan nama perusahaan..."
                           value="{{ $filters['search_company'] }}">
                </div>

                <div class="filter-group">
                    <label for="search_name" class="filter-label">Cari Nama</label>
                    <input type="text"
                           id="search_name"
                           name="search_name"
                           class="filter-input"
                           placeholder="Masukkan nama responden..."
                           value="{{ $filters['search_name'] }}">
                </div>

                <div class="filter-group">
                    <label for="company_type" class="filter-label">Jenis Perusahaan</label>
                    <select id="company_type" name="company_type" class="filter-input">
                        <option value="">Semua Jenis</option>
                        <option value="industri" {{ $filters['company_type'] == 'industri' ? 'selected' : '' }}>Industri</option>
                        <option value="non-industri" {{ $filters['company_type'] == 'non-industri' ? 'selected' : '' }}>Non-Industri</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date_from" class="filter-label">Tanggal Dari</label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           class="filter-input"
                           value="{{ $filters['date_from'] }}">
                </div>

                <div class="filter-group">
                    <label for="date_to" class="filter-label">Tanggal Sampai</label>
                    <input type="date"
                           id="date_to"
                           name="date_to"
                           class="filter-input"
                           value="{{ $filters['date_to'] }}">
                </div>
            </div>

            <div class="filter-actions">
                <a href="{{ route('superadmin.dashboard') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1,4 1,10 7,10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                    Reset Filter
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </form>

        <!-- Active Filter Badges -->
        @if(array_filter($filters))
            <div class="filter-badges">
                @if($filters['search_company'])
                    <span class="filter-badge">
                        Perusahaan: "{{ $filters['search_company'] }}"
                        <a href="{{ request()->fullUrlWithQuery(['search_company' => null]) }}" class="filter-badge-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </span>
                @endif
                @if($filters['search_name'])
                    <span class="filter-badge">
                        Nama: "{{ $filters['search_name'] }}"
                        <a href="{{ request()->fullUrlWithQuery(['search_name' => null]) }}" class="filter-badge-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </span>
                @endif
                @if($filters['company_type'])
                    <span class="filter-badge">
                        Jenis: {{ ucfirst($filters['company_type']) }}
                        <a href="{{ request()->fullUrlWithQuery(['company_type' => null]) }}" class="filter-badge-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </span>
                @endif
                @if($filters['date_from'])
                    <span class="filter-badge">
                        Dari: {{ \Carbon\Carbon::parse($filters['date_from'])->setTimezone('Asia/Jakarta')->format('d/m/Y') }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null]) }}" class="filter-badge-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </span>
                @endif
                @if($filters['date_to'])
                    <span class="filter-badge">
                        Sampai: {{ \Carbon\Carbon::parse($filters['date_to'])->setTimezone('Asia/Jakarta')->format('d/m/Y') }}
                        <a href="{{ request()->fullUrlWithQuery(['date_to' => null]) }}" class="filter-badge-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="table-container" data-aos="fade-up" data-aos-delay="200">
        <div class="table-header">
            <div class="table-header-left">
                <h3 class="table-title">Data Submissions</h3>
                <div class="table-info">
                    @if($filteredCount !== $totalCount)
                        <span class="result-count">
                            Menampilkan {{ $filteredCount }} dari {{ $totalCount }} data
                            <span class="filter-indicator">(Terfilter)</span>
                        </span>
                    @else
                        <span class="result-count">Total: {{ $totalCount }} data</span>
                    @endif
                </div>
            </div>
            <div class="table-header-right">
                <a href="{{ route('superadmin.submissions.create') }}"
                   class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Data
                </a>
            </div>
        </div>

        @if($submissions->count() > 0)
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>No. HP</th>
                            <th>Email</th>
                            <th>Perusahaan</th>
                            <th>Alamat</th>
                            <th>Jenis</th>
                            <th>Files</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                            <tr>
                                <td>{{ $submission->nama }}</td>
                                <td>{{ $submission->jabatan }}</td>
                                <td>{{ $submission->no_hp }}</td>
                                <td>{{ $submission->email }}</td>
                                <td>{{ $submission->perusahaan }}</td>
                                <td>{{ $submission->alamat ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $submission->jenis_perusahaan }}">
                                        {{ ucfirst($submission->jenis_perusahaan) }}
                                    </span>
                                </td>
                                <td>
                                    @if($submission->file_paths && count($submission->file_paths) > 0)
                                        <div class="file-links">
                                            @foreach($submission->file_paths as $filePath)
                                                @php
                                                    $filename = basename($filePath);
                                                @endphp
                                                <a href="{{ route('superadmin.download.file', $filename) }}" 
                                                   class="file-link" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="7,10 12,15 17,10"></polyline>
                                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                                    </svg>
                                                    {{ Str::limit($filename, 15) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">No files</span>
                                    @endif
                                </td>
                                <td>{{ $submission->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
                                <td>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('superadmin.submissions.edit', $submission) }}"
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('superadmin.submissions.destroy', $submission) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data submission ini? File yang terkait juga akan dihapus.')"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
        <div class="pagination-container">
            {{ $submissions->appends(request()->query())->links() }}
        </div>
        @else
            <div class="no-data">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-gray-400">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10,9 9,9 8,9"></polyline>
                </svg>
                <p>Belum ada data survei yang masuk.</p>
            </div>
        @endif
    </div>

    <!-- Back to Dashboard Button -->
    <div class="mt-8 text-center" data-aos="fade-up" data-aos-delay="300">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Enhanced filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form on date change (optional enhancement)
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');

        if (dateFromInput) {
            dateFromInput.addEventListener('change', function() {
                if (this.value && dateToInput.value) {
                    // Optional: Auto-submit when both dates are selected
                    // this.form.submit();
                }
            });
        }

        if (dateToInput) {
            dateToInput.addEventListener('change', function() {
                if (this.value && dateFromInput.value) {
                    // Optional: Auto-submit when both dates are selected
                    // this.form.submit();
                }
            });
        }

        // Add smooth transitions for filter badges
        const filterBadges = document.querySelectorAll('.filter-badge-remove');
        filterBadges.forEach(badge => {
            badge.addEventListener('click', function(e) {
                e.preventDefault();
                const badge = this.closest('.filter-badge');
                badge.style.opacity = '0.5';
                badge.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    window.location.href = this.href;
                }, 150);
            });
        });
    });
</script>
@endpush
