@extends('layouts.user-dashboard')

@section('title', 'Survei Listrik — Blok II: Produksi Listrik Bulanan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<style>
.ub-card{background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:1.5rem;margin-bottom:1.25rem;}
.dark .ub-card{background:#1f2937;border-color:#374151;}
.ub-section-title{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#3b82f6;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #dbeafe;}
.dark .ub-section-title{color:#93c5fd;border-color:#1e3a5f;}

/* ── Monthly grid table ── */
.lst-scroll{overflow-x:auto;border:1px solid #e5e7eb;border-radius:.75rem;}
.dark .lst-scroll{border-color:#374151;}
table.lst-grid{border-collapse:separate;border-spacing:0;width:100%;min-width:1420px;font-size:.78rem;}
.lst-grid th,.lst-grid td{border-bottom:1px solid #e5e7eb;border-right:1px solid #e5e7eb;padding:.3rem .4rem;background:#fff;}
.dark .lst-grid th,.dark .lst-grid td{border-color:#374151;background:#1f2937;}
.lst-grid thead th{position:sticky;top:0;z-index:2;background:#dbeafe;color:#1e3a8a;font-weight:700;text-align:center;font-size:.72rem;line-height:1.25;}
.dark .lst-grid thead th{background:#1e3a5f;color:#bfdbfe;}
.lst-grid thead tr:nth-child(2) th{font-size:.66rem;font-weight:600;background:#eff6ff;}
.dark .lst-grid thead tr:nth-child(2) th{background:#172a45;}
.lst-grid thead th.jumlah{background:#0ea5e9;color:#fff;}
.dark .lst-grid thead th.jumlah{background:#0369a1;}
.lst-grid thead tr:nth-child(2) th.jumlah{background:#0ea5e9;color:#fff;}
.dark .lst-grid thead tr:nth-child(2) th.jumlah{background:#0369a1;color:#fff;}
.lst-grid td.month{position:sticky;left:0;z-index:1;background:#f8fafc;font-weight:700;color:#334155;white-space:nowrap;min-width:6.6rem;vertical-align:top;padding-top:.55rem;}
.dark .lst-grid td.month{background:#111827;color:#cbd5e1;}
.lst-grid input.cell{width:100%;min-width:5.2rem;border:1px solid transparent;background:transparent;text-align:right;font-size:.78rem;padding:.28rem .35rem;border-radius:.375rem;color:#111827;font-variant-numeric:tabular-nums;}
.dark .lst-grid input.cell{color:#f9fafb;}
.lst-grid input.cell:hover{border-color:#cbd5e1;}
.lst-grid input.cell:focus{outline:none;border-color:#3b82f6;background:#eff6ff;box-shadow:0 0 0 2px rgba(59,130,246,.18);}
.dark .lst-grid input.cell:focus{background:#172a45;}
.lst-grid input.cell.cell-empty{background:#fff7ed;}
.dark .lst-grid input.cell.cell-empty{background:#3b2a16;}
.lst-grid input.cell.cell-flash{background:#fee2e2 !important;border-color:#ef4444 !important;}
.lst-grid td.rowtotal{background:#ecfdf5;font-weight:700;text-align:right;color:#065f46;white-space:nowrap;font-variant-numeric:tabular-nums;}
.dark .lst-grid td.rowtotal{background:#0c2f24;color:#6ee7b7;}
.lst-grid tr.subtotalrow td{background:#f0fdf4;font-weight:700;color:#166534;text-align:right;font-size:.72rem;font-variant-numeric:tabular-nums;}
.dark .lst-grid tr.subtotalrow td{background:#0b2b1a;color:#86efac;}
.lst-grid tr.subtotalrow td.sublabel{text-align:left;font-style:italic;}
.lst-grid tr.totalrow td{background:#fef9c3;font-weight:800;color:#713f12;text-align:right;font-variant-numeric:tabular-nums;}
.dark .lst-grid tr.totalrow td{background:#3a3110;color:#fde68a;}
.lst-grid tr.totalrow td.month{background:#fef08a;text-align:left;}
.dark .lst-grid tr.totalrow td.month{background:#4a3f12;}

/* ── Wilayah tujuan cell (label only — dikelola dari panel di atas grid) ── */
.lst-grid td.wilayah{min-width:9.5rem;vertical-align:middle;background:#fbfcfe;}
.dark .lst-grid td.wilayah{background:#18202f;}
.w-cell-name{font-size:.75rem;font-weight:700;color:#334155;line-height:1.3;}
.dark .w-cell-name{color:#e2e8f0;}
.w-cell-badge{display:inline-block;margin-top:.15rem;font-size:.6rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:.08rem .4rem;border-radius:9999px;background:#e0f2fe;color:#0369a1;}
.w-cell-badge.ln{background:#fef3c7;color:#92400e;}
.dark .w-cell-badge{background:#0c3550;color:#7dd3fc;}
.dark .w-cell-badge.ln{background:#4a3510;color:#fcd34d;}

/* ── per-row remove + per-month add ── */
.w-del{border:0;background:#fee2e2;color:#b91c1c;width:1.2rem;height:1.2rem;border-radius:9999px;font-size:.68rem;font-weight:800;line-height:1;cursor:pointer;flex-shrink:0;}
.w-del:hover{background:#fecaca;}
.dark .w-del{background:#7f1d1d;color:#fca5a5;}
.w-cell-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.35rem;}
.w-add{display:inline-flex;align-items:center;gap:.25rem;margin-top:.5rem;border:1.5px dashed #93c5fd;background:#eff6ff;color:#1d4ed8;font-size:.66rem;font-weight:700;padding:.26rem .55rem;border-radius:9999px;cursor:pointer;white-space:nowrap;}
.w-add:hover{background:#dbeafe;}
.dark .w-add{background:#172a45;border-color:#1d4ed8;color:#93c5fd;}

/* ── wilayah popover (quick picks + form baru) ── */
.w-pop{position:fixed;z-index:60;width:min(21rem,92vw);background:#fff;border:1px solid #e5e7eb;border-radius:.9rem;box-shadow:0 18px 44px rgba(9,12,20,.2);animation:wPopIn .13s ease-out;}
.dark .w-pop{background:#1f2937;border-color:#374151;}
@keyframes wPopIn{from{transform:translateY(6px) scale(.99);}to{transform:none;}}
.w-pop-head{padding:.7rem .9rem .5rem;border-bottom:1px solid #f3f4f6;}
.dark .w-pop-head{border-color:#374151;}
.w-pop-head-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;}
.w-pop-title{font-size:.75rem;font-weight:800;color:#111827;}
.dark .w-pop-title{color:#f9fafb;}
.w-pop-sub{font-size:.66rem;color:#9ca3af;margin-top:.1rem;}
.w-pop-x{flex-shrink:0;border:0;background:transparent;color:#9ca3af;font-size:.8rem;line-height:1;cursor:pointer;padding:.2rem;border-radius:.4rem;}
.w-pop-x:hover{background:#f3f4f6;color:#374151;}
.dark .w-pop-x:hover{background:#374151;color:#f9fafb;}
.w-pop-list{max-height:11rem;overflow-y:auto;padding:.35rem;}
.w-pick{display:flex;align-items:center;gap:.55rem;width:100%;border:0;background:transparent;text-align:left;padding:.45rem .55rem;border-radius:.55rem;cursor:pointer;font:inherit;}
.w-pick:hover{background:#f3f4f6;}
.dark .w-pick:hover{background:#374151;}
.w-pick .plus{width:1.35rem;height:1.35rem;border-radius:9999px;background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}
.dark .w-pick .plus{background:#172a45;color:#93c5fd;}
.w-pick .nm{font-size:.78rem;font-weight:700;color:#111827;flex:1;min-width:0;}
.dark .w-pick .nm{color:#f9fafb;}
.w-pick .bd{font-size:.58rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.1rem .4rem;border-radius:9999px;background:#e0f2fe;color:#0369a1;flex-shrink:0;}
.w-pick .bd.ln{background:#fef3c7;color:#92400e;}
.w-pop-empty{font-size:.7rem;color:#9ca3af;padding:.5rem .7rem;}
.w-pop-newbtn{display:block;width:calc(100% - .7rem);margin:.2rem .35rem .45rem;border:1.5px dashed #93c5fd;background:#eff6ff;color:#1d4ed8;font-size:.72rem;font-weight:700;padding:.4rem .6rem;border-radius:.6rem;cursor:pointer;text-align:center;}
.w-pop-newbtn:hover{background:#dbeafe;}
.dark .w-pop-newbtn{background:#172a45;border-color:#1d4ed8;color:#93c5fd;}
.w-form{display:none;flex-direction:column;gap:.55rem;padding:.7rem .9rem;border-top:1px dashed #dbeafe;}
.dark .w-form{border-color:#1e3a5f;}
.w-form.open{display:flex;}
.w-form label{display:block;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.2rem;}
.dark .w-form label{color:#94a3b8;}
.w-form select,.w-form input[type=text]{width:100%;border:1px solid #d1d5db;border-radius:.55rem;padding:.38rem .55rem;font-size:.78rem;background:#fff;color:#111827;}
.dark .w-form select,.dark .w-form input[type=text]{background:#111827;border-color:#4b5563;color:#f9fafb;}
.w-form select:focus,.w-form input[type=text]:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.w-form .w-invalid{border-color:#ef4444 !important;background:#fef2f2;}
.w-form-btn{border:0;border-radius:.55rem;padding:.42rem .85rem;font-size:.75rem;font-weight:700;cursor:pointer;}
.w-form-btn.primary{background:#2563eb;color:#fff;}
.w-form-btn.primary:hover{background:#1d4ed8;}
.w-form-btn.ghost{background:transparent;color:#6b7280;}
.w-form-btn.ghost:hover{color:#374151;}
.w-pop-foot{display:flex;align-items:center;gap:.45rem;padding:.55rem .9rem .7rem;border-top:1px solid #f3f4f6;}
.dark .w-pop-foot{border-color:#374151;}
.w-pop-foot label{display:inline-flex;align-items:center;gap:.4rem;font-size:.7rem;font-weight:600;color:#374151;cursor:pointer;}
.dark .w-pop-foot label{color:#d1d5db;}

.lst-toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1rem;}
.lst-progress{font-size:.78rem;font-weight:600;color:#6b7280;}
.lst-progress strong{color:#111827;}
.dark .lst-progress strong{color:#f9fafb;}
.lst-btn-zero{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;border-radius:.625rem;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:.78rem;font-weight:600;cursor:pointer;transition:background .12s;}
.lst-btn-zero:hover{background:#f9fafb;}
.dark .lst-btn-zero{background:#374151;border-color:#4b5563;color:#e5e7eb;}
</style>
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>Menu
  </button>
</div>

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Blok II — Produksi Listrik Bulanan</h1>
    <p class="ud-page-description">Produksi listrik (KWH) dan nilai produksi (rupiah) per bulan menurut kategori pelanggan dan wilayah tujuan · Januari 2025 s.d. bulan berjalan</p>
  </div>
  <a href="{{ route('survey.listrik.blok1') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

@if(session('error'))
<div class="mt-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
@endif
@if(session('info'))
<div class="mt-4 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-300 text-sm">{{ session('info') }}</div>
@endif

<div class="flex gap-5 items-start mt-4">
@include('survey.listrik.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<div class="ub-card">
  <p class="ub-section-title">BLOK II : PRODUKSI DAN NILAI PRODUKSI LISTRIK PER BULAN</p>

  <div class="lst-toolbar">
    <p class="lst-progress"><strong id="lst-filled">0</strong> dari <strong id="lst-total">0</strong> sel terisi — isi <strong>0</strong> jika tidak ada nilai.</p>
    <button type="button" id="lst-fill-zero" class="lst-btn-zero">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      Isi 0 pada semua sel kosong
    </button>
  </div>

  <div id="lst-tables"></div>

  <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
    <strong>Petunjuk:</strong> Kolom <em>Produksi Listrik</em> diisi dalam satuan <strong>KWH</strong>, kolom <em>Nilai Produksi</em> dalam <strong>rupiah</strong>.
    Gunakan tombol <strong>+ Wilayah</strong> pada kolom periode untuk menambah wilayah tujuan pada bulan itu — pilih cepat dari wilayah yang pernah dipakai, atau buat wilayah baru
    (Dalam Negeri: kab/kota Kepri atau isi manual untuk luar Kepri; Luar Negeri: nama negara). Centang <em>"Terapkan ke semua bulan"</em> jika wilayahnya berlaku untuk seluruh periode.
    Baris <strong>Subtotal</strong>, <strong>TOTAL</strong>, dan kolom <strong>JUMLAH</strong> terhitung otomatis. Data tersimpan otomatis beberapa saat setelah Anda berhenti mengetik.
  </p>
</div>

<form id="lst-form" method="POST" action="{{ route('survey.listrik.blok2.save') }}">
  @csrf
  <input type="hidden" name="data_listrik" id="lst-json">
  <div class="flex flex-wrap items-center justify-between gap-4 mt-2 mb-8">
    <a href="{{ route('survey.listrik.blok1') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Kembali ke Blok I
    </a>
    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
      Simpan &amp; Lanjut ke Blok III
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</form>

</div>
</div>

@push('scripts')
<script>
window.__LST__ = {
    monthsByYear: @json($monthsByYear),
    categories:   @json($categories),
    kepri:        @json($kepriKabkota),
    data:         @json($response->data_listrik ?? new stdClass),
    autoSaveUrl:  @json(route('survey.listrik.blok2.autosave')),
    csrf:         @json(csrf_token())
};
</script>
<script>
(function () {
    'use strict';
    var CFG = window.__LST__;
    var CATS = Object.keys(CFG.categories);
    var MONTH_SHORT = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    var nf = new Intl.NumberFormat('id-ID');
    var COLS = 2 + CATS.length * 2 + 2; // periode + wilayah + cells + jumlah

    function defaultWilayah() { return { jenis: 'dn', area: 'kepri', kabkota: 'Kota Batam', negara: null }; }
    function emptyRowFor(w) {
        var row = { w: { jenis: w.jenis, area: w.area, kabkota: w.kabkota, negara: w.negara } };
        CATS.forEach(function (cat) { row[cat] = { kwh: null, rp: null }; });
        return row;
    }
    function normNum(v) {
        return v === undefined || v === null || v === '' ? null : Number(v);
    }
    function wKey(w) {
        return [w.jenis, w.area || '', String(w.kabkota || '').trim().toLowerCase(), String(w.negara || '').trim().toLowerCase()].join('|');
    }
    function wLabel(w) {
        if (w.jenis === 'ln') return String(w.negara || '').trim() || 'Luar Negeri';
        return String(w.kabkota || '').trim() || 'Dalam Negeri';
    }
    function wBadge(w) {
        if (w.jenis === 'ln') return 'Luar Negeri';
        return w.area === 'luar_kepri' ? 'DN · Luar Kepri' : 'DN · Kepri';
    }

    // grid[ym] = array of wilayah rows
    var grid = {};
    (function initGrid() {
        var d = CFG.data || {};
        Object.keys(CFG.monthsByYear).forEach(function (year) {
            CFG.monthsByYear[year].forEach(function (ym) {
                var m = d[ym];
                var rows;
                if (Array.isArray(m)) rows = m;
                else if (m && typeof m === 'object') rows = [Object.assign({ w: defaultWilayah() }, m)]; // legacy shape
                else rows = [];
                grid[ym] = rows.map(function (r) {
                    var w = (r && typeof r.w === 'object' && r.w) ? r.w : defaultWilayah();
                    var row = { w: {
                        jenis: w.jenis === 'ln' ? 'ln' : 'dn',
                        area: w.area === 'luar_kepri' ? 'luar_kepri' : 'kepri',
                        kabkota: typeof w.kabkota === 'string' ? w.kabkota : (w.jenis === 'ln' ? null : 'Kota Batam'),
                        negara: typeof w.negara === 'string' ? w.negara : null
                    } };
                    CATS.forEach(function (cat) {
                        var c = (r && r[cat]) || {};
                        row[cat] = { kwh: normNum(c.kwh), rp: normNum(c.rp) };
                    });
                    return row;
                });
            });
        });
    })();

    // Every month is independent — it just needs at least one row to start.
    Object.keys(grid).forEach(function (ym) {
        if (!grid[ym].length) grid[ym] = [emptyRowFor(defaultWilayah())];
    });

    // Wilayah already used anywhere in the survey — the popover's quick picks.
    function knownWilayah() {
        var seen = {}, list = [];
        Object.keys(grid).forEach(function (ym) {
            grid[ym].forEach(function (row) {
                var k = wKey(row.w);
                if (!seen[k]) {
                    seen[k] = true;
                    list.push({ jenis: row.w.jenis, area: row.w.area, kabkota: row.w.kabkota, negara: row.w.negara });
                }
            });
        });
        return list;
    }

    function fmt(v) { return v === null || isNaN(v) ? '' : nf.format(v); }
    function parseCell(str) {
        var s = String(str || '').replace(/\./g, '').replace(/,/g, '.').trim();
        if (s === '') return null;
        var n = Number(s);
        return isNaN(n) || n < 0 ? null : n;
    }
    function monthLabel(ym) {
        var p = ym.split('_');
        return MONTH_SHORT[parseInt(p[1], 10)] + ' ' + p[0];
    }
    function wilayahValid(w) {
        if (w.jenis === 'ln') return String(w.negara || '').trim() !== '';
        return String(w.kabkota || '').trim() !== '';
    }

    /* ── wilayah label cell: name + badge + hapus (per-month) ── */
    function rowHasData(row) {
        return CATS.some(function (cat) {
            return (row[cat].kwh !== null && row[cat].kwh !== 0) || (row[cat].rp !== null && row[cat].rp !== 0);
        });
    }

    function removeRow(ym, ri) {
        if (grid[ym].length <= 1) {
            showStatus('Minimal satu wilayah tujuan per bulan.', 'error');
            return;
        }
        var row = grid[ym][ri];
        if (rowHasData(row) && !window.confirm('Baris "' + wLabel(row.w) + '" pada ' + monthLabel(ym) + ' sudah memiliki isian. Hapus baris ini?')) {
            return;
        }
        grid[ym].splice(ri, 1);
        renderTables();
        recompute();
        scheduleSave();
    }

    function buildWilayahCell(ym, ri) {
        var row = grid[ym][ri];
        var td = document.createElement('td');
        td.className = 'wilayah';

        var top = document.createElement('div');
        top.className = 'w-cell-top';
        var name = document.createElement('div');
        name.className = 'w-cell-name';
        name.textContent = wLabel(row.w);
        top.appendChild(name);
        if (grid[ym].length > 1) {
            var del = document.createElement('button');
            del.type = 'button';
            del.className = 'w-del';
            del.textContent = '×';
            del.title = 'Hapus wilayah ini dari ' + monthLabel(ym);
            del.addEventListener('click', function () { removeRow(ym, ri); });
            top.appendChild(del);
        }
        td.appendChild(top);

        var badge = document.createElement('span');
        badge.className = 'w-cell-badge' + (row.w.jenis === 'ln' ? ' ln' : '');
        badge.textContent = wBadge(row.w);
        td.appendChild(badge);
        return td;
    }

    function buildCellInput(ym, ri, cat, f) {
        var inp = document.createElement('input');
        inp.type = 'text';
        inp.inputMode = 'numeric';
        inp.autocomplete = 'off';
        inp.className = 'cell';
        inp.dataset.ym = ym; inp.dataset.ri = ri; inp.dataset.cat = cat; inp.dataset.f = f;
        inp.value = fmt(grid[ym][ri][cat][f]);
        inp.setAttribute('aria-label', CFG.categories[cat] + ' ' + (f === 'kwh' ? 'produksi KWH' : 'nilai rupiah') + ' ' + monthLabel(ym) + ' wilayah ' + (ri + 1));
        inp.addEventListener('focus', function () {
            var v = grid[ym][ri][cat][f];
            inp.value = v === null ? '' : String(v).replace('.', ',');
            inp.select();
        });
        inp.addEventListener('input', function () {
            grid[ym][ri][cat][f] = parseCell(inp.value);
            recompute();
            scheduleSave();
        });
        inp.addEventListener('blur', function () {
            inp.value = fmt(grid[ym][ri][cat][f]);
        });
        inp.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); inp.blur(); }
        });
        return inp;
    }

    /* ── build one table per year (rebuilt on structural changes) ── */
    var wrap = document.getElementById('lst-tables');
    function renderTables() {
        wrap.textContent = '';
        Object.keys(CFG.monthsByYear).sort().forEach(function (year) {
            var months = CFG.monthsByYear[year];

            var title = document.createElement('p');
            title.className = 'text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 mt-4 first:mt-0';
            title.textContent = 'Tahun ' + year;
            wrap.appendChild(title);

            var scroll = document.createElement('div');
            scroll.className = 'lst-scroll';
            var table = document.createElement('table');
            table.className = 'lst-grid';

            var thead = document.createElement('thead');
            var tr1 = document.createElement('tr');
            var thP = document.createElement('th');
            thP.rowSpan = 2; thP.textContent = 'Periode';
            tr1.appendChild(thP);
            var thW = document.createElement('th');
            thW.rowSpan = 2; thW.textContent = 'Wilayah Tujuan';
            tr1.appendChild(thW);
            CATS.forEach(function (cat) {
                var th = document.createElement('th');
                th.colSpan = 2; th.textContent = CFG.categories[cat];
                tr1.appendChild(th);
            });
            var thJ = document.createElement('th');
            thJ.colSpan = 2; thJ.textContent = 'JUMLAH'; thJ.className = 'jumlah';
            tr1.appendChild(thJ);
            thead.appendChild(tr1);

            var tr2 = document.createElement('tr');
            CATS.forEach(function () {
                var a = document.createElement('th'); a.textContent = 'Produksi (KWH)';
                var b = document.createElement('th'); b.textContent = 'Nilai (Rp)';
                tr2.appendChild(a); tr2.appendChild(b);
            });
            var ja = document.createElement('th'); ja.textContent = 'Produksi (KWH)'; ja.className = 'jumlah';
            var jb = document.createElement('th'); jb.textContent = 'Nilai (Rp)'; jb.className = 'jumlah';
            tr2.appendChild(ja); tr2.appendChild(jb);
            thead.appendChild(tr2);
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
            months.forEach(function (ym) {
                var rows = grid[ym];
                var nRows = rows.length;
                var hasSubtotal = nRows > 1;

                rows.forEach(function (row, ri) {
                    var tr = document.createElement('tr');
                    if (ri === 0) {
                        var tdM = document.createElement('td');
                        tdM.className = 'month';
                        tdM.rowSpan = nRows + (hasSubtotal ? 1 : 0);
                        var lbl = document.createElement('div');
                        lbl.textContent = monthLabel(ym);
                        tdM.appendChild(lbl);
                        var add = document.createElement('button');
                        add.type = 'button';
                        add.className = 'w-add';
                        add.textContent = '+ Wilayah';
                        add.title = 'Tambah wilayah tujuan untuk ' + monthLabel(ym);
                        add.addEventListener('click', function (e) {
                            e.stopPropagation();
                            openWilayahPop(add, ym);
                        });
                        tdM.appendChild(add);
                        tr.appendChild(tdM);
                    }
                    tr.appendChild(buildWilayahCell(ym, ri));
                    CATS.forEach(function (cat) {
                        ['kwh', 'rp'].forEach(function (f) {
                            var td = document.createElement('td');
                            td.appendChild(buildCellInput(ym, ri, cat, f));
                            tr.appendChild(td);
                        });
                    });
                    var tKwh = document.createElement('td'); tKwh.className = 'rowtotal'; tKwh.dataset.rowtotal = ym + ':' + ri + ':kwh';
                    var tRp  = document.createElement('td'); tRp.className  = 'rowtotal'; tRp.dataset.rowtotal  = ym + ':' + ri + ':rp';
                    tr.appendChild(tKwh); tr.appendChild(tRp);
                    tbody.appendChild(tr);
                });

                if (hasSubtotal) {
                    var trS = document.createElement('tr');
                    trS.className = 'subtotalrow';
                    var tdL = document.createElement('td');
                    tdL.className = 'sublabel';
                    tdL.textContent = 'Subtotal ' + monthLabel(ym) + ' (' + nRows + ' wilayah)';
                    trS.appendChild(tdL);
                    CATS.forEach(function (cat) {
                        ['kwh', 'rp'].forEach(function (f) {
                            var td = document.createElement('td');
                            td.dataset.subtotal = ym + ':' + cat + ':' + f;
                            trS.appendChild(td);
                        });
                    });
                    var sK = document.createElement('td'); sK.dataset.subtotal = ym + ':__all__:kwh';
                    var sR = document.createElement('td'); sR.dataset.subtotal = ym + ':__all__:rp';
                    trS.appendChild(sK); trS.appendChild(sR);
                    tbody.appendChild(trS);
                }
            });

            // TOTAL row for the year
            var trT = document.createElement('tr');
            trT.className = 'totalrow';
            var tdT = document.createElement('td');
            tdT.className = 'month'; tdT.textContent = 'TOTAL ' + year;
            trT.appendChild(tdT);
            var tdW = document.createElement('td');
            tdW.textContent = 'Seluruh wilayah';
            trT.appendChild(tdW);
            CATS.forEach(function (cat) {
                ['kwh', 'rp'].forEach(function (f) {
                    var td = document.createElement('td');
                    td.dataset.coltotal = year + ':' + cat + ':' + f;
                    trT.appendChild(td);
                });
            });
            var tGK = document.createElement('td'); tGK.dataset.coltotal = year + ':__all__:kwh';
            var tGR = document.createElement('td'); tGR.dataset.coltotal = year + ':__all__:rp';
            trT.appendChild(tGK); trT.appendChild(tGR);
            tbody.appendChild(trT);

            table.appendChild(tbody);
            scroll.appendChild(table);
            wrap.appendChild(scroll);
        });
    }

    /* ── totals + progress ── */
    function recompute() {
        Object.keys(CFG.monthsByYear).forEach(function (year) {
            var colSums = {};
            CATS.forEach(function (cat) { colSums[cat] = { kwh: 0, rp: 0 }; });
            var allSum = { kwh: 0, rp: 0 };

            CFG.monthsByYear[year].forEach(function (ym) {
                var monthSums = {};
                CATS.forEach(function (cat) { monthSums[cat] = { kwh: 0, rp: 0 }; });
                var monthAll = { kwh: 0, rp: 0 };

                grid[ym].forEach(function (row, ri) {
                    var rowKwh = 0, rowRp = 0;
                    CATS.forEach(function (cat) {
                        var c = row[cat];
                        rowKwh += c.kwh || 0;  rowRp += c.rp || 0;
                        monthSums[cat].kwh += c.kwh || 0;
                        monthSums[cat].rp  += c.rp  || 0;
                        colSums[cat].kwh += c.kwh || 0;
                        colSums[cat].rp  += c.rp  || 0;
                    });
                    monthAll.kwh += rowKwh; monthAll.rp += rowRp;
                    allSum.kwh += rowKwh;   allSum.rp += rowRp;
                    var ek = document.querySelector('[data-rowtotal="' + ym + ':' + ri + ':kwh"]');
                    var er = document.querySelector('[data-rowtotal="' + ym + ':' + ri + ':rp"]');
                    if (ek) ek.textContent = nf.format(rowKwh);
                    if (er) er.textContent = nf.format(rowRp);
                });

                CATS.forEach(function (cat) {
                    ['kwh', 'rp'].forEach(function (f) {
                        var el = document.querySelector('[data-subtotal="' + ym + ':' + cat + ':' + f + '"]');
                        if (el) el.textContent = nf.format(monthSums[cat][f]);
                    });
                });
                var mk = document.querySelector('[data-subtotal="' + ym + ':__all__:kwh"]');
                var mr = document.querySelector('[data-subtotal="' + ym + ':__all__:rp"]');
                if (mk) mk.textContent = nf.format(monthAll.kwh);
                if (mr) mr.textContent = nf.format(monthAll.rp);
            });

            CATS.forEach(function (cat) {
                ['kwh', 'rp'].forEach(function (f) {
                    var el = document.querySelector('[data-coltotal="' + year + ':' + cat + ':' + f + '"]');
                    if (el) el.textContent = nf.format(colSums[cat][f]);
                });
            });
            var gk = document.querySelector('[data-coltotal="' + year + ':__all__:kwh"]');
            var gr = document.querySelector('[data-coltotal="' + year + ':__all__:rp"]');
            if (gk) gk.textContent = nf.format(allSum.kwh);
            if (gr) gr.textContent = nf.format(allSum.rp);
        });

        var filled = 0, total = 0;
        document.querySelectorAll('input.cell').forEach(function (inp) {
            total++;
            var v = grid[inp.dataset.ym][inp.dataset.ri][inp.dataset.cat][inp.dataset.f];
            var ok = v !== null && !isNaN(v);
            if (ok) filled++;
            inp.classList.toggle('cell-empty', !ok);
        });
        document.getElementById('lst-filled').textContent = nf.format(filled);
        document.getElementById('lst-total').textContent = nf.format(total);
        return { filled: filled, total: total };
    }

    function allWilayahValid() {
        return Object.keys(grid).every(function (ym) {
            return grid[ym].every(function (row) { return wilayahValid(row.w); });
        });
    }

    /* ── "+ Wilayah" popover: quick picks + form wilayah baru ── */
    var wPop = null;
    function closeWPop() {
        if (wPop) {
            if (wPop.parentNode) wPop.parentNode.removeChild(wPop);
            wPop = null;
        }
    }
    // Stays open until the user explicitly closes it (✕ button, Escape, or
    // an action inside the popup like a quick-pick / "Tambahkan") — clicking
    // elsewhere on the page no longer dismisses it, so mid-entry input isn't lost.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeWPop();
    });

    function addWilayahRows(w, ym, applyAll) {
        var targets = applyAll ? Object.keys(grid) : [ym];
        var added = 0;
        targets.forEach(function (m) {
            var exists = grid[m].some(function (row) { return wKey(row.w) === wKey(w); });
            if (!exists) {
                grid[m].push(emptyRowFor(w));
                added++;
            }
        });
        closeWPop();
        if (!added) {
            showStatus('Wilayah "' + wLabel(w) + '" sudah ada pada ' + (applyAll ? 'semua bulan' : monthLabel(ym)) + '.', 'error');
            return;
        }
        renderTables();
        recompute();
        scheduleSave();
        showStatus('Wilayah "' + wLabel(w) + '" ditambahkan ke ' + (applyAll ? added + ' bulan' : monthLabel(ym)) + '.', 'success');
    }

    function openWilayahPop(anchor, ym) {
        closeWPop();
        var pop = document.createElement('div');
        pop.className = 'w-pop';

        var head = document.createElement('div');
        head.className = 'w-pop-head';
        var headTop = document.createElement('div');
        headTop.className = 'w-pop-head-top';
        var titleWrap = document.createElement('div');
        var title = document.createElement('div');
        title.className = 'w-pop-title';
        title.textContent = 'Tambah wilayah — ' + monthLabel(ym);
        titleWrap.appendChild(title);
        var sub = document.createElement('div');
        sub.className = 'w-pop-sub';
        sub.textContent = 'Pilih wilayah yang pernah dipakai, atau buat wilayah baru.';
        titleWrap.appendChild(sub);
        headTop.appendChild(titleWrap);
        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'w-pop-x';
        closeBtn.setAttribute('aria-label', 'Tutup');
        closeBtn.textContent = '✕';
        closeBtn.addEventListener('click', closeWPop);
        headTop.appendChild(closeBtn);
        head.appendChild(headTop);
        pop.appendChild(head);

        // footer checkbox (declared early so both paths can read it)
        var applyAllCb = document.createElement('input');
        applyAllCb.type = 'checkbox';
        applyAllCb.id = 'w-applyall';

        // quick picks: known wilayah not already in this month
        var inMonth = {};
        grid[ym].forEach(function (row) { inMonth[wKey(row.w)] = true; });
        var picks = knownWilayah().filter(function (w) { return !inMonth[wKey(w)]; });

        var list = document.createElement('div');
        list.className = 'w-pop-list';
        if (picks.length) {
            picks.forEach(function (w) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-pick';
                var plus = document.createElement('span');
                plus.className = 'plus';
                plus.textContent = '+';
                btn.appendChild(plus);
                var nm = document.createElement('span');
                nm.className = 'nm';
                nm.textContent = wLabel(w);
                btn.appendChild(nm);
                var bd = document.createElement('span');
                bd.className = 'bd' + (w.jenis === 'ln' ? ' ln' : '');
                bd.textContent = wBadge(w);
                btn.appendChild(bd);
                btn.addEventListener('click', function () { addWilayahRows(w, ym, applyAllCb.checked); });
                list.appendChild(btn);
            });
        } else {
            var none = document.createElement('div');
            none.className = 'w-pop-empty';
            none.textContent = 'Semua wilayah yang pernah dipakai sudah ada di bulan ini.';
            list.appendChild(none);
        }
        pop.appendChild(list);

        var newBtn = document.createElement('button');
        newBtn.type = 'button';
        newBtn.className = 'w-pop-newbtn';
        newBtn.textContent = '+ Wilayah baru…';
        pop.appendChild(newBtn);

        // ── form wilayah baru (hidden until "+ Wilayah baru…") ──
        var form = document.createElement('div');
        form.className = 'w-form';

        function field(labelText, control) {
            var box = document.createElement('div');
            var lb = document.createElement('label');
            lb.textContent = labelText;
            box.appendChild(lb);
            box.appendChild(control);
            return box;
        }
        var selJenis = document.createElement('select');
        [['dn', 'Dalam Negeri'], ['ln', 'Luar Negeri']].forEach(function (o) {
            var op = document.createElement('option');
            op.value = o[0]; op.textContent = o[1];
            selJenis.appendChild(op);
        });
        var selArea = document.createElement('select');
        [['kepri', 'Provinsi Kepri'], ['luar_kepri', 'Luar Kepri']].forEach(function (o) {
            var op = document.createElement('option');
            op.value = o[0]; op.textContent = o[1];
            selArea.appendChild(op);
        });
        var selKab = document.createElement('select');
        CFG.kepri.forEach(function (k) {
            var op = document.createElement('option');
            op.value = k; op.textContent = k;
            selKab.appendChild(op);
        });
        var inpKab = document.createElement('input');
        inpKab.type = 'text'; inpKab.placeholder = 'Nama kab/kota'; inpKab.maxLength = 100;
        var inpNeg = document.createElement('input');
        inpNeg.type = 'text'; inpNeg.placeholder = 'Contoh: Singapura'; inpNeg.maxLength = 100;

        var fJenis = field('Wilayah', selJenis);
        var fArea = field('Area', selArea);
        var fKab = field('Kab/Kota', selKab);
        var fKabMan = field('Kab/Kota (manual)', inpKab);
        var fNeg = field('Nama negara', inpNeg);
        form.appendChild(fJenis);
        form.appendChild(fArea);
        form.appendChild(fKab);
        form.appendChild(fKabMan);
        form.appendChild(fNeg);

        function refreshFormVis() {
            var ln = selJenis.value === 'ln';
            var luarKepri = selArea.value === 'luar_kepri';
            fArea.style.display = ln ? 'none' : '';
            fKab.style.display = (!ln && !luarKepri) ? '' : 'none';
            fKabMan.style.display = (!ln && luarKepri) ? '' : 'none';
            fNeg.style.display = ln ? '' : 'none';
        }
        selJenis.addEventListener('change', refreshFormVis);
        selArea.addEventListener('change', refreshFormVis);
        refreshFormVis();

        var actions = document.createElement('div');
        actions.style.display = 'flex';
        actions.style.gap = '0.5rem';
        var saveBtn = document.createElement('button');
        saveBtn.type = 'button';
        saveBtn.className = 'w-form-btn primary';
        saveBtn.textContent = 'Tambahkan';
        var cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'w-form-btn ghost';
        cancelBtn.textContent = 'Batal';
        actions.appendChild(saveBtn);
        actions.appendChild(cancelBtn);
        form.appendChild(actions);
        pop.appendChild(form);

        newBtn.addEventListener('click', function () {
            form.classList.add('open');
            newBtn.style.display = 'none';
        });
        cancelBtn.addEventListener('click', function () {
            form.classList.remove('open');
            newBtn.style.display = '';
        });
        saveBtn.addEventListener('click', function () {
            var w;
            if (selJenis.value === 'ln') {
                var neg = inpNeg.value.trim();
                if (!neg) { inpNeg.classList.add('w-invalid'); inpNeg.focus(); return; }
                w = { jenis: 'ln', area: null, kabkota: null, negara: neg };
            } else if (selArea.value === 'luar_kepri') {
                var kab = inpKab.value.trim();
                if (!kab) { inpKab.classList.add('w-invalid'); inpKab.focus(); return; }
                w = { jenis: 'dn', area: 'luar_kepri', kabkota: kab, negara: null };
            } else {
                w = { jenis: 'dn', area: 'kepri', kabkota: selKab.value, negara: null };
            }
            addWilayahRows(w, ym, applyAllCb.checked);
        });
        inpKab.addEventListener('input', function () { inpKab.classList.remove('w-invalid'); });
        inpNeg.addEventListener('input', function () { inpNeg.classList.remove('w-invalid'); });

        // footer: terapkan ke semua bulan
        var foot = document.createElement('div');
        foot.className = 'w-pop-foot';
        var cbLabel = document.createElement('label');
        cbLabel.appendChild(applyAllCb);
        cbLabel.appendChild(document.createTextNode('Terapkan ke semua bulan'));
        foot.appendChild(cbLabel);
        pop.appendChild(foot);

        document.body.appendChild(pop);
        var r = anchor.getBoundingClientRect();
        var pw = pop.getBoundingClientRect().width;
        var ph = pop.getBoundingClientRect().height;
        var left = Math.max(8, Math.min(r.left, window.innerWidth - pw - 8));
        var top = r.bottom + 8;
        if (top + ph > window.innerHeight - 8) top = Math.max(8, r.top - ph - 8);
        pop.style.left = Math.round(left) + 'px';
        pop.style.top = Math.round(top) + 'px';
        wPop = pop;
    }

    /* ── autosave (whole grid, debounced) ── */
    var statusBox = document.getElementById('autosave-status');
    var statusText = document.getElementById('autosave-text');
    function showStatus(msg, kind) {
        statusBox.classList.remove('hidden');
        statusBox.className = 'autosave-status ' + (kind || '');
        statusText.textContent = msg;
    }
    var saveT = null;
    function scheduleSave() {
        clearTimeout(saveT);
        saveT = setTimeout(doSave, 1500);
    }
    function doSave() {
        showStatus('Menyimpan...', 'saving');
        fetch(CFG.autoSaveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CFG.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ field: 'data_listrik', value: grid })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.success) {
                showStatus('Tersimpan otomatis · ' + (data.last_saved_at || ''), 'success');
                document.dispatchEvent(new CustomEvent('ub:autosave', {
                    detail: { blok_completed: data.blok_completed, field: 'data_listrik', value: null }
                }));
            } else {
                showStatus('Gagal menyimpan: ' + (data.message || ''), 'error');
            }
        }).catch(function (e) {
            showStatus('Gagal menyimpan: ' + e.message, 'error');
        });
    }

    /* ── fill zeros helper ── */
    document.getElementById('lst-fill-zero').addEventListener('click', function () {
        Object.keys(grid).forEach(function (ym) {
            grid[ym].forEach(function (row) {
                CATS.forEach(function (cat) {
                    ['kwh', 'rp'].forEach(function (f) {
                        if (row[cat][f] === null) row[cat][f] = 0;
                    });
                });
            });
        });
        document.querySelectorAll('input.cell').forEach(function (inp) {
            inp.value = fmt(grid[inp.dataset.ym][inp.dataset.ri][inp.dataset.cat][inp.dataset.f]);
        });
        recompute();
        doSave();
    });

    /* ── submit: block until complete ── */
    document.getElementById('lst-form').addEventListener('submit', function (e) {
        var state = recompute();
        document.getElementById('lst-json').value = JSON.stringify(grid);
        if (!allWilayahValid()) {
            e.preventDefault();
            showStatus('Ada wilayah tujuan yang belum lengkap. Periksa daftar wilayah di bagian atas.', 'error');
            return;
        }
        if (state.filled < state.total) {
            e.preventDefault();
            var firstEmpty = document.querySelector('input.cell.cell-empty');
            showStatus('Masih ada ' + nf.format(state.total - state.filled) + ' sel kosong. Isi 0 jika tidak ada nilai, atau gunakan tombol "Isi 0 pada semua sel kosong".', 'error');
            if (firstEmpty) {
                firstEmpty.classList.add('cell-flash');
                firstEmpty.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstEmpty.focus({ preventScroll: true });
                setTimeout(function () { firstEmpty.classList.remove('cell-flash'); }, 2500);
            }
        }
    });

    renderTables();
    recompute();
})();
</script>
@endpush
@endsection
