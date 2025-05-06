@extends('layouts.app')

@section('title', 'Dashboard - DataKita')
@section('description', 'Dashboard pengguna DataKita')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard</h1>
                
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-500 rounded-lg p-4 mb-6">
                    <p class="text-blue-700 dark:text-blue-400">
                        Selamat datang, <span class="font-semibold">{{ $user->name }}</span>! Anda telah berhasil login ke DataKita.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Data Dilihat</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-500">{{ $stats['data_views'] }}</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Unduhan</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-500">{{ $stats['downloads'] }}</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Favorit</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-500">{{ $stats['favorites'] }}</p>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Akun Anda</h2>
                    
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-1/4">Nama:</span>
                            <span class="text-gray-900 dark:text-white">{{ $user->name }}</span>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-1/4">Email:</span>
                            <span class="text-gray-900 dark:text-white">{{ $user->email }}</span>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-1/4">Bergabung pada:</span>
                            <span class="text-gray-900 dark:text-white">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex flex-wrap gap-4">
                        <a href="{{ route('user.profile.show') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 px-4 py-2">
                            Edit Profil
                        </a>
                        
                        <a href="{{ route('user.password.edit') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 px-4 py-2">
                            Ubah Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
