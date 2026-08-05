@extends('layouts.bps')

@section('title', 'Portal Pengembang - BPS Dashboard')
@section('description', 'Daftarkan dan kelola aplikasi buatan sendiri di bawah domain DataKita')

@section('content')
    @include('develop.partials.flash')

    <div class="bps-card">
        <div class="bps-card-header">
            <div>
                <h1 class="bps-title">Portal Pengembang</h1>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Jalankan aplikasi dari repositori Git Anda sendiri di bawah alamat DataKita, lengkap dengan login DataKita.
                </p>
            </div>
            <a href="{{ route('develop.create') }}" class="bps-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Aplikasi Baru
            </a>
        </div>

        <div class="bps-card-body">
            {{-- Configuration warnings: without these nothing will actually deploy or route. --}}
            @unless($dokployReady)
                <div class="bps-info-box mb-6 !bg-amber-50 dark:!bg-amber-900/20 !border-amber-200 dark:!border-amber-800">
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        <strong>Dokploy belum dikonfigurasi.</strong>
                        Aplikasi masih bisa didaftarkan, tapi tombol Deploy akan gagal sampai
                        <code class="text-xs">DOKPLOY_URL</code> dan <code class="text-xs">DOKPLOY_API_KEY</code> diisi.
                        Uji koneksi dengan <code class="text-xs">php artisan dokploy:ping</code>.
                    </p>
                </div>
            @endunless

            @unless($traefikWritable)
                <div class="bps-info-box mb-6">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Konfigurasi rute dibuat manual.</strong>
                        <code class="text-xs">DEVAPPS_TRAEFIK_DYNAMIC_PATH</code> belum di-mount, jadi berkas routing tiap
                        aplikasi perlu diunduh dari halaman detail lalu disalin ke direktori dynamic Traefik.
                    </p>
                </div>
            @endunless

            @forelse($apps as $app)
                @if($loop->first)
                    <div class="overflow-x-auto">
                    <table class="bps-table">
                        <thead class="bps-table-header">
                            <tr>
                                <th>Aplikasi</th>
                                <th>Alamat</th>
                                <th>Akses</th>
                                <th>Status</th>
                                @if($canManageAll)<th>Pemilik</th>@endif
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bps-table-body">
                @endif

                <tr>
                    <td>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $app->name }}</div>
                        @if($app->description)
                            <div class="text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $app->description }}</div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ $app->publicUrl() }}" target="_blank" rel="noopener"
                           class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-mono">
                            {{ $app->mountPath() }}
                        </a>
                    </td>
                    <td>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ \App\Models\DevApp::authModeDefinitions()[$app->auth_mode]['label'] ?? $app->auth_mode }}
                        </span>
                    </td>
                    <td>
                        <span class="bps-badge bps-badge-{{ $app->statusBadge() }}">{{ $app->statusLabel() }}</span>
                        @unless($app->enabled)
                            <span class="bps-badge bps-badge-gray ml-1">Nonaktif</span>
                        @endunless
                        @if($app->isConfirmedUnprotected())
                            <span class="bps-badge bps-badge-red ml-1">Tidak terlindungi</span>
                        @endif
                    </td>
                    @if($canManageAll)
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ $app->owner->name ?? '—' }}</td>
                    @endif
                    <td class="text-right">
                        <a href="{{ route('develop.show', $app) }}" class="bps-btn-secondary bps-btn-sm">Kelola</a>
                    </td>
                </tr>

                @if($loop->last)
                        </tbody>
                    </table>
                    </div>
                @endif
            @empty
                <div class="bps-empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" class="bps-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    <h2 class="bps-empty-title">Belum ada aplikasi</h2>
                    <p class="bps-empty-text">
                        Punya aplikasi di GitHub? Daftarkan di sini dan DataKita akan membangun,
                        menjalankan, serta memasangnya di alamat pilihan Anda.
                    </p>
                    <a href="{{ route('develop.create') }}" class="bps-btn-primary">Daftarkan Aplikasi Pertama</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Short orientation for a developer landing here for the first time. --}}
    <div class="bps-card mt-6">
        <div class="bps-card-body">
            <h2 class="bps-subtitle">Cara kerjanya</h2>
            <ol class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-400 list-decimal list-inside">
                <li>Anda mendaftarkan repositori Git dan memilih alamat, misalnya <code class="text-xs">/survei-listrik</code>.</li>
                <li>DataKita membuat kontainer terpisah di Dokploy, membangunnya dari repositori Anda, lalu memasangnya di alamat itu.</li>
                <li>Setiap permintaan ke alamat tersebut diperiksa dulu oleh DataKita sesuai mode akses yang Anda pilih.</li>
                <li>Aplikasi Anda menerima identitas pengguna lewat header
                    <code class="text-xs">{{ config('devapps.identity_headers.id') }}</code> —
                    tidak perlu membuat sistem login sendiri.</li>
            </ol>
            <p class="bps-help-text mt-4">
                Kontainer Anda berjalan terpisah dan tidak memiliki kredensial basis data DataKita.
                Butuh data DataKita? Minta token API khusus ke pengelola.
            </p>
        </div>
    </div>
@endsection
