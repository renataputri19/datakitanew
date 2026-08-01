/**
 * Statistik UB — BPS-only dashboard over Survei Usaha/Perusahaan (SE2026-L.UB).
 *
 * Same architecture as bps-statistik.js (SIBSTR) and bps-statistik-listrik.js:
 * payload embedded by the Blade view, all filtering client-side, hand-rolled
 * theme-aware SVG charts. All dynamic strings enter the DOM via textContent —
 * respondents control company and product names.
 *
 * UB is an annual snapshot, so every figure is a per-usaha total for the
 * selected tahun; there is no monthly series to trend.
 */
(function () {
    'use strict';

    var DATA = window.__UB_STAT__;
    if (!DATA || !document.getElementById('stx-kpis')) return;

    /* ═══════════════ constants ═══════════════ */

    // Metrics the kategori chart and several modals can switch between.
    var METRICS = [
        { key: 'produksi',    label: 'Nilai produksi', money: true },
        { key: 'pengeluaran', label: 'Pengeluaran',    money: true },
        { key: 'aset',        label: 'Aset',           money: true },
        { key: 'pekerja',     label: 'Tenaga kerja',   money: false },
        { key: 'usaha',       label: 'Jumlah usaha',   money: false }
    ];

    // Skala usaha menurut jumlah tenaga kerja (batas baku BPS).
    var SKALA = [
        { label: 'Tanpa pekerja', sub: '0 orang',     min: 0,   max: 0 },
        { label: 'Mikro',         sub: '1–4 orang',   min: 1,   max: 4 },
        { label: 'Kecil',         sub: '5–19 orang',  min: 5,   max: 19 },
        { label: 'Sedang',        sub: '20–99 orang', min: 20,  max: 99 },
        { label: 'Besar',         sub: '≥ 100 orang', min: 100, max: Infinity }
    ];

    var PENGELUARAN_PARTS = [
        { key: 'upah',           label: 'Upah & gaji' },
        { key: 'biayaProduksi',  label: 'Biaya produksi' },
        { key: 'pembelian',      label: 'Pembelian barang dagangan' },
        { key: 'operasional',    label: 'Pengeluaran operasional' },
        { key: 'nonOperasional', label: 'Pengeluaran non-operasional' }
    ];

    // Blok I-B/I-C yes-no indicators. "n" counts only usaha that answered, so a
    // blank questionnaire never drags a percentage down.
    var PROFIL = [
        { key: 'nib',             label: 'Memiliki NIB',                    group: 'Legalitas' },
        { key: 'laporanKeuangan', label: 'Menyusun laporan keuangan',       group: 'Legalitas' },
        { key: 'internet',        label: 'Menggunakan internet untuk usaha', group: 'Digital' },
        { key: 'digital',         label: 'Menggunakan teknologi digital',   group: 'Digital' },
        { key: 'halal',           label: 'Sertifikat halal dari BPJPH',     group: 'Sertifikasi' },
        { key: 'izinEdar',        label: 'Izin edar dari BPOM',             group: 'Sertifikasi' },
        { key: 'eksporBarang',    label: 'Ekspor/impor barang',             group: 'Perdagangan luar negeri' },
        { key: 'eksporJasa',      label: 'Ekspor/impor jasa',               group: 'Perdagangan luar negeri' },
        { key: 'ramahLingkungan', label: 'Menghasilkan produk ramah lingkungan', group: 'Lingkungan & seni' },
        { key: 'inputLingkungan', label: 'Memakai input ramah lingkungan',  group: 'Lingkungan & seni' },
        { key: 'karyaSeni',       label: 'Memanfaatkan karya seni/budaya',  group: 'Lingkungan & seni' },
        { key: 'mitraKdkmp',      label: 'Bermitra dengan KDKMP',           group: 'Kemitraan' },
        { key: 'mbg',             label: 'Terlibat program MBG',            group: 'Kemitraan' }
    ];

    var MODAL_PARTS = [
        { key: 'pribadi',            label: 'Pribadi/rumah tangga' },
        { key: 'nonprofit',          label: 'Lembaga nirlaba' },
        { key: 'korporasiPublik',    label: 'Korporasi publik' },
        { key: 'korporasiNonPublik', label: 'Korporasi non-publik' },
        { key: 'pemerintah',         label: 'Pemerintah' },
        { key: 'asing',              label: 'Asing' }
    ];

    /* ═══════════════ tiny DOM helpers ═══════════════ */

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined && text !== null) n.textContent = text;
        return n;
    }
    function svgEl(tag, attrs) {
        var n = document.createElementNS('http://www.w3.org/2000/svg', tag);
        if (attrs) for (var k in attrs) n.setAttribute(k, attrs[k]);
        return n;
    }
    function clear(node) { while (node.firstChild) node.removeChild(node.firstChild); }

    var rootEl = document.querySelector('.stx-root');
    function cssVar(name) { return getComputedStyle(rootEl).getPropertyValue(name).trim(); }
    function theme() {
        return {
            ink: cssVar('--stx-ink'), ink2: cssVar('--stx-ink-2'), muted: cssVar('--stx-muted'),
            grid: cssVar('--stx-grid'), axis: cssVar('--stx-axis'), surface: cssVar('--stx-surface'),
            s1: cssVar('--stx-s1'), s2: cssVar('--stx-s2'), s3: cssVar('--stx-s3'), s4: cssVar('--stx-s4'),
            neg: cssVar('--stx-neg'), wash: cssVar('--stx-wash')
        };
    }

    /* ═══════════════ formatting (id-ID) ═══════════════ */

    var nfFull = new Intl.NumberFormat('id-ID');
    var nfCompact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 });
    var nfPct = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });
    function fmtRp(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfCompact.format(v); }
    function fmtRpFull(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfFull.format(v); }
    function fmtN(v) { return v === null || v === undefined ? '—' : nfFull.format(v); }
    function fmtNc(v) { return v === null || v === undefined ? '—' : nfCompact.format(v); }
    function fmtPct(v) { return v === null || v === undefined ? '—' : nfPct.format(v) + '%'; }
    function fmtM2(v) { return v === null || v === undefined ? '—' : nfFull.format(v) + ' m²'; }
    function metricDef(key) {
        for (var i = 0; i < METRICS.length; i++) if (METRICS[i].key === key) return METRICS[i];
        return METRICS[0];
    }
    function fmtMetric(key) { return metricDef(key).money ? fmtRp : fmtN; }
    function fmtMetricFull(key) { return metricDef(key).money ? fmtRpFull : fmtN; }
    function share(part, total) { return total ? nfPct.format(part / total * 100) + '%' : '—'; }

    /* ═══════════════ state + filtering ═══════════════ */

    // katSel / badanSel: checkbox multi-select (empty = semua). Status defaults
    // to Selesai so the dashboard opens on final data only.
    var state = {
        tahun: DATA.years[0],
        katSel: {}, badanSel: {}, status: 'done', excluded: {},
        metricKategori: 'produksi',
        sortKey: 'produksi', sortDir: -1, tableLimit: 10
    };
    function katSelCount() { return Object.keys(state.katSel).length; }
    function badanSelCount() { return Object.keys(state.badanSel).length; }

    var EXCL_KEY = 'stx-ub-excl';
    try {
        (JSON.parse(sessionStorage.getItem(EXCL_KEY) || '[]') || []).forEach(function (uid) { state.excluded[uid] = true; });
    } catch (e) { /* corrupt storage — start clean */ }
    function saveExcluded() {
        try { sessionStorage.setItem(EXCL_KEY, JSON.stringify(Object.keys(state.excluded))); } catch (e) {}
    }
    function matchesStatus(r) {
        if (state.status === 'done' && !r.selesai) return false;
        if (state.status === 'draft' && r.selesai) return false;
        return true;
    }

    /**
     * Faceted filtering. `skip` names the single facet to ignore, so each
     * control can count its own options against every OTHER active filter
     * without narrowing itself — the rule that stops cross-filtering from
     * trapping the user in a choice they can no longer widen.
     * skip: 'tahun' | 'kat' | 'badan' | 'status' | 'company' | null
     */
    function rowsFor(skip) {
        return DATA.rows.filter(function (r) {
            if (skip !== 'company' && state.excluded[r.uid]) return false;
            if (skip !== 'tahun' && r.tahun !== state.tahun) return false;
            if (skip !== 'kat' && katSelCount() && !state.katSel[r.kat]) return false;
            if (skip !== 'badan' && badanSelCount() && !state.badanSel[r.badanUsaha]) return false;
            if (skip !== 'status' && !matchesStatus(r)) return false;
            return true;
        });
    }
    function filteredRows() { return rowsFor(null); }

    /**
     * Usaha the other filters still admit — the pool the Perusahaan picker
     * lists. One already dropped by Tahun/Kategori/Status must not sit there
     * checked, claiming to be part of the aggregation.
     */
    function eligibleCompanies() { return rowsFor('company'); }
    function excludedEligible() {
        return eligibleCompanies().filter(function (c) { return state.excluded[c.uid]; }).length;
    }

    /* ═══════════════ aggregation ═══════════════ */

    function rowMetric(r, key) {
        if (key === 'usaha') return 1;
        var v = r[key];
        return v === null || v === undefined ? null : v;
    }
    function sumField(rows, key) {
        var any = false, total = 0;
        rows.forEach(function (r) {
            var v = rowMetric(r, key);
            if (v !== null) { any = true; total += v; }
        });
        return any ? total : null;
    }
    function countField(rows, key) {
        return rows.filter(function (r) { return rowMetric(r, key) !== null; }).length;
    }
    function katLabelOf(kat) {
        if (kat === '—' || !kat) return 'Belum dikategorikan';
        return kat + ' · ' + (DATA.kategori[kat] || ('Kategori ' + kat));
    }
    function katShort(kat) {
        if (kat === '—' || !kat) return 'Belum dikategorikan';
        return DATA.kategori[kat] || ('Kategori ' + kat);
    }
    /** Distinct kategori present in a row set, ordered A → Z with "—" last. */
    function katsOf(rows) {
        var seen = {};
        rows.forEach(function (r) { seen[r.kat] = true; });
        return Object.keys(seen).sort(function (a, b) {
            if (a === '—') return 1;
            if (b === '—') return -1;
            return a.localeCompare(b, 'id');
        });
    }
    function badanOf(rows) {
        var seen = {};
        rows.forEach(function (r) { seen[r.badanUsaha] = true; });
        return Object.keys(seen).sort(function (a, b) { return a.localeCompare(b, 'id'); });
    }
    function skalaIndex(r) {
        if (r.pekerja === null || r.pekerja === undefined) return -1;
        for (var i = 0; i < SKALA.length; i++) {
            if (r.pekerja >= SKALA[i].min && r.pekerja <= SKALA[i].max) return i;
        }
        return -1;
    }

    /* ═══════════════ tooltip ═══════════════ */

    var tip = document.getElementById('stx-tip');
    function tipShow(x, y, title, rows) {
        clear(tip);
        if (title) tip.appendChild(el('div', 't-title', title));
        (rows || []).forEach(function (r) {
            var line = el('div', 't-row');
            if (r.color) {
                var key = el('span', 't-key');
                key.style.borderTopColor = r.color;
                line.appendChild(key);
            }
            line.appendChild(el('span', null, r.label));
            line.appendChild(el('span', 't-val', r.value));
            tip.appendChild(line);
        });
        tip.style.display = 'block';
        var rect = tip.getBoundingClientRect();
        var px = Math.min(x + 14, window.innerWidth - rect.width - 10);
        var py = Math.min(y + 14, window.innerHeight - rect.height - 10);
        if (py < y && y - rect.height - 10 > 0) py = y - rect.height - 10;
        tip.style.left = Math.max(8, px) + 'px';
        tip.style.top = Math.max(8, py) + 'px';
    }
    function tipHide() { tip.style.display = 'none'; }

    /* ═══════════════ modal ═══════════════ */

    var modalOv = document.getElementById('stx-modal-ov');
    var modalTitle = document.getElementById('stx-modal-title');
    var modalSub = document.getElementById('stx-modal-sub');
    var modalBody = document.getElementById('stx-modal-body');
    var modalX = document.getElementById('stx-modal-x');
    var lastFocus = null;

    function openModal(title, pills, buildBody) {
        lastFocus = document.activeElement;
        modalTitle.textContent = title;
        clear(modalSub);
        (pills || []).forEach(function (p) { modalSub.appendChild(el('span', 'stx-pill', p)); });
        clear(modalBody);
        buildBody(modalBody);
        modalOv.classList.add('open');
        document.body.style.overflow = 'hidden';
        modalX.focus();
    }
    function closeModal() {
        modalOv.classList.remove('open');
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    modalX.addEventListener('click', closeModal);
    modalOv.addEventListener('click', function (e) { if (e.target === modalOv) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modalOv.classList.contains('open')) closeModal(); });

    function sect(parent, title) {
        var s = el('div', 'stx-sect');
        if (title) s.appendChild(el('div', 'stx-sect-title', title));
        parent.appendChild(s);
        return s;
    }
    function kvGrid(parent, pairs) {
        var g = el('div', 'stx-kv');
        pairs.forEach(function (p) {
            var box = el('div');
            box.appendChild(el('div', 'kv-label', p[0]));
            box.appendChild(el('div', 'kv-val', p[1] === null || p[1] === undefined || p[1] === '' ? '—' : p[1]));
            g.appendChild(box);
        });
        parent.appendChild(g);
        return g;
    }
    function moneyRows(parent, rows) {
        var box = el('div', 'stx-money-rows');
        rows.forEach(function (r) {
            var line = el('div', 'm-row' + (r.total ? ' total' : ''));
            line.appendChild(el('span', null, r.label));
            line.appendChild(el('span', 'v', r.value));
            box.appendChild(line);
        });
        parent.appendChild(box);
        return box;
    }

    /* ═══════════════ popover ═══════════════ */

    var popLayer = document.querySelectorAll('.stx-root')[1] || rootEl;
    var activePop = null;
    function closePop() {
        if (activePop) {
            if (activePop.panel.parentNode) activePop.panel.parentNode.removeChild(activePop.panel);
            activePop.btn.setAttribute('aria-expanded', 'false');
            activePop = null;
        }
    }
    /**
     * Anchor the panel to its button. The sort control sits in the last card,
     * so a panel that always dropped downward would run off the bottom edge
     * with no way back — it is position:fixed, and page scroll closes it.
     * Flip above the button when that side has more room, and clamp the option
     * list so the panel always fits on screen.
     */
    function togglePop(wrapper, btn, build) {
        if (activePop && activePop.btn === btn) { closePop(); return; }
        closePop();
        var panel = el('div', 'stx-pop');
        panel.setAttribute('role', 'dialog');
        build(panel);
        popLayer.appendChild(panel);
        panel.style.position = 'fixed';

        var r = btn.getBoundingClientRect();
        var GAP = 8, EDGE = 10;
        var below = window.innerHeight - r.bottom - GAP - EDGE;
        var above = r.top - GAP - EDGE;
        var rect = panel.getBoundingClientRect();
        var up = rect.height > below && above > below;
        var room = Math.max(160, up ? above : below);

        var list = panel.querySelector('.stx-pop-list');
        if (list && rect.height > room) {
            // shrink the scrollable list only — the head and footer stay visible
            var chrome = rect.height - list.getBoundingClientRect().height;
            list.style.maxHeight = Math.max(96, room - chrome) + 'px';
            rect = panel.getBoundingClientRect();
        }

        panel.style.top = Math.round(up ? Math.max(EDGE, r.top - GAP - rect.height) : r.bottom + GAP) + 'px';
        panel.style.left = Math.round(Math.max(8, Math.min(r.left, window.innerWidth - rect.width - 8))) + 'px';
        btn.setAttribute('aria-expanded', 'true');
        activePop = { panel: panel, btn: btn };
    }
    document.addEventListener('pointerdown', function (e) {
        if (activePop && !activePop.panel.contains(e.target) && !activePop.btn.contains(e.target)) closePop();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && activePop) { var b = activePop.btn; closePop(); if (b && b.focus) b.focus(); }
    });
    document.addEventListener('scroll', function (e) {
        if (activePop && !(e.target && e.target.closest && e.target.closest('.stx-pop'))) closePop();
    }, true);

    /* ═══════════════ styled dropdowns (single + checkbox multi) ═══════════════ */

    /**
     * Cross-filtering: every control's option list is counted against the OTHER
     * active filters, so those counts go stale the moment a sibling changes.
     * Options are therefore passed as a function and evaluated when the panel
     * opens; the collapsed button faces refresh through these hooks.
     */
    var facetRefreshers = [];
    function refreshFilterBar() { facetRefreshers.forEach(function (f) { f(); }); }

    function ddSingle(bar, labelText, options, current, onPick) {
        var optsOf = typeof options === 'function' ? options : function () { return options; };
        bar.appendChild(el('span', 'stx-filter-label', labelText));
        var wrapEl = el('div', 'stx-popwrap');
        var btn = el('button', 'stx-popbtn');
        btn.type = 'button';
        var lbl = el('span', null, '');
        function refreshBtn() {
            var cur = optsOf().filter(function (o) { return o.v === current; })[0];
            lbl.textContent = cur ? cur.t : '—';
        }
        refreshBtn();
        facetRefreshers.push(refreshBtn);
        btn.appendChild(lbl);
        btn.appendChild(el('span', 'caret', '▼'));
        btn.addEventListener('click', function () {
            togglePop(wrapEl, btn, function (panel) {
                panel.style.width = 'min(18rem, 90vw)';
                var list = el('div', 'stx-pop-list');
                optsOf().forEach(function (o) {
                    var row = el('button', 'stx-pop-row' + (o.v === current ? ' on' : ''));
                    row.type = 'button';
                    if (o.disabled) row.disabled = true;
                    row.appendChild(el('span', 'p-check', o.v === current ? '✓' : ''));
                    var main = el('div', 'p-main');
                    main.appendChild(el('div', 'p-name', o.t));
                    if (o.sub) main.appendChild(el('div', 'p-meta', o.sub));
                    row.appendChild(main);
                    if (!o.disabled) row.addEventListener('click', function () { closePop(); onPick(o.v); });
                    list.appendChild(row);
                });
                panel.appendChild(list);
            });
        });
        wrapEl.appendChild(btn);
        bar.appendChild(wrapEl);
    }

    function ddMulti(bar, labelText, options, selSet, allLabel, onChange) {
        var optsOf = typeof options === 'function' ? options : function () { return options; };
        bar.appendChild(el('span', 'stx-filter-label', labelText));
        var wrapEl = el('div', 'stx-popwrap');
        var btn = el('button', 'stx-popbtn');
        btn.type = 'button';
        var lbl = el('span', null, '');
        var badge = el('span', 'n', '');
        function refreshBtn() {
            var sel = Object.keys(selSet);
            if (!sel.length) lbl.textContent = allLabel;
            else if (sel.length === 1) {
                var o = optsOf().filter(function (x) { return x.v === sel[0]; })[0];
                lbl.textContent = o ? o.t : sel[0];
            } else lbl.textContent = sel.length + ' dipilih';
            badge.textContent = String(optsOf().length);
            btn.classList.toggle('active', sel.length > 0);
        }
        refreshBtn();
        facetRefreshers.push(refreshBtn);
        btn.appendChild(lbl);
        btn.appendChild(badge);
        btn.appendChild(el('span', 'caret', '▼'));
        btn.addEventListener('click', function () {
            togglePop(wrapEl, btn, function (panel) {
                var head = el('div', 'stx-pop-head');
                head.appendChild(el('div', 'stx-pop-title', labelText));
                head.appendChild(el('div', 'stx-pop-sub', 'Centang satu atau beberapa pilihan — kosongkan semua untuk menampilkan seluruhnya.'));
                panel.appendChild(head);
                var list = el('div', 'stx-pop-list');
                var foot = el('div', 'stx-pop-foot');
                var cnt = el('span', 'cnt', '');
                function refreshCnt() {
                    var n = Object.keys(selSet).length;
                    cnt.textContent = n ? n + ' dipilih' : 'Semua ditampilkan';
                }
                optsOf().forEach(function (o) {
                    var on = !!selSet[o.v];
                    var row = el('button', 'stx-pop-row' + (on ? ' on' : ''));
                    row.type = 'button';
                    row.setAttribute('role', 'checkbox');
                    row.setAttribute('aria-checked', on ? 'true' : 'false');
                    // a checked option is never disabled — the user must always
                    // be able to undo a choice that emptied its own slice
                    if (o.disabled && !on) row.disabled = true;
                    var check = el('span', 'p-check', on ? '✓' : '');
                    row.appendChild(check);
                    var main = el('div', 'p-main');
                    main.appendChild(el('div', 'p-name', o.t));
                    if (o.sub) main.appendChild(el('div', 'p-meta', o.sub));
                    row.appendChild(main);
                    if (o.n !== undefined) row.appendChild(el('span', 'p-val', String(o.n)));
                    row.addEventListener('click', function () {
                        if (row.disabled) return;
                        if (selSet[o.v]) delete selSet[o.v]; else selSet[o.v] = true;
                        var isOn = !!selSet[o.v];
                        row.classList.toggle('on', isOn);
                        row.setAttribute('aria-checked', isOn ? 'true' : 'false');
                        check.textContent = isOn ? '✓' : '';
                        refreshBtn();
                        refreshCnt();
                        onChange();
                    });
                    list.appendChild(row);
                });
                panel.appendChild(list);
                refreshCnt();
                var clearB = el('button', 'stx-pop-link', 'Tampilkan semua');
                clearB.type = 'button';
                clearB.addEventListener('click', function () {
                    Object.keys(selSet).forEach(function (k) { delete selSet[k]; });
                    list.querySelectorAll('.stx-pop-row').forEach(function (r) {
                        r.classList.remove('on');
                        r.setAttribute('aria-checked', 'false');
                        var c = r.querySelector('.p-check');
                        if (c) c.textContent = '';
                    });
                    refreshBtn();
                    refreshCnt();
                    onChange();
                });
                foot.appendChild(cnt);
                foot.appendChild(clearB);
                panel.appendChild(foot);
            });
        });
        wrapEl.appendChild(btn);
        bar.appendChild(wrapEl);
    }

    /* ═══════════════ shared card scaffolding ═══════════════ */

    function cardShell(cardId, title, sub, opts) {
        var card = document.getElementById(cardId);
        clear(card);
        var head = el('div', 'stx-chart-head');
        var titles = el('div');
        titles.appendChild(el('div', 'stx-chart-title', title));
        if (sub) titles.appendChild(el('div', 'stx-chart-sub', sub));
        head.appendChild(titles);

        var actions = el('div');
        actions.style.display = 'flex';
        actions.style.gap = '0.4rem';
        actions.style.flexShrink = '0';

        // optional metric toggle, e.g. [Nilai produksi | Pengeluaran | …]
        if (opts && opts.metricKey) {
            var mToggle = el('div', 'stx-toggle');
            (opts.metrics || METRICS).forEach(function (m) {
                var b = el('button', state[opts.metricKey] === m.key ? 'on' : null, m.label);
                b.type = 'button';
                b.addEventListener('click', function () {
                    if (state[opts.metricKey] !== m.key) { state[opts.metricKey] = m.key; rerenderData(); }
                });
                mToggle.appendChild(b);
            });
            actions.appendChild(mToggle);
        }

        var toggle = el('div', 'stx-toggle');
        var btnChart = el('button', 'on', 'Grafik');
        var btnTable = el('button', null, 'Tabel');
        btnChart.type = 'button'; btnTable.type = 'button';
        toggle.appendChild(btnChart); toggle.appendChild(btnTable);
        actions.appendChild(toggle);
        head.appendChild(actions);
        card.appendChild(head);

        var legend = el('div', 'stx-legend');
        card.appendChild(legend);

        var body = el('div', 'stx-chart-body');
        card.appendChild(body);

        var chartPane = el('div');
        var tablePane = el('div'); // simpleTable() supplies its own .stx-tablewrap
        tablePane.style.display = 'none';
        body.appendChild(chartPane);
        body.appendChild(tablePane);

        btnChart.addEventListener('click', function () {
            btnChart.classList.add('on'); btnTable.classList.remove('on');
            chartPane.style.display = ''; tablePane.style.display = 'none';
        });
        btnTable.addEventListener('click', function () {
            btnTable.classList.add('on'); btnChart.classList.remove('on');
            tablePane.style.display = ''; chartPane.style.display = 'none';
        });

        return { card: card, legend: legend, chartPane: chartPane, tablePane: tablePane };
    }

    function legendItem(container, kind, color, label) {
        var item = el('span', 'item');
        var sw = el('span', kind === 'line' ? 'linekey' : 'swatch');
        if (kind === 'line') sw.style.borderTopColor = color; else sw.style.background = color;
        item.appendChild(sw);
        item.appendChild(el('span', null, label));
        container.appendChild(item);
    }

    function emptyState(pane, msg) {
        var box = el('div', 'stx-empty');
        box.appendChild(el('div', 'big', '◔'));
        box.appendChild(el('div', null, msg));
        pane.appendChild(box);
    }

    // Always wrapped in .stx-tablewrap: cells are nowrap, so an unwrapped table
    // overflows its container (visibly spilling outside the modal card).
    //
    // Only a table this helper added before is replaced — modal sections keep
    // their .stx-sect-title in the same node, and clearing the whole pane would
    // silently swallow it.
    function simpleTable(pane, headers, rows, numericFrom) {
        var old = pane.querySelector(':scope > .stx-tablewrap');
        if (old) pane.removeChild(old);
        var wrap = el('div', 'stx-tablewrap');
        var t = el('table', 'stx-table');
        var thead = el('thead'); var trh = el('tr');
        headers.forEach(function (h, i) {
            var th = el('th', i >= numericFrom ? 'num' : null, h);
            th.style.cursor = 'default';
            trh.appendChild(th);
        });
        thead.appendChild(trh); t.appendChild(thead);
        var tb = el('tbody');
        rows.forEach(function (cells) {
            var tr = el('tr');
            tr.style.cursor = 'default';
            cells.forEach(function (c, i) { tr.appendChild(el('td', i >= numericFrom ? 'num' : (i === 0 ? 'strong' : null), c)); });
            tb.appendChild(tr);
        });
        t.appendChild(tb);
        wrap.appendChild(t);
        pane.appendChild(wrap);
    }

    /* ═══════════════ scale helpers ═══════════════ */

    function roundRightBarPath(x, y, w, h, r) {
        if (w <= 0) return '';
        r = Math.min(r, h / 2, w);
        return 'M' + x + ',' + y +
            ' L' + (x + w - r) + ',' + y +
            ' Q' + (x + w) + ',' + y + ' ' + (x + w) + ',' + (y + r) +
            ' L' + (x + w) + ',' + (y + h - r) +
            ' Q' + (x + w) + ',' + (y + h) + ' ' + (x + w - r) + ',' + (y + h) +
            ' L' + x + ',' + (y + h) + ' Z';
    }

    /**
     * One horizontal ranked bar row. Every card here is a "kategori vs value"
     * ranking, so they all share this drawing routine.
     */
    function barRows(pane, list, opts) {
        var t = theme();
        var W = Math.max(300, pane.clientWidth || opts.fallbackW || 460);
        var rowH = opts.rowH || 38, padT = 4;
        var labelW = Math.min(opts.labelMax || 170, Math.max(110, W * 0.3));
        var valueW = opts.valueW || 90;
        var plotW = Math.max(40, W - labelW - valueW - 12);
        var H = padT + list.length * rowH + 4;
        // opts.maxV pins the scale (percentages read against a full 100, not
        // against whichever indicator happens to be highest).
        var maxV = opts.maxV || Math.max.apply(null, list.map(function (g) { return Math.abs(g.v || 0); }).concat([1]));

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', opts.aria || 'Grafik batang');

        list.forEach(function (g, i) {
            var cy = padT + i * rowH + rowH / 2;
            var dim = !!g.dim;
            var nameY = g.sub ? cy + 1 : cy + 4;
            var name = svgEl('text', { x: 0, y: nameY, 'font-size': 11.5, 'font-weight': g.strong ? 800 : 600, fill: dim ? t.muted : t.ink2 });
            var maxChars = Math.floor(labelW / 6.4);
            name.textContent = g.label.length > maxChars ? g.label.slice(0, maxChars - 1) + '…' : g.label;
            var titleEl = svgEl('title');
            titleEl.textContent = g.label;
            name.appendChild(titleEl);
            svg.appendChild(name);
            if (g.sub) {
                var sub = svgEl('text', { x: 0, y: cy + 13, 'font-size': 9.5, fill: t.muted });
                sub.textContent = g.sub;
                svg.appendChild(sub);
            }

            var barH = 16;
            svg.appendChild(svgEl('rect', { x: labelW, y: cy - barH / 2, width: plotW, height: barH, rx: 4, fill: t.grid, opacity: 0.45 }));
            var bw = Math.abs(g.v || 0) / maxV * plotW;
            if (bw > 0) {
                svg.appendChild(svgEl('path', {
                    d: roundRightBarPath(labelW, cy - barH / 2, Math.max(bw, 2), barH, 4),
                    fill: (g.v < 0 ? t.neg : (g.color || t.s1)), opacity: dim ? 0.3 : 1
                }));
            }
            var val = svgEl('text', { x: labelW + plotW + 8, y: cy + 4, 'font-size': 11, 'font-weight': 700, fill: dim ? t.muted : t.ink });
            val.textContent = g.valueLabel;
            svg.appendChild(val);

            var hit = svgEl('rect', { x: 0, y: padT + i * rowH, width: W, height: rowH, fill: 'transparent' });
            if (g.onClick) {
                hit.style.cursor = 'pointer';
                hit.setAttribute('tabindex', 0);
                hit.setAttribute('role', 'button');
                hit.setAttribute('aria-label', g.label);
                hit.addEventListener('click', function () { tipHide(); g.onClick(); });
                hit.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); tipHide(); g.onClick(); }
                });
            }
            hit.addEventListener('pointermove', function (e) { tipShow(e.clientX, e.clientY, g.label, g.tip || []); });
            hit.addEventListener('pointerleave', tipHide);
            svg.appendChild(hit);
        });

        pane.appendChild(svg);
    }

    /* ═══════════════ FILTER BAR ═══════════════ */

    function renderFilters() {
        var bar = document.getElementById('stx-filters');
        closePop();
        clear(bar);
        facetRefreshers = [];

        // Tahun — UB is an annual snapshot, so one tahun at a time keeps a
        // usaha from being counted twice across reporting years.
        ddSingle(bar, 'Tahun', function () {
            var pool = rowsFor('tahun');
            return DATA.years.map(function (y) {
                var n = pool.filter(function (r) { return r.tahun === y; }).length;
                return { v: y, t: String(y), disabled: state.tahun !== y && !n, sub: n ? n + ' usaha' : 'Kosong pada filter lain' };
            });
        }, state.tahun, function (v) {
            state.tahun = v;
            // Kategori/badan usaha picked in the previous year may not exist in
            // this one — keeping them would silently empty the whole dashboard.
            var inYear = DATA.rows.filter(function (r) { return r.tahun === v; });
            var kats = {}, badans = {};
            inYear.forEach(function (r) { kats[r.kat] = true; badans[r.badanUsaha] = true; });
            Object.keys(state.katSel).forEach(function (k) { if (!kats[k]) delete state.katSel[k]; });
            Object.keys(state.badanSel).forEach(function (b) { if (!badans[b]) delete state.badanSel[b]; });
            rerender();
        });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // Kategori lapangan usaha — counted against every other facet
        ddMulti(bar, 'Kategori', function () {
            var pool = rowsFor('kat');
            return katsOf(DATA.rows.filter(function (r) { return r.tahun === state.tahun; })).map(function (k) {
                var n = pool.filter(function (r) { return r.kat === k; }).length;
                return { v: k, t: katLabelOf(k), n: n, disabled: !n, sub: n ? n + ' usaha' : 'Kosong pada filter lain' };
            });
        }, state.katSel, 'Semua kategori',
            function () { rerenderData(); refreshFilterBar(); });

        // Badan usaha
        ddMulti(bar, 'Badan usaha', function () {
            var pool = rowsFor('badan');
            return badanOf(DATA.rows.filter(function (r) { return r.tahun === state.tahun; })).map(function (b) {
                var n = pool.filter(function (r) { return r.badanUsaha === b; }).length;
                return { v: b, t: b, n: n, disabled: !n, sub: n ? n + ' usaha' : 'Kosong pada filter lain' };
            });
        }, state.badanSel, 'Semua badan usaha',
            function () { rerenderData(); refreshFilterBar(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // Perusahaan picker
        bar.appendChild(el('span', 'stx-filter-label', 'Usaha'));
        var pickWrap = el('div', 'stx-popwrap');
        var pickBtn = el('button', 'stx-popbtn' + (excludedEligible() ? ' active' : ''));
        pickBtn.type = 'button';
        var pickLabel = el('span', null, '');
        var pickBadge = el('span', 'n', '');
        function refreshPickBtn() {
            var total = eligibleCompanies().length, out = excludedEligible();
            pickLabel.textContent = out ? ((total - out) + ' dari ' + total + ' dipilih') : 'Semua usaha';
            pickBadge.textContent = String(total);
            pickBtn.classList.toggle('active', out > 0);
        }
        refreshPickBtn();
        facetRefreshers.push(refreshPickBtn);
        pickBtn.appendChild(pickLabel);
        pickBtn.appendChild(pickBadge);
        pickBtn.appendChild(el('span', 'caret', '▼'));
        pickBtn.addEventListener('click', function () {
            togglePop(pickWrap, pickBtn, function (panel) { buildCompanyPicker(panel, refreshPickBtn); });
        });
        pickWrap.appendChild(pickBtn);
        bar.appendChild(pickWrap);

        bar.appendChild(el('div', 'stx-filter-sep'));

        // Status — counted against everything else. Subtitles carry the usaha
        // count each status yields, so it is clear up front that switching
        // status also resizes the Usaha picker.
        ddSingle(bar, 'Status', function () {
            var pool = rowsFor('status');
            var nDone = pool.filter(function (r) { return r.selesai; }).length;
            return [
                { v: 'done', t: 'Selesai', sub: 'hanya isian final · ' + nDone + ' usaha' },
                { v: 'all', t: 'Semua status', sub: 'termasuk draf · ' + pool.length + ' usaha' },
                { v: 'draft', t: 'Masih draf', sub: 'belum diselesaikan · ' + (pool.length - nDone) + ' usaha' }
            ];
        }, state.status, function (v) { state.status = v; rerender(); });
    }

    function buildCompanyPicker(panel, refreshBtn) {
        var head = el('div', 'stx-pop-head');
        head.appendChild(el('div', 'stx-pop-title', 'Usaha dalam agregasi'));
        head.appendChild(el('div', 'stx-pop-sub', 'Hilangkan centang untuk mengeluarkan usaha dari seluruh perhitungan dashboard.'));
        var search = el('input', 'stx-pop-search');
        search.type = 'search';
        search.placeholder = 'Cari nama usaha…';
        head.appendChild(search);
        panel.appendChild(head);

        var list = el('div', 'stx-pop-list');
        panel.appendChild(list);

        var foot = el('div', 'stx-pop-foot');
        var cnt = el('span', 'cnt', '');
        var actions = el('div');
        var allBtn = el('button', 'stx-pop-link', 'Pilih semua');
        var noneBtn = el('button', 'stx-pop-link', 'Kosongkan');
        allBtn.type = 'button'; noneBtn.type = 'button';
        actions.appendChild(allBtn);
        actions.appendChild(noneBtn);
        foot.appendChild(cnt);
        foot.appendChild(actions);
        panel.appendChild(foot);

        function refreshCnt() {
            var total = eligibleCompanies().length;
            var hidden = DATA.rows.filter(function (r) { return r.tahun === state.tahun; }).length - total;
            cnt.textContent = (total - excludedEligible()) + ' dari ' + total + ' usaha dihitung'
                + (hidden ? ' · ' + hidden + ' di luar filter lain' : '');
        }
        function apply() {
            saveExcluded();
            refreshBtn();
            refreshCnt();
            rerenderData();
            refreshFilterBar();
        }

        function buildList() {
            clear(list);
            var q = (search.value || '').toLowerCase();
            var shown = 0;
            eligibleCompanies().forEach(function (c) {
                var hay = ((c.perusahaan || '') + ' ' + (c.komersial || '') + ' ' + (c.kbli || '')).toLowerCase();
                if (q && hay.indexOf(q) === -1) return;
                shown++;
                var on = !state.excluded[c.uid];
                var row = el('button', 'stx-pop-row' + (on ? ' on' : ' off'));
                row.type = 'button';
                row.setAttribute('role', 'checkbox');
                row.setAttribute('aria-checked', on ? 'true' : 'false');
                var check = el('span', 'p-check', on ? '✓' : '');
                row.appendChild(check);
                var main = el('div', 'p-main');
                main.appendChild(el('div', 'p-name', c.perusahaan || 'Tanpa nama'));
                var meta = el('div', 'p-meta');
                meta.appendChild(el('span', null, c.kat === '—' ? 'Kategori —' : ('Kategori ' + c.kat)));
                meta.appendChild(el('span', null, c.selesai ? 'Selesai' : 'Draf'));
                main.appendChild(meta);
                row.appendChild(main);
                row.appendChild(el('span', 'p-val', fmtRp(c.produksi)));
                row.addEventListener('click', function () {
                    if (state.excluded[c.uid]) delete state.excluded[c.uid];
                    else state.excluded[c.uid] = true;
                    var incl = !state.excluded[c.uid];
                    row.classList.toggle('on', incl);
                    row.classList.toggle('off', !incl);
                    row.setAttribute('aria-checked', incl ? 'true' : 'false');
                    check.textContent = incl ? '✓' : '';
                    apply();
                });
                list.appendChild(row);
            });
            if (!shown) {
                var none = el('div', 'stx-pop-group', q ? 'Tidak ada usaha yang cocok.' : 'Tidak ada usaha pada filter ini.');
                none.style.padding = '0.8rem 0.6rem';
                list.appendChild(none);
            }
        }

        // Both bulk actions stay inside the eligible pool so they never silently
        // flip usaha the user cannot currently see.
        allBtn.addEventListener('click', function () {
            eligibleCompanies().forEach(function (c) { delete state.excluded[c.uid]; });
            buildList(); apply();
        });
        noneBtn.addEventListener('click', function () {
            eligibleCompanies().forEach(function (c) { state.excluded[c.uid] = true; });
            buildList(); apply();
        });
        search.addEventListener('input', buildList);

        buildList();
        refreshCnt();
        setTimeout(function () { search.focus(); }, 30);
    }

    function filterPills() {
        var pills = ['Tahun ' + state.tahun];
        var katKeys = Object.keys(state.katSel);
        if (katKeys.length === 1) pills.push(katLabelOf(katKeys[0]));
        else if (katKeys.length > 1) pills.push(katKeys.length + ' kategori');
        var badanKeys = Object.keys(state.badanSel);
        if (badanKeys.length === 1) pills.push(badanKeys[0]);
        else if (badanKeys.length > 1) pills.push(badanKeys.length + ' badan usaha');
        if (state.status === 'done') pills.push('Hanya selesai');
        if (state.status === 'draft') pills.push('Hanya draf');
        if (excludedEligible()) pills.push(excludedEligible() + ' usaha dikecualikan');
        return pills;
    }

    /* ═══════════════ KPI ROW ═══════════════ */

    var KPI_ICONS = {
        usaha: 'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h1m4 0h1M9 12h1m4 0h1M9 16h1m4 0h1',
        produksi: 'M3 17l6-6 4 4 8-8M14 7h7v7',
        pengeluaran: 'M21 7l-6 6-4-4-8 8M10 17H3v-7',
        surplus: 'M12 3a9 9 0 100 18 9 9 0 000-18zM14.5 9.3c-.4-.8-1.4-1.3-2.5-1.3-1.4 0-2.5.8-2.5 1.9s1.1 1.5 2.5 1.9c1.4.4 2.5.8 2.5 1.9s-1.1 1.9-2.5 1.9c-1.1 0-2.1-.5-2.5-1.3M12 6.7V8m0 8v1.3',
        pekerja: 'M17 20h5v-2a3 3 0 00-5.36-1.87M17 20H7m10 0v-2c0-.66-.13-1.29-.36-1.87M7 20H2v-2a3 3 0 015.36-1.87M7 20v-2c0-.66.13-1.29.36-1.87m0 0a5 5 0 019.28 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        aset: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
    };

    function kpiIcon(name) {
        var span = el('span', 'k-ico');
        var svg = svgEl('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.8, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' });
        svg.appendChild(svgEl('path', { d: KPI_ICONS[name] || KPI_ICONS.usaha }));
        span.appendChild(svg);
        return span;
    }

    function kpiTile(container, def) {
        var tile = el('button', 'stx-card stx-kpi');
        tile.type = 'button';
        var top = el('div', 'k-top');
        top.appendChild(el('div', 'k-label', def.label));
        if (def.icon) top.appendChild(kpiIcon(def.icon));
        tile.appendChild(top);
        tile.appendChild(el('div', 'k-value', def.value));
        var foot = el('div', 'k-foot');
        foot.appendChild(el('span', 'k-sub', def.sub || ''));
        tile.appendChild(foot);
        tile.title = def.tooltip || 'Klik untuk rincian';
        tile.addEventListener('click', def.onClick);
        container.appendChild(tile);
    }

    /** Per-usaha breakdown of one metric, ranked biggest first. */
    function companyBreakdownModal(title, rows, valueFn, fmt) {
        openModal(title, filterPills(), function (body) {
            var s = sect(body, 'Rincian per usaha — terbesar lebih dulu');
            var sorted = rows.slice().sort(function (a, b) {
                var av = valueFn(a), bv = valueFn(b);
                if (av === null || av === undefined) return 1;
                if (bv === null || bv === undefined) return -1;
                return bv - av;
            });
            simpleTable(s, ['Usaha', 'Kategori', 'Nilai'],
                sorted.map(function (r) { return [r.perusahaan, r.kat, fmt(valueFn(r))]; }), 2);
        });
    }

    function renderKpis() {
        var wrap = document.getElementById('stx-kpis');
        clear(wrap);
        var rows = filteredRows();

        // 1 — usaha reporting
        var done = rows.filter(function (r) { return r.selesai; }).length;
        kpiTile(wrap, {
            label: 'Usaha melapor',
            icon: 'usaha',
            value: fmtN(rows.length),
            sub: done + ' selesai · ' + (rows.length - done) + ' draf',
            onClick: function () {
                openModal('Usaha melapor', filterPills(), function (body) {
                    var s = sect(body, 'Daftar usaha');
                    simpleTable(s, ['Usaha', 'Kategori', 'Badan usaha', 'Status', 'Kelengkapan', 'Diperbarui'],
                        rows.map(function (r) {
                            return [r.perusahaan, r.kat, r.badanUsaha, r.selesai ? 'Selesai' : 'Draf', r.progress + '%', r.updatedAt || '—'];
                        }), 4);
                });
            }
        });

        // 2 — nilai produksi
        var totProduksi = sumField(rows, 'produksi');
        kpiTile(wrap, {
            label: 'Total nilai produksi',
            icon: 'produksi',
            value: fmtRp(totProduksi),
            tooltip: fmtRpFull(totProduksi),
            sub: countField(rows, 'produksi') + ' dari ' + rows.length + ' usaha mengisi',
            onClick: function () {
                companyBreakdownModal('Total nilai produksi', rows, function (r) { return r.produksi; }, fmtRpFull);
            }
        });

        // 3 — pengeluaran
        var totPengeluaran = sumField(rows, 'pengeluaran');
        kpiTile(wrap, {
            label: 'Total pengeluaran',
            icon: 'pengeluaran',
            value: fmtRp(totPengeluaran),
            tooltip: fmtRpFull(totPengeluaran),
            sub: countField(rows, 'pengeluaran') + ' dari ' + rows.length + ' usaha mengisi',
            onClick: function () {
                companyBreakdownModal('Total pengeluaran', rows, function (r) { return r.pengeluaran; }, fmtRpFull);
            }
        });

        // 4 — perkiraan surplus
        var totSurplus = sumField(rows, 'surplus');
        kpiTile(wrap, {
            label: 'Perkiraan surplus',
            icon: 'surplus',
            value: fmtRp(totSurplus),
            tooltip: fmtRpFull(totSurplus),
            sub: 'nilai produksi − pengeluaran',
            onClick: function () {
                companyBreakdownModal('Perkiraan surplus usaha', rows, function (r) { return r.surplus; }, fmtRpFull);
            }
        });

        // 5 — tenaga kerja
        var totTk = sumField(rows, 'pekerja');
        var totP = sumField(rows, 'pekerjaP');
        kpiTile(wrap, {
            label: 'Total tenaga kerja',
            icon: 'pekerja',
            value: fmtN(totTk),
            sub: (totTk && totP !== null) ? share(totP, totTk) + ' perempuan' : 'belum ada data',
            onClick: function () {
                companyBreakdownModal('Tenaga kerja per usaha', rows, function (r) { return r.pekerja; }, fmtN);
            }
        });

        // 6 — nilai aset
        var totAset = sumField(rows, 'aset');
        kpiTile(wrap, {
            label: 'Total nilai aset',
            icon: 'aset',
            value: fmtRp(totAset),
            tooltip: fmtRpFull(totAset),
            sub: countField(rows, 'aset') + ' dari ' + rows.length + ' usaha mengisi',
            onClick: function () {
                companyBreakdownModal('Nilai total aset', rows, function (r) { return r.aset; }, fmtRpFull);
            }
        });
    }

    /* ═══════════════ CHART 1 — per kategori lapangan usaha ═══════════════ */

    function renderKategori() {
        var metric = state.metricKategori;
        var def = metricDef(metric);
        var shell = cardShell('card-kategori',
            'Per kategori lapangan usaha',
            'Kategori KBLI dari isian Blok I-B — klik baris untuk memfokuskan seluruh dashboard pada kategori itu',
            { metricKey: 'metricKategori' });
        var rows = filteredRows();
        var fmt = fmtMetric(metric), fmtF = fmtMetricFull(metric);

        var list = katsOf(rows).map(function (k) {
            var sub = rows.filter(function (r) { return r.kat === k; });
            return {
                kat: k,
                label: katLabelOf(k),
                n: sub.length,
                v: sumField(sub, metric),
                produksi: sumField(sub, 'produksi'),
                pengeluaran: sumField(sub, 'pengeluaran'),
                pekerja: sumField(sub, 'pekerja'),
                aset: sumField(sub, 'aset')
            };
        }).sort(function (a, b) { return (b.v || 0) - (a.v || 0); });

        var total = list.reduce(function (a, g) { return a + (g.v || 0); }, 0);

        simpleTable(shell.tablePane,
            ['Kategori', 'Usaha', 'Nilai produksi', 'Pengeluaran', 'Tenaga kerja', 'Aset', 'Porsi ' + def.label.toLowerCase()],
            list.map(function (g) {
                return [g.label, String(g.n), fmtRpFull(g.produksi), fmtRpFull(g.pengeluaran),
                    fmtN(g.pekerja), fmtRpFull(g.aset), share(g.v || 0, total)];
            }), 1);

        if (!list.length || total <= 0) {
            emptyState(shell.chartPane, 'Belum ada data pada irisan filter ini.');
            return;
        }

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: g.n + ' usaha · ' + share(g.v || 0, total) + ' dari total',
                v: g.v,
                valueLabel: fmtNc(g.v),
                dim: katSelCount() > 0 && !state.katSel[g.kat],
                strong: !!state.katSel[g.kat],
                tip: [
                    { color: theme().s1, label: def.label, value: fmtF(g.v) },
                    { label: 'Jumlah usaha', value: fmtN(g.n) },
                    { label: 'Porsi', value: share(g.v || 0, total) }
                ],
                onClick: function () {
                    if (state.katSel[g.kat]) delete state.katSel[g.kat]; else state.katSel[g.kat] = true;
                    rerender();
                }
            };
        }), { rowH: 44, labelMax: 230, aria: 'Per kategori lapangan usaha', fallbackW: 640 });
    }

    /* ═══════════════ CHART 2 — skala usaha (tenaga kerja) ═══════════════ */

    function renderSkala() {
        var shell = cardShell('card-skala',
            'Skala usaha menurut tenaga kerja',
            'Jumlah usaha per kelas pekerja — klik baris untuk melihat daftarnya');
        var rows = filteredRows();
        var t = theme();

        var buckets = SKALA.map(function (s) { return { def: s, rows: [] }; });
        var unknown = [];
        rows.forEach(function (r) {
            var i = skalaIndex(r);
            if (i < 0) unknown.push(r); else buckets[i].rows.push(r);
        });

        var known = rows.length - unknown.length;

        simpleTable(shell.tablePane, ['Skala', 'Usaha', 'Porsi', 'Tenaga kerja', 'Nilai produksi'],
            buckets.map(function (b) {
                return [b.def.label + ' (' + b.def.sub + ')', String(b.rows.length),
                    share(b.rows.length, known), fmtN(sumField(b.rows, 'pekerja')), fmtRpFull(sumField(b.rows, 'produksi'))];
            }).concat(unknown.length ? [['Belum mengisi tenaga kerja', String(unknown.length), '—', '—', fmtRpFull(sumField(unknown, 'produksi'))]] : []), 1);

        if (!known) {
            emptyState(shell.chartPane, 'Belum ada usaha yang mengisi jumlah tenaga kerja.');
            return;
        }

        barRows(shell.chartPane, buckets.map(function (b) {
            return {
                label: b.def.label,
                sub: b.def.sub + ' · ' + share(b.rows.length, known),
                v: b.rows.length,
                valueLabel: fmtN(b.rows.length),
                tip: [
                    { color: t.s1, label: 'Jumlah usaha', value: fmtN(b.rows.length) },
                    { label: 'Tenaga kerja', value: fmtN(sumField(b.rows, 'pekerja')) },
                    { label: 'Nilai produksi', value: fmtRpFull(sumField(b.rows, 'produksi')) }
                ],
                onClick: function () {
                    openModal('Skala ' + b.def.label + ' (' + b.def.sub + ')', filterPills(), function (body) {
                        var s = sect(body, 'Usaha pada kelas ini');
                        if (!b.rows.length) {
                            s.appendChild(el('p', 'stx-note', 'Tidak ada usaha pada kelas ini.'));
                            return;
                        }
                        var sorted = b.rows.slice().sort(function (x, y) { return (y.pekerja || 0) - (x.pekerja || 0); });
                        simpleTable(s, ['Usaha', 'Kategori', 'Pekerja', 'Nilai produksi'],
                            sorted.map(function (r) { return [r.perusahaan, r.kat, fmtN(r.pekerja), fmtRpFull(r.produksi)]; }), 2);
                    });
                }
            };
        }), { rowH: 42, labelMax: 150, aria: 'Skala usaha menurut tenaga kerja' });

        if (unknown.length) {
            shell.chartPane.appendChild(el('p', 'stx-note', unknown.length + ' usaha belum mengisi jumlah tenaga kerja dan tidak masuk pembagian kelas.'));
        }
    }

    /* ═══════════════ CHART 3 — struktur pengeluaran ═══════════════ */

    function renderStruktur() {
        var shell = cardShell('card-struktur',
            'Struktur pengeluaran',
            'Komposisi pengeluaran usaha pada irisan filter aktif (Blok I-D rincian 22)');
        var rows = filteredRows();
        var t = theme();

        var list = PENGELUARAN_PARTS.map(function (p) {
            return { key: p.key, label: p.label, v: sumField(rows, p.key), n: countField(rows, p.key) };
        });
        var total = list.reduce(function (a, g) { return a + (g.v || 0); }, 0);

        simpleTable(shell.tablePane, ['Komponen', 'Nilai', 'Porsi', 'Usaha mengisi'],
            list.map(function (g) { return [g.label, fmtRpFull(g.v), share(g.v || 0, total), String(g.n)]; })
                .concat([['TOTAL PENGELUARAN', fmtRpFull(total || null), '100%', String(countField(rows, 'pengeluaran'))]]), 1);

        if (total <= 0) {
            emptyState(shell.chartPane, 'Belum ada pengeluaran yang dilaporkan pada irisan filter ini.');
            return;
        }

        barRows(shell.chartPane, list.slice().sort(function (a, b) { return (b.v || 0) - (a.v || 0); }).map(function (g) {
            return {
                label: g.label,
                sub: share(g.v || 0, total) + ' dari total pengeluaran',
                v: g.v,
                valueLabel: fmtNc(g.v),
                tip: [
                    { color: t.s1, label: 'Nilai', value: fmtRpFull(g.v) },
                    { label: 'Porsi', value: share(g.v || 0, total) },
                    { label: 'Usaha mengisi', value: fmtN(g.n) }
                ],
                onClick: function () {
                    companyBreakdownModal(g.label, rows, function (r) { return r[g.key]; }, fmtRpFull);
                }
            };
        }), { rowH: 42, labelMax: 190, aria: 'Struktur pengeluaran' });
    }

    /* ═══════════════ CHART 4 — tenaga kerja menurut jenis kelamin ═══════════════ */

    function renderTenaga() {
        var shell = cardShell('card-tenaga',
            'Tenaga kerja menurut jenis kelamin',
            'Komposisi pekerja laki-laki dan perempuan per kategori lapangan usaha');
        var t = theme();
        var rows = filteredRows();

        var list = katsOf(rows).map(function (k) {
            var sub = rows.filter(function (r) { return r.kat === k; });
            return { kat: k, label: katShort(k), l: sumField(sub, 'pekerjaL'), p: sumField(sub, 'pekerjaP') };
        }).map(function (g) {
            g.tot = (g.l === null && g.p === null) ? null : (g.l || 0) + (g.p || 0);
            return g;
        }).filter(function (g) { return g.tot; })
          .sort(function (a, b) { return b.tot - a.tot; });

        var totL = sumField(rows, 'pekerjaL'), totP = sumField(rows, 'pekerjaP');
        var totAll = (totL || 0) + (totP || 0);

        simpleTable(shell.tablePane, ['Kategori', 'Laki-laki', 'Perempuan', 'Total', '% Perempuan'],
            list.map(function (g) {
                return [g.label, fmtN(g.l), fmtN(g.p), fmtN(g.tot), share(g.p || 0, g.tot)];
            }).concat([['TOTAL', fmtN(totL), fmtN(totP), fmtN(totAll || null), share(totP || 0, totAll)]]), 1);

        if (!list.length || !totAll) {
            emptyState(shell.chartPane, 'Belum ada data tenaga kerja pada irisan filter ini.');
            return;
        }

        legendItem(shell.legend, 'swatch', t.s1, 'Laki-laki');
        legendItem(shell.legend, 'swatch', t.s3, 'Perempuan');

        // Stacked bars — one row per kategori, split L/P.
        var top = list.slice(0, 8);
        var W = Math.max(300, shell.chartPane.clientWidth || 460);
        var rowH = 42, padT = 4;
        var labelW = Math.min(160, Math.max(105, W * 0.3));
        var valueW = 78;
        var plotW = Math.max(40, W - labelW - valueW - 12);
        var H = padT + top.length * rowH + 4;
        var maxV = Math.max.apply(null, top.map(function (g) { return g.tot; }).concat([1]));

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Tenaga kerja menurut jenis kelamin per kategori');

        top.forEach(function (g, i) {
            var cy = padT + i * rowH + rowH / 2;
            var name = svgEl('text', { x: 0, y: cy + 1, 'font-size': 11.5, 'font-weight': 600, fill: t.ink2 });
            var maxChars = Math.floor(labelW / 6.4);
            name.textContent = g.label.length > maxChars ? g.label.slice(0, maxChars - 1) + '…' : g.label;
            var ttl = svgEl('title');
            ttl.textContent = katLabelOf(g.kat);
            name.appendChild(ttl);
            svg.appendChild(name);
            var sub = svgEl('text', { x: 0, y: cy + 13, 'font-size': 9.5, fill: t.muted });
            sub.textContent = share(g.p || 0, g.tot) + ' perempuan';
            svg.appendChild(sub);

            var barH = 16, y0 = cy - barH / 2;
            var full = g.tot / maxV * plotW;
            var lw = g.tot ? (g.l || 0) / g.tot * full : 0;
            svg.appendChild(svgEl('rect', { x: labelW, y: y0, width: plotW, height: barH, rx: 4, fill: t.grid, opacity: 0.45 }));
            if (lw > 0) svg.appendChild(svgEl('rect', { x: labelW, y: y0, width: Math.max(lw, 1), height: barH, fill: t.s1 }));
            if (full - lw > 0) {
                svg.appendChild(svgEl('path', {
                    d: roundRightBarPath(labelW + lw, y0, Math.max(full - lw, 1), barH, 4), fill: t.s3
                }));
            }
            var val = svgEl('text', { x: labelW + plotW + 8, y: cy + 4, 'font-size': 11, 'font-weight': 700, fill: t.ink });
            val.textContent = fmtN(g.tot);
            svg.appendChild(val);

            var hit = svgEl('rect', { x: 0, y: padT + i * rowH, width: W, height: rowH, fill: 'transparent' });
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, katLabelOf(g.kat), [
                    { color: t.s1, label: 'Laki-laki', value: fmtN(g.l) },
                    { color: t.s3, label: 'Perempuan', value: fmtN(g.p) },
                    { label: 'Total', value: fmtN(g.tot) }
                ]);
            });
            hit.addEventListener('pointerleave', tipHide);
            svg.appendChild(hit);
        });

        shell.chartPane.appendChild(svg);
        if (list.length > top.length) {
            shell.chartPane.appendChild(el('p', 'stx-note', 'Menampilkan 8 kategori dengan tenaga kerja terbanyak — lihat tab Tabel untuk seluruhnya.'));
        }
    }

    /* ═══════════════ CHART 5 — rentang nilai aset ═══════════════ */

    function renderAset() {
        var shell = cardShell('card-aset',
            'Rentang nilai total aset',
            'Sebaran usaha menurut nilai aset (rentang Blok I-D 24c1, dilengkapi dari nilai nominal)');
        var rows = filteredRows();
        var t = theme();

        var bands = Object.keys(DATA.rangeAset).map(function (k) { return parseInt(k, 10); }).sort(function (a, b) { return a - b; });
        var list = bands.map(function (b) {
            var sub = rows.filter(function (r) { return r.rangeAset === b; });
            return { band: b, label: DATA.rangeAset[b], rows: sub };
        });
        var unknown = rows.filter(function (r) { return r.rangeAset === null || r.rangeAset === undefined; });
        var known = rows.length - unknown.length;

        simpleTable(shell.tablePane, ['Rentang aset', 'Usaha', 'Porsi', 'Nilai aset', 'Tenaga kerja'],
            list.map(function (g) {
                return [g.label, String(g.rows.length), share(g.rows.length, known),
                    fmtRpFull(sumField(g.rows, 'aset')), fmtN(sumField(g.rows, 'pekerja'))];
            }).concat(unknown.length ? [['Belum diisi', String(unknown.length), '—', '—', fmtN(sumField(unknown, 'pekerja'))]] : []), 1);

        if (!known) {
            emptyState(shell.chartPane, 'Belum ada usaha yang mengisi nilai atau rentang aset.');
            return;
        }

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: share(g.rows.length, known) + ' dari usaha terisi',
                v: g.rows.length,
                valueLabel: fmtN(g.rows.length),
                tip: [
                    { color: t.s1, label: 'Jumlah usaha', value: fmtN(g.rows.length) },
                    { label: 'Nilai aset', value: fmtRpFull(sumField(g.rows, 'aset')) }
                ],
                onClick: function () {
                    openModal('Aset ' + g.label, filterPills(), function (body) {
                        var s = sect(body, 'Usaha pada rentang ini');
                        if (!g.rows.length) {
                            s.appendChild(el('p', 'stx-note', 'Tidak ada usaha pada rentang ini.'));
                            return;
                        }
                        var sorted = g.rows.slice().sort(function (x, y) { return (y.aset || 0) - (x.aset || 0); });
                        simpleTable(s, ['Usaha', 'Kategori', 'Nilai aset', 'Luas tanah'],
                            sorted.map(function (r) { return [r.perusahaan, r.kat, fmtRpFull(r.aset), fmtM2(r.luasTanah)]; }), 2);
                    });
                }
            };
        }), { rowH: 42, labelMax: 210, aria: 'Rentang nilai total aset' });

        if (unknown.length) {
            shell.chartPane.appendChild(el('p', 'stx-note', unknown.length + ' usaha belum mengisi nilai maupun rentang aset.'));
        }
    }

    /* ═══════════════ CHART 6 — profil digital, sertifikasi & kemitraan ═══════════════ */

    function flagCounts(rows, key) {
        var ya = 0, jawab = 0;
        rows.forEach(function (r) {
            var v = r.flags ? r.flags[key] : null;
            if (v === null || v === undefined) return;
            jawab++;
            if (v) ya++;
        });
        return { ya: ya, jawab: jawab, pct: jawab ? ya / jawab * 100 : null };
    }

    function renderProfil() {
        var shell = cardShell('card-profil',
            'Profil digital, sertifikasi & kemitraan',
            'Persentase dihitung dari usaha yang menjawab pertanyaannya, bukan dari seluruh responden — klik baris untuk daftar usahanya');
        var rows = filteredRows();
        var t = theme();

        var list = PROFIL.map(function (f) {
            var c = flagCounts(rows, f.key);
            return { key: f.key, label: f.label, group: f.group, ya: c.ya, jawab: c.jawab, pct: c.pct };
        });

        simpleTable(shell.tablePane, ['Indikator', 'Kelompok', 'Ya', 'Menjawab', '% Ya'],
            list.map(function (g) {
                return [g.label, g.group, String(g.ya), String(g.jawab), g.pct === null ? '—' : nfPct.format(g.pct) + '%'];
            }), 2);

        var answered = list.filter(function (g) { return g.jawab > 0; });
        if (!answered.length) {
            emptyState(shell.chartPane, 'Belum ada usaha yang menjawab pertanyaan profil pada irisan filter ini.');
            return;
        }

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: g.jawab ? (g.ya + ' dari ' + g.jawab + ' usaha menjawab ya') : 'belum ada yang menjawab',
                v: g.pct === null ? 0 : g.pct,
                valueLabel: g.pct === null ? '—' : nfPct.format(g.pct) + '%',
                color: t.s1,
                dim: g.jawab === 0,
                tip: [
                    { color: t.s1, label: 'Menjawab ya', value: fmtN(g.ya) },
                    { label: 'Menjawab', value: fmtN(g.jawab) },
                    { label: 'Persentase', value: g.pct === null ? '—' : nfPct.format(g.pct) + '%' }
                ],
                onClick: !g.jawab ? null : function () {
                    openModal(g.label, filterPills(), function (body) {
                        var s = sect(body, 'Jawaban per usaha');
                        var ordered = rows.slice().sort(function (a, b) {
                            var av = a.flags[g.key], bv = b.flags[g.key];
                            var rank = function (v) { return v === true ? 0 : (v === false ? 1 : 2); };
                            return rank(av) - rank(bv);
                        });
                        simpleTable(s, ['Usaha', 'Kategori', 'Jawaban', 'Nilai produksi'],
                            ordered.map(function (r) {
                                var v = r.flags[g.key];
                                return [r.perusahaan, r.kat, v === null || v === undefined ? 'Belum dijawab' : (v ? 'Ya' : 'Tidak'), fmtRpFull(r.produksi)];
                            }), 3);
                    });
                }
            };
        }), { rowH: 40, labelMax: 260, valueW: 74, maxV: 100, aria: 'Profil digital, sertifikasi dan kemitraan', fallbackW: 640 });
    }

    /* ═══════════════ TABLE — per usaha ═══════════════ */

    var TABLE_COLS = [
        { key: 'perusahaan', label: 'Usaha', num: false },
        { key: 'kat', label: 'Kategori', num: false },
        { key: 'selesai', label: 'Status', num: false },
        { key: 'produksi', label: 'Nilai produksi', num: true },
        { key: 'pengeluaran', label: 'Pengeluaran', num: true },
        { key: 'surplus', label: 'Surplus', num: true },
        { key: 'pekerja', label: 'TK', num: true },
        { key: 'aset', label: 'Aset', num: true },
        { key: 'updatedAt', label: 'Diperbarui', num: false }
    ];

    /**
     * Everything the detail table can be ranked by — a superset of the fixed
     * columns, so an analyst can ask "usaha mana yang upahnya terbesar?" even
     * though Upah is not one of the standing columns. Picking such a metric
     * adds its own column (see activeCols), otherwise the ordering would be
     * invisible.
     */
    var SORT_OPTS = [
        { key: 'perusahaan',    label: 'Nama usaha',            num: false, sub: 'urut abjad' },
        { key: 'kat',           label: 'Kategori lapangan usaha', num: false, sub: 'urut kode kategori' },
        { key: 'produksi',      label: 'Nilai produksi',        num: true,  sub: 'barang/jasa + pendapatan lainnya' },
        { key: 'pengeluaran',   label: 'Pengeluaran total',     num: true,  sub: 'upah + biaya + operasional' },
        { key: 'surplus',       label: 'Perkiraan surplus',     num: true,  sub: 'nilai produksi − pengeluaran' },
        { key: 'aset',          label: 'Nilai total aset',      num: true,  sub: 'tanah & bangunan + lainnya' },
        { key: 'pekerja',       label: 'Jumlah tenaga kerja',   num: true },
        { key: 'pekerjaP',      label: 'Pekerja perempuan',     num: true },
        { key: 'upah',          label: 'Upah & gaji',           num: true },
        { key: 'biayaProduksi', label: 'Biaya produksi',        num: true },
        { key: 'persenOnline',  label: 'Porsi pendapatan online', num: true, sub: '% dari pendapatan' },
        { key: 'luasTanah',     label: 'Luas tanah',            num: true,  sub: 'm² yang dikuasai' },
        { key: 'tahunOperasi',  label: 'Tahun mulai beroperasi', num: true },
        { key: 'progress',      label: 'Kelengkapan pengisian', num: true,  sub: '% blok yang selesai' },
        { key: 'updatedAt',     label: 'Terakhir diperbarui',   num: true,  sub: 'tanggal simpan terakhir' }
    ];
    function sortOpt(key) {
        for (var i = 0; i < SORT_OPTS.length; i++) if (SORT_OPTS[i].key === key) return SORT_OPTS[i];
        return null;
    }
    // Column headings can set a sort key the picker does not list (Status);
    // the collapsed button still has to name it in words.
    function sortLabel(key) {
        var o = sortOpt(key);
        if (o) return o.label;
        for (var i = 0; i < TABLE_COLS.length; i++) if (TABLE_COLS[i].key === key) return TABLE_COLS[i].label;
        return key;
    }
    // Direction wording follows the metric — "Terbesar" is nonsense for a name.
    function dirLabels(key) {
        if (key === 'updatedAt') return ['Terbaru', 'Terlama'];
        if (key === 'tahunOperasi') return ['Termuda', 'Tertua'];
        if (key === 'selesai') return ['Selesai dulu', 'Draf dulu'];
        var o = sortOpt(key);
        return (o && !o.num) ? ['Z → A', 'A → Z'] : ['Terbesar', 'Terkecil'];
    }
    // updatedAt is displayed as "12 Mei 2026" — rank on the timestamp twin.
    function sortValue(r, key) {
        if (key === 'updatedAt') return r.updatedTs === undefined ? null : r.updatedTs;
        if (key === 'selesai') return r.selesai ? 1 : 0;
        var v = r[key];
        return v === undefined ? null : v;
    }
    function setSort(key) {
        if (state.sortKey === key) { state.sortDir *= -1; }
        else {
            var o = sortOpt(key);
            state.sortKey = key;
            state.sortDir = (o && !o.num) ? 1 : -1;
        }
        renderTable();
    }

    /** Sort picker + direction toggle, mounted in the table card header. */
    function sortTools(container) {
        var tools = el('div', 'stx-sorttools');
        tools.appendChild(el('span', 'stx-filter-label', 'Urutkan'));

        var wrapEl = el('div', 'stx-popwrap');
        var btn = el('button', 'stx-popbtn active');
        btn.type = 'button';
        btn.appendChild(el('span', null, sortLabel(state.sortKey)));
        btn.appendChild(el('span', 'caret', '▼'));
        btn.setAttribute('aria-label', 'Pilih kolom pengurutan');
        btn.addEventListener('click', function () {
            togglePop(wrapEl, btn, function (panel) {
                panel.style.width = 'min(20rem, 90vw)';
                var head = el('div', 'stx-pop-head');
                head.appendChild(el('div', 'stx-pop-title', 'Urutkan usaha menurut'));
                head.appendChild(el('div', 'stx-pop-sub', 'Metrik di luar kolom tetap akan ditambahkan sebagai kolom sendiri.'));
                panel.appendChild(head);
                var list = el('div', 'stx-pop-list');
                SORT_OPTS.forEach(function (o) {
                    var on = o.key === state.sortKey;
                    var row = el('button', 'stx-pop-row' + (on ? ' on' : ''));
                    row.type = 'button';
                    row.appendChild(el('span', 'p-check', on ? '✓' : ''));
                    var main = el('div', 'p-main');
                    main.appendChild(el('div', 'p-name', o.label));
                    if (o.sub) main.appendChild(el('div', 'p-meta', o.sub));
                    row.appendChild(main);
                    row.addEventListener('click', function () {
                        closePop();
                        if (state.sortKey !== o.key) setSort(o.key); else renderTable();
                    });
                    list.appendChild(row);
                });
                panel.appendChild(list);
            });
        });
        wrapEl.appendChild(btn);
        tools.appendChild(wrapEl);

        var labels = dirLabels(state.sortKey);
        var toggle = el('div', 'stx-toggle');
        [[-1, labels[0]], [1, labels[1]]].forEach(function (d) {
            var b = el('button', state.sortDir === d[0] ? 'on' : null, d[1]);
            b.type = 'button';
            b.addEventListener('click', function () {
                if (state.sortDir === d[0]) return;
                state.sortDir = d[0];
                renderTable();
            });
            toggle.appendChild(b);
        });
        tools.appendChild(toggle);
        container.appendChild(tools);
    }

    /** Fixed columns, plus a column for the sort metric when it has none. */
    function activeCols() {
        var cols = TABLE_COLS.slice();
        for (var i = 0; i < cols.length; i++) if (cols[i].key === state.sortKey) return cols;
        var o = sortOpt(state.sortKey);
        if (o) cols.splice(3, 0, { key: o.key, label: o.label, num: true, extra: true });
        return cols;
    }

    function cellFor(r, col) {
        var td;
        switch (col.key) {
            case 'perusahaan':
                td = el('td', 'strong', r.perusahaan);
                if (r.komersial) td.title = r.komersial;
                return td;
            case 'kat':
                td = el('td', null, r.kat);
                td.title = katLabelOf(r.kat);
                return td;
            case 'selesai':
                td = el('td');
                td.appendChild(el('span', 'stx-badge ' + (r.selesai ? 'ok' : 'draft'), r.selesai ? 'Selesai' : 'Draf'));
                return td;
            case 'updatedAt':
                return el('td', null, r.updatedAt || '—');
            case 'pekerja':
            case 'pekerjaP':
                return el('td', 'num', fmtN(r[col.key]));
            case 'tahunOperasi':
                return el('td', 'num', r.tahunOperasi === null ? '—' : String(r.tahunOperasi));
            case 'persenOnline':
            case 'progress':
                return el('td', 'num', fmtPct(r[col.key]));
            case 'luasTanah':
                return el('td', 'num', fmtM2(r.luasTanah));
            default:
                td = el('td', 'num', fmtRp(r[col.key]));
                td.title = fmtRpFull(r[col.key]);
                return td;
        }
    }

    var TABLE_PAGE = 10;

    // Paged footer: keeps the card short by default instead of one endless scroll.
    function pagerFoot(card, total, limit) {
        if (total <= TABLE_PAGE) return;
        var foot = el('div', 'stx-more');
        foot.appendChild(el('span', 'cnt', 'Menampilkan ' + limit + ' dari ' + total + ' baris'));
        var btns = el('div', 'stx-more-btns');
        function btn(label, onClick) {
            var b = el('button', null, label);
            b.type = 'button';
            b.addEventListener('click', onClick);
            btns.appendChild(b);
        }
        if (limit < total) {
            btn('Tampilkan ' + Math.min(TABLE_PAGE, total - limit) + ' lagi', function () {
                state.tableLimit = limit + TABLE_PAGE;
                renderTable();
            });
            btn('Tampilkan semua (' + total + ')', function () {
                state.tableLimit = total;
                renderTable();
            });
        } else {
            btn('Tampilkan lebih sedikit', function () {
                state.tableLimit = TABLE_PAGE;
                renderTable();
                card.scrollIntoView({ block: 'nearest' });
            });
        }
        foot.appendChild(btns);
        card.appendChild(foot);
    }

    function renderTable() {
        var card = document.getElementById('card-table');
        clear(card);
        var head = el('div', 'stx-chart-head');
        var titles = el('div');
        titles.appendChild(el('div', 'stx-chart-title', 'Rincian per usaha'));
        titles.appendChild(el('div', 'stx-chart-sub', 'Klik baris untuk membuka detail isian — pakai Urutkan atau klik judul kolom untuk memeringkat'));
        head.appendChild(titles);
        sortTools(head);
        card.appendChild(head);

        var rows = filteredRows().slice();
        var cols = activeCols();

        // Rows with nothing reported for the sort metric always sink to the
        // bottom — in either direction "terkecil" must not mean "belum mengisi".
        rows.sort(function (a, b) {
            var av = sortValue(a, state.sortKey), bv = sortValue(b, state.sortKey);
            if (av === null || av === undefined) return 1;
            if (bv === null || bv === undefined) return -1;
            if (typeof av === 'string') return state.sortDir * av.localeCompare(bv, 'id');
            return state.sortDir * (av - bv);
        });

        var wrapT = el('div', 'stx-tablewrap');
        wrapT.style.padding = '0 0.5rem 0.75rem';
        var table = el('table', 'stx-table');
        var thead = el('thead'), trh = el('tr');
        cols.forEach(function (c) {
            var sorted = state.sortKey === c.key;
            var th = el('th', (c.num ? 'num' : '') + (sorted ? ' sorted' : '') || null, c.label);
            if (sorted) {
                th.setAttribute('aria-sort', state.sortDir === 1 ? 'ascending' : 'descending');
                th.appendChild(el('span', 'arrow', state.sortDir === 1 ? '▲' : '▼'));
            }
            th.addEventListener('click', function () { setSort(c.key); });
            trh.appendChild(th);
        });
        thead.appendChild(trh);
        table.appendChild(thead);

        var tb = el('tbody');
        if (!rows.length) {
            var tr0 = el('tr'), td0 = el('td', null, 'Tidak ada data pada irisan filter ini.');
            td0.colSpan = cols.length;
            td0.style.textAlign = 'center';
            td0.style.padding = '2rem';
            tr0.appendChild(td0); tb.appendChild(tr0);
        }
        var limit = Math.min(Math.max(state.tableLimit, TABLE_PAGE), rows.length);
        rows.slice(0, limit).forEach(function (r) {
            var tr = el('tr');
            tr.setAttribute('tabindex', 0);
            cols.forEach(function (c) { tr.appendChild(cellFor(r, c)); });
            function openCompany() { companyModal(r); }
            tr.addEventListener('click', openCompany);
            tr.addEventListener('keydown', function (e) { if (e.key === 'Enter') openCompany(); });
            tb.appendChild(tr);
        });
        table.appendChild(tb);
        wrapT.appendChild(table);
        card.appendChild(wrapT);
        pagerFoot(card, rows.length, limit);
    }

    /* ═══════════════ company drill-down modal ═══════════════ */

    function companyModal(r) {
        openModal(r.perusahaan, [
            'Tahun ' + r.tahun,
            katLabelOf(r.kat),
            r.selesai ? 'Selesai' : 'Draf ' + r.progress + '%'
        ], function (body) {
            var s1 = sect(body, 'Identitas');
            kvGrid(s1, [
                ['Nama komersial', r.komersial],
                ['Kabupaten/Kota', r.kabupaten],
                ['Kecamatan', r.kecamatan],
                ['Kode KBLI', r.kbli],
                ['Badan usaha', r.badanUsaha],
                ['Mulai beroperasi', r.tahunOperasi === null ? null : String(r.tahunOperasi)],
                ['Pengusaha', r.pengusaha.nama],
                ['Jenis kelamin pengusaha', r.pengusaha.jk],
                ['Umur pengusaha', r.pengusaha.umur === null ? null : r.pengusaha.umur + ' tahun'],
                ['Terakhir disimpan', r.updatedAt]
            ]);

            if (r.kegiatan || r.produk) {
                var s2 = sect(body, 'Kegiatan usaha');
                kvGrid(s2, [
                    ['Kegiatan utama', r.kegiatan],
                    ['Produk utama', r.produk]
                ]);
            }

            var s3 = sect(body, 'Pendapatan & pengeluaran');
            moneyRows(s3, [
                { label: 'Nilai produksi barang/jasa', value: fmtRpFull(r.produksiBarangJasa) },
                { label: 'Pendapatan lainnya', value: fmtRpFull(r.pendapatanLainnya) },
                { label: 'Total nilai produksi', value: fmtRpFull(r.produksi) },
                { label: 'Upah & gaji', value: fmtRpFull(r.upah) },
                { label: 'Biaya produksi', value: fmtRpFull(r.biayaProduksi) },
                { label: 'Pembelian barang dagangan', value: fmtRpFull(r.pembelian) },
                { label: 'Pengeluaran operasional', value: fmtRpFull(r.operasional) },
                { label: 'Pengeluaran non-operasional', value: fmtRpFull(r.nonOperasional) },
                { label: 'Total pengeluaran', value: fmtRpFull(r.pengeluaran) },
                { label: 'Perkiraan surplus', value: fmtRpFull(r.surplus), total: true }
            ]);
            if (r.persenOnline !== null) {
                s3.appendChild(el('p', 'stx-note', fmtPct(r.persenOnline) + ' dari pendapatan berasal dari penjualan daring.'));
            }

            var s4 = sect(body, 'Tenaga kerja & aset');
            kvGrid(s4, [
                ['Pekerja laki-laki', fmtN(r.pekerjaL)],
                ['Pekerja perempuan', fmtN(r.pekerjaP)],
                ['Total pekerja', fmtN(r.pekerja)],
                ['Aset tanah & bangunan', fmtRpFull(r.asetTanahBangunan)],
                ['Aset lainnya', fmtRpFull(r.asetLainnya)],
                ['Nilai total aset', fmtRpFull(r.aset)],
                ['Rentang aset', r.rangeAset === null ? null : DATA.rangeAset[r.rangeAset]],
                ['Luas tanah', fmtM2(r.luasTanah)]
            ]);

            var modalFilled = MODAL_PARTS.filter(function (m) { return r.modal[m.key] !== null; });
            if (modalFilled.length) {
                var s5 = sect(body, 'Struktur kepemilikan modal');
                simpleTable(s5, ['Sumber modal', 'Persentase'],
                    modalFilled.map(function (m) { return [m.label, fmtPct(r.modal[m.key])]; }), 1);
            }

            var s6 = sect(body, 'Profil digital, sertifikasi & kemitraan');
            simpleTable(s6, ['Indikator', 'Jawaban'],
                PROFIL.map(function (f) {
                    var v = r.flags[f.key];
                    return [f.label, v === null || v === undefined ? 'Belum dijawab' : (v ? 'Ya' : 'Tidak')];
                }), 1);

            if (r.catatan) {
                var s7 = sect(body, 'Catatan responden');
                var p = el('p', null, r.catatan);
                p.style.fontSize = '0.8125rem';
                p.style.color = 'var(--stx-ink-2)';
                p.style.lineHeight = '1.6';
                s7.appendChild(p);
            }
        });
    }

    /* ═══════════════ orchestration ═══════════════ */

    function rerenderData() {
        renderKpis();
        renderKategori();
        renderSkala();
        renderStruktur();
        renderTenaga();
        renderAset();
        renderProfil();
        renderTable();
    }
    function rerender() {
        renderFilters();
        rerenderData();
    }

    var resizeT = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeT);
        resizeT = setTimeout(function () {
            if (activePop) rerenderData(); else rerender();
        }, 180);
    });
    var lastDark = document.documentElement.classList.contains('dark');
    new MutationObserver(function (muts) {
        for (var i = 0; i < muts.length; i++) {
            if (muts[i].attributeName !== 'class') continue;
            var nowDark = document.documentElement.classList.contains('dark');
            if (nowDark !== lastDark) {
                lastDark = nowDark;
                if (activePop) rerenderData(); else rerender();
            }
            return;
        }
    }).observe(document.documentElement, { attributes: true });

    rerender();
})();
