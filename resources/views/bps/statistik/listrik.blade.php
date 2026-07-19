@extends('layouts.bps')

@section('title', 'Statistik Listrik - DataKita')
@section('description', 'Dashboard statistik hasil Survei Listrik Kota Batam')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/bps-statistik.css') }}?v={{ filemtime(public_path('css/bps-statistik.css')) }}">
@endpush

@section('content')
<div class="stx-root">
    {{-- ── Hero header ────────────────────────────────────── --}}
    <div class="stx-hero">
        <div class="stx-hero-bars" aria-hidden="true">
            <span style="height:34px"></span><span style="height:52px"></span><span style="height:28px"></span><span style="height:64px"></span><span style="height:44px"></span><span style="height:72px"></span>
        </div>
        <div class="stx-hero-row">
            <div>
                <span class="stx-hero-kicker"><span class="dot"></span> BPS Kota Batam</span>
                <h1 class="stx-title">Statistik Listrik</h1>
                <p class="stx-sub">Ringkasan hasil Survei Listrik — produksi listrik (KWH) dan nilai produksi (rupiah) per bulan menurut kategori pelanggan: rumah tangga, industri, sosial, bisnis, pemerintah, dan multiguna/T/L.</p>
                <div class="stx-hero-meta">
                    <span class="m">⚡ {{ count($payload['months']) }} bulan periode</span>
                    <span class="m">🏭 {{ count($payload['rows']) }} perusahaan</span>
                    <span class="m">🕐 Diperbarui {{ $payload['generatedAt'] }}</span>
                </div>
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
        <div class="stx-card" id="card-kategori"></div>
        <div class="stx-card" id="card-harga"></div>
        <div class="stx-card stx-span-2" id="card-wilayah"></div>
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
<script>window.__LISTRIK_STAT__ = @json($payload);</script>
<script src="{{ asset('js/bps-statistik-listrik.js') }}?v={{ filemtime(public_path('js/bps-statistik-listrik.js')) }}"></script>
@endpush
