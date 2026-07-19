@extends('layouts.superadmin')

@section('title', 'Tambah Pengguna - Superadmin - DataKita')
@section('description', 'Tambah akun pengguna baru dan tetapkan role')

@section('dashboard-content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <nav class="text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard Superadmin</a>
            <span class="mx-1">/</span>
            <a href="{{ route('superadmin.users.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Manajemen Pengguna</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 dark:text-gray-300">Tambah</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Tambah Pengguna</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Buat akun baru dan pilih satu role. Role menentukan fungsi/akses pengguna dan bersifat eksklusif (tidak tumpang tindih).</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Terdapat kesalahan pada input:</p>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.users.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Akun</h2>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required autocomplete="new-password"
                           placeholder="Min. 8 karakter, huruf & angka"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                           placeholder="Ulangi password"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Role Pengguna <span class="text-red-500">*</span></h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Pilih satu role. Setiap role memberi akses fungsi yang berbeda.</p>
            @include('superadmin.users.partials.role-selector', ['definitions' => $definitions, 'selected' => old('role')])
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('superadmin.users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Pengguna
            </button>
        </div>
    </form>
</div>
@endsection
