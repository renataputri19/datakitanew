@extends('layouts.bps')

@section('title', 'Daftarkan Aplikasi - Portal Pengembang')
@section('description', 'Daftarkan aplikasi dari repositori Git Anda ke DataKita')

@section('content')
    @include('develop.partials.flash')

    <div class="bps-card">
        <div class="bps-card-header">
            <h1 class="bps-title">Daftarkan Aplikasi</h1>
            <a href="{{ route('develop.index') }}" class="bps-btn-secondary bps-btn-sm">Kembali</a>
        </div>

        <form action="{{ route('develop.store') }}" method="POST">
            @csrf
            <div class="bps-card-body">
                @include('develop.partials.form')
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('develop.index') }}" class="bps-btn-secondary">Batal</a>
                <button type="submit" class="bps-btn-primary">Simpan</button>
            </div>
        </form>
    </div>

    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        Menyimpan hanya mendaftarkan aplikasi. Pembuatan kontainer dan build pertama dijalankan
        dari halaman detail setelah ini.
    </p>
@endsection
