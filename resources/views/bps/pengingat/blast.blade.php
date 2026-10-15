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
    // queued and sending are one "still waiting" group for the filter.
    $group  = fn ($status) => in_array($status, ['queued', 'sending'], true) ? 'menunggu' : $status;
    $total  = $logs->count();
    $period = 'Triwulan ' . ($roman[$blast->triwulan] ?? $blast->triwulan) . ' ' . $blast->tahun;
    $filters = array_filter([
        'sent'     => ['Terkirim', $blast->sent_count],
        'menunggu' => ['Menunggu', $blast->queued_count],
        'failed'   => ['Gagal', $blast->failed_count],
    ], fn ($f) => $f[1] > 0);
@endphp

@push('styles')
@include('bps.pengingat.partials.styles')
{{-- Keep the counts moving while the scheduler works through the queue. With
     JS the reload waits out an open confirmation (see the script below). --}}
@if($blast->queued_count > 0)
    <noscript><meta http-equiv="refresh" content="20"></noscript>
@endif
@endpush

@section('content')
<div class="pg">
    <div class="pg-head">
        <div>
            <a href="{{ route('bps.pengingat.index', ['tab' => 'riwayat']) }}" class="pg-link pg-back">
                @include('bps.pengingat.partials.icon', ['name' => 'back', 'class' => 'pg-ico'])
                Kembali ke riwayat
            </a>
            <h1 class="pg-title">Pengingat SIBSTR {{ $period }}</h1>
            <p class="pg-subtitle">
                {{ $blast->trigger === 'otomatis' ? 'Dikirim otomatis' : 'Dikirim manual oleh ' . ($blast->createdBy->name ?? '-') }}
                · {{ $wib($blast->created_at)->translatedFormat('l, d M Y H.i') }} WIB
                @if($blast->sources)
                    · dari data: {{ collect($blast->sources)->map(fn ($s) => $sourceLabels[$s] ?? \App\Support\SibstrReminder::sourceTag($s, $blast->triwulan))->implode(', ') }}
                @else
                    · ke responden yang {{ collect($blast->target_statuses)->map(fn ($s) => mb_strtolower($statusLabels[$s] ?? $s))->implode(' / ') }}
                @endif
            </p>
        </div>
        <a href="{{ route('bps.pengingat.blast.email', $blast) }}" target="_blank" rel="noopener" class="pg-btn pg-btn-ghost">
            @include('bps.pengingat.partials.icon', ['name' => 'eye'])
            Lihat isi email
        </a>
    </div>

    {{-- Outcome --}}
    <div class="pg-panel">
        <div class="pg-result-head">
            <div>
                <div class="pg-overview-label">Hasil pengiriman</div>
                <div class="pg-result-title">
                    @if($total === 0)
                        Tidak ada email yang dikirim
                    @elseif($blast->queued_count > 0)
                        @include('bps.pengingat.partials.icon', ['name' => 'refresh'])
                        Sedang dikirim: {{ $blast->sent_count }} dari {{ $total }}
                    @elseif($blast->failed_count > 0)
                        <span class="pg-bad">@include('bps.pengingat.partials.icon', ['name' => 'warn'])</span>
                        {{ $blast->failed_count }} email gagal terkirim
                    @else
                        <span class="pg-ok">@include('bps.pengingat.partials.icon', ['name' => 'check'])</span>
                        Semua email terkirim
                    @endif
                </div>
                <div class="pg-hint">
                    @if($blast->queued_count > 0)
                        Email terkirim bertahap. Halaman ini diperbarui otomatis setiap 20 detik.
                    @elseif($blast->failed_count > 0)
                        Penyebabnya ada di kolom Keterangan. Gangguan sementara biasanya beres bila dikirim ulang.
                    @endif
                </div>
            </div>
            @if($blast->failed_count > 0)
                <form method="POST" action="{{ route('bps.pengingat.retry', $blast) }}"
                      data-confirm="Kirim ulang email yang gagal?"
                      data-confirm-text="{{ $blast->failed_count }} email yang gagal masuk antrean lagi dan terkirim bertahap dalam beberapa menit."
                      data-confirm-ok="Ya, Kirim Ulang">
                    @csrf
                    <button type="submit" class="pg-btn pg-btn-primary" @disabled(! $serverEnabled)>
                        @include('bps.pengingat.partials.icon', ['name' => 'refresh'])
                        Kirim ulang {{ $blast->failed_count }} yang gagal
                    </button>
                </form>
            @endif
        </div>
        @include('bps.pengingat.partials.progress', [
            'total' => $total, 'sent' => $blast->sent_count,
            'queued' => $blast->queued_count, 'failed' => $blast->failed_count, 'large' => true,
        ])
    </div>

    {{-- What was sent --}}
    <div class="pg-panel">
        <dl class="pg-dl">
            <dt>Template</dt>
            <dd>
                {{ $blast->template->name ?? 'Template sudah dihapus' }}
                <span class="pg-hint">(teks yang dikirim tersimpan di riwayat ini)</span>
            </dd>
            <dt>Subjek</dt>
            <dd>{{ \App\Support\ReminderTemplate::subject($blast->subject, \App\Support\ReminderTemplate::variables($blast->tahun, $blast->triwulan, null, null, false, '')) }}</dd>
            @if($blast->extra_message)
                <dt>Pesan tambahan</dt>
                <dd class="pg-message">{{ $blast->extra_message }}</dd>
            @endif
        </dl>
    </div>

    {{-- Per recipient --}}
    <div class="pg-panel pg-flush">
        @if($logs->isEmpty())
            <div class="pg-empty">
                @include('bps.pengingat.partials.icon', ['name' => 'inbox'])
                <p>Tidak ada email pada pengiriman ini. Semua responden yang sesuai sudah dikirimi hari itu atau tidak punya email Blok I.</p>
            </div>
        @else
            @if(count($filters) > 1)
                <div class="pg-filters" role="group" aria-label="Saring menurut status">
                    <button type="button" class="pg-seg pg-seg-sm is-active" data-filter="" aria-pressed="true">
                        Semua <span class="pg-count">{{ $total }}</span>
                    </button>
                    @foreach($filters as $key => [$label, $count])
                        <button type="button" class="pg-seg pg-seg-sm" data-filter="{{ $key }}" aria-pressed="false">
                            {{ $label }} <span class="pg-count">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
            <div class="pg-scroll-x">
                <table class="pg-table">
                    <thead>
                        <tr><th>Perusahaan</th><th>Email</th><th>Status</th><th>Waktu kirim</th><th>Keterangan</th></tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php [$cls, $label] = $badges[$log->status] ?? ['pg-badge-gray', $log->status]; @endphp
                            <tr data-group="{{ $group($log->status) }}">
                                <td>
                                    <div class="pg-strong">{{ $log->nama_perusahaan ?: '-' }}</div>
                                    @if($log->penghubung)<div class="pg-muted">{{ $log->penghubung }}</div>@endif
                                </td>
                                <td class="pg-break">{{ $log->email }}</td>
                                <td><span class="pg-badge {{ $cls }}">{{ $label }}</span></td>
                                <td class="pg-nowrap">{{ $log->sent_at ? $wib($log->sent_at)->format('d/m/Y H.i') : '-' }}</td>
                                <td class="pg-break"><div class="pg-muted">{{ $log->error ?: '' }}</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @include('bps.pengingat.partials.confirm')
</div>
@endsection

@push('scripts')
<script>
@if($blast->queued_count > 0)
// Refresh the counts every 20s, but never reload out from under an open
// "Kirim ulang" confirmation — skip that tick and try again on the next.
setInterval(function () {
    if (!document.querySelector('dialog[open]')) location.reload();
}, 20000);
@endif

(function () {
    var chips = [].slice.call(document.querySelectorAll('[data-filter]'));
    var rows  = [].slice.call(document.querySelectorAll('tr[data-group]'));
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var want = chip.getAttribute('data-filter');
            chips.forEach(function (c) {
                c.classList.toggle('is-active', c === chip);
                c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
            });
            rows.forEach(function (r) { r.hidden = want !== '' && r.getAttribute('data-group') !== want; });
        });
    });
})();
</script>
@endpush
