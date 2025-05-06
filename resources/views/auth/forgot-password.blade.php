@extends('layouts.app')

@section('title', 'Lupa Password - DataKita')
@section('description', 'Reset password akun DataKita')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-500">DataKita</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Reset Password
                </p>
            </div>

            <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                Lupa password Anda? Tidak masalah. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan reset password yang memungkinkan Anda memilih yang baru.
            </div>

            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 p-4 rounded-md mb-6">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-800/20 text-red-600 dark:text-red-500 p-4 rounded-md mb-6">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="nama@contoh.com">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-300">
                    Kirim Link Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 dark:text-blue-500 hover:underline">
                    Kembali ke halaman login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
