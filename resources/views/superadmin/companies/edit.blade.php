@extends('layouts.superadmin')

@section('title', 'Edit Perusahaan - Superadmin - DataKita')

@section('dashboard-content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <nav class="text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>
            <span class="mx-1">/</span>
            <a href="{{ route('superadmin.companies.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Data Perusahaan</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 dark:text-gray-300">Edit</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Edit Perusahaan</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Perbarui data perusahaan.</p>
    </div>

    <form action="{{ route('superadmin.companies.update', $company) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @php
            $base = 'w-full px-4 py-2.5 border rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500';
            $ok   = 'border-gray-300 dark:border-gray-600';
            $err  = 'border-red-500';
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
            <div>
                <label for="nama_perusahaan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Perusahaan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_perusahaan" id="nama_perusahaan" value="{{ old('nama_perusahaan', $company->nama_perusahaan) }}" required
                       placeholder="Masukkan nama perusahaan lengkap"
                       class="{{ $base }} {{ $errors->has('nama_perusahaan') ? $err : $ok }}">
                @error('nama_perusahaan')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
                <textarea name="alamat" id="alamat" rows="4" placeholder="Masukkan alamat lengkap perusahaan (opsional)"
                          class="{{ $base }} {{ $errors->has('alamat') ? $err : $ok }}">{{ old('alamat', $company->alamat) }}</textarea>
                @error('alamat')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('superadmin.companies.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Perbarui
            </button>
        </div>
    </form>
</div>
@endsection
