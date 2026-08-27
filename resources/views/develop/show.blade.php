@extends('layouts.bps')

@section('title', $app->name . ' - Portal Pengembang')
@section('description', 'Kelola dan deploy aplikasi pengembang')

@section('content')
    @include('develop.partials.flash')

    {{-- ── Header + primary actions ───────────────────────────────────── --}}
    <div class="bps-card">
        <div class="bps-card-header">
            <div>
                <h1 class="bps-title">{{ $app->name }}</h1>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="bps-badge bps-badge-{{ $app->statusBadge() }}">{{ $app->statusLabel() }}</span>
                    {{-- The routing badge reports on the Traefik edge gate. Under
                         the in-app proxy there is no edge to report on — the gate
                         is unconditional, so the badge would only add noise. --}}
                    @if($edgeIsTraefik)
                        <span class="bps-badge bps-badge-{{ $app->routingBadge() }}">{{ $app->routingLabel() }}</span>
                    @endif
                    @unless($app->enabled)
                        <span class="bps-badge bps-badge-gray">Nonaktif</span>
                    @endunless
                    <a href="{{ $app->publicUrl() }}" target="_blank" rel="noopener"
                       class="text-sm font-mono text-blue-600 dark:text-blue-400 hover:underline">{{ $app->publicUrl() }}</a>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('develop.edit', $app) }}" class="bps-btn-secondary bps-btn-sm">Ubah</a>
                <a href="{{ route('develop.index') }}" class="bps-btn-secondary bps-btn-sm">Kembali</a>
            </div>
        </div>

        <div class="bps-card-body">
            {{-- The loudest thing on the page, because an app that is running
                 without its gate is the one failure that looks like success.
                 Traefik-edge only: the proxy gate cannot go missing, because
                 it IS the route. --}}
            @if($edgeIsTraefik && $app->isConfirmedUnprotected())
                <div class="mb-6 rounded-md border-2 border-red-500 bg-red-50 dark:bg-red-900/30 p-4">
                    <p class="text-sm font-bold text-red-800 dark:text-red-300 mb-1">
                        Aplikasi ini TIDAK terlindungi
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-400">
                        Gerbang akses DataKita tidak terpasang di proxy, sehingga siapa pun dapat
                        membuka aplikasi ini tanpa login. Kontainer sudah dihentikan otomatis.
                        Jalankan <strong>Deploy Ulang</strong> untuk memasang ulang gerbangnya.
                    </p>
                    @if($app->routing_error)
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400 font-mono">{{ $app->routing_error }}</p>
                    @endif
                </div>
            @elseif($edgeIsTraefik && $app->routing_status === \App\Models\DevApp::ROUTING_UNVERIFIABLE)
                <div class="mb-6 rounded-md border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-4">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300 mb-1">
                        Status perlindungan tidak dapat diperiksa
                    </p>
                    <p class="text-sm text-amber-700 dark:text-amber-400">
                        DataKita tidak bisa membaca konfigurasi Traefik dari Dokploy, jadi belum
                        bisa dipastikan gerbang akses masih terpasang. Periksa koneksi Dokploy.
                    </p>
                    @if($app->routing_error)
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400 font-mono">{{ $app->routing_error }}</p>
                    @endif
                </div>
            @endif

            @if($app->last_error)
                <div class="mb-6 rounded-md border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">Kesalahan terakhir</p>
                    <pre class="text-xs text-red-700 dark:text-red-400 whitespace-pre-wrap font-mono">{{ $app->last_error }}</pre>
                </div>
            @endif

            @unless($dokployReady)
                <div class="bps-info-box mb-6 !bg-amber-50 dark:!bg-amber-900/20 !border-amber-200 dark:!border-amber-800">
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        Dokploy belum dikonfigurasi, jadi Deploy akan gagal.
                        Jalankan <code class="text-xs">php artisan dokploy:ping</code> untuk memeriksa.
                    </p>
                </div>
            @endunless

            <div class="flex flex-wrap gap-2">
                <form action="{{ route('develop.deploy', $app) }}" method="POST">
                    @csrf
                    <button type="submit" class="bps-btn-primary">
                        {{ $app->isProvisioned() ? 'Deploy Ulang' : 'Siapkan & Deploy' }}
                    </button>
                </form>

                @if($app->isProvisioned())
                    <form action="{{ route('develop.refresh', $app) }}" method="POST">
                        @csrf
                        <button type="submit" class="bps-btn-secondary">Perbarui Status</button>
                    </form>

                    @if($app->status === \App\Models\DevApp::STATUS_STOPPED)
                        <form action="{{ route('develop.start', $app) }}" method="POST">
                            @csrf
                            <button type="submit" class="bps-btn-secondary">Jalankan</button>
                        </form>
                    @else
                        <form action="{{ route('develop.stop', $app) }}" method="POST">
                            @csrf
                            <button type="submit" class="bps-btn-secondary">Hentikan</button>
                        </form>
                    @endif
                @endif

                {{-- The kill switch: closes access without touching the container. --}}
                <form action="{{ route('develop.toggle', $app) }}" method="POST">
                    @csrf
                    <button type="submit" class="{{ $app->enabled ? 'bps-btn-danger' : 'bps-btn-primary' }}">
                        {{ $app->enabled ? 'Nonaktifkan Akses' : 'Aktifkan Akses' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── Configuration summary ──────────────────────────────────── --}}
        <div class="bps-card">
            <div class="bps-card-body">
                <h2 class="bps-subtitle">Konfigurasi</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    @php
                        $rows = [
                            'Pemilik'        => $app->owner->name ?? '—',
                            'Repositori'     => $app->git_repo,
                            'Branch'         => $app->git_branch,
                            'Direktori build'=> $app->git_build_path,
                            'Metode build'   => $app->build_type . ($app->build_type === 'dockerfile' ? " ({$app->dockerfile_path})" : ''),
                            'Port kontainer' => $app->container_port,
                            'Potong awalan'  => $app->strip_prefix ? 'Ya' : 'Tidak',
                            'Mode akses'     => $authModes[$app->auth_mode]['label'] ?? $app->auth_mode,
                        ];
                    @endphp
                    @foreach($rows as $label => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400 flex-shrink-0">{{ $label }}</dt>
                            <dd class="text-gray-900 dark:text-gray-200 font-mono text-xs text-right break-all">{{ $value }}</dd>
                        </div>
                    @endforeach

                    @if($app->auth_mode === \App\Models\DevApp::AUTH_ROLE)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Role diizinkan</dt>
                            <dd class="text-gray-900 dark:text-gray-200 text-xs text-right">
                                {{ collect($app->allowed_roles ?? [])->map(fn ($r) => $roles[$r]['label'] ?? $r)->join(', ') ?: '— belum dipilih —' }}
                            </dd>
                        </div>
                    @endif

                    @if($app->auth_mode === \App\Models\DevApp::AUTH_ALLOWLIST)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Pengguna diizinkan</dt>
                            <dd class="text-gray-900 dark:text-gray-200 text-xs text-right">
                                {{ $app->allowedUsers->pluck('name')->join(', ') ?: 'hanya pemilik' }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- ── Integration contract for the developer ─────────────────── --}}
        <div class="bps-card">
            <div class="bps-card-body">
                <h2 class="bps-subtitle">Cara Membaca Identitas Pengguna</h2>
                <p class="bps-help-text mt-2 mb-4">
                    Permintaan yang lolos pemeriksaan tiba di aplikasi Anda dengan header berikut.
                    Aplikasi Anda tidak perlu — dan tidak boleh — membuat sistem login sendiri.
                </p>

                <div class="rounded-md bg-gray-900 dark:bg-black p-4 overflow-x-auto">
                    <pre class="text-xs text-gray-300 font-mono">@foreach(config('devapps.identity_headers') as $key => $header){{ $header }}: {{ ['id' => '128', 'name' => 'Renata Putri', 'email' => 'renata@bps.go.id', 'role' => 'admin'][$key] ?? '' }}
@endforeach</pre>
                </div>

                <div class="bps-info-box mt-4">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        Header ini hanya bisa dipercaya karena proxy menghapus header bawaan klien
                        dan mengisinya ulang. Jangan pernah menerima identitas dari query string atau body.
                    </p>
                </div>

                <p class="bps-help-text mt-4">
                    Kontainer Anda juga menerima <code class="text-xs">DATAKITA_BASE_PATH</code> dan
                    <code class="text-xs">DATAKITA_PUBLIC_URL</code> sebagai variabel lingkungan.
                </p>
            </div>
        </div>
    </div>

    {{-- ── Routing config ─────────────────────────────────────────────── --}}
    {{-- Only in traefik edge mode. Under the in-app proxy this card describes
         a mechanism that does not run here, which would be actively
         misleading about how the app is actually protected. --}}
    @if($edgeIsTraefik)
    <div class="bps-card mt-6">
        <div class="bps-card-body">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="bps-subtitle">Konfigurasi Rute (Traefik)</h2>
                    <p class="bps-help-text mt-2">
                        @if($app->isProtected())
                            Sudah dipasang otomatis ke Traefik lewat Dokploy dan diverifikasi.
                            Tidak perlu disalin manual — ditampilkan untuk rujukan saja.
                        @elseif($dokployReady)
                            Akan dipasang otomatis lewat Dokploy saat Anda menekan Deploy.
                        @elseif($traefikWritable)
                            Ditulis otomatis ke <code class="text-xs">{{ $traefikFile }}</code> setiap kali pengaturan disimpan.
                        @else
                            Dokploy belum tersedia — salin isi berikut ke
                            <code class="text-xs">{{ $traefikFile }}</code> di direktori dynamic Traefik.
                        @endif
                    </p>
                </div>
                <a href="{{ route('develop.traefik', $app) }}" class="bps-btn-secondary bps-btn-sm">Unduh</a>
            </div>

            <div class="mt-4 rounded-md bg-gray-900 dark:bg-black p-4 overflow-x-auto">
                <pre class="text-xs text-gray-300 font-mono whitespace-pre">{{ $traefikConfig }}</pre>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Deploy history ─────────────────────────────────────────────── --}}
    <div class="bps-card mt-6">
        <div class="bps-card-body">
            <h2 class="bps-subtitle">Riwayat Deploy</h2>

            @forelse($deployments as $deployment)
                @if($loop->first)
                    <div class="mt-4 overflow-x-auto">
                    <table class="bps-table">
                        <thead class="bps-table-header">
                            <tr><th>Waktu</th><th>Oleh</th><th>Status</th><th>Catatan</th></tr>
                        </thead>
                        <tbody class="bps-table-body">
                @endif
                <tr>
                    <td class="text-sm">{{ $deployment->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-sm">{{ $deployment->triggeredBy->name ?? '—' }}</td>
                    <td><span class="bps-badge bps-badge-{{ $deployment->statusBadge() }}">{{ $deployment->statusLabel() }}</span></td>
                    <td class="text-xs text-gray-500 dark:text-gray-400 max-w-md !whitespace-normal">{{ $deployment->log ?: '—' }}</td>
                </tr>
                @if($loop->last)
                        </tbody>
                    </table>
                    </div>
                @endif
            @empty
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Belum pernah dideploy.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Danger zone ────────────────────────────────────────────────── --}}
    <div class="bps-card mt-6 border-red-200 dark:border-red-900">
        <div class="bps-card-body">
            <h2 class="bps-subtitle">Hapus Aplikasi</h2>
            <p class="bps-help-text mt-2 mb-4">
                Menghapus rute dan kontainer aplikasi ini di Dokploy. Repositori Git Anda tidak tersentuh.
            </p>
            <form action="{{ route('develop.destroy', $app) }}" method="POST"
                  onsubmit="return confirm('Hapus aplikasi &quot;{{ $app->name }}&quot; beserta kontainernya? Tindakan ini tidak bisa dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bps-btn-danger">Hapus Aplikasi</button>
            </form>
        </div>
    </div>
@endsection
