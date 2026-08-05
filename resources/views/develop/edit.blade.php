@extends('layouts.bps')

@section('title', 'Ubah ' . $app->name . ' - Portal Pengembang')
@section('description', 'Ubah pengaturan aplikasi pengembang')

@section('content')
    @include('develop.partials.flash')

    <div class="bps-card">
        <div class="bps-card-header">
            <h1 class="bps-title">Ubah {{ $app->name }}</h1>
            <a href="{{ route('develop.show', $app) }}" class="bps-btn-secondary bps-btn-sm">Kembali</a>
        </div>

        <form action="{{ route('develop.update', $app) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bps-card-body">
                @include('develop.partials.form')
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('develop.show', $app) }}" class="bps-btn-secondary">Batal</a>
                <button type="submit" class="bps-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    @if($app->isProvisioned())
        <div class="bps-info-box mt-4">
            <p class="text-sm text-blue-800 dark:text-blue-300">
                Perubahan mode akses berlaku seketika. Perubahan sumber kode, port, atau alamat
                baru berlaku setelah Deploy ulang.
            </p>
        </div>
    @endif
@endsection
