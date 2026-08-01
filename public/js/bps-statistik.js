/**
 * Statistik SIBSTR Triwulanan — BPS-only dashboard.
 *
 * Reads window.__SIBSTR_STAT__ (embedded by the Blade view), keeps all
 * filtering client-side, and renders every chart as hand-rolled SVG/HTML:
 * no chart library, theme-aware via the .stx-root CSS custom properties.
 *
 * Every dynamic string goes into the DOM through textContent — survey
 * respondents control company/product names, so nothing is ever injected
 * as HTML.
 */
(function () {
    'use strict';

    var DATA = window.__SIBSTR_STAT__;
    if (!DATA || !document.getElementById('stx-kpis')) return;

    /* ═══════════════ constants ═══════════════ */

    var MONTH_LABEL = { jan: 'Jan', feb: 'Feb', mar: 'Mar', apr: 'Apr', mei: 'Mei', jun: 'Jun', jul: 'Jul', agu: 'Agu', sep: 'Sep', okt: 'Okt', nov: 'Nov', des: 'Des' };
    var MONTH_ORDER = { jan: 1, feb: 2, mar: 3, apr: 4, mei: 5, jun: 6, jul: 7, agu: 8, sep: 9, okt: 10, nov: 11, des: 12 };
    var TW_ROMAN = { 1: 'I', 2: 'II', 3: 'III', 4: 'IV' };
    var TW_MONTHS = { 1: ['jan', 'feb', 'mar'], 2: ['apr', 'mei', 'jun'], 3: ['jul', 'agu', 'sep'], 4: ['okt', 'nov', 'des'] };

    var BLOK5 = [
        { key: '501', label: 'Pesanan' },
        { key: '502', label: 'Produksi' },
        { key: '503', label: 'Kapasitas Produksi' },
        { key: '504', label: 'Tenaga Kerja' },
        { key: '505', label: 'Jam Kerja' },
        { key: '506', label: 'Waktu Pengiriman Pemasok', delivery: true },
        { key: '507', label: 'Persediaan Bahan Baku' }
    ];
    var ANS_LABEL = { naik: 'Naik', tetap: 'Tetap', turun: 'Turun', lebih_cepat: 'Lebih cepat', lebih_lambat: 'Lebih lambat' };
    function ansClass(v) {
        if (v === 'naik' || v === 'lebih_cepat') return 'pos';
        if (v === 'turun' || v === 'lebih_lambat') return 'neg';
        if (v === 'tetap') return 'neu';
        return 'none';
    }

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
            neg: cssVar('--stx-neg'), neu: cssVar('--stx-neu'), pos: cssVar('--stx-pos'),
            wash: cssVar('--stx-wash')
        };
    }

    /* ═══════════════ number formatting (id-ID) ═══════════════ */

    var nfFull = new Intl.NumberFormat('id-ID');
    var nfCompact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 });
    var nfPct = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });
    function fmtRp(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfCompact.format(v); }
    function fmtRpFull(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfFull.format(v); }
    function fmtN(v) { return v === null || v === undefined ? '—' : nfFull.format(v); }
    function fmtNc(v) { return v === null || v === undefined ? '—' : nfCompact.format(v); }
    function fmtPct(v) { return v === null || v === undefined ? '—' : nfPct.format(v) + '%'; }

    /* ═══════════════ state + filtering ═══════════════ */

    // kbliSel: checkbox multi-select (empty object = semua). Status defaults to
    // "Selesai" so the dashboard opens on final data only.
    var state = { tw: 'all', kbliSel: {}, status: 'done', sortKey: 'pendapatanTotal', sortDir: -1, excluded: {}, tableLimit: 10 };

    var EXCL_KEY = 'stx-excl-' + DATA.tahun;
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

    // Registry of distinct respondents (a company may report several quarters).
    // Identity only — figures are computed per slice, never cached here.
    var COMPANIES = (function () {
        var byUid = {};
        DATA.rows.forEach(function (r) {
            if (!byUid[r.uid]) {
                byUid[r.uid] = { uid: r.uid, name: r.perusahaan, kbli: r.kbli, kbli2: r.kbli2, kbliGroup: r.kbliGroup };
            }
            var c = byUid[r.uid];
            if (!c.name && r.perusahaan) c.name = r.perusahaan;
        });
        var list = Object.keys(byUid).map(function (k) { return byUid[k]; });
        list.sort(function (a, b) { return (a.kbliGroup || '').localeCompare(b.kbliGroup || '', 'id') || (a.name || '').localeCompare(b.name || '', 'id'); });
        return list;
    })();

    function kbliSelCount() { return Object.keys(state.kbliSel).length; }

    /**
     * Faceted filtering. `skip` names the single facet to ignore, so each
     * control can count its own options against every OTHER active filter
     * without narrowing itself — the rule that stops cross-filtering from
     * trapping the user in a choice they can no longer widen.
     * skip: 'tw' | 'kbli' | 'status' | 'company' | null (null = the live slice)
     */
    function rowsFor(skip) {
        return DATA.rows.filter(function (r) {
            if (skip !== 'company' && state.excluded[r.uid]) return false;
            if (skip !== 'tw' && state.tw !== 'all' && r.triwulan !== state.tw) return false;
            if (skip !== 'kbli' && kbliSelCount() && !state.kbliSel[r.kbli2]) return false;
            if (skip !== 'status' && !matchesStatus(r)) return false;
            return true;
        });
    }
    function filteredRows() { return rowsFor(null); }
    // every facet except triwulan, then pinned to one quarter
    function rowsOfQuarter(q) {
        return rowsFor('tw').filter(function (r) { return r.triwulan === q; });
    }

    /**
     * Respondents the other filters still admit — the pool the Perusahaan
     * picker lists. A company already dropped by Triwulan/KBLI/Status must not
     * sit there checked, claiming to be part of the aggregation.
     */
    function eligibleCompanies() {
        var seen = {};
        rowsFor('company').forEach(function (r) { seen[r.uid] = true; });
        return COMPANIES.filter(function (c) { return seen[c.uid]; });
    }
    function excludedEligible() {
        return eligibleCompanies().filter(function (c) { return state.excluded[c.uid]; }).length;
    }

    function sumField(rows, key) {
        var any = false, total = 0;
        rows.forEach(function (r) { if (r[key] !== null && r[key] !== undefined) { any = true; total += r[key]; } });
        return any ? total : null;
    }
    function countField(rows, key) {
        return rows.filter(function (r) { return r[key] !== null && r[key] !== undefined; }).length;
    }
    function avgField(rows, key) {
        var s = sumField(rows, key), n = countField(rows, key);
        return n ? s / n : null;
    }
    function distinctCompanies(rows) {
        var seen = {};
        rows.forEach(function (r) { seen[r.uid] = true; });
        return Object.keys(seen).length;
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

    /* ═══════════════ popover (one open at a time) ═══════════════ */

    // Panels live on a fixed layer at body level (inside the second .stx-root
    // so they inherit the theme variables) — never inside the frosted sticky
    // bar, whose backdrop-filter stacking corrupts descendant painting.
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
    // page scroll shifts the anchor away — close; scrolling inside the panel is fine
    document.addEventListener('scroll', function (e) {
        if (activePop && !(e.target && e.target.closest && e.target.closest('.stx-pop'))) closePop();
    }, true);

    /* ═══════════════ hero year filter (navigates — data is server-rendered per year) ═══════════════ */

    function renderYearFilter() {
        var bar = document.getElementById('stx-year-filter');
        if (!bar) return;
        clear(bar);
        var years = DATA.availableYears && DATA.availableYears.length ? DATA.availableYears : [DATA.tahun];
        ddSingle(bar, 'Tahun',
            years.map(function (y) { return { v: y, t: String(y) }; }),
            DATA.tahun,
            function (y) { window.location.href = window.location.pathname + '?tahun=' + encodeURIComponent(y); });
    }

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
                panel.style.width = 'min(16rem, 90vw)';
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

    function cardShell(cardId, title, sub) {
        var card = document.getElementById(cardId);
        clear(card);
        var head = el('div', 'stx-chart-head');
        var titles = el('div');
        titles.appendChild(el('div', 'stx-chart-title', title));
        if (sub) titles.appendChild(el('div', 'stx-chart-sub', sub));
        head.appendChild(titles);

        var toggle = el('div', 'stx-toggle');
        var btnChart = el('button', 'on', 'Grafik');
        var btnTable = el('button', null, 'Tabel');
        btnChart.type = 'button'; btnTable.type = 'button';
        toggle.appendChild(btnChart); toggle.appendChild(btnTable);
        head.appendChild(toggle);
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
    function simpleTable(pane, headers, rows, numericFrom) {
        clear(pane);
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

    function niceTicks(maxVal) {
        if (!isFinite(maxVal) || maxVal <= 0) maxVal = 1;
        var raw = maxVal / 4;
        var mag = Math.pow(10, Math.floor(Math.log(raw) / Math.LN10));
        var norm = raw / mag, step;
        if (norm <= 1) step = 1; else if (norm <= 2) step = 2; else if (norm <= 5) step = 5; else step = 10;
        step *= mag;
        var top = Math.ceil(maxVal / step) * step;
        var ticks = [];
        for (var v = 0; v <= top + step * 0.001; v += step) ticks.push(v);
        return { top: top, ticks: ticks };
    }

    function roundTopBarPath(x, y, w, h, r) {
        if (h <= 0) return '';
        r = Math.min(r, w / 2, h);
        return 'M' + x + ',' + (y + h) +
            ' L' + x + ',' + (y + r) +
            ' Q' + x + ',' + y + ' ' + (x + r) + ',' + y +
            ' L' + (x + w - r) + ',' + y +
            ' Q' + (x + w) + ',' + y + ' ' + (x + w) + ',' + (y + r) +
            ' L' + (x + w) + ',' + (y + h) + ' Z';
    }
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

    /* ═══════════════ month axis helpers ═══════════════ */

    function monthKeyParts(key) {
        var i = key.indexOf('_');
        return { year: parseInt(key.slice(0, i), 10), m: key.slice(i + 1) };
    }
    function monthKeySort(a, b) {
        var pa = monthKeyParts(a), pb = monthKeyParts(b);
        return (pa.year - pb.year) || (MONTH_ORDER[pa.m] - MONTH_ORDER[pb.m]);
    }
    function monthShort(key) {
        var p = monthKeyParts(key);
        return MONTH_LABEL[p.m] + ' ' + String(p.year).slice(2);
    }
    function monthLong(key) {
        var p = monthKeyParts(key);
        return MONTH_LABEL[p.m] + ' ' + p.year;
    }
    function activeMonthKeys() {
        var keys = [];
        if (state.tw === 'all') {
            DATA.quarters.forEach(function (q) {
                TW_MONTHS[q].forEach(function (m) { keys.push(DATA.tahun + '_' + m); });
            });
        } else {
            keys.push((DATA.tahun - 1) + '_des');
            TW_MONTHS[state.tw].forEach(function (m) { keys.push(DATA.tahun + '_' + m); });
        }
        keys.sort(monthKeySort);
        return keys;
    }

    /* ═══════════════ FILTER BAR ═══════════════ */

    function kbliGroups() {
        var groups = {};
        DATA.rows.forEach(function (r) {
            var key = r.kbli2 || '??';
            if (!groups[key]) groups[key] = { key: r.kbli2, label: r.kbliGroup, companies: {} };
            groups[key].companies[r.uid] = true;
        });
        return Object.keys(groups).sort().map(function (k) {
            var g = groups[k];
            g.n = Object.keys(g.companies).length;
            return g;
        });
    }

    function renderFilters() {
        var bar = document.getElementById('stx-filters');
        closePop();
        clear(bar);
        facetRefreshers = [];

        // ── Triwulan: counted against KBLI + Status + Perusahaan ──
        ddSingle(bar, 'Triwulan', function () {
            var pool = rowsFor('tw');
            var opts = [{ v: 'all', t: 'Semua triwulan', sub: distinctCompanies(pool) + ' perusahaan' }];
            [1, 2, 3, 4].forEach(function (q) {
                var has = DATA.quarters.indexOf(q) !== -1;
                var n = has ? distinctCompanies(pool.filter(function (r) { return r.triwulan === q; })) : 0;
                opts.push({
                    v: q,
                    t: 'Triwulan ' + TW_ROMAN[q],
                    // never disable the active option — it stays the way back out
                    disabled: state.tw !== q && !n,
                    sub: !has ? 'Belum ada data' : (n ? n + ' perusahaan' : 'Kosong pada filter lain')
                });
            });
            return opts;
        }, state.tw, function (v) { state.tw = v; rerender(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // ── Kelompok KBLI: counted against Triwulan + Status + Perusahaan ──
        ddMulti(bar, 'Kelompok KBLI', function () {
            var pool = rowsFor('kbli');
            return kbliGroups().map(function (g) {
                var n = distinctCompanies(pool.filter(function (r) { return (r.kbli2 || '??') === (g.key || '??'); }));
                return {
                    v: g.key, t: g.label, n: n,
                    sub: n ? n + ' perusahaan' : 'Kosong pada filter lain',
                    disabled: !n
                };
            });
        }, state.kbliSel, 'Semua KBLI',
            function () { rerenderData(); refreshFilterBar(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // ── Perusahaan picker (checkbox = included in aggregation) ──
        bar.appendChild(el('span', 'stx-filter-label', 'Perusahaan'));
        var pickWrap = el('div', 'stx-popwrap');
        var pickBtn = el('button', 'stx-popbtn' + (excludedEligible() ? ' active' : ''));
        pickBtn.type = 'button';
        var pickLabel = el('span', null, '');
        var pickBadge = el('span', 'n', '');
        function refreshPickBtn() {
            var total = eligibleCompanies().length, out = excludedEligible();
            pickLabel.textContent = out ? ((total - out) + ' dari ' + total + ' dipilih') : 'Semua perusahaan';
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

        // ── Status: counted against Triwulan + KBLI + Perusahaan ──
        // Subtitles carry the company count each status yields, so it is clear
        // up front that switching status also resizes the Perusahaan picker.
        ddSingle(bar, 'Status', function () {
            var pool = rowsFor('status');
            var done = pool.filter(function (r) { return r.selesai; });
            var draft = pool.filter(function (r) { return !r.selesai; });
            return [
                { v: 'done', t: 'Selesai', sub: 'hanya isian final · ' + distinctCompanies(done) + ' perusahaan' },
                { v: 'all', t: 'Semua status', sub: 'termasuk draf · ' + distinctCompanies(pool) + ' perusahaan' },
                { v: 'draft', t: 'Masih draf', sub: 'belum diselesaikan · ' + distinctCompanies(draft) + ' perusahaan' }
            ];
        }, state.status, function (v) { state.status = v; rerender(); });
    }

    /**
     * Company picker popover: every respondent with a checkbox. Unchecking a
     * company removes its data from every KPI, chart, and table immediately;
     * the choice persists for the session (per tahun).
     */
    function buildCompanyPicker(panel, refreshBtn) {
        var head = el('div', 'stx-pop-head');
        head.appendChild(el('div', 'stx-pop-title', 'Perusahaan dalam agregasi'));
        head.appendChild(el('div', 'stx-pop-sub', 'Hilangkan centang untuk mengeluarkan perusahaan dari seluruh perhitungan dashboard.'));
        var search = el('input', 'stx-pop-search');
        search.type = 'search';
        search.placeholder = 'Cari perusahaan atau KBLI…';
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
            var total = eligibleCompanies().length, hidden = COMPANIES.length - total;
            cnt.textContent = (total - excludedEligible()) + ' dari ' + total + ' perusahaan dihitung'
                + (hidden ? ' · ' + hidden + ' di luar filter lain' : '');
        }

        function apply() {
            saveExcluded();
            refreshBtn();
            refreshCnt();
            rerenderData();
            refreshFilterBar();
        }

        /**
         * Per-company figures for the current slice (its own exclusion aside),
         * so the quarters, report count, and rupiah beside each name match what
         * the dashboard is actually showing rather than the company's all-time
         * totals.
         */
        function sliceInfo() {
            var by = {};
            rowsFor('company').forEach(function (r) {
                var i = by[r.uid] || (by[r.uid] = { quarters: [], n: 0, pend: null });
                i.n++;
                if (i.quarters.indexOf(r.triwulan) === -1) i.quarters.push(r.triwulan);
                if (r.pendapatanTotal !== null && r.pendapatanTotal !== undefined) i.pend = (i.pend || 0) + r.pendapatanTotal;
            });
            Object.keys(by).forEach(function (k) { by[k].quarters.sort(); });
            return by;
        }

        var rows = [];
        function buildList() {
            clear(list);
            rows = [];
            var q = (search.value || '').toLowerCase();
            var lastGroup = null;
            var info = sliceInfo();
            eligibleCompanies().forEach(function (c) {
                var hay = ((c.name || '') + ' ' + (c.kbli || '') + ' ' + (c.kbliGroup || '')).toLowerCase();
                if (q && hay.indexOf(q) === -1) return;
                if (c.kbliGroup !== lastGroup) {
                    list.appendChild(el('div', 'stx-pop-group', c.kbliGroup));
                    lastGroup = c.kbliGroup;
                }
                var on = !state.excluded[c.uid];
                var row = el('button', 'stx-pop-row' + (on ? ' on' : ' off'));
                row.type = 'button';
                row.setAttribute('role', 'checkbox');
                row.setAttribute('aria-checked', on ? 'true' : 'false');
                var check = el('span', 'p-check', on ? '✓' : '');
                row.appendChild(check);
                var main = el('div', 'p-main');
                main.appendChild(el('div', 'p-name', c.name || 'Tanpa nama'));
                var si = info[c.uid] || { quarters: [], n: 0, pend: null };
                var meta = el('div', 'p-meta');
                meta.appendChild(el('span', null, 'KBLI ' + (c.kbli || '—')));
                meta.appendChild(el('span', null, 'TW ' + si.quarters.map(function (t) { return TW_ROMAN[t]; }).join(', ')));
                meta.appendChild(el('span', null, si.n + ' laporan'));
                main.appendChild(meta);
                row.appendChild(main);
                row.appendChild(el('span', 'p-val', fmtRp(si.pend)));
                row.addEventListener('click', function () {
                    var nowOn = !!state.excluded[c.uid]; // will become included?
                    if (nowOn) delete state.excluded[c.uid];
                    else state.excluded[c.uid] = true;
                    var incl = !state.excluded[c.uid];
                    row.classList.toggle('on', incl);
                    row.classList.toggle('off', !incl);
                    row.setAttribute('aria-checked', incl ? 'true' : 'false');
                    check.textContent = incl ? '✓' : '';
                    apply();
                });
                list.appendChild(row);
                rows.push(row);
            });
            if (!rows.length) {
                var none = el('div', 'stx-pop-group', q ? 'Tidak ada perusahaan yang cocok.' : 'Tidak ada perusahaan pada irisan filter ini.');
                none.style.padding = '0.8rem 0.6rem';
                list.appendChild(none);
            }
        }

        // Both bulk actions stay inside the status-eligible pool so they never
        // silently flip companies the user cannot currently see.
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

    /* ═══════════════ KPI ROW ═══════════════ */

    function sparkline(values, w, h) {
        var pts = values.filter(function (v) { return v !== null; });
        if (pts.length < 2) return null;
        var svg = svgEl('svg', { class: 'spark', width: w, height: h, viewBox: '0 0 ' + w + ' ' + h });
        var max = Math.max.apply(null, pts), min = Math.min.apply(null, pts);
        if (max === min) { max += 1; min -= 1; }
        var t = theme();
        var step = w / (values.length - 1);
        var d = '';
        values.forEach(function (v, i) {
            if (v === null) return;
            var x = i * step, y = 2 + (h - 4) * (1 - (v - min) / (max - min));
            d += (d ? ' L' : 'M') + x.toFixed(1) + ',' + y.toFixed(1);
        });
        svg.appendChild(svgEl('path', { d: d, fill: 'none', stroke: t.axis, 'stroke-width': 1.5, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }));
        // current period dot in accent
        for (var i = values.length - 1; i >= 0; i--) {
            if (values[i] !== null) {
                var x = i * step, y = 2 + (h - 4) * (1 - (values[i] - min) / (max - min));
                svg.appendChild(svgEl('circle', { cx: x, cy: y, r: 3, fill: t.s1, stroke: cssVar('--stx-surface'), 'stroke-width': 1.5 }));
                break;
            }
        }
        return svg;
    }

    function deltaBadge(cur, prev, upIsGood) {
        if (cur === null || prev === null || prev === 0) return null;
        var pct = (cur - prev) / Math.abs(prev) * 100;
        var span = el('span', 'k-delta');
        var dir = pct > 0.05 ? 'up' : (pct < -0.05 ? 'down' : 'flat');
        if (upIsGood === null) span.classList.add('flat');
        else span.classList.add(dir === 'flat' ? 'flat' : ((dir === 'up') === upIsGood ? 'up' : 'down'));
        var arrow = dir === 'up' ? '▲' : (dir === 'down' ? '▼' : '•');
        span.textContent = arrow + ' ' + nfPct.format(Math.abs(pct)) + '%';
        span.title = 'Dibanding triwulan sebelumnya';
        return span;
    }

    var KPI_ICONS = {
        melapor: 'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h1m4 0h1M9 12h1m4 0h1M9 16h1m4 0h1',
        pendapatan: 'M12 3a9 9 0 100 18 9 9 0 000-18zM14.5 9.3c-.4-.8-1.4-1.3-2.5-1.3-1.4 0-2.5.8-2.5 1.9s1.1 1.5 2.5 1.9c1.4.4 2.5.8 2.5 1.9s-1.1 1.9-2.5 1.9c-1.1 0-2.1-.5-2.5-1.3M12 6.7V8m0 8v1.3',
        pengeluaran: 'M3 8a2 2 0 012-2h13a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8zM3 8V6.5A1.5 1.5 0 014.5 5H16M14.5 12.5a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0z',
        surplus: 'M3 17l6-6 4 4 8-8M14 7h7v7',
        tk: 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 3.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zM22 21v-2a4 4 0 00-3-3.87M16 3.63a3.5 3.5 0 010 6.74',
        ekspor: 'M12 3a9 9 0 100 18 9 9 0 000-18zM3 12h18M12 3c2.3 2.6 3.6 5.7 3.6 9s-1.3 6.4-3.6 9c-2.3-2.6-3.6-5.7-3.6-9S9.7 5.6 12 3z'
    };

    function kpiIcon(name) {
        var span = el('span', 'k-ico');
        var svg = svgEl('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.8, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' });
        svg.appendChild(svgEl('path', { d: KPI_ICONS[name] || KPI_ICONS.melapor }));
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
        var left = el('div');
        if (def.delta) left.appendChild(def.delta);
        else if (def.sub) left.appendChild(el('span', 'k-sub', def.sub));
        foot.appendChild(left);
        if (def.spark) foot.appendChild(def.spark);
        tile.appendChild(foot);
        if (def.sub && def.delta) tile.appendChild(el('div', 'k-sub', def.sub));
        tile.title = def.tooltip || 'Klik untuk rincian';
        tile.addEventListener('click', def.onClick);
        container.appendChild(tile);
    }

    function quarterSeries(metric) {
        // per-quarter aggregate honoring the kbli/status filters (not tw)
        return DATA.quarters.map(function (q) { return metric(rowsOfQuarter(q)); });
    }

    function companyBreakdownModal(title, pills, rows, field, fmt) {
        openModal(title, pills, function (body) {
            var s = sect(body, 'Rincian per perusahaan');
            var sorted = rows.slice().sort(function (a, b) { return (b[field] || 0) - (a[field] || 0); });
            simpleTable(s, ['Perusahaan', 'TW', 'KBLI', 'Nilai'],
                sorted.map(function (r) {
                    return [r.perusahaan, TW_ROMAN[r.triwulan], r.kbli || '—', fmt(r[field])];
                }), 3);
            var filled = rows.filter(function (r) { return r[field] !== null && r[field] !== undefined; }).length;
            s.appendChild(el('p', 'stx-note', filled + ' dari ' + rows.length + ' laporan mengisi nilai ini. Nilai kosong (—) belum dilaporkan dan tidak dihitung.'));
        });
    }

    function renderKpis() {
        var wrap = document.getElementById('stx-kpis');
        clear(wrap);
        var rows = filteredRows();
        var singleTw = state.tw !== 'all';
        var prevRows = null;
        if (singleTw && DATA.quarters.indexOf(state.tw - 1) !== -1) prevRows = rowsOfQuarter(state.tw - 1);

        var pills = filterPills();

        // 1 — companies reporting (distinct respondents; a company may report several quarters)
        var done = rows.filter(function (r) { return r.selesai; }).length;
        kpiTile(wrap, {
            label: 'Perusahaan melapor',
            icon: 'melapor',
            value: fmtN(distinctCompanies(rows)),
            sub: rows.length + ' laporan · ' + done + ' selesai · ' + (rows.length - done) + ' draf',
            spark: sparkline(quarterSeries(function (rs) { return rs.length; }), 64, 26),
            onClick: function () {
                openModal('Perusahaan melapor', pills, function (body) {
                    var s = sect(body, 'Daftar perusahaan');
                    simpleTable(s, ['Perusahaan', 'TW', 'KBLI', 'Status'],
                        rows.map(function (r) { return [r.perusahaan, TW_ROMAN[r.triwulan], r.kbli || '—', r.selesai ? 'Selesai' : 'Draf']; }), 99);
                });
            }
        });

        // 2 — total revenue
        var pend = sumField(rows, 'pendapatanTotal');
        kpiTile(wrap, {
            label: 'Total pendapatan',
            icon: 'pendapatan',
            value: fmtRp(pend),
            tooltip: fmtRpFull(pend),
            delta: prevRows ? deltaBadge(pend, sumField(prevRows, 'pendapatanTotal'), true) : null,
            sub: countField(rows, 'pendapatanTotal') + ' dari ' + rows.length + ' mengisi',
            spark: sparkline(quarterSeries(function (rs) { return sumField(rs, 'pendapatanTotal'); }), 64, 26),
            onClick: function () { companyBreakdownModal('Total pendapatan', pills, rows, 'pendapatanTotal', fmtRpFull); }
        });

        // 3 — total expenses
        var peng = sumField(rows, 'pengeluaranTotal');
        kpiTile(wrap, {
            label: 'Total pengeluaran',
            icon: 'pengeluaran',
            value: fmtRp(peng),
            tooltip: fmtRpFull(peng),
            delta: prevRows ? deltaBadge(peng, sumField(prevRows, 'pengeluaranTotal'), null) : null,
            sub: countField(rows, 'pengeluaranTotal') + ' dari ' + rows.length + ' mengisi',
            spark: sparkline(quarterSeries(function (rs) { return sumField(rs, 'pengeluaranTotal'); }), 64, 26),
            onClick: function () { companyBreakdownModal('Total pengeluaran', pills, rows, 'pengeluaranTotal', fmtRpFull); }
        });

        // 4 — operating surplus proxy
        var sur = sumField(rows, 'surplus');
        kpiTile(wrap, {
            label: 'Surplus usaha (perkiraan)',
            icon: 'surplus',
            value: fmtRp(sur),
            tooltip: fmtRpFull(sur),
            delta: prevRows ? deltaBadge(sur, sumField(prevRows, 'surplus'), true) : null,
            sub: countField(rows, 'surplus') + ' laporan lengkap dihitung',
            spark: sparkline(quarterSeries(function (rs) { return sumField(rs, 'surplus'); }), 64, 26),
            onClick: function () {
                openModal('Surplus usaha (perkiraan)', pills, function (body) {
                    var s = sect(body, 'Rincian per perusahaan');
                    var sorted = rows.slice().sort(function (a, b) { return (b.surplus || 0) - (a.surplus || 0); });
                    simpleTable(s, ['Perusahaan', 'TW', 'Pendapatan', 'Pengeluaran', 'Surplus'],
                        sorted.map(function (r) { return [r.perusahaan, TW_ROMAN[r.triwulan], fmtRpFull(r.pendapatanTotal), fmtRpFull(r.pengeluaranTotal), fmtRpFull(r.surplus)]; }), 2);
                    s.appendChild(el('p', 'stx-note', 'Perkiraan surplus usaha = total pendapatan − (upah/gaji + biaya produksi + biaya operasional). Penambahan aset tetap (investasi) tidak dikurangkan. Hanya laporan dengan kedua sisi terisi yang dihitung.'));
                });
            }
        });

        // 5 — workers
        var tkRows = rows, tkNote = '';
        if (!singleTw && DATA.quarters.length > 1) {
            var lastQ = DATA.quarters[DATA.quarters.length - 1];
            tkRows = rows.filter(function (r) { return r.triwulan === lastQ; });
            tkNote = 'TW ' + TW_ROMAN[lastQ] + ' (triwulan terakhir)';
        }
        var tk = sumField(tkRows, 'tenagaKerja');
        kpiTile(wrap, {
            label: 'Tenaga kerja (rata-rata)',
            icon: 'tk',
            value: fmtNc(tk),
            tooltip: fmtN(tk),
            delta: prevRows ? deltaBadge(tk, sumField(prevRows, 'tenagaKerja'), true) : null,
            sub: tkNote || (countField(tkRows, 'tenagaKerja') + ' dari ' + tkRows.length + ' mengisi'),
            spark: sparkline(quarterSeries(function (rs) { return sumField(rs, 'tenagaKerja'); }), 64, 26),
            onClick: function () { companyBreakdownModal('Tenaga kerja (rata-rata per triwulan)', pills, tkRows, 'tenagaKerja', fmtN); }
        });

        // 6 — export share
        var eks = avgField(rows, 'eksporPct');
        kpiTile(wrap, {
            label: 'Rata-rata ekspor',
            icon: 'ekspor',
            value: fmtPct(eks),
            delta: prevRows ? deltaBadge(eks, avgField(prevRows, 'eksporPct'), true) : null,
            sub: 'rata-rata sederhana % produksi diekspor',
            spark: sparkline(quarterSeries(function (rs) { return avgField(rs, 'eksporPct'); }), 64, 26),
            onClick: function () { companyBreakdownModal('Persentase produksi yang diekspor', pills, rows, 'eksporPct', fmtPct); }
        });
    }

    function filterPills() {
        var pills = ['Tahun ' + DATA.tahun];
        pills.push(state.tw === 'all' ? 'Semua triwulan' : 'Triwulan ' + TW_ROMAN[state.tw]);
        var kbliKeys = Object.keys(state.kbliSel);
        if (kbliKeys.length === 1) {
            var g = DATA.rows.filter(function (r) { return r.kbli2 === kbliKeys[0]; })[0];
            pills.push(g ? g.kbliGroup : 'KBLI ' + kbliKeys[0]);
        } else if (kbliKeys.length > 1) {
            pills.push(kbliKeys.length + ' kelompok KBLI');
        }
        if (state.status === 'done') pills.push('Hanya selesai');
        if (state.status === 'draft') pills.push('Hanya draf');
        if (excludedEligible()) pills.push(excludedEligible() + ' perusahaan dikecualikan');
        return pills;
    }

    /* ═══════════════ CHART 1 — monthly production line ═══════════════ */

    function renderMonthly() {
        var shell = cardShell('card-monthly',
            'Nilai produksi dan pendapatan bulanan',
            'Blok IIIA (301 + 302) perusahaan industri, dalam rupiah' + (state.tw !== 'all' ? ' — termasuk bulan jembatan Des ' + (DATA.tahun - 1) : ''));
        var rows = filteredRows().filter(function (r) { return r.sektor === 'industri'; });
        var months = activeMonthKeys();
        var t = theme();

        // group series by KBLI (≤3 groups + fold), else single total
        var byGroup = {};
        rows.forEach(function (r) {
            var g = r.kbliGroup;
            if (!byGroup[g]) byGroup[g] = [];
            byGroup[g].push(r);
        });
        var groupNames = Object.keys(byGroup);
        var series = [];
        function seriesOf(name, list) {
            return {
                name: name,
                values: months.map(function (m) {
                    var any = false, sum = 0;
                    list.forEach(function (r) {
                        var v = r.monthlyNilai[m];
                        if (v !== null && v !== undefined) { any = true; sum += v; }
                    });
                    return any ? sum : null;
                })
            };
        }
        if (groupNames.length >= 2 && groupNames.length <= 4) {
            groupNames.sort().forEach(function (g) { series.push(seriesOf(g, byGroup[g])); });
        } else if (groupNames.length > 4) {
            var ranked = groupNames.map(function (g) {
                return { g: g, total: sumField(byGroup[g], 'pendapatanTotal') || 0 };
            }).sort(function (a, b) { return b.total - a.total; });
            var top = ranked.slice(0, 3).map(function (x) { return x.g; });
            top.forEach(function (g) { series.push(seriesOf(g, byGroup[g])); });
            var rest = [];
            ranked.slice(3).forEach(function (x) { rest = rest.concat(byGroup[x.g]); });
            series.push(seriesOf('Lainnya', rest));
        } else {
            series.push(seriesOf('Nilai produksi', rows));
        }

        var hasData = series.some(function (s) { return s.values.some(function (v) { return v !== null; }); });
        if (!hasData || !months.length) {
            emptyState(shell.chartPane, 'Belum ada nilai produksi bulanan pada irisan filter ini.');
            simpleTable(shell.tablePane, ['Bulan'], months.map(function (m) { return [monthLong(m)]; }), 1);
            return;
        }

        var colors = [t.s1, t.s2, t.s3, t.s4];
        if (series.length >= 2) {
            series.forEach(function (s, i) { legendItem(shell.legend, 'line', colors[i], s.name); });
        }

        // table twin
        simpleTable(shell.tablePane,
            ['Bulan'].concat(series.map(function (s) { return s.name; })),
            months.map(function (m, mi) {
                return [monthLong(m)].concat(series.map(function (s) { return fmtRpFull(s.values[mi]); }));
            }), 1);

        // geometry
        var W = Math.max(320, shell.chartPane.clientWidth || shell.card.clientWidth - 40);
        var H = 260, padL = 64, padR = 84, padT = 14, padB = 30;
        var maxV = 0;
        series.forEach(function (s) { s.values.forEach(function (v) { if (v !== null && v > maxV) maxV = v; }); });
        var scale = niceTicks(maxV);
        var plotW = W - padL - padR, plotH = H - padT - padB;
        var x = function (i) { return padL + (months.length === 1 ? plotW / 2 : plotW * i / (months.length - 1)); };
        var y = function (v) { return padT + plotH * (1 - v / scale.top); };

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Grafik garis nilai produksi bulanan');

        scale.ticks.forEach(function (tv) {
            svg.appendChild(svgEl('line', { x1: padL, x2: W - padR, y1: y(tv), y2: y(tv), stroke: tv === 0 ? t.axis : t.grid, 'stroke-width': 1 }));
            var lbl = svgEl('text', { x: padL - 8, y: y(tv) + 3.5, 'text-anchor': 'end', 'font-size': 11, fill: t.muted });
            lbl.textContent = nfCompact.format(tv);
            svg.appendChild(lbl);
        });
        months.forEach(function (m, i) {
            var lbl = svgEl('text', { x: x(i), y: H - 8, 'text-anchor': 'middle', 'font-size': 11, fill: t.muted });
            lbl.textContent = monthShort(m);
            svg.appendChild(lbl);
        });

        // single-series area wash
        if (series.length === 1) {
            var s0 = series[0], area = '', started = false;
            s0.values.forEach(function (v, i) {
                if (v === null) return;
                area += (started ? ' L' : 'M') + x(i) + ',' + y(v);
                started = true;
            });
            if (started) {
                var lastIdx = null, firstIdx = null;
                s0.values.forEach(function (v, i) { if (v !== null) { if (firstIdx === null) firstIdx = i; lastIdx = i; } });
                area += ' L' + x(lastIdx) + ',' + y(0) + ' L' + x(firstIdx) + ',' + y(0) + ' Z';
                svg.appendChild(svgEl('path', { d: area, fill: t.wash, stroke: 'none' }));
            }
        }

        series.forEach(function (s, si) {
            var d = '', prevNull = true;
            s.values.forEach(function (v, i) {
                if (v === null) { prevNull = true; return; }
                d += (prevNull ? ' M' : ' L') + x(i) + ',' + y(v);
                prevNull = false;
            });
            svg.appendChild(svgEl('path', { d: d.trim(), fill: 'none', stroke: colors[si], 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }));
            s.values.forEach(function (v, i) {
                if (v === null) return;
                svg.appendChild(svgEl('circle', { cx: x(i), cy: y(v), r: 4, fill: colors[si], stroke: t.surface, 'stroke-width': 2 }));
            });
            // endpoint direct label
            for (var i = s.values.length - 1; i >= 0; i--) {
                if (s.values[i] !== null) {
                    var lbl = svgEl('text', { x: x(i) + 10, y: y(s.values[i]) + 4, 'font-size': 11, 'font-weight': 700, fill: t.ink2 });
                    lbl.textContent = fmtRp(s.values[i]);
                    svg.appendChild(lbl);
                    break;
                }
            }
        });

        // crosshair + tooltip overlay
        var cross = svgEl('line', { y1: padT, y2: padT + plotH, stroke: t.axis, 'stroke-width': 1, visibility: 'hidden' });
        svg.appendChild(cross);
        var overlay = svgEl('rect', { x: padL, y: padT, width: plotW, height: plotH, fill: 'transparent', tabindex: 0 });
        overlay.style.cursor = 'crosshair';
        var focusIdx = null;
        function showAt(i, cx, cy) {
            cross.setAttribute('x1', x(i)); cross.setAttribute('x2', x(i));
            cross.setAttribute('visibility', 'visible');
            tipShow(cx, cy, monthLong(months[i]), series.map(function (s, si) {
                return { color: colors[si], label: s.name, value: fmtRpFull(s.values[i]) };
            }));
        }
        overlay.addEventListener('pointermove', function (e) {
            var rect = svg.getBoundingClientRect();
            var relX = (e.clientX - rect.left) * (W / rect.width) - padL;
            var i = Math.round(relX / (months.length === 1 ? 1 : plotW / (months.length - 1)));
            i = Math.max(0, Math.min(months.length - 1, i));
            focusIdx = i;
            showAt(i, e.clientX, e.clientY);
        });
        overlay.addEventListener('pointerleave', function () { cross.setAttribute('visibility', 'hidden'); tipHide(); focusIdx = null; });
        overlay.addEventListener('keydown', function (e) {
            if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
            e.preventDefault();
            focusIdx = focusIdx === null ? 0 : Math.max(0, Math.min(months.length - 1, focusIdx + (e.key === 'ArrowRight' ? 1 : -1)));
            var rect = svg.getBoundingClientRect();
            showAt(focusIdx, rect.left + x(focusIdx) * (rect.width / W), rect.top + padT);
        });
        overlay.addEventListener('blur', function () { cross.setAttribute('visibility', 'hidden'); tipHide(); focusIdx = null; });
        svg.appendChild(overlay);

        shell.chartPane.appendChild(svg);
    }

    /* ═══════════════ CHART 2 — revenue vs expenses per quarter ═══════════════ */

    function renderQuarter() {
        var shell = cardShell('card-quarter',
            'Pendapatan vs pengeluaran per triwulan',
            state.tw === 'all' ? 'Klik kolom untuk rincian per perusahaan' : 'Triwulan terpilih ditonjolkan — klik kolom untuk rincian');
        var t = theme();
        var quarters = DATA.quarters;
        if (!quarters.length) { emptyState(shell.chartPane, 'Belum ada data triwulanan.'); return; }

        var data = quarters.map(function (q) {
            var rs = rowsOfQuarter(q);
            return { q: q, rows: rs, pend: sumField(rs, 'pendapatanTotal'), peng: sumField(rs, 'pengeluaranTotal') };
        });

        legendItem(shell.legend, 'swatch', t.s1, 'Pendapatan');
        legendItem(shell.legend, 'swatch', t.s2, 'Pengeluaran');

        simpleTable(shell.tablePane, ['Triwulan', 'Pendapatan', 'Pengeluaran', 'Selisih'],
            data.map(function (d) {
                var diff = (d.pend !== null && d.peng !== null) ? d.pend - d.peng : null;
                return ['TW ' + TW_ROMAN[d.q], fmtRpFull(d.pend), fmtRpFull(d.peng), fmtRpFull(diff)];
            }), 1);

        var W = Math.max(300, shell.chartPane.clientWidth || 420);
        var H = 250, padL = 58, padR = 12, padT = 20, padB = 30;
        var plotW = W - padL - padR, plotH = H - padT - padB;
        var maxV = 0;
        data.forEach(function (d) { [d.pend, d.peng].forEach(function (v) { if (v !== null && v > maxV) maxV = v; }); });
        var scale = niceTicks(maxV);
        var y = function (v) { return padT + plotH * (1 - v / scale.top); };

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Grafik kolom pendapatan dan pengeluaran per triwulan');

        scale.ticks.forEach(function (tv) {
            svg.appendChild(svgEl('line', { x1: padL, x2: W - padR, y1: y(tv), y2: y(tv), stroke: tv === 0 ? t.axis : t.grid, 'stroke-width': 1 }));
            var lbl = svgEl('text', { x: padL - 8, y: y(tv) + 3.5, 'text-anchor': 'end', 'font-size': 11, fill: t.muted });
            lbl.textContent = nfCompact.format(tv);
            svg.appendChild(lbl);
        });

        var slot = plotW / data.length;
        var barW = Math.min(24, (slot - 30) / 2);
        data.forEach(function (d, i) {
            var cx = padL + slot * i + slot / 2;
            var dim = state.tw !== 'all' && state.tw !== d.q;
            var lbl = svgEl('text', { x: cx, y: H - 8, 'text-anchor': 'middle', 'font-size': 11, fill: dim ? t.muted : t.ink2, 'font-weight': dim ? 400 : 700 });
            lbl.textContent = 'TW ' + TW_ROMAN[d.q];
            svg.appendChild(lbl);

            [{ v: d.pend, c: t.s1, name: 'Pendapatan', off: -barW - 1 }, { v: d.peng, c: t.s2, name: 'Pengeluaran', off: 1 }].forEach(function (b) {
                if (b.v === null) return;
                var bx = cx + b.off, by = y(b.v), bh = padT + plotH - by;
                var p = svgEl('path', { d: roundTopBarPath(bx, by, barW, Math.max(bh, 1.5), 4), fill: b.c, opacity: dim ? 0.32 : 1 });
                svg.appendChild(p);
                if (!dim) {
                    var cap = svgEl('text', { x: bx + barW / 2, y: by - 6, 'text-anchor': 'middle', 'font-size': 10.5, 'font-weight': 700, fill: t.ink2 });
                    cap.textContent = fmtNc(b.v);
                    svg.appendChild(cap);
                }
            });

            // hit target = the whole quarter slot
            var hit = svgEl('rect', { x: padL + slot * i, y: padT, width: slot, height: plotH, fill: 'transparent' });
            hit.style.cursor = 'pointer';
            hit.setAttribute('tabindex', 0);
            hit.setAttribute('role', 'button');
            hit.setAttribute('aria-label', 'Rincian triwulan ' + TW_ROMAN[d.q]);
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, 'Triwulan ' + TW_ROMAN[d.q] + ' ' + DATA.tahun, [
                    { color: t.s1, label: 'Pendapatan', value: fmtRpFull(d.pend) },
                    { color: t.s2, label: 'Pengeluaran', value: fmtRpFull(d.peng) },
                    { label: 'Perusahaan', value: String(d.rows.length) }
                ]);
            });
            hit.addEventListener('pointerleave', tipHide);
            function openQ() {
                tipHide();
                openModal('Triwulan ' + TW_ROMAN[d.q] + ' ' + DATA.tahun, filterPills(), function (body) {
                    var s1 = sect(body, 'Ringkasan');
                    moneyRows(s1, [
                        { label: 'Total pendapatan', value: fmtRpFull(d.pend) },
                        { label: 'Total pengeluaran', value: fmtRpFull(d.peng) },
                        { label: 'Selisih', value: fmtRpFull(d.pend !== null && d.peng !== null ? d.pend - d.peng : null), total: true }
                    ]);
                    var s2 = sect(body, 'Per perusahaan');
                    simpleTable(s2, ['Perusahaan', 'Pendapatan', 'Pengeluaran', 'Surplus'],
                        d.rows.map(function (r) { return [r.perusahaan, fmtRpFull(r.pendapatanTotal), fmtRpFull(r.pengeluaranTotal), fmtRpFull(r.surplus)]; }), 1);
                });
            }
            hit.addEventListener('click', openQ);
            hit.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openQ(); } });
            svg.appendChild(hit);
        });

        shell.chartPane.appendChild(svg);
    }

    /* ═══════════════ CHART 3 — revenue composition ═══════════════ */

    function renderCompo() {
        var shell = cardShell('card-compo',
            'Komposisi pendapatan',
            'Sumber pendapatan pada irisan filter aktif');
        var t = theme();
        var rows = filteredRows();

        var parts = [
            { label: 'Produksi (301)', color: t.s1, v: sumField(rows, 'pendapatanProduk') },
            { label: 'Pendapatan lainnya (302)', color: t.s2, v: sumField(rows, 'pendapatanLainnya') },
            { label: 'Penjualan barang/jasa non-industri (303)', color: t.s3, v: sumField(rows, 'penjualan') },
            { label: 'Royalti, bunga, dividen (304)', color: t.s4, v: sumField(rows, 'pendapatanRoyalti') }
        ].filter(function (p) { return p.v !== null && p.v > 0; });

        var total = parts.reduce(function (a, p) { return a + p.v; }, 0);

        simpleTable(shell.tablePane, ['Sumber', 'Nilai', 'Porsi'],
            parts.map(function (p) { return [p.label, fmtRpFull(p.v), total ? nfPct.format(p.v / total * 100) + '%' : '—']; }), 1);

        if (!parts.length || total <= 0) {
            emptyState(shell.chartPane, 'Belum ada rincian pendapatan pada irisan filter ini.');
            return;
        }

        parts.forEach(function (p) { legendItem(shell.legend, 'swatch', p.color, p.label); });

        var W = Math.max(300, shell.chartPane.clientWidth || 420);
        var H = 96, barY = 16, barH = 26;
        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Komposisi pendapatan');

        var gap = 2, xCur = 0;
        var innerW = W - (parts.length - 1) * gap;
        parts.forEach(function (p) {
            var w = innerW * (p.v / total);
            var r = svgEl('rect', { x: xCur, y: barY, width: Math.max(w, 1), height: barH, rx: 4, fill: p.color });
            svg.appendChild(r);
            var pctText = nfPct.format(p.v / total * 100) + '%';
            if (w >= 52) {
                // in-fill label: white or ink by luminance is overkill here — all four fills are mid-tone; white passes on s1/s2, ink on s3/s4
                var useInk = (p.color === t.s3 || p.color === t.s4);
                var lbl = svgEl('text', { x: xCur + w / 2, y: barY + barH / 2 + 4, 'text-anchor': 'middle', 'font-size': 11.5, 'font-weight': 700, fill: useInk ? '#3b2f16' : '#ffffff' });
                lbl.textContent = pctText;
                svg.appendChild(lbl);
            }
            var hit = svgEl('rect', { x: xCur, y: 4, width: Math.max(w, 24), height: barH + 24, fill: 'transparent' });
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, p.label, [
                    { color: p.color, label: 'Nilai', value: fmtRpFull(p.v) },
                    { label: 'Porsi', value: pctText }
                ]);
            });
            hit.addEventListener('pointerleave', tipHide);
            svg.appendChild(hit);
            xCur += w + gap;
        });

        var totalLbl = svgEl('text', { x: 0, y: barY + barH + 26, 'font-size': 11.5, fill: t.ink2 });
        totalLbl.textContent = 'Total: ' + fmtRpFull(total);
        totalLbl.setAttribute('font-weight', 700);
        svg.appendChild(totalLbl);

        shell.chartPane.appendChild(svg);
        var note = el('p', 'stx-note', 'Nilai per komponen juga tersedia pada tampilan Tabel.');
        shell.chartPane.appendChild(note);
    }

    /* ═══════════════ CHART 4 — revenue per KBLI group ═══════════════ */

    function renderKbli() {
        var shell = cardShell('card-kbli',
            'Pendapatan per kelompok KBLI',
            'Golongan pokok (2 digit) — klik baris untuk daftar perusahaan');
        var t = theme();
        var rows = filteredRows();

        var groups = {};
        rows.forEach(function (r) {
            var key = r.kbliGroup;
            if (!groups[key]) groups[key] = { label: key, rows: [], companies: {} };
            groups[key].rows.push(r);
            groups[key].companies[r.uid] = true;
        });
        var list = Object.keys(groups).map(function (k) {
            var g = groups[k];
            return {
                label: g.label,
                rows: g.rows,
                nComp: Object.keys(g.companies).length,
                pend: sumField(g.rows, 'pendapatanTotal'),
                tk: sumField(g.rows, 'tenagaKerja')
            };
        }).sort(function (a, b) { return (b.pend || 0) - (a.pend || 0); });

        simpleTable(shell.tablePane, ['Kelompok KBLI', 'Perusahaan', 'Pendapatan', 'Tenaga kerja'],
            list.map(function (g) { return [g.label, String(g.nComp), fmtRpFull(g.pend), fmtN(g.tk)]; }), 1);

        if (!list.length) { emptyState(shell.chartPane, 'Tidak ada data pada irisan filter ini.'); return; }

        var W = Math.max(360, shell.chartPane.clientWidth || 640);
        var rowH = 42, padT2 = 6;
        var labelW = Math.min(280, Math.max(170, W * 0.3));
        var valueW = 92;
        var plotW = W - labelW - valueW - 16;
        var H = padT2 + list.length * rowH + 6;
        var maxV = Math.max.apply(null, list.map(function (g) { return g.pend || 0; }).concat([1]));

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Pendapatan per kelompok KBLI');

        list.forEach(function (g, i) {
            var cy = padT2 + i * rowH + rowH / 2;
            var name = svgEl('text', { x: 0, y: cy + 4, 'font-size': 12, 'font-weight': 600, fill: t.ink2 });
            name.textContent = g.label.length > 34 ? g.label.slice(0, 33) + '…' : g.label;
            svg.appendChild(name);
            var sub = svgEl('text', { x: 0, y: cy + 17, 'font-size': 10, fill: t.muted });
            sub.textContent = g.nComp + ' perusahaan';
            svg.appendChild(sub);

            var bw = (g.pend || 0) / maxV * plotW;
            var barH = 18;
            svg.appendChild(svgEl('rect', { x: labelW, y: cy - barH / 2, width: plotW, height: barH, rx: 4, fill: t.grid, opacity: 0.45 }));
            if (bw > 0) svg.appendChild(svgEl('path', { d: roundRightBarPath(labelW, cy - barH / 2, Math.max(bw, 2), barH, 4), fill: t.s1 }));
            var val = svgEl('text', { x: labelW + plotW + 8, y: cy + 4, 'font-size': 11.5, 'font-weight': 700, fill: t.ink });
            val.textContent = fmtRp(g.pend);
            svg.appendChild(val);

            var hit = svgEl('rect', { x: 0, y: padT2 + i * rowH, width: W, height: rowH, fill: 'transparent' });
            hit.style.cursor = 'pointer';
            hit.setAttribute('tabindex', 0);
            hit.setAttribute('role', 'button');
            hit.setAttribute('aria-label', 'Rincian ' + g.label);
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, g.label, [
                    { color: t.s1, label: 'Pendapatan', value: fmtRpFull(g.pend) },
                    { label: 'Perusahaan', value: String(g.nComp) },
                    { label: 'Tenaga kerja', value: fmtN(g.tk) }
                ]);
            });
            hit.addEventListener('pointerleave', tipHide);
            function openG() {
                tipHide();
                openModal(g.label, filterPills(), function (body) {
                    var s1 = sect(body, 'Ringkasan kelompok');
                    kvGrid(s1, [
                        ['Perusahaan', String(g.nComp)],
                        ['Pendapatan', fmtRp(g.pend)],
                        ['Tenaga kerja', fmtN(g.tk)]
                    ]);
                    var s2 = sect(body, 'Perusahaan dalam kelompok');
                    simpleTable(s2, ['Perusahaan', 'TW', 'KBLI', 'Pendapatan', 'Surplus'],
                        g.rows.map(function (r) { return [r.perusahaan, TW_ROMAN[r.triwulan], r.kbli || '—', fmtRpFull(r.pendapatanTotal), fmtRpFull(r.surplus)]; }), 3);
                });
            }
            hit.addEventListener('click', openG);
            hit.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openG(); } });
            svg.appendChild(hit);
        });

        shell.chartPane.appendChild(svg);
    }

    /* ═══════════════ CHART 4b — Kategori C: Industri Pengolahan ═══════════════ */

    // Vertical bar with the rounded end at the value side (supports negatives).
    function roundBarPathV(x, yTop, w, h, r, roundedEnd) {
        if (h <= 0) return '';
        r = Math.min(r, w / 2, h);
        if (roundedEnd === 'top') return roundTopBarPath(x, yTop, w, h, r);
        // rounded bottom
        return 'M' + x + ',' + yTop +
            ' L' + (x + w) + ',' + yTop +
            ' L' + (x + w) + ',' + (yTop + h - r) +
            ' Q' + (x + w) + ',' + (yTop + h) + ' ' + (x + w - r) + ',' + (yTop + h) +
            ' L' + (x + r) + ',' + (yTop + h) +
            ' Q' + x + ',' + (yTop + h) + ' ' + x + ',' + (yTop + h - r) + ' Z';
    }

    function niceTicksSigned(minVal, maxVal) {
        if (!isFinite(maxVal) || maxVal < 0) maxVal = 0;
        if (!isFinite(minVal) || minVal > 0) minVal = 0;
        if (maxVal === 0 && minVal === 0) maxVal = 1;
        var span = maxVal - minVal;
        var raw = span / 4;
        var mag = Math.pow(10, Math.floor(Math.log(raw) / Math.LN10));
        var norm = raw / mag, step;
        if (norm <= 1) step = 1; else if (norm <= 2) step = 2; else if (norm <= 5) step = 5; else step = 10;
        step *= mag;
        var top = Math.ceil(maxVal / step) * step;
        var bot = Math.floor(minVal / step) * step;
        var ticks = [];
        for (var v = bot; v <= top + step * 0.001; v += step) ticks.push(v);
        return { top: top, bot: bot, ticks: ticks };
    }

    function industriName(kbliGroup) {
        var i = (kbliGroup || '').indexOf('·');
        return i > -1 ? kbliGroup.slice(i + 1).trim() : (kbliGroup || '—');
    }

    function renderIndustri() {
        var shell = cardShell('card-industri',
            'Kategori C — Industri Pengolahan',
            'KBLI 10–33 · Nilai tambah = nilai produksi − biaya produksi (biaya produksi + biaya operasional + pembelian aset)');
        var t = theme();
        var rows = filteredRows().filter(function (r) { return r.sektor === 'industri'; });

        var totNP = sumField(rows, 'nilaiProduksi');
        var totBP = sumField(rows, 'biayaTotal');
        var totNT = sumField(rows, 'nilaiTambah');

        // per-KBLI-group aggregates
        var groups = {};
        rows.forEach(function (r) {
            var key = r.kbli2 || '??';
            if (!groups[key]) groups[key] = { kbli2: r.kbli2 || '—', label: r.kbliGroup, rows: [], companies: {} };
            groups[key].rows.push(r);
            groups[key].companies[r.uid] = true;
        });
        var glist = Object.keys(groups).sort().map(function (k) {
            var g = groups[k];
            return {
                kbli2: g.kbli2, label: g.label, name: industriName(g.label),
                n: Object.keys(g.companies).length, rows: g.rows,
                np: sumField(g.rows, 'nilaiProduksi'),
                bp: sumField(g.rows, 'biayaTotal'),
                nt: sumField(g.rows, 'nilaiTambah')
            };
        });

        // table twin — detail table like the questionnaire spec example
        var tRows = glist.map(function (g) {
            return [g.kbli2, g.name, String(g.n), fmtRpFull(g.np), fmtRpFull(g.bp), fmtRpFull(g.nt)];
        });
        tRows.push(['TOTAL', 'Seluruh industri pengolahan', String(distinctCompanies(rows)), fmtRpFull(totNP), fmtRpFull(totBP), fmtRpFull(totNT)]);
        simpleTable(shell.tablePane, ['KBLI', 'Kelompok Industri', 'Perusahaan', 'Nilai Produksi', 'Biaya Produksi', 'Nilai Tambah'], tRows, 2);

        // KPI strip between header and legend
        var strip = el('div', 'stx-strip');
        [['Total perusahaan', fmtN(distinctCompanies(rows)), rows.length + ' laporan'],
         ['Nilai produksi', fmtRp(totNP), countField(rows, 'nilaiProduksi') + ' dari ' + rows.length + ' mengisi'],
         ['Biaya produksi', fmtRp(totBP), 'termasuk pembelian aset'],
         ['Nilai tambah', fmtRp(totNT), 'nilai produksi − biaya produksi']].forEach(function (d) {
            var it = el('div', 's-item');
            it.appendChild(el('div', 's-label', d[0]));
            var val = el('div', 's-value', d[1]);
            val.title = d[0];
            it.appendChild(val);
            it.appendChild(el('div', 's-sub', d[2]));
            strip.appendChild(it);
        });
        shell.card.insertBefore(strip, shell.legend);

        if (!rows.length) {
            emptyState(shell.chartPane, 'Tidak ada perusahaan industri pengolahan pada irisan filter ini.');
            return;
        }

        legendItem(shell.legend, 'swatch', t.s1, 'Nilai produksi');
        legendItem(shell.legend, 'swatch', t.s2, 'Biaya produksi');
        legendItem(shell.legend, 'swatch', t.s3, 'Nilai tambah');

        var split = el('div', 'stx-split');

        // ── left: kinerja grouped columns (NP / BP / NT) ──
        var left = el('div');
        left.appendChild(el('div', 'sp-title', 'Kinerja industri pengolahan'));
        var Wl = Math.max(280, Math.floor((shell.chartPane.clientWidth || 700) / 2) - 30);
        var Hl = 230, padL = 62, padR = 12, padT = 18, padB = 26;
        var vals = [
            { name: 'Nilai produksi', v: totNP, c: t.s1 },
            { name: 'Biaya produksi', v: totBP, c: t.s2 },
            { name: 'Nilai tambah', v: totNT, c: t.s3 }
        ];
        var maxV = 0, minV = 0;
        vals.forEach(function (d) { if (d.v !== null) { maxV = Math.max(maxV, d.v); minV = Math.min(minV, d.v); } });
        var scale = niceTicksSigned(minV, maxV);
        var plotW = Wl - padL - padR, plotH = Hl - padT - padB;
        var y = function (v) { return padT + plotH * (1 - (v - scale.bot) / (scale.top - scale.bot)); };

        var svgL = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + Wl + ' ' + Hl, height: Hl, role: 'img' });
        svgL.setAttribute('aria-label', 'Kinerja industri pengolahan');
        scale.ticks.forEach(function (tv) {
            svgL.appendChild(svgEl('line', { x1: padL, x2: Wl - padR, y1: y(tv), y2: y(tv), stroke: tv === 0 ? t.axis : t.grid, 'stroke-width': 1 }));
            var lbl = svgEl('text', { x: padL - 8, y: y(tv) + 3.5, 'text-anchor': 'end', 'font-size': 10.5, fill: t.muted });
            lbl.textContent = nfCompact.format(tv);
            svgL.appendChild(lbl);
        });
        var slot = plotW / vals.length;
        var barW = Math.min(24, slot - 26);
        vals.forEach(function (d, i) {
            var cx = padL + slot * i + slot / 2;
            var lbl = svgEl('text', { x: cx, y: Hl - 6, 'text-anchor': 'middle', 'font-size': 10, fill: t.ink2, 'font-weight': 600 });
            lbl.textContent = d.name;
            svgL.appendChild(lbl);
            if (d.v === null) return;
            var y0 = y(0), yv = y(d.v);
            var top = Math.min(y0, yv), h = Math.max(Math.abs(y0 - yv), 1.5);
            svgL.appendChild(svgEl('path', { d: roundBarPathV(cx - barW / 2, top, barW, h, 4, d.v >= 0 ? 'top' : 'bottom'), fill: d.c }));
            var cap = svgEl('text', { x: cx, y: d.v >= 0 ? top - 5 : top + h + 11, 'text-anchor': 'middle', 'font-size': 10.5, 'font-weight': 700, fill: t.ink2 });
            cap.textContent = fmtRp(d.v);
            svgL.appendChild(cap);
            var hit = svgEl('rect', { x: padL + slot * i, y: padT, width: slot, height: plotH, fill: 'transparent' });
            hit.style.cursor = 'pointer';
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, d.name, [{ color: d.c, label: 'Total', value: fmtRpFull(d.v) }]);
            });
            hit.addEventListener('pointerleave', tipHide);
            hit.addEventListener('click', function () {
                tipHide();
                openModal('Kinerja industri pengolahan', filterPills(), function (body) {
                    var s = sect(body, 'Per perusahaan (Kategori C)');
                    simpleTable(s, ['Perusahaan', 'TW', 'Nilai Produksi', 'Biaya Produksi', 'Nilai Tambah'],
                        rows.slice().sort(function (a, b) { return (b.nilaiTambah || 0) - (a.nilaiTambah || 0); })
                            .map(function (r) { return [r.perusahaan, TW_ROMAN[r.triwulan], fmtRpFull(r.nilaiProduksi), fmtRpFull(r.biayaTotal), fmtRpFull(r.nilaiTambah)]; }), 2);
                });
            });
            svgL.appendChild(hit);
        });
        left.appendChild(svgL);
        split.appendChild(left);

        // ── right: Top 5 nilai tambah per KBLI 2-digit (ranked rows) ──
        var right = el('div');
        right.appendChild(el('div', 'sp-title', 'Top 5 nilai tambah per KBLI'));
        var top5 = glist.filter(function (g) { return g.nt !== null; })
            .sort(function (a, b) { return (b.nt || 0) - (a.nt || 0); })
            .slice(0, 5);
        if (!top5.length) {
            right.appendChild(el('p', 'stx-note', 'Belum ada nilai tambah yang dapat dihitung.'));
        } else {
            var maxAbs = Math.max.apply(null, top5.map(function (g) { return Math.abs(g.nt || 0); }).concat([1]));
            var rank = el('div', 'stx-rank');
            top5.forEach(function (g, i) {
                var row = el('button', 'r-row');
                row.type = 'button';
                row.setAttribute('aria-label', 'Rincian ' + g.label);
                row.appendChild(el('span', 'r-badge', String(i + 1)));

                var main = el('div', 'r-main');
                var headR = el('div', 'r-head');
                headR.appendChild(el('span', 'r-name', g.kbli2 + ' · ' + g.name));
                var neg = (g.nt || 0) < 0;
                headR.appendChild(el('span', 'r-val' + (neg ? ' neg' : ''), fmtRp(g.nt)));
                main.appendChild(headR);

                var track = el('div', 'r-track');
                var fill = el('div', 'r-fill' + (neg ? ' neg' : ''));
                fill.style.width = Math.max(2, Math.abs(g.nt || 0) / maxAbs * 100) + '%';
                track.appendChild(fill);
                main.appendChild(track);

                main.appendChild(el('div', 'r-sub',
                    g.n + ' perusahaan · Produksi ' + fmtRp(g.np) + ' · Biaya ' + fmtRp(g.bp) + (neg ? ' · nilai tambah negatif' : '')));
                row.appendChild(main);

                row.addEventListener('pointermove', function (e) {
                    tipShow(e.clientX, e.clientY, g.label, [
                        { color: t.s1, label: 'Nilai produksi', value: fmtRpFull(g.np) },
                        { color: t.s2, label: 'Biaya produksi', value: fmtRpFull(g.bp) },
                        { color: t.s3, label: 'Nilai tambah', value: fmtRpFull(g.nt) },
                        { label: 'Perusahaan', value: String(g.n) }
                    ]);
                });
                row.addEventListener('pointerleave', tipHide);
                row.addEventListener('click', function () {
                    tipHide();
                    openModal(g.label, filterPills(), function (body) {
                        var s1 = sect(body, 'Ringkasan kelompok');
                        moneyRows(s1, [
                            { label: 'Nilai produksi', value: fmtRpFull(g.np) },
                            { label: 'Biaya produksi (termasuk aset)', value: fmtRpFull(g.bp) },
                            { label: 'Nilai tambah', value: fmtRpFull(g.nt), total: true }
                        ]);
                        var s2 = sect(body, 'Perusahaan dalam kelompok');
                        simpleTable(s2, ['Perusahaan', 'TW', 'Nilai Produksi', 'Biaya Produksi', 'Nilai Tambah'],
                            g.rows.map(function (r) { return [r.perusahaan, TW_ROMAN[r.triwulan], fmtRpFull(r.nilaiProduksi), fmtRpFull(r.biayaTotal), fmtRpFull(r.nilaiTambah)]; }), 2);
                    });
                });
                rank.appendChild(row);
            });
            right.appendChild(rank);
        }
        split.appendChild(right);
        shell.chartPane.appendChild(split);
        shell.chartPane.appendChild(el('p', 'stx-note', 'Rincian per KBLI 2-digit (tabel seperti contoh kuesioner) tersedia pada tampilan Tabel.'));
    }

    /* ═══════════════ CHART 5 — Blok V sentiment (diverging) ═══════════════ */

    function blok5Counts(rows, key, period) {
        var pos = 0, neu = 0, neg = 0, answers = [];
        rows.forEach(function (r) {
            var v = r.blok5 && r.blok5[key] ? r.blok5[key][period] : null;
            if (!v) return;
            var c = ansClass(v);
            if (c === 'pos') pos++; else if (c === 'neg') neg++; else if (c === 'neu') neu++;
            answers.push({ perusahaan: r.perusahaan, tw: r.triwulan, v: v });
        });
        return { pos: pos, neu: neu, neg: neg, n: pos + neu + neg, answers: answers };
    }

    function divergingRow(container, comp, counts, t) {
        var row = el('div', 'stx-div-row');
        row.setAttribute('tabindex', 0);
        row.setAttribute('role', 'button');
        var lbl = el('div', 'stx-div-label', comp.label);
        row.appendChild(lbl);

        var track = el('div');
        track.style.position = 'relative';
        track.style.height = '18px';
        var mid = el('div');
        mid.style.position = 'absolute';
        mid.style.left = '50%'; mid.style.top = '-3px'; mid.style.bottom = '-3px';
        mid.style.width = '1px'; mid.style.background = 'var(--stx-axis)';
        track.appendChild(mid);

        if (counts.n > 0) {
            var scaleW = 46; // percent of half-width used at 100%
            var pPos = counts.pos / counts.n, pNeu = counts.neu / counts.n, pNeg = counts.neg / counts.n;
            var neuHalf = pNeu / 2 * scaleW;
            function seg(leftPct, widthPct, color, radiusSide) {
                if (widthPct <= 0) return;
                var d = el('div');
                d.style.position = 'absolute';
                d.style.top = '0'; d.style.height = '100%';
                d.style.left = leftPct + '%';
                d.style.width = 'max(' + widthPct + '%, 3px)';
                d.style.background = color;
                d.style.borderRadius = radiusSide === 'l' ? '4px 0 0 4px' : (radiusSide === 'r' ? '0 4px 4px 0' : '0');
                d.style.boxShadow = '0 0 0 1px var(--stx-surface)';
                track.appendChild(d);
            }
            seg(50 - neuHalf - pNeg * scaleW, pNeg * scaleW, t.neg, 'l');
            seg(50 - neuHalf, pNeu * scaleW, t.neu, null);
            seg(50 + neuHalf, pPos * scaleW, t.pos, 'r');
        }
        row.appendChild(track);

        var netVal = counts.n ? Math.round((counts.pos - counts.neg) / counts.n * 100) : null;
        var net = el('div', 'stx-div-net', counts.n ? (netVal > 0 ? '+' : '') + netVal + ' pp' : '—');
        net.style.color = 'var(--stx-ink)';
        row.appendChild(net);

        row.addEventListener('pointermove', function (e) {
            var posL = comp.delivery ? 'Lebih cepat' : 'Naik';
            var negL = comp.delivery ? 'Lebih lambat' : 'Turun';
            tipShow(e.clientX, e.clientY, comp.label, [
                { color: t.pos, label: posL, value: String(counts.pos) },
                { color: t.neu, label: 'Tetap', value: String(counts.neu) },
                { color: t.neg, label: negL, value: String(counts.neg) },
                { label: 'Menjawab', value: String(counts.n) }
            ]);
        });
        row.addEventListener('pointerleave', tipHide);
        container.appendChild(row);
        return row;
    }

    function renderBlok5() {
        var twSel = state.tw === 'all' ? null : state.tw;
        var kondisiSub = twSel ? ('Kondisi TW ' + TW_ROMAN[twSel] + ' vs triwulan sebelumnya') : 'Kondisi triwulan berjalan vs sebelumnya';
        var prospekSub = twSel ? ('Prospek triwulan berikutnya vs TW ' + TW_ROMAN[twSel]) : 'Prospek triwulan berikutnya';
        var shell = cardShell('card-blok5',
            'Kondisi dan prospek usaha (Blok V)',
            'Persepsi responden — persentase menjawab turun / tetap / naik. Klik baris untuk jawaban per perusahaan');
        var t = theme();
        var rows = filteredRows();

        legendItem(shell.legend, 'swatch', t.neg, 'Turun / lebih lambat');
        legendItem(shell.legend, 'swatch', t.neu, 'Tetap');
        legendItem(shell.legend, 'swatch', t.pos, 'Naik / lebih cepat');

        var anyAnswer = rows.some(function (r) {
            return BLOK5.some(function (c) { return r.blok5[c.key] && (r.blok5[c.key].p1 || r.blok5[c.key].p2); });
        });

        // table twin
        var tRows = [];
        BLOK5.forEach(function (c) {
            var k = blok5Counts(rows, c.key, 'p1'), p = blok5Counts(rows, c.key, 'p2');
            tRows.push([c.label,
                k.neg + ' / ' + k.neu + ' / ' + k.pos,
                k.n ? (Math.round((k.pos - k.neg) / k.n * 100) + ' pp') : '—',
                p.neg + ' / ' + p.neu + ' / ' + p.pos,
                p.n ? (Math.round((p.pos - p.neg) / p.n * 100) + ' pp') : '—']);
        });
        simpleTable(shell.tablePane, ['Komponen', 'Kondisi (− / = / +)', 'Saldo kondisi', 'Prospek (− / = / +)', 'Saldo prospek'], tRows, 1);

        if (!anyAnswer) { emptyState(shell.chartPane, 'Belum ada jawaban Blok V pada irisan filter ini.'); return; }

        var wrap = el('div');
        wrap.style.display = 'grid';
        wrap.style.gap = '1.5rem';
        if ((shell.chartPane.clientWidth || 800) > 720) wrap.style.gridTemplateColumns = '1fr 1fr';

        [['p1', kondisiSub], ['p2', prospekSub]].forEach(function (pdef) {
            var panel = el('div');
            var head = el('div', 'stx-sect-title', pdef[1]);
            head.style.marginBottom = '0.4rem';
            panel.appendChild(head);
            BLOK5.forEach(function (c) {
                var counts = blok5Counts(rows, c.key, pdef[0]);
                var row = divergingRow(panel, c, counts, t);
                function openB5() {
                    tipHide();
                    openModal(c.label + ' — ' + (pdef[0] === 'p1' ? 'Kondisi' : 'Prospek'), filterPills(), function (body) {
                        var s = sect(body, 'Jawaban per perusahaan');
                        if (!counts.answers.length) { s.appendChild(el('p', 'stx-note', 'Belum ada jawaban.')); return; }
                        var wrapB5 = el('div', 'stx-tablewrap');
                        var tt = el('table', 'stx-table');
                        var tb = el('tbody');
                        counts.answers.forEach(function (a) {
                            var tr = el('tr');
                            tr.style.cursor = 'default';
                            tr.appendChild(el('td', 'strong', a.perusahaan));
                            tr.appendChild(el('td', null, 'TW ' + TW_ROMAN[a.tw]));
                            var td = el('td');
                            td.appendChild(el('span', 'stx-ans ' + ansClass(a.v), ANS_LABEL[a.v] || a.v));
                            tr.appendChild(td);
                            tb.appendChild(tr);
                        });
                        tt.appendChild(tb);
                        wrapB5.appendChild(tt);
                        s.appendChild(wrapB5);
                    });
                }
                row.addEventListener('click', openB5);
                row.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openB5(); } });
            });
            wrap.appendChild(panel);
        });

        shell.chartPane.appendChild(wrap);
        shell.chartPane.appendChild(el('p', 'stx-note', 'Saldo bersih (pp) = % jawaban positif − % jawaban negatif. Untuk komponen 506, "lebih cepat" dihitung positif.'));
    }

    /* ═══════════════ TABLE — company detail rows ═══════════════ */

    var TABLE_COLS = [
        { key: 'perusahaan', label: 'Perusahaan', num: false },
        { key: 'kbliGroup', label: 'KBLI', num: false },
        { key: 'triwulan', label: 'TW', num: false },
        { key: 'selesai', label: 'Status', num: false },
        { key: 'pendapatanTotal', label: 'Pendapatan', num: true },
        { key: 'pengeluaranTotal', label: 'Pengeluaran', num: true },
        { key: 'surplus', label: 'Surplus', num: true },
        { key: 'tenagaKerja', label: 'TK', num: true },
        { key: 'eksporPct', label: 'Ekspor', num: true },
        { key: 'updatedAt', label: 'Diperbarui', num: false }
    ];

    /**
     * Everything the detail table can be ranked by — a superset of the fixed
     * columns, so an analyst can ask "perusahaan mana yang nilai tambahnya
     * terbesar?" even though Nilai Tambah is not one of the standing columns.
     * Picking such a metric adds its own column (see activeCols), otherwise the
     * ordering would be invisible.
     */
    var SORT_OPTS = [
        { key: 'perusahaan',       label: 'Nama perusahaan',    num: false, sub: 'urut abjad' },
        { key: 'pendapatanTotal',  label: 'Pendapatan total',   num: true,  sub: 'produk + lainnya + royalti' },
        { key: 'pengeluaranTotal', label: 'Pengeluaran total',  num: true,  sub: 'upah + biaya produksi + operasional' },
        { key: 'surplus',          label: 'Perkiraan surplus',  num: true,  sub: 'pendapatan − pengeluaran' },
        { key: 'nilaiProduksi',    label: 'Nilai produksi',     num: true,  sub: 'output Blok IIIA (301 + 302)' },
        { key: 'biayaTotal',       label: 'Biaya produksi',     num: true,  sub: 'termasuk pembelian aset' },
        { key: 'nilaiTambah',      label: 'Nilai tambah',       num: true,  sub: 'nilai produksi − biaya produksi' },
        { key: 'upah',             label: 'Upah & gaji',        num: true },
        { key: 'capex',            label: 'Pembelian aset',     num: true },
        { key: 'tenagaKerja',      label: 'Tenaga kerja',       num: true,  sub: 'rata-rata pekerja' },
        { key: 'eksporPct',        label: 'Porsi ekspor',       num: true,  sub: '% dari penjualan' },
        { key: 'imporPct',         label: 'Porsi impor',        num: true,  sub: '% dari bahan baku' },
        { key: 'updatedAt',        label: 'Terakhir diperbarui', num: true, sub: 'tanggal simpan terakhir' }
    ];
    function sortOpt(key) {
        for (var i = 0; i < SORT_OPTS.length; i++) if (SORT_OPTS[i].key === key) return SORT_OPTS[i];
        return null;
    }
    // Column headings can set a sort key the picker does not list (TW, Status);
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
        if (key === 'selesai') return ['Selesai dulu', 'Draf dulu'];
        if (key === 'triwulan') return ['TW terakhir', 'TW pertama'];
        var o = sortOpt(key);
        if ((o && !o.num) || key === 'kbliGroup') return ['Z → A', 'A → Z'];
        return ['Terbesar', 'Terkecil'];
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
            state.sortDir = (o ? o.num : key !== 'perusahaan' && key !== 'kbliGroup') ? -1 : 1;
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
                head.appendChild(el('div', 'stx-pop-title', 'Urutkan perusahaan menurut'));
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
        if (o) cols.splice(4, 0, { key: o.key, label: o.label, num: true, extra: true });
        return cols;
    }

    function cellFor(r, col) {
        var td;
        switch (col.key) {
            case 'perusahaan':
                return el('td', 'strong', r.perusahaan);
            case 'kbliGroup':
                td = el('td', null, r.kbli ? r.kbli : '—');
                td.title = r.kbliGroup;
                return td;
            case 'triwulan':
                return el('td', null, TW_ROMAN[r.triwulan]);
            case 'selesai':
                td = el('td');
                td.appendChild(el('span', 'stx-badge ' + (r.selesai ? 'ok' : 'draft'), r.selesai ? 'Selesai' : 'Draf'));
                return td;
            case 'updatedAt':
                return el('td', null, r.updatedAt || '—');
            case 'tenagaKerja':
                return el('td', 'num', fmtN(r.tenagaKerja));
            case 'eksporPct':
            case 'imporPct':
                return el('td', 'num', fmtPct(r[col.key]));
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
        titles.appendChild(el('div', 'stx-chart-title', 'Rincian per perusahaan'));
        titles.appendChild(el('div', 'stx-chart-sub', 'Klik baris untuk membuka detail lengkap isian — pakai Urutkan atau klik judul kolom untuk memeringkat'));
        head.appendChild(titles);
        sortTools(head);
        card.appendChild(head);

        var rows = filteredRows().slice();
        var cols = activeCols();
        var wrapT = el('div', 'stx-tablewrap');
        wrapT.style.padding = '0 0.5rem 0.75rem';

        // Rows with nothing reported for the sort metric always sink to the
        // bottom — in either direction "terkecil" must not mean "belum mengisi".
        rows.sort(function (a, b) {
            var av = sortValue(a, state.sortKey), bv = sortValue(b, state.sortKey);
            if (av === null || av === undefined) return 1;
            if (bv === null || bv === undefined) return -1;
            if (typeof av === 'string') return state.sortDir * av.localeCompare(bv, 'id');
            return state.sortDir * (av - bv);
        });

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
            'TW ' + TW_ROMAN[r.triwulan] + ' ' + DATA.tahun,
            r.kbliGroup,
            r.sektor === 'industri' ? 'Industri (KBLI 10–33)' : 'Non-industri',
            r.selesai ? 'Selesai' : 'Draf'
        ], function (body) {
            var s1 = sect(body, 'Identitas');
            kvGrid(s1, [
                ['Kabupaten/Kota', r.kabupaten],
                ['KIP', r.kip],
                ['KBLI utama', r.kbli],
                ['Kegiatan utama', r.kegiatan],
                ['Tenaga kerja (rata-rata)', r.tenagaKerja === null ? null : fmtN(r.tenagaKerja)],
                ['Terakhir disimpan', r.updatedAt]
            ]);

            var s2 = sect(body, 'Pendapatan triwulan ini');
            var pendRows = [];
            if (r.sektor === 'industri') {
                pendRows.push({ label: 'Nilai produksi (301)', value: fmtRpFull(r.pendapatanProduk) });
                pendRows.push({ label: 'Pendapatan lainnya (302)', value: fmtRpFull(r.pendapatanLainnya) });
            } else {
                pendRows.push({ label: 'Penjualan barang/jasa (303)', value: fmtRpFull(r.penjualan) });
            }
            pendRows.push({ label: 'Royalti, bunga, dividen (304)', value: fmtRpFull(r.pendapatanRoyalti) });
            pendRows.push({ label: 'Total pendapatan', value: fmtRpFull(r.pendapatanTotal), total: true });
            moneyRows(s2, pendRows);

            var s3 = sect(body, 'Pengeluaran triwulan ini');
            moneyRows(s3, [
                { label: 'Upah, gaji, dan jaminan sosial (310)', value: fmtRpFull(r.upah) },
                { label: 'Biaya produksi / bahan baku (312)', value: fmtRpFull(r.biayaProduksi) },
                { label: 'Biaya operasional (313)', value: fmtRpFull(r.biayaOperasional) },
                { label: 'Total pengeluaran', value: fmtRpFull(r.pengeluaranTotal), total: true },
                { label: 'Surplus usaha (perkiraan)', value: fmtRpFull(r.surplus), total: true }
            ]);

            var s4 = sect(body, 'Investasi dan persediaan');
            kvGrid(s4, [
                ['Penambahan aset tetap (311)', fmtRpFull(r.capex)],
                ['Persediaan awal (309a)', fmtRpFull(r.persediaanAwal)],
                ['Persediaan akhir (309b)', fmtRpFull(r.persediaanAkhir)],
                ['Ekspor produksi (314)', fmtPct(r.eksporPct)],
                ['Impor bahan baku (315)', fmtPct(r.imporPct)]
            ]);

            if (r.products && r.products.length) {
                var s5 = sect(body, 'Produk (Blok IIIA)');
                r.products.forEach(function (p, pi) {
                    var name = el('div', null, (pi + 1) + '. ' + (p.jenis || 'Tanpa nama') + (p.satuan ? ' — satuan: ' + p.satuan : ''));
                    name.style.fontSize = '0.8125rem';
                    name.style.fontWeight = '600';
                    name.style.margin = '0.6rem 0 0.3rem';
                    s5.appendChild(name);
                    var months = Object.keys(p.nilai).sort(monthKeySort);
                    var scr = el('div');
                    simpleTable(scr, ['Bulan', 'Banyaknya', 'Nilai (Rp)'],
                        months.map(function (m) { return [monthLong(m), fmtN(p.banyak[m]), fmtRpFull(p.nilai[m])]; }), 1);
                    s5.appendChild(scr);
                });
            }

            var s6 = sect(body, 'Kondisi dan prospek usaha (Blok V)');
            var wrap6 = el('div', 'stx-tablewrap');
            var tt = el('table', 'stx-table');
            var tb = el('tbody');
            BLOK5.forEach(function (c) {
                var a = r.blok5[c.key] || {};
                var tr = el('tr');
                tr.style.cursor = 'default';
                tr.appendChild(el('td', 'strong', c.label));
                var td1 = el('td');
                td1.appendChild(el('span', 'stx-ans ' + ansClass(a.p1), a.p1 ? (ANS_LABEL[a.p1] || a.p1) : '—'));
                tr.appendChild(td1);
                var td2 = el('td');
                td2.appendChild(el('span', 'stx-ans ' + ansClass(a.p2), a.p2 ? (ANS_LABEL[a.p2] || a.p2) : '—'));
                tr.appendChild(td2);
                tb.appendChild(tr);
            });
            var thead = el('thead'); var trh = el('tr');
            ['Komponen', 'Kondisi', 'Prospek'].forEach(function (h) { var th = el('th', null, h); th.style.cursor = 'default'; trh.appendChild(th); });
            thead.appendChild(trh);
            // insertBefore(thead, tb) would throw here — tb is not a child of
            // tt yet, and the exception aborted openModal before it opened.
            tt.appendChild(thead);
            tt.appendChild(tb);
            wrap6.appendChild(tt);
            s6.appendChild(wrap6);

            if (r.catatan) {
                var s7 = sect(body, 'Catatan responden (Blok VI)');
                var pcat = el('p', null, r.catatan);
                pcat.style.fontSize = '0.8125rem';
                pcat.style.color = 'var(--stx-ink-2)';
                pcat.style.lineHeight = '1.6';
                s7.appendChild(pcat);
            }
        });
    }

    /* ═══════════════ orchestration ═══════════════ */

    // Everything except the filter bar — used by the company picker so the
    // open popover survives while the rest of the dashboard recomputes.
    function rerenderData() {
        renderKpis();
        renderMonthly();
        renderQuarter();
        renderCompo();
        renderIndustri();
        renderKbli();
        renderBlok5();
        renderTable();
    }

    function rerender() {
        renderFilters();
        rerenderData();
    }

    // re-render on container resize (debounced) and on theme toggle;
    // keep an open picker popover alive by leaving the filter bar alone
    var resizeT = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeT);
        resizeT = setTimeout(function () {
            if (activePop) rerenderData(); else rerender();
        }, 180);
    });
    // theme toggle: only react when dark mode actually flips — scripts that
    // rewrite the html class without changing it must not nuke open popovers
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

    renderYearFilter();
    rerender();
})();
