@extends('layouts.bps')

@section('title', 'Statistik SIBSTR - DataKita')
@section('description', 'Dashboard statistik hasil Survei IBS Triwulanan (SIBSTR) Kota Batam')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/bps-statistik.css') }}?v={{ filemtime(public_path('css/bps-statistik.css')) }}">
@endpush

@section('content')
<div class="stx-root">
    {{-- ── Hero header ────────────────────────────────────── --}}
    <div class="stx-hero">
        <div class="stx-hero-bars" aria-hidden="true">
            <span style="height:26px"></span><span style="height:44px"></span><span style="height:34px"></span><span style="height:58px"></span><span style="height:47px"></span><span style="height:70px"></span>
        </div>
        <div class="stx-hero-row">
            <div>
                <span class="stx-hero-kicker"><span class="dot"></span> BPS Kota Batam</span>
                <h1 class="stx-title">Statistik SIBSTR Triwulanan</h1>
                <p class="stx-sub">Ringkasan hasil Survei Industri Besar dan Sedang Triwulanan — nilai produksi, pendapatan, pengeluaran, perkiraan surplus usaha, tenaga kerja, serta kondisi &amp; prospek usaha, dikelompokkan menurut KBLI.</p>
                <div class="stx-hero-meta">
                    <span class="m">📅 Tahun {{ $payload['tahun'] }}</span>
                    <span class="m">🗂 {{ count($payload['quarters']) }} triwulan berjalan</span>
                    <span class="m">🕐 Diperbarui {{ $payload['generatedAt'] }}</span>
                </div>
            </div>
            <div class="stx-hero-year" id="stx-year-filter">
                <noscript>
                    <form method="GET" action="{{ route('bps.statistik.index') }}">
                        <label class="stx-filter-label" for="stx-year">Tahun</label>
                        <select id="stx-year" name="tahun" class="stx-select" onchange="this.form.submit()">
                            @foreach($payload['availableYears'] as $y)
                                <option value="{{ $y }}" {{ $y === $payload['tahun'] ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </form>
                </noscript>
            </div>
        </div>
    </div>

    {{-- ── Filter row (scopes everything below) ───────────── --}}
    <div class="stx-card stx-filters" id="stx-filters"></div>

    {{-- ── KPI row ────────────────────────────────────────── --}}
    <div class="stx-kpis" id="stx-kpis"></div>

    {{-- ── Charts ─────────────────────────────────────────── --}}
    <div class="stx-grid stx-grid-2">
        <div class="stx-card stx-span-2" id="card-monthly"></div>
        <div class="stx-card" id="card-quarter"></div>
        <div class="stx-card" id="card-compo"></div>
        <div class="stx-card stx-span-2" id="card-industri"></div>
        <div class="stx-card stx-span-2" id="card-kbli"></div>
        <div class="stx-card stx-span-2" id="card-blok5"></div>
        <div class="stx-card stx-span-2" id="card-table"></div>
    </div>
</div>

{{-- tooltip + modal roots --}}
<div class="stx-root">
    <div class="stx-tip" id="stx-tip"></div>
    <div class="stx-modal-ov" id="stx-modal-ov" role="dialog" aria-modal="true" aria-labelledby="stx-modal-title">
        <div class="stx-modal">
            <div class="stx-modal-head">
                <div>
                    <div class="stx-modal-title" id="stx-modal-title"></div>
                    <div class="stx-modal-sub" id="stx-modal-sub"></div>
                </div>
                <button type="button" class="stx-x" id="stx-modal-x" aria-label="Tutup">✕</button>
            </div>
            <div class="stx-modal-body" id="stx-modal-body"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>window.__SIBSTR_STAT__ = @json($payload);</script>
<script src="{{ asset('js/bps-statistik.js') }}?v={{ filemtime(public_path('js/bps-statistik.js')) }}"></script>
@endpush
