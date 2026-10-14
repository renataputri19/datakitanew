{{-- A blast's outcome as one bar: sent (green), failed (red), still queued (blue).
     Expects $total, $sent, $queued, $failed; optional $large. --}}
@php
    $pct = fn ($n) => $total > 0 ? round($n / $total * 100, 2) : 0;
@endphp
@if($total === 0)
    <div class="pg-hint">Tidak ada email — semua sudah dikirimi hari itu atau tidak punya email Blok I.</div>
@else
    <div class="pg-progress {{ ! empty($large) ? 'pg-progress-lg' : '' }}" role="img"
         aria-label="{{ $sent }} terkirim, {{ $queued }} dalam antrean, {{ $failed }} gagal, dari {{ $total }} email">
        @if($sent)<span class="pg-progress-sent" style="width: {{ $pct($sent) }}%"></span>@endif
        @if($failed)<span class="pg-progress-failed" style="width: {{ $pct($failed) }}%"></span>@endif
        @if($queued)<span class="pg-progress-queued" style="width: {{ $pct($queued) }}%"></span>@endif
    </div>
    <div class="pg-legend">
        <span><i class="pg-dot pg-dot-green"></i><b>{{ $sent }}</b> terkirim</span>
        @if($queued)<span><i class="pg-dot pg-dot-blue"></i><b>{{ $queued }}</b> menunggu</span>@endif
        @if($failed)<span><i class="pg-dot pg-dot-red"></i><b>{{ $failed }}</b> gagal</span>@endif
        <span>dari {{ $total }} email</span>
    </div>
@endif
