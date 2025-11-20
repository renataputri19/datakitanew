@extends('layouts.app')

@section('title', 'Dashboard MONALISA - Monitoring dan Evaluasi Statistik Sektoral')
@section('description', 'Dashboard untuk monitoring dan evaluasi statistik sektoral')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-8">
        <div class="container mx-auto px-4 md:px-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-100">
                            Dashboard <span class="text-blue-600 dark:text-blue-500">MONALISA</span>
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            Monitoring dan Evaluasi Statistik Sektoral
                        </p>
                    </div>
                    <a href="{{ route('monalisa.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
                
                <!-- User Info -->
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Selamat datang,</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @if(auth()->user()->is_bps)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        BPS User
                                    </span>
                                @endif
                                @if(auth()->user()->is_kominfo_user)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                        Kominfo User
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coming Soon Notice -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-8 md:p-12 text-center">
                <div class="max-w-2xl mx-auto">
                    <div class="mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Dashboard Segera Hadir
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                        Fitur dashboard MONALISA sedang dalam tahap pengembangan. Anda akan dapat mengakses monitoring dan evaluasi statistik sektoral secara real-time dalam waktu dekat.
                    </p>
                    
                    <!-- Features Preview -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <div class="bg-gray-50 dark:bg-gray-950 rounded-lg p-6">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Upload Dokumen</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Unggah dan kelola dokumen statistik sektoral</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-950 rounded-lg p-6">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Monitoring Real-time</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pantau perkembangan indikator secara langsung</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-950 rounded-lg p-6">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Evaluasi Otomatis</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Hitung nilai dan tingkat kematangan indikator</p>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Untuk informasi lebih lanjut, silakan hubungi administrator sistem.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

