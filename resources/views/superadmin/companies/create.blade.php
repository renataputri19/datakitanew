@extends('layouts.app')

@section('title', 'Tambah Perusahaan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Perusahaan</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Tambahkan data perusahaan baru ke dalam sistem</p>
                    </div>
                    <a href="{{ route('superadmin.companies.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('superadmin.companies.store') }}" method="POST" class="p-6">
                @csrf

                <!-- Nama Perusahaan -->
                <div class="mb-6">
                    <label for="nama_perusahaan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Perusahaan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama_perusahaan" 
                           id="nama_perusahaan" 
                           value="{{ old('nama_perusahaan') }}"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('nama_perusahaan') border-red-500 @enderror"
                           placeholder="Masukkan nama perusahaan lengkap">
                    @error('nama_perusahaan')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="mb-6">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Alamat
                    </label>
                    <textarea name="alamat" 
                              id="alamat" 
                              rows="4"
                              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('alamat') border-red-500 @enderror"
                              placeholder="Masukkan alamat lengkap perusahaan (opsional)">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('superadmin.companies.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
