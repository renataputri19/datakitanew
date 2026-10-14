/*
 * Statistik Konstruksi — BPS dashboard over Survei Perusahaan Konstruksi.
 *
 * Same architecture as bps-statistik-{ub,listrik}.js: the controller embeds one
 * flattened payload and everything below filters/aggregates client-side. The
 * shared look comes from css/bps-statistik.css; the helpers below are kept
 * local (as in the sibling files) so each dashboard stays independently
 * editable.
 *
 * Every figure is already a Number here — the id-ID parsing and the Blok VI
 * column (17) formula run server-side, so this file never re-implements them
 * and can never disagree with the questionnaire.
 *
 * Units: all money is in THOUSANDS of rupiah (000 Rp), matching the form.
 */
(function () {
    'use strict';

    var DATA = window.__KONSTRUKSI_STAT__;
    if (!DATA) return;

    /* ═══════════════ constants ═══════════════ */

    // Metrics the ranking cards can be switched between.
    var METRICS = [
        { key: 'nilaiKonstruksi', label: 'Nilai konstruksi', money: true },
        { key: 'nilaiKontrak',    label: 'Nilai kontrak',    money: true },
        { key: 'jumlahProyek',    label: 'Jumlah proyek',    money: false }
    ];

    // Blok IX cost structure, in the order the questionnaire prints it.
    var STRUKTUR = [
        { key: 'balasJasaTotal', label: 'Balas jasa & upah pekerja' },
        { key: 'bahanProyek',    label: 'Bahan / material' },
        { key: 'operasional',    label: 'Pengeluaran operasional' }
    ];

    var TABLE_PAGE = 10;

    var KPI_ICONS = {
        usaha: 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5',
        nilai: 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
        proyek: 'M4 7h16M4 12h16M4 17h10',
        tenaga: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0 .01M23 21v-2a4 4 0 0 0-3-3.87',
        margin: 'M23 6l-9.5 9.5-5-5L1 18'
    };

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

    // Stored values are thousands of rupiah; "Rp 1,2 jt (000)" would read as a
    // unit error, so the thousands are folded in before formatting instead.
    function fmtRp(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfCompact.format(v * 1000); }
    function fmtRpFull(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfFull.format(v * 1000); }
    function fmtN(v) { return v === null || v === undefined ? '—' : nfFull.format(v); }
    function fmtPct(v) { return v === null || v === undefined ? '—' : nfPct.format(v) + '%'; }
    function share(part, total) { return total ? nfPct.format(part / total * 100) + '%' : '—'; }

    function metricDef(key) {
        for (var i = 0; i < METRICS.length; i++) if (METRICS[i].key === key) return METRICS[i];
        return METRICS[0];
    }
    function fmtMetric(key) { return metricDef(key).money ? fmtRp : fmtN; }
    function fmtMetricFull(key) { return metricDef(key).money ? fmtRpFull : fmtN; }

    /* ═══════════════ state + filtering ═══════════════ */

    var state = {
        // Konstruksi rows are period-scoped; one period at a time keeps a
        // company from being counted twice across reporting periods.
        period: (DATA.periods[0] && DATA.periods[0].key) || '',
        kualSel: {}, kabSel: {},
        status: 'done',
        excluded: {},
        metricWilayah: 'nilaiKonstruksi',
        sortKey: 'nilaiKonstruksi', sortDir: -1, tableLimit: TABLE_PAGE
    };

    var EXCL_KEY = 'stx-konstruksi-excl';
    try {
        (JSON.parse(sessionStorage.getItem(EXCL_KEY) || '[]') || []).forEach(function (uid) { state.excluded[uid] = true; });
    } catch (e) { /* corrupt storage — start clean */ }
    function saveExcluded() {
        try { sessionStorage.setItem(EXCL_KEY, JSON.stringify(Object.keys(state.excluded))); } catch (e) { /* quota / private mode */ }
    }

    function matchesStatus(r) {
        if (state.status === 'done') return r.selesai;
        if (state.status === 'draft') return !r.selesai;
        return true;
    }

    /**
     * Rows passing every filter except the one named, so each dropdown can
     * show how many rows its own options would yield.
     */
    function rowsFor(skip) {
        return DATA.rows.filter(function (r) {
            if (skip !== 'period' && r.periode !== state.period) return false;
            if (skip !== 'status' && !matchesStatus(r)) return false;
            if (skip !== 'kual' && Object.keys(state.kualSel).length && !state.kualSel[r.kualifikasi]) return false;
            if (skip !== 'kab' && Object.keys(state.kabSel).length && !state.kabSel[r.kabupaten || '—']) return false;
            if (skip !== 'company' && state.excluded[r.uid]) return false;
            return true;
        });
    }
    function filteredRows() { return rowsFor(null); }

    /**
     * Is the selected period the quarterly instrument?
     *
     * SKTR collects far less than SKTH — no pendidikan breakdown, no operating
     * expenses, no material structure, no Blok IX summary — so the cards built
     * on those say so rather than drawing a chart of zeroes.
     */
    function isTwPeriod() {
        return /-[1-4]$/.test(state.period);
    }

    /** Message the annual-only cards show while a quarterly period is selected. */
    var TW_NOT_COLLECTED = 'Kuesioner triwulanan (SKTR) tidak mengumpulkan rincian ini — '
        + 'pilih periode Tahunan (SKTH) untuk melihatnya.';
    function eligibleCompanies() { return rowsFor('company'); }
    function excludedEligible() {
        return eligibleCompanies().filter(function (r) { return state.excluded[r.uid]; }).length;
    }

    function sumField(rows, key) {
        return rows.reduce(function (a, r) {
            var v = typeof key === 'function' ? key(r) : r[key];
            return a + (typeof v === 'number' && isFinite(v) ? v : 0);
        }, 0);
    }
    function countField(rows, key) {
        return rows.filter(function (r) {
            var v = typeof key === 'function' ? key(r) : r[key];
            return typeof v === 'number' && v > 0;
        }).length;
    }

    /** Blok IX pengeluaran component for one company. */
    function strukturValue(r, key) {
        if (key === 'balasJasaTotal') return r.balasJasa + r.upahHarian;
        if (key === 'bahanProyek') return r.bahanProyek;
        if (key === 'operasional') return sumField([r], function (x) { return sumObj(x.pengeluaran); });
        return 0;
    }
    function sumObj(obj) {
        var t = 0;
        for (var k in obj) if (typeof obj[k] === 'number') t += obj[k];
        return t;
    }
    function totalTenaga(r) {
        var t = 0;
        for (var j in r.tenaga) t += (r.tenaga[j].l || 0) + (r.tenaga[j].p || 0);
        return t;
    }

    function kualLabel(code) { return DATA.kualifikasi[code] || (code ? code : 'Tidak diisi'); }
    function periodLabel(key) {
        for (var i = 0; i < DATA.periods.length; i++) if (DATA.periods[i].key === key) return DATA.periods[i].label;
        return key;
    }
    function kabsOf(rows) {
        var seen = {};
        rows.forEach(function (r) { seen[r.kabupaten || '—'] = true; });
        return Object.keys(seen).sort();
    }
    function kualsOf(rows) {
        var seen = {};
        rows.forEach(function (r) { seen[r.kualifikasi] = true; });
        return Object.keys(seen).sort();
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
    function filterPills() {
        var pills = [periodLabel(state.period)];
        pills.push(state.status === 'done' ? 'Selesai' : (state.status === 'draft' ? 'Draf' : 'Semua status'));
        var k = Object.keys(state.kualSel);
        if (k.length) pills.push(k.length === 1 ? kualLabel(k[0]) : k.length + ' kualifikasi');
        var kb = Object.keys(state.kabSel);
        if (kb.length) pills.push(kb.length === 1 ? kb[0] : kb.length + ' kab/kota');
        var out = excludedEligible();
        if (out) pills.push(out + ' perusahaan disembunyikan');
        return pills;
    }

    /* ═══════════════ popovers ═══════════════ */

    var popLayer = document.querySelectorAll('.stx-root')[1] || rootEl;
    var activePop = null;
    function closePop() {
        if (activePop) {
            if (activePop.panel.parentNode) activePop.panel.parentNode.removeChild(activePop.panel);
            activePop.btn.setAttribute('aria-expanded', 'false');
            activePop = null;
        }
    }
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

    /* ═══════════════ dropdowns ═══════════════ */

    var facetRefreshers = [];
    function refreshFilterBar() { facetRefreshers.forEach(function (f) { f(); }); }

    function ddSingle(bar, labelText, options, currentFn, onPick) {
        var optsOf = typeof options === 'function' ? options : function () { return options; };
        bar.appendChild(el('span', 'stx-filter-label', labelText));
        var wrapEl = el('div', 'stx-popwrap');
        var btn = el('button', 'stx-popbtn');
        btn.type = 'button';
        var lbl = el('span', null, '');
        function refreshBtn() {
            var cur = optsOf().filter(function (o) { return o.v === currentFn(); })[0];
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
                    var row = el('button', 'stx-pop-row' + (o.v === currentFn() ? ' on' : ''));
                    row.type = 'button';
                    if (o.disabled) row.disabled = true;
                    row.appendChild(el('span', 'p-check', o.v === currentFn() ? '✓' : ''));
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

    /* ═══════════════ card scaffolding ═══════════════ */

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
        var tablePane = el('div');
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
    // overflows its container. Only a table this helper added before is
    // replaced — modal sections keep their .stx-sect-title in the same node.
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

    /* ═══════════════ bar chart ═══════════════ */

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

    /** One horizontal ranked bar row per entry — every card here is a ranking. */
    function barRows(pane, list, opts) {
        var t = theme();
        var W = Math.max(300, pane.clientWidth || opts.fallbackW || 460);
        var rowH = opts.rowH || 38, padT = 4;
        var labelW = Math.min(opts.labelMax || 170, Math.max(110, W * 0.3));
        var valueW = opts.valueW || 90;
        var plotW = Math.max(40, W - labelW - valueW - 12);
        var H = padT + list.length * rowH + 4;
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

        ddSingle(bar, 'Periode', function () {
            var pool = rowsFor('period');
            return DATA.periods.map(function (p) {
                var n = pool.filter(function (r) { return r.periode === p.key; }).length;
                return {
                    v: p.key, t: p.label,
                    disabled: state.period !== p.key && !n,
                    sub: n ? n + ' perusahaan' : 'Kosong pada filter lain'
                };
            });
        }, function () { return state.period; }, function (v) {
            state.period = v;
            // Kualifikasi / kab-kota chosen in the previous period may not exist
            // in this one — keeping them would silently empty the dashboard.
            var inPeriod = DATA.rows.filter(function (r) { return r.periode === v; });
            var kuals = {}, kabs = {};
            inPeriod.forEach(function (r) { kuals[r.kualifikasi] = true; kabs[r.kabupaten || '—'] = true; });
            Object.keys(state.kualSel).forEach(function (k) { if (!kuals[k]) delete state.kualSel[k]; });
            Object.keys(state.kabSel).forEach(function (k) { if (!kabs[k]) delete state.kabSel[k]; });
            rerender();
        });

        bar.appendChild(el('div', 'stx-filter-sep'));

        ddMulti(bar, 'Kualifikasi', function () {
            var pool = rowsFor('kual');
            return kualsOf(DATA.rows.filter(function (r) { return r.periode === state.period; })).map(function (k) {
                var n = pool.filter(function (r) { return r.kualifikasi === k; }).length;
                return { v: k, t: kualLabel(k), n: n, disabled: !n, sub: n ? n + ' perusahaan' : 'Kosong pada filter lain' };
            });
        }, state.kualSel, 'Semua kualifikasi', function () { rerenderData(); refreshFilterBar(); });

        ddMulti(bar, 'Kab/Kota', function () {
            var pool = rowsFor('kab');
            return kabsOf(DATA.rows.filter(function (r) { return r.periode === state.period; })).map(function (k) {
                var n = pool.filter(function (r) { return (r.kabupaten || '—') === k; }).length;
                return { v: k, t: k, n: n, disabled: !n, sub: n ? n + ' perusahaan' : 'Kosong pada filter lain' };
            });
        }, state.kabSel, 'Semua kab/kota', function () { rerenderData(); refreshFilterBar(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        ddSingle(bar, 'Status', [
            { v: 'done', t: 'Hanya selesai' },
            { v: 'draft', t: 'Hanya draf' },
            { v: 'all', t: 'Semua status' }
        ], function () { return state.status; }, function (v) { state.status = v; rerender(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // Company picker — lets BPS drop an outlier without editing the data.
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
            togglePop(pickWrap, pickBtn, function (panel) {
                var head = el('div', 'stx-pop-head');
                head.appendChild(el('div', 'stx-pop-title', 'Perusahaan'));
                head.appendChild(el('div', 'stx-pop-sub', 'Hilangkan centang untuk mengeluarkan perusahaan dari seluruh perhitungan.'));
                panel.appendChild(head);
                var list = el('div', 'stx-pop-list');
                eligibleCompanies().slice().sort(function (a, b) {
                    return (b.nilaiKonstruksi || 0) - (a.nilaiKonstruksi || 0);
                }).forEach(function (r) {
                    var on = !state.excluded[r.uid];
                    var row = el('button', 'stx-pop-row' + (on ? ' on' : ''));
                    row.type = 'button';
                    row.setAttribute('role', 'checkbox');
                    row.setAttribute('aria-checked', on ? 'true' : 'false');
                    var check = el('span', 'p-check', on ? '✓' : '');
                    row.appendChild(check);
                    var main = el('div', 'p-main');
                    main.appendChild(el('div', 'p-name', r.perusahaan));
                    main.appendChild(el('div', 'p-meta', (r.kabupaten || '—') + ' · ' + kualLabel(r.kualifikasi)));
                    row.appendChild(main);
                    row.appendChild(el('span', 'p-val', fmtRp(r.nilaiKonstruksi)));
                    row.addEventListener('click', function () {
                        if (state.excluded[r.uid]) delete state.excluded[r.uid]; else state.excluded[r.uid] = true;
                        var isOn = !state.excluded[r.uid];
                        row.classList.toggle('on', isOn);
                        row.setAttribute('aria-checked', isOn ? 'true' : 'false');
                        check.textContent = isOn ? '✓' : '';
                        saveExcluded();
                        refreshPickBtn();
                        rerenderData();
                    });
                    list.appendChild(row);
                });
                panel.appendChild(list);
                var foot = el('div', 'stx-pop-foot');
                var resetB = el('button', 'stx-pop-link', 'Pilih semua');
                resetB.type = 'button';
                resetB.addEventListener('click', function () {
                    Object.keys(state.excluded).forEach(function (k) { delete state.excluded[k]; });
                    saveExcluded();
                    closePop();
                    refreshPickBtn();
                    rerenderData();
                });
                foot.appendChild(el('span', 'cnt', eligibleCompanies().length + ' perusahaan'));
                foot.appendChild(resetB);
                panel.appendChild(foot);
            });
        });
        pickWrap.appendChild(pickBtn);
        bar.appendChild(pickWrap);
    }

    /* ═══════════════ KPI ROW ═══════════════ */

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

    /** Per-perusahaan breakdown of one metric, ranked biggest first. */
    function companyBreakdownModal(title, rows, valueFn, fmt) {
        openModal(title, filterPills(), function (body) {
            var s = sect(body, 'Rincian per perusahaan — terbesar lebih dulu');
            var sorted = rows.slice().sort(function (a, b) { return (valueFn(b) || 0) - (valueFn(a) || 0); });
            simpleTable(s, ['Perusahaan', 'Kab/Kota', 'Kualifikasi', 'Nilai'],
                sorted.map(function (r) {
                    return [r.perusahaan, r.kabupaten || '—', kualLabel(r.kualifikasi), fmt(valueFn(r))];
                }), 3);
        });
    }

    function renderKpis() {
        var wrap = document.getElementById('stx-kpis');
        clear(wrap);
        var rows = filteredRows();

        var done = rows.filter(function (r) { return r.selesai; }).length;
        kpiTile(wrap, {
            label: 'Perusahaan melapor',
            icon: 'usaha',
            value: fmtN(rows.length),
            sub: done + ' selesai · ' + (rows.length - done) + ' draf',
            onClick: function () {
                openModal('Perusahaan melapor', filterPills(), function (body) {
                    var s = sect(body, 'Daftar perusahaan');
                    simpleTable(s, ['Perusahaan', 'Kab/Kota', 'Kualifikasi', 'Status', 'Kelengkapan', 'Diperbarui'],
                        rows.map(function (r) {
                            return [r.perusahaan, r.kabupaten || '—', kualLabel(r.kualifikasi),
                                r.selesai ? 'Selesai' : 'Draf', r.progress + '%', r.updatedAt || '—'];
                        }), 4);
                });
            }
        });

        var totNilai = sumField(rows, 'nilaiKonstruksi');
        kpiTile(wrap, {
            label: 'Nilai konstruksi diselesaikan',
            icon: 'nilai',
            value: fmtRp(totNilai),
            tooltip: fmtRpFull(totNilai),
            sub: countField(rows, 'nilaiKonstruksi') + ' dari ' + rows.length + ' perusahaan mengisi',
            onClick: function () {
                companyBreakdownModal('Nilai konstruksi yang diselesaikan', rows,
                    function (r) { return r.nilaiKonstruksi; }, fmtRpFull);
            }
        });

        var totProyek = sumField(rows, 'jumlahProyek');
        var totKontrak = sumField(rows, 'nilaiKontrak');
        kpiTile(wrap, {
            label: 'Proyek dilaporkan',
            icon: 'proyek',
            value: fmtN(totProyek),
            sub: 'nilai kontrak ' + fmtRp(totKontrak),
            onClick: function () {
                openModal('Proyek dilaporkan', filterPills(), function (body) {
                    var s = sect(body, 'Seluruh proyek — nilai konstruksi terbesar lebih dulu');
                    var all = [];
                    rows.forEach(function (r) {
                        r.proyek.forEach(function (p) { all.push({ r: r, p: p }); });
                    });
                    all.sort(function (a, b) { return (b.p.nilai || 0) - (a.p.nilai || 0); });
                    simpleTable(s, ['Proyek', 'Perusahaan', 'Lokasi', 'Sumber dana', 'Kontrak', 'Nilai konstruksi'],
                        all.map(function (x) {
                            return [x.p.nama || '—', x.r.perusahaan, x.p.kabkota || '—',
                                DATA.sumberDana[x.p.sumberDana] || '—', fmtRpFull(x.p.kontrak), fmtRpFull(x.p.nilai)];
                        }), 4);
                });
            }
        });

        // SKTR reports one headcount per quarter rather than a pendidikan
        // matrix, so the tenaga tile reads its own field on quarterly periods.
        var tw = isTwPeriod();
        var tenagaFn = tw ? function (r) { return r.pekerjaTw || 0; } : totalTenaga;
        kpiTile(wrap, {
            label: 'Tenaga kerja tetap & kontrak',
            icon: 'tenaga',
            value: fmtN(sumField(rows, tenagaFn)),
            sub: 'balas jasa ' + fmtRp(sumField(rows, 'balasJasa'))
                + (tw && rows.length ? ' · ' + (rows[0].twLabels ? rows[0].twLabels.cur : '') : ''),
            onClick: function () {
                companyBreakdownModal('Tenaga kerja tetap & kontrak', rows, tenagaFn, fmtN);
            }
        });

        // Blok IX does not exist on SKTR, so a quarterly period gets the
        // reporting-status tile in place of the margin one.
        if (tw) {
            var withProyek = rows.filter(function (r) { return r.adaProyek; }).length;
            kpiTile(wrap, {
                label: 'Perusahaan dengan proyek',
                icon: 'margin',
                value: fmtN(withProyek),
                sub: (rows.length - withProyek) + ' tanpa proyek konstruksi',
                tooltip: 'Jawaban "Keberadaan Proyek" pada Updating Status Perusahaan',
                onClick: function () {
                    openModal('Status pencacahan triwulan', filterPills(), function (body) {
                        var s = sect(body, 'Per perusahaan');
                        simpleTable(s, ['Perusahaan', 'Status perusahaan', 'Ada proyek?', 'Proyek', 'Nilai konstruksi'],
                            rows.slice().sort(function (a, b) { return (b.nilaiKonstruksi || 0) - (a.nilaiKonstruksi || 0); })
                                .map(function (r) {
                                    return [r.perusahaan, r.statusLabel || '—', r.adaProyek ? 'Ada' : 'Tidak ada',
                                        fmtN(r.jumlahProyek), fmtRpFull(r.nilaiKonstruksi)];
                                }), 3);
                    });
                }
            });
            return;
        }

        // Aggregate margin, not an average of per-company margins: a company
        // with tiny pengeluaran would otherwise dominate the mean.
        var totA = sumField(rows, 'totalA');
        var totB = sumField(rows, 'totalB');
        var margin = totA > 0 ? ((totB / totA) - 1) * 100 : null;
        kpiTile(wrap, {
            label: 'Keuntungan thd pengeluaran',
            icon: 'margin',
            value: margin === null ? '—' : fmtPct(margin),
            sub: 'B ' + fmtRp(totB) + ' · A ' + fmtRp(totA),
            tooltip: 'Dihitung agregat: (ΣB / ΣA − 1) × 100%',
            onClick: function () {
                openModal('Ringkasan Blok IX', filterPills(), function (body) {
                    var s = sect(body, 'Agregat seluruh perusahaan terpilih');
                    kvGrid(s, [
                        ['Jumlah pengeluaran (A)', fmtRpFull(totA)],
                        ['Jumlah pendapatan (B)', fmtRpFull(totB)],
                        ['Keuntungan thd pengeluaran', margin === null ? '—' : fmtPct(margin)]
                    ]);
                    var s2 = sect(body, 'Per perusahaan');
                    simpleTable(s2, ['Perusahaan', 'Pengeluaran A', 'Pendapatan B', 'Keuntungan'],
                        rows.slice().sort(function (a, b) { return (b.totalB || 0) - (a.totalB || 0); })
                            .map(function (r) {
                                return [r.perusahaan, fmtRpFull(r.totalA), fmtRpFull(r.totalB),
                                    r.marginPct === null ? '—' : fmtPct(r.marginPct)];
                            }), 1);
                });
            }
        });
    }

    /* ═══════════════ CARD — sebaran wilayah proyek ═══════════════ */

    function renderWilayah() {
        var metric = state.metricWilayah;
        var shell = cardShell('card-wilayah',
            'Sebaran proyek menurut lokasi',
            'Dikelompokkan dari kolom (3) Blok VI — satu proyek dihitung di kabupaten/kota lokasinya, bukan lokasi perusahaan',
            { metricKey: 'metricWilayah' });

        var rows = filteredRows();
        var byKab = {};
        rows.forEach(function (r) {
            r.proyek.forEach(function (p) {
                var k = p.kabkota || 'Tidak diisi';
                if (!byKab[k]) byKab[k] = { nilaiKonstruksi: 0, nilaiKontrak: 0, jumlahProyek: 0, firms: {} };
                byKab[k].nilaiKonstruksi += p.nilai || 0;
                byKab[k].nilaiKontrak += p.kontrak || 0;
                byKab[k].jumlahProyek += 1;
                byKab[k].firms[r.uid] = true;
            });
        });

        var list = Object.keys(byKab).map(function (k) {
            return { label: k, d: byKab[k] };
        }).sort(function (a, b) { return b.d[metric] - a.d[metric]; });

        if (!list.length) {
            emptyState(shell.chartPane, 'Belum ada proyek pada filter ini.');
            return;
        }

        var t = theme();
        var fmt = fmtMetric(metric);
        var fmtFull = fmtMetricFull(metric);
        var total = list.reduce(function (a, g) { return a + g.d[metric]; }, 0);

        legendItem(shell.legend, 'bar', t.s1, metricDef(metric).label + ' per kabupaten/kota');

        barRows(shell.chartPane, list.slice(0, 12).map(function (g) {
            return {
                label: g.label,
                sub: g.d.jumlahProyek + ' proyek · ' + Object.keys(g.d.firms).length + ' perusahaan',
                v: g.d[metric],
                valueLabel: fmt(g.d[metric]),
                color: t.s1,
                tip: [
                    { label: 'Nilai konstruksi', value: fmtRpFull(g.d.nilaiKonstruksi) },
                    { label: 'Nilai kontrak', value: fmtRpFull(g.d.nilaiKontrak) },
                    { label: 'Jumlah proyek', value: fmtN(g.d.jumlahProyek) },
                    { label: 'Pangsa', value: share(g.d[metric], total) }
                ],
                onClick: function () {
                    openModal('Proyek di ' + g.label, filterPills(), function (body) {
                        var s = sect(body, 'Daftar proyek');
                        var all = [];
                        rows.forEach(function (r) {
                            r.proyek.forEach(function (p) {
                                if ((p.kabkota || 'Tidak diisi') === g.label) all.push({ r: r, p: p });
                            });
                        });
                        all.sort(function (a, b) { return (b.p.nilai || 0) - (a.p.nilai || 0); });
                        simpleTable(s, ['Proyek', 'Perusahaan', 'Sumber dana', 'Realisasi', 'Kontrak', 'Nilai konstruksi'],
                            all.map(function (x) {
                                return [x.p.nama || '—', x.r.perusahaan, DATA.sumberDana[x.p.sumberDana] || '—',
                                    fmtPct(x.p.realisasi), fmtRpFull(x.p.kontrak), fmtRpFull(x.p.nilai)];
                            }), 3);
                    });
                }
            };
        }), { labelMax: 200, valueW: 110, aria: 'Sebaran proyek menurut kabupaten/kota' });

        simpleTable(shell.tablePane,
            ['Kabupaten/Kota', 'Proyek', 'Perusahaan', 'Nilai kontrak', 'Nilai konstruksi', 'Pangsa'],
            list.map(function (g) {
                return [g.label, fmtN(g.d.jumlahProyek), fmtN(Object.keys(g.d.firms).length),
                    fmtRpFull(g.d.nilaiKontrak), fmtRpFull(g.d.nilaiKonstruksi), share(g.d[metric], total)];
            }), 1);
    }

    /* ═══════════════ CARD — struktur biaya (Blok IX-A) ═══════════════ */

    function renderStruktur() {
        var shell = cardShell('card-struktur', 'Struktur pengeluaran',
            'Blok IX rincian A — balas jasa, bahan/material, dan operasional');

        if (isTwPeriod()) { emptyState(shell.chartPane, TW_NOT_COLLECTED); return; }

        var rows = filteredRows();
        var t = theme();
        var colors = [t.s1, t.s2, t.s3];

        var list = STRUKTUR.map(function (s, i) {
            return { key: s.key, label: s.label, v: sumField(rows, function (r) { return strukturValue(r, s.key); }), color: colors[i] };
        });
        var total = list.reduce(function (a, g) { return a + g.v; }, 0);

        if (!total) {
            emptyState(shell.chartPane, 'Belum ada pengeluaran yang terisi pada filter ini.');
            return;
        }

        list.forEach(function (g) { legendItem(shell.legend, 'bar', g.color, g.label); });

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: share(g.v, total) + ' dari total',
                v: g.v,
                valueLabel: fmtRp(g.v),
                color: g.color,
                tip: [{ label: 'Nilai', value: fmtRpFull(g.v) }, { label: 'Pangsa', value: share(g.v, total) }],
                onClick: function () {
                    companyBreakdownModal(g.label, rows, function (r) { return strukturValue(r, g.key); }, fmtRpFull);
                }
            };
        }), { labelMax: 175, valueW: 100, aria: 'Struktur pengeluaran' });

        simpleTable(shell.tablePane, ['Komponen', 'Nilai', 'Pangsa'],
            list.map(function (g) { return [g.label, fmtRpFull(g.v), share(g.v, total)]; }).concat([
                ['JUMLAH A', fmtRpFull(total), '100%']
            ]), 1);
    }

    /* ═══════════════ CARD — sumber dana proyek ═══════════════ */

    function renderDana() {
        var shell = cardShell('card-dana', 'Sumber dana proyek',
            'Kolom (4) Blok VI — nilai konstruksi menurut sumber pembiayaan utama');

        var rows = filteredRows();
        var t = theme();
        var colors = [t.s1, t.s2, t.s3, t.s4];

        var by = {};
        rows.forEach(function (r) {
            r.proyek.forEach(function (p) {
                var k = p.sumberDana || '';
                if (!by[k]) by[k] = { nilai: 0, n: 0 };
                by[k].nilai += p.nilai || 0;
                by[k].n += 1;
            });
        });

        var codes = Object.keys(DATA.sumberDana).concat(by[''] ? [''] : []);
        var list = codes.filter(function (c) { return by[c]; }).map(function (c, i) {
            return {
                code: c,
                label: DATA.sumberDana[c] || 'Tidak diisi',
                v: by[c].nilai,
                n: by[c].n,
                color: colors[i % colors.length]
            };
        }).sort(function (a, b) { return b.v - a.v; });

        if (!list.length) {
            emptyState(shell.chartPane, 'Belum ada proyek pada filter ini.');
            return;
        }

        var total = list.reduce(function (a, g) { return a + g.v; }, 0);
        list.forEach(function (g) { legendItem(shell.legend, 'bar', g.color, g.label); });

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: g.n + ' proyek · ' + share(g.v, total),
                v: g.v,
                valueLabel: fmtRp(g.v),
                color: g.color,
                tip: [{ label: 'Nilai konstruksi', value: fmtRpFull(g.v) }, { label: 'Proyek', value: fmtN(g.n) }],
                onClick: function () {
                    openModal('Proyek — ' + g.label, filterPills(), function (body) {
                        var s = sect(body, 'Daftar proyek');
                        var all = [];
                        rows.forEach(function (r) {
                            r.proyek.forEach(function (p) { if ((p.sumberDana || '') === g.code) all.push({ r: r, p: p }); });
                        });
                        all.sort(function (a, b) { return (b.p.nilai || 0) - (a.p.nilai || 0); });
                        simpleTable(s, ['Proyek', 'Perusahaan', 'Lokasi', 'Kontrak', 'Nilai konstruksi'],
                            all.map(function (x) {
                                return [x.p.nama || '—', x.r.perusahaan, x.p.kabkota || '—',
                                    fmtRpFull(x.p.kontrak), fmtRpFull(x.p.nilai)];
                            }), 3);
                    });
                }
            };
        }), { labelMax: 175, valueW: 100, aria: 'Sumber dana proyek' });

        simpleTable(shell.tablePane, ['Sumber dana', 'Proyek', 'Nilai konstruksi', 'Pangsa'],
            list.map(function (g) { return [g.label, fmtN(g.n), fmtRpFull(g.v), share(g.v, total)]; }), 1);
    }

    /* ═══════════════ CARD — tenaga kerja ═══════════════ */

    function renderTenaga() {
        var shell = cardShell('card-tenaga', 'Tenaga kerja menurut pendidikan',
            'Blok II rincian A — pekerja tetap & kontrak, kondisi akhir periode');

        if (isTwPeriod()) { emptyState(shell.chartPane, TW_NOT_COLLECTED); return; }

        var rows = filteredRows();
        var t = theme();
        var colors = [t.s1, t.s2, t.s3];

        var jenjang = Object.keys(DATA.pendidikan);
        var list = jenjang.map(function (j, i) {
            var l = sumField(rows, function (r) { return (r.tenaga[j] || {}).l || 0; });
            var p = sumField(rows, function (r) { return (r.tenaga[j] || {}).p || 0; });
            return { key: j, label: DATA.pendidikan[j], l: l, p: p, v: l + p, color: colors[i % colors.length] };
        });
        var total = list.reduce(function (a, g) { return a + g.v; }, 0);

        if (!total) {
            emptyState(shell.chartPane, 'Belum ada data tenaga kerja pada filter ini.');
            return;
        }

        list.forEach(function (g) { legendItem(shell.legend, 'bar', g.color, g.label); });

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: 'L ' + fmtN(g.l) + ' · P ' + fmtN(g.p) + ' · ' + share(g.v, total),
                v: g.v,
                valueLabel: fmtN(g.v),
                color: g.color,
                tip: [
                    { label: 'Laki-laki', value: fmtN(g.l) },
                    { label: 'Perempuan', value: fmtN(g.p) },
                    { label: 'Jumlah', value: fmtN(g.v) }
                ],
                onClick: function () {
                    companyBreakdownModal(g.label, rows, function (r) {
                        return ((r.tenaga[g.key] || {}).l || 0) + ((r.tenaga[g.key] || {}).p || 0);
                    }, fmtN);
                }
            };
        }), { labelMax: 190, valueW: 90, aria: 'Tenaga kerja menurut pendidikan' });

        var totL = list.reduce(function (a, g) { return a + g.l; }, 0);
        var totP = list.reduce(function (a, g) { return a + g.p; }, 0);
        simpleTable(shell.tablePane, ['Jenjang pendidikan', 'Laki-laki', 'Perempuan', 'Jumlah', 'Pangsa'],
            list.map(function (g) { return [g.label, fmtN(g.l), fmtN(g.p), fmtN(g.v), share(g.v, total)]; })
                .concat([['JUMLAH', fmtN(totL), fmtN(totP), fmtN(total), '100%']]), 1);
    }

    /* ═══════════════ CARD — kualifikasi ═══════════════ */

    function renderKualifikasi() {
        var shell = cardShell('card-kualifikasi', 'Perusahaan menurut kualifikasi',
            'Blok I rincian 6h — kualifikasi SBU perusahaan');

        var rows = filteredRows();
        var t = theme();
        var colors = [t.s1, t.s2, t.s3, t.s4];

        var by = {};
        rows.forEach(function (r) {
            var k = r.kualifikasi || '';
            if (!by[k]) by[k] = { n: 0, nilai: 0 };
            by[k].n += 1;
            by[k].nilai += r.nilaiKonstruksi || 0;
        });

        var list = Object.keys(by).map(function (k, i) {
            return { code: k, label: kualLabel(k), n: by[k].n, nilai: by[k].nilai, color: colors[i % colors.length] };
        }).sort(function (a, b) { return b.n - a.n; });

        if (!list.length) {
            emptyState(shell.chartPane, 'Belum ada perusahaan pada filter ini.');
            return;
        }

        var total = rows.length;
        list.forEach(function (g) { legendItem(shell.legend, 'bar', g.color, g.label); });

        barRows(shell.chartPane, list.map(function (g) {
            return {
                label: g.label,
                sub: share(g.n, total) + ' · nilai ' + fmtRp(g.nilai),
                v: g.n,
                valueLabel: fmtN(g.n),
                color: g.color,
                tip: [
                    { label: 'Perusahaan', value: fmtN(g.n) },
                    { label: 'Nilai konstruksi', value: fmtRpFull(g.nilai) }
                ],
                onClick: function () {
                    openModal('Kualifikasi ' + g.label, filterPills(), function (body) {
                        var s = sect(body, 'Daftar perusahaan');
                        simpleTable(s, ['Perusahaan', 'Kab/Kota', 'Proyek', 'Nilai konstruksi'],
                            rows.filter(function (r) { return (r.kualifikasi || '') === g.code; })
                                .sort(function (a, b) { return (b.nilaiKonstruksi || 0) - (a.nilaiKonstruksi || 0); })
                                .map(function (r) {
                                    return [r.perusahaan, r.kabupaten || '—', fmtN(r.jumlahProyek), fmtRpFull(r.nilaiKonstruksi)];
                                }), 2);
                    });
                }
            };
        }), { labelMax: 190, valueW: 90, aria: 'Perusahaan menurut kualifikasi' });

        simpleTable(shell.tablePane, ['Kualifikasi', 'Perusahaan', 'Pangsa', 'Nilai konstruksi'],
            list.map(function (g) { return [g.label, fmtN(g.n), share(g.n, total), fmtRpFull(g.nilai)]; }), 1);
    }

    /* ═══════════════ CARD — bahan/material ═══════════════ */

    function renderBahan() {
        var shell = cardShell('card-bahan', 'Bahan / material proyek utama',
            'Blok VII — digabung menurut nama bahan yang dituliskan responden');

        if (isTwPeriod()) { emptyState(shell.chartPane, TW_NOT_COLLECTED); return; }

        var rows = filteredRows();
        var t = theme();

        // Names are free text, so fold case and whitespace before grouping —
        // "Besi Beton" and "besi beton" are the same material.
        var by = {};
        rows.forEach(function (r) {
            r.bahan.forEach(function (b) {
                var raw = (b.nama || '').trim();
                if (!raw) return;
                var key = raw.toLowerCase().replace(/\s+/g, ' ');
                if (!by[key]) by[key] = { label: raw, v: 0, n: 0, firms: {} };
                by[key].v += b.nilai || 0;
                by[key].n += 1;
                by[key].firms[r.uid] = true;
            });
        });

        var list = Object.keys(by).map(function (k) { return by[k]; })
            .sort(function (a, b) { return b.v - a.v; });

        if (!list.length) {
            emptyState(shell.chartPane, 'Belum ada bahan/material yang diisi pada filter ini.');
            return;
        }

        var total = list.reduce(function (a, g) { return a + g.v; }, 0);
        legendItem(shell.legend, 'bar', t.s2, 'Nilai bahan/material');

        barRows(shell.chartPane, list.slice(0, 12).map(function (g) {
            return {
                label: g.label,
                sub: Object.keys(g.firms).length + ' perusahaan · ' + share(g.v, total),
                v: g.v,
                valueLabel: fmtRp(g.v),
                color: t.s2,
                tip: [
                    { label: 'Nilai', value: fmtRpFull(g.v) },
                    { label: 'Disebut', value: g.n + '×' },
                    { label: 'Pangsa', value: share(g.v, total) }
                ]
            };
        }), { labelMax: 220, valueW: 110, aria: 'Bahan/material proyek utama' });

        simpleTable(shell.tablePane, ['Bahan / material', 'Perusahaan', 'Disebut', 'Nilai', 'Pangsa'],
            list.map(function (g) {
                return [g.label, fmtN(Object.keys(g.firms).length), g.n + '×', fmtRpFull(g.v), share(g.v, total)];
            }), 1);
    }

    /* ═══════════════ CARD — proyek terbesar ═══════════════ */

    function renderProyek() {
        var shell = cardShell('card-proyek', 'Proyek terbesar',
            'Sepuluh proyek dengan nilai konstruksi diselesaikan tertinggi');

        var rows = filteredRows();
        var t = theme();
        var all = [];
        rows.forEach(function (r) { r.proyek.forEach(function (p) { all.push({ r: r, p: p }); }); });
        all.sort(function (a, b) { return (b.p.nilai || 0) - (a.p.nilai || 0); });

        if (!all.length) {
            emptyState(shell.chartPane, 'Belum ada proyek pada filter ini.');
            return;
        }

        legendItem(shell.legend, 'bar', t.s3, 'Nilai konstruksi diselesaikan');

        barRows(shell.chartPane, all.slice(0, 10).map(function (x) {
            return {
                label: x.p.nama || 'Tanpa nama',
                sub: x.r.perusahaan + ' · ' + (x.p.kabkota || '—') + ' · realisasi ' + fmtPct(x.p.realisasi),
                v: x.p.nilai,
                valueLabel: fmtRp(x.p.nilai),
                color: t.s3,
                tip: [
                    { label: 'Nilai kontrak', value: fmtRpFull(x.p.kontrak) },
                    { label: 'Disubkontrakkan', value: fmtRpFull(x.p.subkontrak) },
                    { label: 'Bahan/material', value: fmtRpFull(x.p.bahan) },
                    { label: 'Upah harian', value: fmtRpFull(x.p.upah) },
                    { label: 'Pelaksanaan', value: (x.p.mulai || '—') + ' → ' + (x.p.selesai || '—') }
                ]
            };
        }), { labelMax: 240, valueW: 110, rowH: 42, aria: 'Proyek terbesar' });

        simpleTable(shell.tablePane,
            ['Proyek', 'Perusahaan', 'Lokasi', 'Sumber dana', 'Realisasi', 'Kontrak', 'Nilai konstruksi'],
            all.map(function (x) {
                return [x.p.nama || '—', x.r.perusahaan, x.p.kabkota || '—',
                    DATA.sumberDana[x.p.sumberDana] || '—', fmtPct(x.p.realisasi),
                    fmtRpFull(x.p.kontrak), fmtRpFull(x.p.nilai)];
            }), 4);
    }

    /* ═══════════════ CARD — tabel perusahaan ═══════════════ */

    var TABLE_COLS = [
        { key: 'perusahaan', label: 'Perusahaan', num: false },
        { key: 'kabupaten', label: 'Kab/Kota', num: false },
        { key: 'kualifikasi', label: 'Kualifikasi', num: false },
        { key: 'jumlahProyek', label: 'Proyek', num: true },
        { key: 'nilaiKontrak', label: 'Nilai kontrak', num: true },
        { key: 'nilaiKonstruksi', label: 'Nilai konstruksi', num: true },
        { key: 'tenaga', label: 'Tenaga kerja', num: true },
        { key: 'marginPct', label: 'Keuntungan', num: true },
        { key: 'updatedTs', label: 'Diperbarui', num: true }
    ];

    function sortValue(r, key) {
        if (key === 'tenaga') return totalTenaga(r);
        if (key === 'kualifikasi') return kualLabel(r.kualifikasi);
        if (key === 'kabupaten') return r.kabupaten || '';
        return r[key];
    }

    function cellFor(r, key) {
        switch (key) {
            case 'perusahaan': return r.perusahaan;
            case 'kabupaten': return r.kabupaten || '—';
            case 'kualifikasi': return kualLabel(r.kualifikasi);
            case 'jumlahProyek': return fmtN(r.jumlahProyek);
            case 'nilaiKontrak': return fmtRpFull(r.nilaiKontrak);
            case 'nilaiKonstruksi': return fmtRpFull(r.nilaiKonstruksi);
            case 'tenaga': return fmtN(totalTenaga(r));
            case 'marginPct': return r.marginPct === null ? '—' : fmtPct(r.marginPct);
            case 'updatedTs': return r.updatedAt || '—';
            default: return '—';
        }
    }

    function renderTable() {
        var card = document.getElementById('card-table');
        clear(card);

        var head = el('div', 'stx-chart-head');
        var titles = el('div');
        titles.appendChild(el('div', 'stx-chart-title', 'Rincian per perusahaan'));
        titles.appendChild(el('div', 'stx-chart-sub', 'Klik judul kolom untuk mengurutkan · klik baris untuk rincian lengkap'));
        head.appendChild(titles);
        card.appendChild(head);

        var rows = filteredRows().slice().sort(function (a, b) {
            var av = sortValue(a, state.sortKey), bv = sortValue(b, state.sortKey);
            if (typeof av === 'string' || typeof bv === 'string') {
                return String(av).localeCompare(String(bv), 'id') * state.sortDir;
            }
            if (av === null || av === undefined) return 1;
            if (bv === null || bv === undefined) return -1;
            return (av - bv) * state.sortDir;
        });

        if (!rows.length) {
            emptyState(card, 'Tidak ada perusahaan yang cocok dengan filter ini.');
            return;
        }

        var shown = rows.slice(0, state.tableLimit);

        var wrap = el('div', 'stx-tablewrap');
        var t = el('table', 'stx-table');
        var thead = el('thead'), trh = el('tr');
        TABLE_COLS.forEach(function (c) {
            var th = el('th', c.num ? 'num' : null, c.label + (state.sortKey === c.key ? (state.sortDir < 0 ? ' ↓' : ' ↑') : ''));
            th.style.cursor = 'pointer';
            th.addEventListener('click', function () {
                if (state.sortKey === c.key) state.sortDir = -state.sortDir;
                else { state.sortKey = c.key; state.sortDir = c.num ? -1 : 1; }
                renderTable();
            });
            trh.appendChild(th);
        });
        thead.appendChild(trh);
        t.appendChild(thead);

        var tb = el('tbody');
        shown.forEach(function (r) {
            var tr = el('tr');
            tr.style.cursor = 'pointer';
            TABLE_COLS.forEach(function (c, i) {
                tr.appendChild(el('td', c.num ? 'num' : (i === 0 ? 'strong' : null), cellFor(r, c.key)));
            });
            tr.addEventListener('click', function () { companyModal(r); });
            tb.appendChild(tr);
        });
        t.appendChild(tb);
        wrap.appendChild(t);
        card.appendChild(wrap);

        pagerFoot(card, rows.length, shown.length);
    }

    /** Show-more / show-less footer, matching the other statistik tables. */
    function pagerFoot(card, total, limit) {
        if (total <= TABLE_PAGE) return;

        var foot = el('div', 'stx-more');
        foot.appendChild(el('span', 'cnt', 'Menampilkan ' + limit + ' dari ' + total + ' perusahaan'));
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

    function companyModal(r) {
        openModal(r.perusahaan, [periodLabel(r.periode), r.kabupaten || '—', kualLabel(r.kualifikasi),
            r.selesai ? 'Selesai' : 'Draf ' + r.progress + '%'], function (body) {

            var s1 = sect(body, 'Ringkasan Blok IX');
            kvGrid(s1, [
                ['Jumlah pengeluaran (A)', fmtRpFull(r.totalA)],
                ['Jumlah pendapatan (B)', fmtRpFull(r.totalB)],
                ['Keuntungan thd pengeluaran', r.marginPct === null ? '—' : fmtPct(r.marginPct)],
                ['Nilai konstruksi diselesaikan', fmtRpFull(r.nilaiKonstruksi)],
                ['Nilai kontrak', fmtRpFull(r.nilaiKontrak)],
                ['Pendapatan lain', fmtRpFull(r.pendapatanLain)]
            ]);

            var s2 = sect(body, 'Tenaga kerja & balas jasa');
            var pairs = [];
            Object.keys(DATA.pendidikan).forEach(function (j) {
                var d = r.tenaga[j] || { l: 0, p: 0 };
                pairs.push([DATA.pendidikan[j], 'L ' + fmtN(d.l) + ' · P ' + fmtN(d.p)]);
            });
            pairs.push(['Jumlah pekerja', fmtN(totalTenaga(r))]);
            pairs.push(['Balas jasa', fmtRpFull(r.balasJasa)]);
            pairs.push(['Upah pekerja harian', fmtRpFull(r.upahHarian)]);
            kvGrid(s2, pairs);

            var s3 = sect(body, 'Pengeluaran operasional (Blok IV)');
            simpleTable(s3, ['Rincian', 'Nilai'],
                Object.keys(DATA.pengeluaran).map(function (k) {
                    return [DATA.pengeluaran[k], fmtRpFull(r.pengeluaran[k] || 0)];
                }).concat([['JUMLAH', fmtRpFull(sumObj(r.pengeluaran))]]), 1);

            if (r.proyek.length) {
                var s4 = sect(body, 'Proyek (Blok VI)');
                simpleTable(s4, ['Proyek', 'Lokasi', 'Sumber dana', 'Realisasi', 'Kontrak', 'Nilai konstruksi'],
                    r.proyek.slice().sort(function (a, b) { return (b.nilai || 0) - (a.nilai || 0); })
                        .map(function (p) {
                            return [p.nama || '—', p.kabkota || '—', DATA.sumberDana[p.sumberDana] || '—',
                                fmtPct(p.realisasi), fmtRpFull(p.kontrak), fmtRpFull(p.nilai)];
                        }), 3);
            }

            if (r.bahan.length) {
                var s5 = sect(body, 'Bahan / material (Blok VII)');
                simpleTable(s5, ['Bahan', 'Nilai'],
                    r.bahan.map(function (b) { return [b.nama || '—', fmtRpFull(b.nilai)]; }), 1);
            }
        });
    }

    /* ═══════════════ render pipeline ═══════════════ */

    function rerenderData() {
        renderKpis();
        renderWilayah();
        renderStruktur();
        renderDana();
        renderTenaga();
        renderKualifikasi();
        renderBahan();
        renderProyek();
        renderTable();
    }

    function rerender() {
        renderFilters();
        rerenderData();
    }

    // Bar charts size themselves off clientWidth, so a resize has to redraw.
    var resizeT = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeT);
        resizeT = setTimeout(rerenderData, 180);
    });

    if (!DATA.rows.length || !DATA.periods.length) {
        var kpiWrap = document.getElementById('stx-kpis');
        if (kpiWrap) {
            var note = el('div', 'stx-card');
            note.style.padding = '2rem';
            note.style.textAlign = 'center';
            note.textContent = 'Belum ada data Survei Konstruksi yang masuk.';
            kpiWrap.appendChild(note);
        }
        return;
    }

    rerender();
})();
