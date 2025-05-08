@extends('layouts.bps')

@section('title', 'Profil Pengguna - BPS Dashboard')
@section('description', 'Halaman profil pengguna BPS Kota Batam')

@section('content')
    <div class="bps-card mb-6">
        <div class="bps-card-header">
            <h1 class="bps-title">Profil Pengguna</h1>
            <a href="{{ route('bps.dashboard') }}" class="bps-btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <div class="bps-card-body">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Sukses</h3>
                            <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                                <p>{{ session('status') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <button id="tab-profile" class="inline-block py-2 px-4 text-blue-600 dark:text-blue-500 border-b-2 border-blue-600 dark:border-blue-500 font-medium text-sm">
                            Informasi Profil
                        </button>
                    </li>
                    <li class="mr-2">
                        <button id="tab-password" class="inline-block py-2 px-4 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 font-medium text-sm">
                            Ubah Kata Sandi
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Profile Information Tab -->
            <div id="profile-content" class="tab-content">
                <form action="{{ route('user-profile-information.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="name" class="bps-label">Nama</label>
                            <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" class="bps-input" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="bps-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" class="bps-input" required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="bps-info-box">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Informasi</h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                        <p>Untuk mengubah kata sandi, silakan klik tab "Ubah Kata Sandi" di atas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bps-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Perbarui Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Tab -->
            <div id="password-content" class="tab-content hidden">
                <form action="{{ route('user-password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="current_password" class="bps-label">Kata Sandi Saat Ini</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="current_password" id="current_password" class="bps-input pl-10" required>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password" class="bps-label">Kata Sandi Baru</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" class="bps-input pl-10" required>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="bps-label">Konfirmasi Kata Sandi Baru</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="bps-input pl-10" required>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="bps-info-box">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Tips Keamanan</h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                        <p>Gunakan kata sandi yang kuat dengan kombinasi huruf besar, huruf kecil, angka, dan simbol.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bps-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabProfile = document.getElementById('tab-profile');
        const tabPassword = document.getElementById('tab-password');
        const profileContent = document.getElementById('profile-content');
        const passwordContent = document.getElementById('password-content');

        // Function to switch tabs
        function switchTab(activeTab, activeContent, inactiveTab, inactiveContent) {
            // Update tab styles
            activeTab.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300');
            activeTab.classList.add('text-blue-600', 'dark:text-blue-500', 'border-b-2', 'border-blue-600', 'dark:border-blue-500');

            inactiveTab.classList.remove('text-blue-600', 'dark:text-blue-500', 'border-b-2', 'border-blue-600', 'dark:border-blue-500');
            inactiveTab.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300');

            // Show/hide content
            activeContent.classList.remove('hidden');
            inactiveContent.classList.add('hidden');
        }

        // Set up event listeners
        tabProfile.addEventListener('click', function() {
            switchTab(tabProfile, profileContent, tabPassword, passwordContent);
        });

        tabPassword.addEventListener('click', function() {
            switchTab(tabPassword, passwordContent, tabProfile, profileContent);
        });

        // Check if we should show password tab based on URL hash
        if (window.location.hash === '#password') {
            switchTab(tabPassword, passwordContent, tabProfile, profileContent);
        }
    });
</script>
@endpush
