@extends('layouts.app')

@section('title', 'Login - DataKita')
@section('description', 'Login ke akun DataKita untuk mengakses fitur lengkap')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-500">DataKita</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Masukkan email dan password untuk mengakses akun Anda
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-800/20 text-red-600 dark:text-red-500 p-4 rounded-md mb-6">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="nama@contoh.com">
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 dark:text-blue-500 hover:underline">Lupa password?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-300">
                    Masuk
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Belum memiliki akun? <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-500 hover:underline">Daftar</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
