@extends('layouts.bps')

@section('title', 'Statistik Konstruksi - DataKita')
@section('description', 'Dashboard statistik hasil Survei Perusahaan Konstruksi Kota Batam')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/bps-statistik.css') }}?v={{ filemtime(public_path('css/bps-statistik.css')) }}">
@endpush

@section('content')
<div class="stx-root">
    {{-- ── Hero header ────────────────────────────────────── --}}
    <div class="stx-hero">
        <div class="stx-hero-bars" aria-hidden="true">
            <span style="height:30px"></span><span style="height:58px"></span><span style="height:40px"></span><span style="height:68px"></span><span style="height:36px"></span><span style="height:54px"></span>
        </div>
        <div class="stx-hero-row">
            <div>
                <span class="stx-hero-kicker"><span class="dot"></span> BPS Kota Batam</span>
                <h1 class="stx-title">Statistik Konstruksi</h1>
                <p class="stx-sub">
                    Ringkasan hasil Survei Perusahaan Konstruksi (SKTH) — nilai konstruksi yang diselesaikan,
                    struktur biaya, tenaga kerja, dan sebaran proyek menurut lokasi dan sumber dana.
                    Seluruh nilai dalam <strong>ribuan rupiah</strong>, mengikuti kuesioner.
                </p>
                <div class="stx-hero-meta">
                    <span class="m">🏗️ {{ count($payload['rows']) }} perusahaan</span>
                    <span class="m">📅 {{ count($payload['periods']) }} periode</span>
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
        <div class="stx-card stx-span-2" id="card-wilayah"></div>
        <div class="stx-card" id="card-struktur"></div>
        <div class="stx-card" id="card-dana"></div>
        <div class="stx-card" id="card-tenaga"></div>
        <div class="stx-card" id="card-kualifikasi"></div>
        <div class="stx-card stx-span-2" id="card-bahan"></div>
        <div class="stx-card stx-span-2" id="card-proyek"></div>
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
<script>window.__KONSTRUKSI_STAT__ = @json($payload);</script>
<script src="{{ asset('js/bps-statistik-konstruksi.js') }}?v={{ filemtime(public_path('js/bps-statistik-konstruksi.js')) }}"></script>
@endpush
