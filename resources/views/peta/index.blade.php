@extends('layouts.app')

@section('title', 'TUNJUKIN SE — Penuntun dan Penunjuk Arah SE | DataKita BPS Kota Batam')
@section('description', 'TUNJUKIN SE: penuntun dan penunjuk arah untuk petugas Sensus Ekonomi — temukan batas SLS, lihat lokasi Anda secara langsung, dan dapatkan arah menuju lokasi.')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #peta-map { height: 60vh; min-height: 380px; z-index: 0; background: #e5e7eb; }
        @media (min-width: 1024px) { #peta-map { height: calc(100vh - 5.5rem); } }
        .dark #peta-map { background: #1f2937; }

        /* ── Branded select (forms plugin not enabled, so style manually) ── */
        .peta-select {
            -webkit-appearance: none; appearance: none; width: 100%;
            border: 1.5px solid #cbd5e1; border-radius: 0.625rem; background-color: #fff;
            padding: 0.625rem 2.35rem 0.625rem 0.875rem; font-size: 0.875rem; font-weight: 500; color: #0f172a;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2364748b' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 0.7rem center; background-size: 1rem;
            transition: border-color .15s ease, box-shadow .15s ease; cursor: pointer;
        }
        .peta-select:hover:not(:disabled) { border-color: #94a3b8; }
        .peta-select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.18); }
        .peta-select:disabled { background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed; opacity: 1; }
        .dark .peta-select { background-color: #0f172a; border-color: #334155; color: #e2e8f0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); }
        .dark .peta-select:disabled { background-color: #1e293b; color: #64748b; }

        /* Step number badge */
        .peta-step { display:inline-flex; align-items:center; justify-content:center; height:1.25rem; width:1.25rem;
            border-radius:9999px; background:#2563eb; color:#fff; font-size:11px; font-weight:700; flex-shrink:0; }

        /* Live position dot */
        @keyframes petaPulse { 0% { transform: scale(.5); opacity: .75; } 100% { transform: scale(2.6); opacity: 0; } }
        .peta-pulse-ring { animation: petaPulse 1.8s ease-out infinite; }
        .peta-pin { background:#ea580c; width:16px; height:16px; border-radius:50% 50% 50% 0; transform:rotate(-45deg);
            border:2px solid #fff; box-shadow:0 1px 5px rgba(0,0,0,.45); }

        /* Locate button active state */
        #btn-locate.locate-active { background:#ecfdf5; border-color:#6ee7b7; color:#047857; }
        .dark #btn-locate.locate-active { background:#022c22; border-color:#065f46; color:#6ee7b7; }

        /* Popup */
        .leaflet-popup-content { margin: 12px 14px; font-size: 13px; line-height: 1.45; }
        .leaflet-popup-content-wrapper { border-radius: 12px; }
        .peta-popup-btn { display:inline-flex; align-items:center; gap:4px; margin-top:8px; padding:6px 10px;
            border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; border:0; color:#fff; }
    </style>
@endpush

@section('content')
<section class="bg-slate-50 dark:bg-slate-900 min-h-screen">

    {{-- ───────────── Branded banner: TUNJUKIN SE ───────────── --}}
    <div class="bg-gradient-to-r from-blue-600 via-blue-600 to-violet-600">
        <div class="container mx-auto px-4 py-5">
            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
                {{-- Brand: SE logo + product name --}}
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 shrink-0 items-center rounded-2xl bg-white px-3 shadow-md ring-1 ring-white/40">
                        <img src="{{ asset('img/logo-se2026-sm.png') }}" alt="Logo Sensus Ekonomi 2026" class="h-7 sm:h-8 w-auto object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white leading-none">TUNJUKIN SE</h1>
                            <span class="hidden sm:inline-flex items-center rounded-md bg-white/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white ring-1 ring-white/25">Sensus Ekonomi</span>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-blue-50">Penuntun dan Penunjuk Arah SE</p>
                        <p class="text-xs text-blue-100/90 max-w-xl">
                            Panduan lapangan untuk petugas Sensus Ekonomi — temukan batas SLS, lihat lokasi Anda, dan arah menuju lokasi.
                        </p>
                    </div>
                </div>

                {{-- Institutional: BPS logo + wilayah --}}
                <div class="flex items-center gap-3">
                    <div class="flex h-12 items-center rounded-xl bg-white/95 px-3 shadow-sm ring-1 ring-white/40">
                        <img src="{{ asset('img/Logo BPS.png') }}" alt="Logo Badan Pusat Statistik" class="h-8 w-auto object-contain">
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 ring-1 ring-white/25 px-3 py-2 text-xs font-semibold text-white backdrop-blur">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        Kota Batam
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(! $dataReady)
        <div class="container mx-auto px-4 py-8">
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Data peta belum dibangun. Jalankan <code class="font-mono font-semibold">php artisan peta:build</code> terlebih dahulu.
            </div>
        </div>
    @else
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-5">

            {{-- ═══════════════ Sidebar ═══════════════ --}}
            <aside class="space-y-4 lg:max-h-[calc(100vh-5.5rem)] lg:overflow-y-auto lg:pr-1.5">

                {{-- Filter card --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/[0.02]">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 px-4 py-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Filter Wilayah</h2>
                    </div>

                    <div class="p-4 space-y-3.5">
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                                <span class="peta-step">1</span> Kecamatan
                            </label>
                            <select id="f-kec" class="peta-select"><option value="">— Pilih Kecamatan —</option></select>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                                <span class="peta-step">2</span> Kelurahan / Desa
                            </label>
                            <select id="f-desa" disabled class="peta-select"><option value="">— Pilih Kelurahan —</option></select>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                                <span class="peta-step">3</span> SLS (RT/RW)
                                <span id="f-sls-count" class="ml-auto font-normal text-slate-400"></span>
                            </label>
                            <select id="f-sls" disabled class="peta-select"><option value="">— Pilih SLS —</option></select>
                        </div>

                        <p id="f-loading" class="hidden items-center gap-2 text-xs font-medium text-blue-600 dark:text-blue-400">
                            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                            Memuat batas SLS…
                        </p>
                    </div>
                </div>

                {{-- Navigation / target card --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/[0.02]">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 px-4 py-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-950 text-orange-600 dark:text-orange-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Tujuan &amp; Navigasi</h2>
                    </div>

                    <div class="p-4">
                        <div id="target-empty" class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/50 px-3 py-4 text-center">
                            <svg class="mx-auto h-7 w-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                Pilih sebuah SLS atau ketuk poligon di peta,<br>lalu tetapkan sebagai tujuan.
                            </p>
                        </div>

                        <div id="target-info" class="hidden">
                            <div class="rounded-xl border-l-4 border-orange-500 bg-orange-50/60 dark:bg-orange-950/30 px-3 py-2.5">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-600 dark:text-orange-400">Tujuan</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-100" id="t-name">—</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400" id="t-sub">—</p>
                            </div>

                            <div class="mt-3 flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-slate-900 px-3 py-2.5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white dark:bg-slate-800 text-xl shadow-sm">🧭</div>
                                <div class="min-w-0">
                                    <p class="text-lg font-bold leading-tight text-slate-900 dark:text-slate-100" id="t-dist">—</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate" id="t-dir">aktifkan lokasi untuk melihat jarak</p>
                                </div>
                            </div>

                            <a id="t-gmaps" href="https://www.google.com/maps" target="_blank" rel="noopener"
                               class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2.5 shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z"/></svg>
                                Buka di Google Maps
                            </a>
                            <button id="t-focus" type="button"
                               class="mt-2 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium px-4 py-2 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                Fokuskan peta ke SLS
                            </button>
                        </div>

                        <button id="btn-locate" type="button"
                           class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-700 dark:text-blue-300 text-sm font-semibold px-4 py-2.5 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m4-4H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="locate-label">Aktifkan Lokasi Saya</span>
                        </button>
                        <p id="geo-status" class="hidden mt-2 text-xs"></p>
                    </div>
                </div>

                {{-- Legend + help --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/[0.02] p-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Keterangan</h2>
                    <div class="grid grid-cols-2 gap-y-2.5 gap-x-3 text-xs text-slate-600 dark:text-slate-300">
                        <span class="flex items-center gap-2"><span class="inline-block w-5 h-0 border-t-2 border-blue-600"></span> Batas SLS</span>
                        <span class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded-sm bg-orange-500/30 border-2 border-orange-600"></span> SLS tujuan</span>
                        <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-blue-600 ring-2 ring-white shadow"></span> Posisi Anda</span>
                        <span class="flex items-center gap-2"><span class="inline-block w-5 h-0 border-t-2 border-dashed border-red-500"></span> Arah ke tujuan</span>
                    </div>
                    <details class="mt-3.5 border-t border-slate-100 dark:border-slate-700 pt-3 text-xs text-slate-600 dark:text-slate-300">
                        <summary class="cursor-pointer font-semibold text-slate-700 dark:text-slate-200 select-none">Cara pakai</summary>
                        <ol class="mt-2 list-decimal list-inside space-y-1 marker:text-blue-600 marker:font-semibold">
                            <li>Pilih Kecamatan → Kelurahan → SLS.</li>
                            <li>Ketuk <b>Aktifkan Lokasi Saya</b> (izinkan akses lokasi).</li>
                            <li>Untuk berkendara jauh, ketuk <b>Buka di Google Maps</b>.</li>
                            <li>Saat sudah dekat, kembali ke peta ini — titik biru Anda bergerak di atas batas SLS.</li>
                        </ol>
                    </details>
                </div>
            </aside>

            {{-- ═══════════════ Map ═══════════════ --}}
            <div class="relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm ring-1 ring-black/[0.02]">
                <div id="peta-map"></div>
                <button id="btn-recenter" type="button" title="Pusatkan ke lokasi saya"
                    class="hidden absolute bottom-4 right-4 z-[400] h-12 w-12 items-center justify-center rounded-full bg-white dark:bg-slate-800 shadow-lg ring-1 ring-black/5 border border-slate-200 dark:border-slate-700 text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v3m0 14v3m10-10h-3M5 12H2"/></svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection

@push('scripts')
@if($dataReady)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    'use strict';

    const DATA_VER  = @json($dataVersion ?? 0);
    const INDEX_URL = @json(route('peta.data.index')) + '?v=' + DATA_VER;
    const KEL_BASE  = @json(url('tunjukin-se/data/kelurahan')) + '/';

    const map = L.map('peta-map', { zoomControl: true }).setView([1.07, 104.03], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // ── State ──
    let indexData = null, slsLayer = null;
    const layerByKey = {};
    let highlighted = null, target = null, userLatLng = null;
    let userMarker = null, accuracyCircle = null, targetMarker = null, guideLine = null, watchId = null;

    const STYLE_DEFAULT = { color: '#2563eb', weight: 1.5, opacity: 0.9, fillColor: '#3b82f6', fillOpacity: 0.08 };
    const STYLE_HOVER   = { weight: 2.5, fillOpacity: 0.18 };
    const STYLE_TARGET  = { color: '#ea580c', weight: 3, fillColor: '#f97316', fillOpacity: 0.28 };

    const $ = (id) => document.getElementById(id);
    const featKey = (p) => p.id;   // idsubsls — globally unique per (sub-)SLS

    // ─────────── Filters ───────────
    async function loadIndex() {
        const res = await fetch(INDEX_URL, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('index ' + res.status);
        indexData = await res.json();
        indexData.kec.forEach((k, i) => addOption($('f-kec'), String(i), k.n));
    }

    function addOption(sel, value, text) {
        const o = document.createElement('option');
        o.value = value; o.textContent = text; sel.appendChild(o); return o;
    }
    function resetSelect(sel, placeholder) { sel.innerHTML = ''; addOption(sel, '', placeholder); }

    $('f-kec').addEventListener('change', function () {
        resetSelect($('f-desa'), '— Pilih Kelurahan —');
        resetSelect($('f-sls'), '— Pilih SLS —');
        $('f-sls').disabled = true; $('f-sls-count').textContent = '';
        const k = indexData.kec[this.value];
        if (!k) { $('f-desa').disabled = true; return; }
        k.desa.forEach((d, i) => addOption($('f-desa'), String(i), d.n));
        $('f-desa').disabled = false;
    });

    $('f-desa').addEventListener('change', async function () {
        resetSelect($('f-sls'), '— Pilih SLS —');
        $('f-sls').disabled = true; $('f-sls-count').textContent = '';
        const k = indexData.kec[$('f-kec').value];
        const d = k && k.desa[this.value];
        if (!d) return;
        await loadKelurahan(d.file);
        const usedLabels = {};
        d.sls.forEach((s) => {
            // Split SLS carry their real sub-SLS code (kdsubsls), e.g. "(Sub 18)".
            let label = s.sub ? `${s.n} (Sub ${s.sub})` : s.n;
            // Safety net for the rare same-name + same-sub data quirk.
            if (usedLabels[label]) { label = `${label} #${++usedLabels[label]}`; }
            else { usedLabels[label] = 1; }
            const o = addOption($('f-sls'), featKey(s), label);
            o.dataset.lat = s.lat; o.dataset.lng = s.lng;
            o.dataset.name = s.n; o.dataset.subsls = s.sub || '';
            o.dataset.loc = d.n + ', ' + k.n;
        });
        $('f-sls-count').textContent = d.sls.length + ' SLS';
        $('f-sls').disabled = false;
    });

    $('f-sls').addEventListener('change', function () {
        const opt = this.selectedOptions[0];
        if (!opt || !opt.value) return;
        const layer = layerByKey[opt.value];
        if (layer) highlight(layer);
        focusLayer(layer);
        setTarget({ lat: +opt.dataset.lat, lng: +opt.dataset.lng, name: opt.dataset.name, subsls: opt.dataset.subsls, loc: opt.dataset.loc });
    });

    // ─────────── Kelurahan geometry ───────────
    async function loadKelurahan(fileKey) {
        $('f-loading').classList.remove('hidden'); $('f-loading').classList.add('flex');
        try {
            if (slsLayer) { map.removeLayer(slsLayer); slsLayer = null; }
            for (const key in layerByKey) delete layerByKey[key];
            highlighted = null;

            const res = await fetch(KEL_BASE + fileKey + '?v=' + DATA_VER, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('kelurahan ' + res.status);
            const gj = await res.json();

            slsLayer = L.geoJSON(gj, {
                style: STYLE_DEFAULT,
                onEachFeature: (feature, layer) => {
                    const p = feature.properties;
                    layerByKey[featKey(p)] = layer;
                    layer.on('mouseover', () => { if (layer !== highlighted) layer.setStyle(STYLE_HOVER); });
                    layer.on('mouseout',  () => { if (layer !== highlighted) layer.setStyle(STYLE_DEFAULT); });
                    layer.bindPopup(() => buildPopup(p, layer));
                }
            }).addTo(map);

            if (slsLayer.getBounds().isValid()) map.fitBounds(slsLayer.getBounds(), { padding: [20, 20] });
        } finally {
            $('f-loading').classList.add('hidden'); $('f-loading').classList.remove('flex');
        }
    }

    // Tapping a polygon shows info only; "Jadikan Tujuan" sets the destination.
    function buildPopup(p, layer) {
        const wrap = document.createElement('div');
        const subLine = p.sub ? `<br><span style="color:#ea580c;font-weight:600">Sub-SLS ${escapeHtml(p.sub)}</span>` : '';
        wrap.innerHTML =
            `<b style="color:#0f172a">${escapeHtml(p.nmsls)}</b>${subLine}<br>` +
            `<span style="color:#64748b">${escapeHtml(p.nmdesa)}, ${escapeHtml(p.nmkec)}</span><br>` +
            `<button class="peta-popup-btn" style="background:#ea580c" data-act="target">📍 Jadikan Tujuan</button> ` +
            `<button class="peta-popup-btn" style="background:#2563eb" data-act="gmaps">Google Maps</button>`;
        wrap.querySelector('[data-act="target"]').onclick = () => {
            highlight(layer);
            setTarget({ lat: p.lat, lng: p.lng, name: p.nmsls, subsls: p.sub || '', loc: p.nmdesa + ', ' + p.nmkec });
            layer.closePopup();
        };
        wrap.querySelector('[data-act="gmaps"]').onclick = () => window.open(gmapsUrl(p.lat, p.lng), '_blank', 'noopener');
        return wrap;
    }

    // ─────────── Highlight / focus ───────────
    function highlight(layer) {
        if (highlighted && highlighted !== layer) highlighted.setStyle(STYLE_DEFAULT);
        highlighted = layer;
        if (layer) { layer.setStyle(STYLE_TARGET); layer.bringToFront(); }
    }
    function focusLayer(layer) {
        if (layer && layer.getBounds && layer.getBounds().isValid())
            map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 18 });
    }

    // ─────────── Target & navigation ───────────
    function setTarget(t) {
        target = t;
        $('target-empty').classList.add('hidden');
        $('target-info').classList.remove('hidden');
        $('t-name').textContent = t.subsls ? `${t.name} · Sub ${t.subsls}` : t.name;
        $('t-sub').textContent = t.loc || '';
        $('t-gmaps').href = gmapsUrl(t.lat, t.lng);

        const pin = L.divIcon({ className: '', html: '<div class="peta-pin"></div>', iconSize: [16, 16], iconAnchor: [8, 16] });
        if (targetMarker) targetMarker.setLatLng([t.lat, t.lng]).setIcon(pin);
        else targetMarker = L.marker([t.lat, t.lng], { icon: pin }).addTo(map);
        updateGuide();
    }
    $('t-focus').addEventListener('click', () => {
        if (highlighted) focusLayer(highlighted);
        else if (target) map.setView([target.lat, target.lng], 18);
    });
    const gmapsUrl = (lat, lng) => 'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '&travelmode=driving';

    // ─────────── Geolocation ───────────
    function startLocate() {
        if (!('geolocation' in navigator)) return setGeoStatus('Perangkat tidak mendukung lokasi (GPS).', true);
        if (!window.isSecureContext && location.hostname !== 'localhost')
            return setGeoStatus('Lokasi butuh koneksi aman (https). Buka situs ini lewat https.', true);
        setGeoStatus('Mencari lokasi…', false);
        $('locate-label').textContent = 'Melacak lokasi…';
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        watchId = navigator.geolocation.watchPosition(onPosition, onGeoError, { enableHighAccuracy: true, maximumAge: 4000, timeout: 20000 });
    }

    let firstFix = true;
    function onPosition(pos) {
        const { latitude, longitude, accuracy } = pos.coords;
        userLatLng = L.latLng(latitude, longitude);
        if (userMarker) userMarker.setLatLng(userLatLng);
        else userMarker = L.marker(userLatLng, {
            icon: L.divIcon({ className: 'peta-userdot',
                html: '<div style="position:relative;width:18px;height:18px;">' +
                      '<div class="peta-pulse-ring" style="position:absolute;inset:0;border-radius:50%;background:#3b82f6;"></div>' +
                      '<div style="position:absolute;inset:3px;border-radius:50%;background:#2563eb;border:2px solid #fff;box-shadow:0 0 3px rgba(0,0,0,.4);"></div></div>',
                iconSize: [18, 18], iconAnchor: [9, 9] }),
            zIndexOffset: 1000 }).addTo(map);

        if (accuracyCircle) accuracyCircle.setLatLng(userLatLng).setRadius(accuracy);
        else accuracyCircle = L.circle(userLatLng, { radius: accuracy, color: '#3b82f6', weight: 1, fillColor: '#3b82f6', fillOpacity: 0.08 }).addTo(map);

        $('locate-label').textContent = 'Lokasi Aktif';
        $('btn-locate').classList.add('locate-active');
        $('btn-recenter').classList.remove('hidden'); $('btn-recenter').classList.add('flex');
        setGeoStatus('Akurasi ± ' + Math.round(accuracy) + ' m', false);
        if (firstFix && !target) { map.setView(userLatLng, 16); firstFix = false; }
        updateGuide();
    }
    function onGeoError(err) {
        const msg = err.code === 1 ? 'Izin lokasi ditolak. Aktifkan izin lokasi di browser.' :
                    err.code === 2 ? 'Lokasi tidak tersedia. Pastikan GPS aktif.' :
                    err.code === 3 ? 'Waktu pencarian lokasi habis. Coba lagi.' : 'Gagal mendapatkan lokasi.';
        setGeoStatus(msg, true);
        $('locate-label').textContent = 'Coba Aktifkan Lokasi';
        $('btn-locate').classList.remove('locate-active');
    }
    function setGeoStatus(msg, isError) {
        const el = $('geo-status');
        el.textContent = msg;
        el.className = 'mt-2 text-xs ' + (isError ? 'text-red-600 dark:text-red-400' : 'text-slate-500 dark:text-slate-400');
        el.classList.remove('hidden');
    }
    $('btn-locate').addEventListener('click', () => { if (userLatLng) map.setView(userLatLng, 16); else startLocate(); });
    $('btn-recenter').addEventListener('click', () => { if (userLatLng) map.setView(userLatLng, Math.max(map.getZoom(), 16)); });

    // ─────────── Guide line ───────────
    function updateGuide() {
        if (!userLatLng || !target) return;
        const tll = L.latLng(target.lat, target.lng), pts = [userLatLng, tll];
        if (guideLine) guideLine.setLatLngs(pts);
        else guideLine = L.polyline(pts, { color: '#ef4444', weight: 3, dashArray: '6,8', opacity: 0.9 }).addTo(map);
        const dist = userLatLng.distanceTo(tll), dir = bearingLabel(bearing(userLatLng, tll));
        $('t-dist').textContent = formatDist(dist);
        $('t-dir').textContent = 'arah ' + dir + ' dari posisi Anda';
        guideLine.bindTooltip(formatDist(dist) + ' · ' + dir, { sticky: true });
    }
    function bearing(a, b) {
        const toR = Math.PI / 180, toD = 180 / Math.PI;
        const φ1 = a.lat * toR, φ2 = b.lat * toR, Δλ = (b.lng - a.lng) * toR;
        const y = Math.sin(Δλ) * Math.cos(φ2), x = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
        return (Math.atan2(y, x) * toD + 360) % 360;
    }
    const DIRS = ['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'];
    const bearingLabel = (deg) => DIRS[Math.round(deg / 45) % 8];
    const formatDist = (m) => m < 1000 ? Math.round(m) + ' m' : (m / 1000).toFixed(2).replace('.', ',') + ' km';
    const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ─────────── Init ───────────
    loadIndex().catch(() => setGeoStatus('Gagal memuat data wilayah. Muat ulang halaman.', true));
    startLocate();
})();
</script>
@endif
@endpush
