@extends('layouts.bps')

@section('title', 'Detail Pengiriman Pengingat - BPS Dashboard')

@php
    $roman  = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
    $wib    = fn ($dt) => \Illuminate\Support\Carbon::parse($dt)->setTimezone('Asia/Jakarta');
    $badges = [
        'queued'  => ['pg-badge-blue', 'Dalam antrean'],
        'sending' => ['pg-badge-blue', 'Sedang dikirim'],
        'sent'    => ['pg-badge-green', 'Terkirim'],
        'failed'  => ['pg-badge-red', 'Gagal'],
    ];
@endphp

@push('styles')
@include('bps.pengingat.partials.styles')
{{-- Keep the counts moving while the scheduler works through the queue. --}}
@if($blast->queued_count > 0)
    <meta http-equiv="refresh" content="20">
@endif
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <a href="{{ route('bps.pengingat.index', ['tab' => 'riwayat']) }}" class="pg-link text-sm">&larr; Kembali ke riwayat</a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                Pengingat SIBSTR Triwulan {{ $roman[$blast->triwulan] ?? $blast->triwulan }} {{ $blast->tahun }}
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $blast->trigger === 'otomatis' ? 'Otomatis' : 'Manual oleh ' . ($blast->createdBy->name ?? '-') }}
                · {{ $wib($blast->created_at)->translatedFormat('l, d M Y H.i') }} WIB
                · ke responden yang {{ collect($blast->target_statuses)->map(fn ($s) => mb_strtolower($statusLabels[$s] ?? $s))->implode(' / ') }}
            </p>
        </div>
        @if($blast->failed_count > 0)
            <form method="POST" action="{{ route('bps.pengingat.retry', $blast) }}"
                  onsubmit="return confirm('Kirim ulang {{ $blast->failed_count }} email yang gagal?')">
                @csrf
                <button type="submit" class="pg-btn pg-btn-primary" @disabled(! $serverEnabled)>
                    Kirim ulang {{ $blast->failed_count }} yang gagal
                </button>
            </form>
        @endif
    </div>

    <div class="pg-stats">
        <div class="pg-card"><div class="pg-card-label">Penerima</div><div class="pg-card-value">{{ $logs->count() }}</div></div>
        <div class="pg-card"><div class="pg-card-label">Terkirim</div><div class="pg-card-value">{{ $blast->sent_count }}</div></div>
        <div class="pg-card"><div class="pg-card-label">Dalam antrean</div><div class="pg-card-value">{{ $blast->queued_count }}</div>
            @if($blast->queued_count > 0)<div class="pg-card-sub">Halaman diperbarui otomatis</div>@endif
        </div>
        <div class="pg-card"><div class="pg-card-label">Gagal</div><div class="pg-card-value">{{ $blast->failed_count }}</div></div>
    </div>

    <div class="pg-panel">
        <div class="pg-toolbar">
            <div>
                <div class="pg-card-label">Template</div>
                <div class="pg-message mt-1">
                    {{ $blast->template->name ?? 'Template sudah dihapus' }}
                    <span class="pg-muted">— teks yang dikirim tersimpan di riwayat ini</span>
                </div>
                <div class="pg-card-label mt-1">Subjek</div>
                <div class="pg-message mt-1">{{ \App\Support\ReminderTemplate::subject($blast->subject, \App\Support\ReminderTemplate::variables($blast->tahun, $blast->triwulan, null, null, false, '')) }}</div>
            </div>
            <a href="{{ route('bps.pengingat.blast.email', $blast) }}" target="_blank" rel="noopener" class="pg-btn pg-btn-ghost">Lihat isi email</a>
        </div>
        @if($blast->extra_message)
            <div class="pg-card-label mt-1">Pesan tambahan</div>
            <div class="pg-message mt-1">{{ $blast->extra_message }}</div>
        @endif
    </div>

    <div class="pg-panel pg-flush">
        @if($logs->isEmpty())
            <div class="pg-empty">
                Tidak ada email pada pengiriman ini — semua responden yang sesuai sudah dikirimi hari itu atau tidak punya email Blok I.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="pg-table">
                    <thead>
                        <tr><th>Perusahaan</th><th>Penghubung</th><th>Email</th><th>Status</th><th>Waktu Kirim</th><th>Keterangan</th></tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php [$cls, $label] = $badges[$log->status] ?? ['pg-badge-gray', $log->status]; @endphp
                            <tr>
                                <td class="font-medium">{{ $log->nama_perusahaan ?: '-' }}</td>
                                <td>{{ $log->penghubung ?: '-' }}</td>
                                <td class="pg-break">{{ $log->email }}</td>
                                <td><span class="pg-badge {{ $cls }}">{{ $label }}</span></td>
                                <td>{{ $log->sent_at ? $wib($log->sent_at)->format('d/m/Y H.i') : '-' }}</td>
                                <td class="text-xs pg-break">{{ $log->error ?: '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
