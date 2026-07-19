@extends('layouts.superadmin')

@section('title', 'Dashboard Superadmin - DataKita')
@section('description', 'Pusat kontrol Superadmin DataKita')

@section('dashboard-content')
    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 text-white p-6 sm:p-8 mb-8 shadow-lg">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-xs font-semibold mb-3 backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Superadmin
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold">Selamat datang, {{ Auth::user()->name }}</h1>
                <p class="text-blue-100 mt-1 max-w-xl">Pusat kontrol DataKita — kelola pengguna &amp; role, data submission SIBSTR, dan perusahaan dari satu tempat.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('superadmin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-blue-700 font-semibold rounded-lg shadow hover:bg-blue-50 transition-colors whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Tambah Pengguna
                </a>
            </div>
        </div>
        <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10"></div>
        <div class="absolute right-24 -bottom-16 w-40 h-40 rounded-full bg-white/10"></div>
    </div>

    {{-- Management Hub --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('superadmin.users.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Manajemen Pengguna</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $userStats['total'] }} pengguna &middot; tambah &amp; atur role</p>
        </a>

        <a href="{{ route('superadmin.submissions.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-700 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Data Submission SIBSTR</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $stats['total'] }} submission masuk</p>
        </a>

        <a href="{{ route('superadmin.companies.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg hover:border-amber-300 dark:hover:border-amber-700 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-amber-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Data Perusahaan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $companyCount }} perusahaan terdaftar</p>
        </a>
    </div>

    {{-- Platform Overview: users per role --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Pengguna per Role</h2>
            <a href="{{ route('superadmin.users.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Lihat semua &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $roleBorder = [
                    'red'    => 'border-t-red-500',
                    'blue'   => 'border-t-blue-500',
                    'purple' => 'border-t-purple-500',
                    'amber'  => 'border-t-amber-500',
                    'gray'   => 'border-t-gray-400',
                ];
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 border-t-4 border-t-indigo-500 p-4">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $userStats['total'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Total Pengguna</div>
            </div>
            @foreach($roleDefinitions as $key => $def)
                <a href="{{ route('superadmin.users.index', ['role' => $key]) }}"
                   class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 border-t-4 {{ $roleBorder[$def['badge']] ?? $roleBorder['gray'] }} p-4 hover:shadow-md transition-shadow">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $userStats[$key] ?? 0 }}</div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">{{ $def['short'] }}</div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- SIBSTR submission statistics --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Submission SIBSTR</h2>
            <a href="{{ route('superadmin.submissions.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Kelola data &rarr;</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Total Submission</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['industri'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Industri</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['non_industri'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Non-Industri</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['today'] }}</div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Hari Ini</div>
            </div>
        </div>
    </div>
@endsection
