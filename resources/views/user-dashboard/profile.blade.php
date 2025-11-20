@extends('layouts.user-dashboard')

@section('title', 'Profil Saya - DataKita')
@section('description', 'Kelola informasi profil pengguna DataKita')

@section('dashboard-content')
    <!-- Mobile/Tablet Menu Button -->
    <div class="lg:hidden mb-4">
        <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            Menu
        </button>
    </div>

    <!-- Page Header -->
<div class="ud-page-header">
        <div class="ud-page-header-content">
            <h1 class="ud-page-title">Profil Saya</h1>
            <p class="ud-page-description">Kelola informasi profil dan data pribadi Anda</p>
        </div>
    </div>

    <!-- Profile Card -->
<div class="ud-card">
        <!-- Profile Avatar -->
        <div class="text-center ud-mb-5">
            <div class="ud-profile-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white ud-mb-2">{{ $user->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">
                @if($user->institution)
                    @switch($user->institution->type)
                        @case('personal')
                            Personal
                            @break
                        @case('instansi')
                            Instansi
                            @break
                        @case('akademisi')
                            Akademisi
                            @break
                        @default
                            {{ ucfirst($user->institution->type) }}
                    @endswitch
                @else
                    Personal
                @endif
            </p>
        </div>

        <!-- Profile Information -->
        <div class="ud-grid ud-grid-cols-2 ud-mb-5">
            <div class="ud-info-item">
                <div class="ud-info-label">Nama Lengkap</div>
                <div class="ud-info-value">{{ $user->name }}</div>
            </div>

            <div class="ud-info-item">
                <div class="ud-info-label">Email</div>
                <div class="ud-info-value">{{ $user->email }}</div>
            </div>

            <div class="ud-info-item">
                <div class="ud-info-label">Tipe Pengguna</div>
                <div class="ud-info-value">
                    @if($user->institution)
                        @switch($user->institution->type)
                            @case('personal')
                                Personal
                                @break
                            @case('instansi')
                                Instansi
                                @break
                            @case('akademisi')
                                Akademisi
                                @break
                            @default
                                {{ ucfirst($user->institution->type) }}
                        @endswitch
                    @else
                        Personal
                    @endif
                </div>
            </div>

            @if($user->institution && $user->institution->type !== 'personal')
                <div class="ud-info-item">
                    <div class="ud-info-label">Tipe Institusi</div>
                    <div class="ud-info-value">
                        @if($user->institution->institution_type || $user->institution->academic_type)
                            @php
                                $institutionType = $user->institution->institution_type ?? $user->institution->academic_type;
                            @endphp
                            @switch($institutionType)
                                @case('pemerintah')
                                    Pemerintah
                                    @break
                                @case('swasta')
                                    Swasta
                                    @break
                                @case('universitas')
                                @case('university')
                                    Universitas
                                    @break
                                @case('sekolah_tinggi')
                                @case('college')
                                    Sekolah Tinggi
                                    @break
                                @case('institut')
                                @case('institute')
                                    Institut
                                    @break
                                @case('politeknik')
                                @case('polytechnic')
                                    Politeknik
                                    @break
                                @case('lembaga_penelitian')
                                @case('research')
                                    Lembaga Penelitian
                                    @break
                                @case('perusahaan')
                                    Perusahaan
                                    @break
                                @case('organisasi')
                                    Organisasi
                                    @break
                                @case('lainnya')
                                @case('other')
                                    Lainnya
                                    @break
                                @default
                                    {{ ucfirst(str_replace('_', ' ', $institutionType)) }}
                            @endswitch
                        @else
                            <span class="text-gray-400 dark:text-gray-500">Belum diisi</span>
                        @endif
                    </div>
                </div>

                <div class="ud-info-item md:col-span-2">
                    <div class="ud-info-label">Nama Institusi</div>
                    <div class="ud-info-value">
                        @if($user->institution->name)
                            {{ $user->institution->name }}
                        @else
                            <span class="text-gray-400 dark:text-gray-500">Belum diisi</span>
                        @endif
                    </div>
                </div>

                @if($user->institution->type === 'instansi')
                    <div class="ud-info-item md:col-span-2">
                        <div class="ud-info-label">Alamat Institusi</div>
                        <div class="ud-info-value">
                            @if($user->institution->address)
                                {{ $user->institution->address }}
                            @else
                                <span class="text-gray-400 dark:text-gray-500">Belum diisi</span>
                            @endif
                        </div>
                    </div>

                    <div class="ud-info-item">
                        <div class="ud-info-label">Nomor Telepon Institusi</div>
                        <div class="ud-info-value">
                            @if($user->institution->phone)
                                {{ $user->institution->phone }}
                            @else
                                <span class="text-gray-400 dark:text-gray-500">Belum diisi</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            <div class="ud-info-item">
                <div class="ud-info-label">Bergabung Sejak</div>
                <div class="ud-info-value">{{ $user->created_at->format('d M Y') }}</div>
            </div>

            <div class="ud-info-item">
                <div class="ud-info-label">Terakhir Diperbarui</div>
                <div class="ud-info-value">{{ $user->updated_at->format('d M Y, H:i') }}</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard.settings') }}#profile-section" class="ud-btn ud-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Profil
            </a>

            <a href="{{ route('dashboard.settings') }}" class="ud-btn ud-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Ubah Password
            </a>

            <a href="{{ route('dashboard') }}" class="ud-btn ud-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>
@endsection