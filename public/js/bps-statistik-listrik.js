/**
 * Statistik Listrik — BPS-only dashboard over Survei Listrik data.
 *
 * Same architecture as bps-statistik.js (SIBSTR): payload embedded by the
 * Blade view, all filtering client-side, hand-rolled theme-aware SVG charts.
 * All dynamic strings enter the DOM via textContent.
 */
(function () {
    'use strict';

    var DATA = window.__LISTRIK_STAT__;
    if (!DATA || !document.getElementById('stx-kpis')) return;

    var MONTH_SHORT = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    var TW_ROMAN = { 1: 'I', 2: 'II', 3: 'III', 4: 'IV' };
    var CATS = Object.keys(DATA.categories);
    function monthQuarter(m) { return Math.ceil(m / 3); }

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
            good: cssVar('--stx-good'), bad: cssVar('--stx-bad'),
            wash: cssVar('--stx-wash')
        };
    }

    /* ═══════════════ formatting (id-ID) ═══════════════ */

    var nfFull = new Intl.NumberFormat('id-ID');
    var nfCompact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 });
    var nfPct = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });
    function fmtKwh(v) { return v === null || v === undefined ? '—' : nfCompact.format(v) + ' KWH'; }
    function fmtKwhFull(v) { return v === null || v === undefined ? '—' : nfFull.format(v) + ' KWH'; }
    function fmtRp(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfCompact.format(v); }
    function fmtRpFull(v) { return v === null || v === undefined ? '—' : 'Rp ' + nfFull.format(v); }
    function fmtN(v) { return v === null || v === undefined ? '—' : nfFull.format(v); }
    function fmtNc(v) { return v === null || v === undefined ? '—' : nfCompact.format(v); }
    function fmtUnit(unit) { return unit === 'kwh' ? fmtKwh : fmtRp; }
    function fmtUnitFull(unit) { return unit === 'kwh' ? fmtKwhFull : fmtRpFull; }
    function monthLabel(key) {
        var p = key.split('_');
        return MONTH_SHORT[parseInt(p[1], 10)] + ' ' + p[0];
    }
    function monthShort(key) {
        var p = key.split('_');
        return MONTH_SHORT[parseInt(p[1], 10)] + ' ' + p[0].slice(2);
    }

    /* ═══════════════ state + filtering ═══════════════ */

    // katSel / bulanSel: checkbox multi-select (empty = semua). Status defaults
    // to Selesai.
    // Triwulan and Bulan stack: the quarter picks the window and ticks its own
    // months in Bulan, which can then be unticked one at a time. So "TW I" is
    // visibly "Jan + Feb + Mar", not an opaque mode.
    // qoq holds the key of the *current* quarter of the growth pair (the previous
    // one is whatever sits before it chronologically); qoqTouched records that
    // the user picked it by hand, after which Triwulan stops moving it.
    var state = { tahun: 'all', triwulan: 'all', bulanSel: {}, qoq: null, qoqTouched: false, katSel: {}, status: 'done', excluded: {}, sortKey: 'kwh', sortDir: -1, tableLimit: 10 };
    function katSelCount() { return Object.keys(state.katSel).length; }
    function katSelected(cat) { return !katSelCount() || !!state.katSel[cat]; }

    var EXCL_KEY = 'stx-lst-excl';
    try {
        (JSON.parse(sessionStorage.getItem(EXCL_KEY) || '[]') || []).forEach(function (uid) { state.excluded[uid] = true; });
    } catch (e) { /* start clean */ }
    function saveExcluded() {
        try { sessionStorage.setItem(EXCL_KEY, JSON.stringify(Object.keys(state.excluded))); } catch (e) {}
    }
    function matchesStatus(r) {
        if (state.status === 'done' && !r.selesai) return false;
        if (state.status === 'draft' && r.selesai) return false;
        return true;
    }
    /**
     * Faceted filtering, `skip` naming the one facet to ignore so a control can
     * count its options against every OTHER filter without narrowing itself.
     *
     * Only Status and Perusahaan drop rows here — Tahun/Bulan/Kategori pick
     * which months and categories get summed, so a company outside that window
     * still belongs to the dashboard (it just reports nothing). Their option
     * counts therefore read "punya data", not "perusahaan".
     */
    function poolFor(skip) {
        return DATA.rows.filter(function (r) {
            if (skip !== 'company' && state.excluded[r.uid]) return false;
            if (skip !== 'status' && !matchesStatus(r)) return false;
            return true;
        });
    }
    function filteredRows() { return poolFor(null); }

    /**
     * Companies the other row filters still admit — the pool the picker lists.
     * A company Status already drops must not sit there checked, claiming to be
     * part of the aggregation.
     */
    function eligibleCompanies() { return poolFor('company'); }
    function excludedEligible() {
        return eligibleCompanies().filter(function (c) { return state.excluded[c.uid]; }).length;
    }
    // companies in `rows` reporting anything across the given months × categories
    function nWithData(rows, months, cats) {
        return rows.filter(function (r) { return rowSum(r, months, cats, 'kwh') !== null; }).length;
    }
    /** Months the Tahun + Triwulan window admits — the pool the Bulan picker lists. */
    function scopedMonths() {
        return DATA.months.filter(function (m) {
            if (state.tahun !== 'all' && m.year !== state.tahun) return false;
            if (state.triwulan !== 'all' && monthQuarter(m.month) !== state.triwulan) return false;
            return true;
        });
    }
    function bulanSelCount() { return Object.keys(state.bulanSel).length; }
    function activeMonths() {
        return scopedMonths()
            .filter(function (m) { return !bulanSelCount() || state.bulanSel[m.key]; })
            .map(function (m) { return m.key; });
    }
    /**
     * Names the months a total actually covers. "Seluruh bulan pada irisan
     * filter" described the machinery, not the period: the reader still could
     * not tell whether a figure was one month or twenty. A contiguous run reads
     * as a range; a broken one (TW II of two different years, or a month the
     * user unticked) can only be counted, so the full list goes in the tooltip.
     */
    function monthWindowLabel() {
        var ms = activeMonths();
        if (!ms.length) return { text: 'tidak ada bulan terpilih', title: '' };
        if (ms.length === 1) return { text: monthLabel(ms[0]), title: 'Total untuk ' + monthLabel(ms[0]) };
        var all = DATA.months.map(function (m) { return m.key; });
        var contiguous = (all.indexOf(ms[ms.length - 1]) - all.indexOf(ms[0]) + 1) === ms.length;
        return {
            text: contiguous
                ? ms.length + ' bulan · ' + monthShort(ms[0]) + '–' + monthShort(ms[ms.length - 1])
                : ms.length + ' bulan terpilih',
            title: 'Total mencakup ' + ms.length + ' bulan: ' + ms.map(monthLabel).join(', ')
        };
    }

    /** Ticks every month of the current window — how a quarter shows its months. */
    function checkAllScopedMonths() {
        state.bulanSel = {};
        scopedMonths().forEach(function (m) { state.bulanSel[m.key] = true; });
    }
    /** Drops ticks that the new Tahun/Triwulan window no longer contains. */
    function pruneBulanSel() {
        var inScope = {};
        scopedMonths().forEach(function (m) { inScope[m.key] = true; });
        Object.keys(state.bulanSel).forEach(function (k) { if (!inScope[k]) delete state.bulanSel[k]; });
    }
    function activeCats() {
        return katSelCount() ? CATS.filter(function (c) { return state.katSel[c]; }) : CATS;
    }

    // Sum of one field for a row over months × categories (null when nothing reported)
    function rowSum(r, months, cats, f) {
        var any = false, total = 0;
        months.forEach(function (ym) {
            cats.forEach(function (cat) {
                var v = r.monthly[ym] && r.monthly[ym][cat] ? r.monthly[ym][cat][f] : null;
                if (v !== null && v !== undefined) { any = true; total += v; }
            });
        });
        return any ? total : null;
    }
    function groupSum(rows, months, cats, f) {
        var any = false, total = 0;
        rows.forEach(function (r) {
            var v = rowSum(r, months, cats, f);
            if (v !== null) { any = true; total += v; }
        });
        return any ? total : null;
    }

    /* ═══════════════ quarters + q-to-q growth ═══════════════ */

    /**
     * Every quarter the payload covers, chronologically, each holding its month
     * keys. Built from all months regardless of the Tahun/Triwulan/Bulan
     * pickers: the baseline of a q-to-q figure routinely sits outside the window
     * being viewed — TW I is measured against TW IV of the year before.
     */
    var ALL_QUARTERS = (function () {
        var byKey = {}, list = [];
        DATA.months.forEach(function (m) {
            var q = monthQuarter(m.month), key = m.year + 'Q' + q;
            if (!byKey[key]) {
                byKey[key] = { key: key, year: m.year, q: q, months: [] };
                list.push(byKey[key]);
            }
            byKey[key].months.push(m.key);
        });
        list.sort(function (a, b) { return a.year - b.year || a.q - b.q; });
        return list;
    })();
    function quarterLabel(qd) { return 'TW ' + TW_ROMAN[qd.q] + ' ' + qd.year; }

    /**
     * Every quarter transition the payload can report, oldest first. The
     * baseline is the quarter immediately before, so TW I 2026 pairs with
     * TW IV 2025 — the transition does not stop at a year boundary.
     *
     * The option list is scoped to the active Tahun by the *current* quarter
     * only: a 2026 transition stays offered even though its baseline is 2025.
     */
    function qoqPairs() {
        var out = [];
        for (var i = 1; i < ALL_QUARTERS.length; i++) {
            if (state.tahun !== 'all' && ALL_QUARTERS[i].year !== state.tahun) continue;
            out.push({ cur: ALL_QUARTERS[i], prev: ALL_QUARTERS[i - 1] });
        }
        return out;
    }
    /**
     * The dashboard opens on TW II vs TW I — the most recent one when several
     * years are in scope. With no TW II transition available the newest
     * transition stands in, so the picker is never empty-but-shown.
     */
    function defaultQoqKey() {
        var pairs = qoqPairs();
        if (!pairs.length) return null;
        for (var i = pairs.length - 1; i >= 0; i--) if (pairs[i].cur.q === 2) return pairs[i].cur.key;
        return pairs[pairs.length - 1].cur.key;
    }
    /** The two quarters every q-to-q figure compares — whatever the picker holds. */
    function qoqPair() {
        for (var i = 1; i < ALL_QUARTERS.length; i++) {
            if (ALL_QUARTERS[i].key === state.qoq) return { cur: ALL_QUARTERS[i], prev: ALL_QUARTERS[i - 1] };
        }
        return null;
    }
    /**
     * Keeps the growth pair legal after Tahun/Triwulan move. An explicit choice
     * is only overridden when it has fallen out of the Tahun scope entirely —
     * otherwise the picker would silently undo the user.
     */
    function syncQoq() {
        var pairs = qoqPairs();
        var stillValid = pairs.some(function (p) { return p.cur.key === state.qoq; });
        if (!stillValid) { state.qoq = defaultQoqKey(); return; }
        if (state.qoqTouched || state.triwulan === 'all') return;
        // untouched: follow the focused quarter, newest year first
        for (var i = pairs.length - 1; i >= 0; i--) {
            if (pairs[i].cur.q === state.triwulan) { state.qoq = pairs[i].cur.key; return; }
        }
    }
    function qoqPairLabel(pair) { return quarterLabel(pair.cur) + ' vs ' + quarterLabel(pair.prev); }
    // KPI tiles are a sixth of the row wide — the label has to survive there
    // without wrapping onto a third line, so the year is stated once when both
    // quarters share it
    function qoqPairLabelShort(pair) {
        return pair.cur.year === pair.prev.year
            ? 'TW ' + TW_ROMAN[pair.cur.q] + ' vs ' + TW_ROMAN[pair.prev.q] + ' ' + pair.cur.year
            : quarterLabel(pair.cur) + ' vs ' + quarterLabel(pair.prev);
    }
    /**
     * The newest quarter is usually still running — Agustus is 2 of the 3 months
     * of TW III. Its total is then compared against a complete quarter, which
     * reads as a collapse unless the reader is told. Every place that prints a
     * q-to-q figure carries this warning.
     */
    function qoqPartial(pair) {
        return pair.cur.months.length < 3
            ? quarterLabel(pair.cur) + ' masih berjalan (' + pair.cur.months.length + ' dari 3 bulan), sehingga pertumbuhannya belum sebanding dengan triwulan penuh.'
            : '';
    }

    /**
     * Growth against the previous quarter, in percent. null whenever either side
     * is unreported or the base is 0 — a rate off nothing is not a rate, and
     * printing 0% there would read as "tidak berubah".
     */
    function pctChange(cur, prev) {
        if (cur === null || cur === undefined || prev === null || prev === undefined || prev === 0) return null;
        return (cur - prev) / Math.abs(prev) * 100;
    }
    function fmtSigned(pct) {
        if (pct === null) return '—';
        var sign = pct > 0.05 ? '+' : (pct < -0.05 ? '−' : '±');
        return sign + nfPct.format(Math.abs(pct)) + '%';
    }
    function qoqClass(pct) {
        if (pct === null) return 'na';
        return pct > 0.05 ? 'up' : (pct < -0.05 ? 'down' : 'flat');
    }
    function qoqArrow(pct) {
        if (pct === null) return '';
        return pct > 0.05 ? '▲ ' : (pct < -0.05 ? '▼ ' : '• ');
    }
    /**
     * Heading for a q-to-q column. Every one of them names the two quarters it
     * compares: these tables get screenshotted and pasted into reports, where a
     * bare "q-to-q" leaves no way to tell which transition the Pertumbuhan
     * filter was on.
     */
    function qoqHead(unit, pair) {
        return 'q-to-q' + (unit ? ' ' + unit : '') + (pair ? ' (' + qoqPairLabelShort(pair) + ')' : '');
    }
    function qoqColor(t, pct) {
        if (pct === null) return t.muted;
        return pct > 0.05 ? t.good : (pct < -0.05 ? t.bad : t.muted);
    }
    // q-to-q of a whole slice (rows × categories) for one field
    function groupQoq(rows, cats, f, pair) {
        pair = pair || qoqPair();
        if (!pair) return null;
        return pctChange(groupSum(rows, pair.cur.months, cats, f), groupSum(rows, pair.prev.months, cats, f));
    }
    // q-to-q of a single respondent for one field
    function rowQoq(r, cats, f, pair) {
        pair = pair || qoqPair();
        if (!pair) return null;
        return pctChange(rowSum(r, pair.cur.months, cats, f), rowSum(r, pair.prev.months, cats, f));
    }
    state.qoq = defaultQoqKey();

    /** Plain-text q-to-q line for a KPI tile. */
    function qoqLine(rows, cats, f) {
        var pair = qoqPair();
        var line = el('div', 'stx-qoq');
        if (!pair) {
            line.className = 'stx-qoq na';
            line.textContent = 'q-to-q — belum ada triwulan pembanding';
            return line;
        }
        var pct = groupQoq(rows, cats, f, pair);
        var partial = qoqPartial(pair);
        line.className = 'stx-qoq ' + qoqClass(pct);
        line.textContent = qoqArrow(pct) + fmtSigned(pct) + ' q-to-q (' + qoqPairLabelShort(pair) + (partial ? '*' : '') + ')';
        line.title = 'Pertumbuhan triwulanan ' + qoqPairLabel(pair) + ' — dihitung dari total triwulan penuh pada irisan filter aktif'
            + (partial ? '. ' + partial : '');
        return line;
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

    /* ═══════════════ popover (perusahaan picker) ═══════════════ */

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

        // optional unit toggle [KWH | Rp]
        if (opts && opts.unitKey) {
            var unitToggle = el('div', 'stx-toggle');
            [['kwh', 'KWH'], ['rp', 'Rupiah']].forEach(function (u) {
                var b = el('button', state[opts.unitKey] === u[0] ? 'on' : null, u[1]);
                b.type = 'button';
                b.addEventListener('click', function () {
                    if (state[opts.unitKey] !== u[0]) { state[opts.unitKey] = u[0]; rerenderData(); }
                });
                unitToggle.appendChild(b);
            });
            actions.appendChild(unitToggle);
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
    // Replaces only a table this helper mounted earlier, never the whole node:
    // most callers pass a .stx-sect that already holds its heading, and a blanket
    // clear() silently deleted the very title labelling the table.
    function simpleTable(pane, headers, rows, numericFrom) {
        var old = pane.querySelector('.stx-tablewrap');
        if (old && old.parentNode === pane) pane.removeChild(old);
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

    /* ═══════════════ FILTER BAR ═══════════════ */

    function renderFilters() {
        var bar = document.getElementById('stx-filters');
        closePop();
        clear(bar);
        facetRefreshers = [];

        function mKey(m) { return m.key; }
        function dataSub(n) { return n ? n + ' perusahaan punya data' : 'Kosong pada filter lain'; }

        // Tahun — counted against Kategori + Status + Perusahaan
        ddSingle(bar, 'Tahun', function () {
            var rows = filteredRows(), cats = activeCats();
            var opts = [{ v: 'all', t: 'Semua tahun', sub: dataSub(nWithData(rows, DATA.months.map(mKey), cats)) }];
            DATA.years.forEach(function (y) {
                var n = nWithData(rows, DATA.months.filter(function (m) { return m.year === y; }).map(mKey), cats);
                // never disable the active option — it stays the way back out
                opts.push({ v: y, t: String(y), disabled: state.tahun !== y && !n, sub: dataSub(n) });
            });
            return opts;
        }, state.tahun, function (v) {
            state.tahun = v;
            // A focused quarter keeps every one of its months ticked in the new
            // year; otherwise just drop the months this year no longer contains.
            if (state.triwulan !== 'all') checkAllScopedMonths(); else pruneBulanSel();
            syncQoq();
            rerender();
        });

        // Triwulan — the quarter window, scoped to the chosen year. Bulan is not
        // replaced by it: the two stack, so a quarter can still be drilled into
        // one of its months.
        ddSingle(bar, 'Triwulan', function () {
            var rows = filteredRows(), cats = activeCats();
            var ms = DATA.months.filter(function (m) { return state.tahun === 'all' || m.year === state.tahun; });
            var opts = [{ v: 'all', t: 'Semua triwulan', sub: dataSub(nWithData(rows, ms.map(mKey), cats)) }];
            [1, 2, 3, 4].forEach(function (q) {
                var qm = ms.filter(function (m) { return monthQuarter(m.month) === q; });
                var n = qm.length ? nWithData(rows, qm.map(mKey), cats) : 0;
                opts.push({
                    v: q,
                    t: 'Triwulan ' + TW_ROMAN[q],
                    // never disable the active option — it stays the way back out
                    disabled: state.triwulan !== q && !qm.length,
                    sub: !qm.length ? 'Di luar periode survei' : dataSub(n)
                });
            });
            return opts;
        }, state.triwulan, function (v) {
            state.triwulan = v;
            // Picking a quarter ticks its months in Bulan, so the window it
            // opened is visible and can be narrowed further; "Semua triwulan"
            // hands the choice back to Bulan untouched.
            if (v !== 'all') checkAllScopedMonths(); else state.bulanSel = {};
            syncQoq();
            rerender();
        });

        // Bulan — scoped to the chosen year and quarter, counted against the rest.
        // Multi-select, so a quarter can show its own months ticked and the user
        // can drop one without leaving the quarter.
        ddMulti(bar, 'Bulan', function () {
            var rows = filteredRows(), cats = activeCats();
            return scopedMonths().map(function (m) {
                var n = nWithData(rows, [m.key], cats);
                return { v: m.key, t: monthLabel(m.key), n: n, sub: dataSub(n), disabled: !n };
            });
        }, state.bulanSel, 'Semua bulan',
            function () { rerenderData(); refreshFilterBar(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // Pertumbuhan q-to-q — which quarter transition every growth figure
        // reports. Independent of the Tahun/Triwulan/Bulan window above: those
        // choose the levels shown, this one chooses the growth.
        var pairs = qoqPairs();
        if (pairs.length) {
            ddSingle(bar, 'Pertumbuhan', function () {
                var rows = filteredRows(), cats = activeCats();
                return pairs.map(function (p) {
                    var n = rows.filter(function (r) {
                        return rowSum(r, p.cur.months, cats, 'kwh') !== null && rowSum(r, p.prev.months, cats, 'kwh') !== null;
                    }).length;
                    return {
                        v: p.cur.key,
                        t: qoqPairLabelShort(p),
                        sub: (n ? n + ' perusahaan punya data di kedua triwulan' : 'Kosong pada filter lain')
                            + (p.cur.months.length < 3 ? ' · triwulan berjalan' : '')
                    };
                });
            }, state.qoq, function (v) { state.qoq = v; state.qoqTouched = true; rerender(); });

            bar.appendChild(el('div', 'stx-filter-sep'));
        }

        // Kategori — counted against the active month window + Status + Perusahaan
        ddMulti(bar, 'Kategori', function () {
            var rows = filteredRows(), months = activeMonths();
            return CATS.map(function (cat) {
                var n = nWithData(rows, months, [cat]);
                return { v: cat, t: DATA.categories[cat], n: n, sub: dataSub(n), disabled: !n };
            });
        }, state.katSel, 'Semua kategori',
            function () { rerenderData(); refreshFilterBar(); });

        bar.appendChild(el('div', 'stx-filter-sep'));

        // Perusahaan picker
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

        // Status — counted against Perusahaan. Subtitles carry the company count
        // each status yields, so it is clear up front that switching status also
        // resizes the Perusahaan picker.
        ddSingle(bar, 'Status', function () {
            var pool = poolFor('status');
            var nDone = pool.filter(function (r) { return r.selesai; }).length;
            return [
                { v: 'done', t: 'Selesai', sub: 'hanya isian final · ' + nDone + ' perusahaan' },
                { v: 'all', t: 'Semua status', sub: 'termasuk draf · ' + pool.length + ' perusahaan' },
                { v: 'draft', t: 'Masih draf', sub: 'belum diselesaikan · ' + (pool.length - nDone) + ' perusahaan' }
            ];
        }, state.status, function (v) { state.status = v; rerender(); });
    }

    function buildCompanyPicker(panel, refreshBtn) {
        var head = el('div', 'stx-pop-head');
        head.appendChild(el('div', 'stx-pop-title', 'Perusahaan dalam agregasi'));
        head.appendChild(el('div', 'stx-pop-sub', 'Hilangkan centang untuk mengeluarkan perusahaan dari seluruh perhitungan dashboard.'));
        var search = el('input', 'stx-pop-search');
        search.type = 'search';
        search.placeholder = 'Cari perusahaan…';
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
            var total = eligibleCompanies().length, hidden = DATA.rows.length - total;
            cnt.textContent = (total - excludedEligible()) + ' dari ' + total + ' perusahaan dihitung'
                + (hidden ? ' · ' + hidden + ' di luar filter status' : '');
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
                var hay = ((c.perusahaan || '') + ' ' + (c.jenisPembangkit || '')).toLowerCase();
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
                meta.appendChild(el('span', null, c.jenisPembangkit || 'Pembangkit —'));
                meta.appendChild(el('span', null, c.selesai ? 'Selesai' : 'Draf'));
                main.appendChild(meta);
                row.appendChild(main);
                // the active month/kategori window, so the number beside each
                // name matches what the dashboard is showing
                row.appendChild(el('span', 'p-val', fmtKwh(rowSum(c, activeMonths(), activeCats(), 'kwh'))));
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
                var none = el('div', 'stx-pop-group', q ? 'Tidak ada perusahaan yang cocok.' : 'Tidak ada perusahaan pada filter status ini.');
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

    function filterPills() {
        var pills = [];
        pills.push(state.tahun === 'all' ? 'Semua tahun' : 'Tahun ' + state.tahun);
        if (state.triwulan !== 'all') pills.push('Triwulan ' + TW_ROMAN[state.triwulan]);
        // Only worth a pill when it says something Tahun+Triwulan does not: a
        // fully ticked quarter is already described by its own pill.
        var bulanKeys = Object.keys(state.bulanSel);
        if (bulanKeys.length && bulanKeys.length < scopedMonths().length) {
            pills.push(bulanKeys.length === 1 ? 'Bulan ' + monthLabel(bulanKeys[0]) : bulanKeys.length + ' bulan dipilih');
        }
        var katKeys = Object.keys(state.katSel);
        if (katKeys.length === 1) pills.push(DATA.categories[katKeys[0]]);
        else if (katKeys.length > 1) pills.push(katKeys.length + ' kategori');
        var pair = qoqPair();
        if (pair) pills.push('q-to-q ' + qoqPairLabelShort(pair));
        if (state.status === 'done') pills.push('Hanya selesai');
        if (state.status === 'draft') pills.push('Hanya draf');
        if (excludedEligible()) pills.push(excludedEligible() + ' perusahaan dikecualikan');
        return pills;
    }

    /* ═══════════════ KPI ROW ═══════════════ */

    var KPI_ICONS = {
        melapor: 'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h1m4 0h1M9 12h1m4 0h1M9 16h1m4 0h1',
        kwh: 'M13 10V3L4 14h7v7l9-11h-7z',
        rp: 'M12 3a9 9 0 100 18 9 9 0 000-18zM14.5 9.3c-.4-.8-1.4-1.3-2.5-1.3-1.4 0-2.5.8-2.5 1.9s1.1 1.5 2.5 1.9c1.4.4 2.5.8 2.5 1.9s-1.1 1.9-2.5 1.9c-1.1 0-2.1-.5-2.5-1.3M12 6.7V8m0 8v1.3',
        harga: 'M3 17l6-6 4 4 8-8M14 7h7v7',
        kategori: 'M4 6h16M4 12h10M4 18h6',
        bulan: 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'
    };

    function kpiIcon(name) {
        var span = el('span', 'k-ico');
        var svg = svgEl('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.8, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' });
        svg.appendChild(svgEl('path', { d: KPI_ICONS[name] || KPI_ICONS.melapor }));
        span.appendChild(svg);
        return span;
    }

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
        for (var i = values.length - 1; i >= 0; i--) {
            if (values[i] !== null) {
                var x = i * step, y = 2 + (h - 4) * (1 - (values[i] - min) / (max - min));
                svg.appendChild(svgEl('circle', { cx: x, cy: y, r: 3, fill: t.s1, stroke: cssVar('--stx-surface'), 'stroke-width': 1.5 }));
                break;
            }
        }
        return svg;
    }

    function kpiTile(container, def) {
        var tile = el('button', 'stx-card stx-kpi');
        tile.type = 'button';
        var top = el('div', 'k-top');
        top.appendChild(el('div', 'k-label', def.label));
        if (def.icon) top.appendChild(kpiIcon(def.icon));
        tile.appendChild(top);
        tile.appendChild(el('div', 'k-value', def.value));
        if (def.qoq) tile.appendChild(def.qoq);
        var foot = el('div', 'k-foot');
        var left = el('div');
        if (def.sub) {
            var subEl = el('span', 'k-sub', def.sub);
            if (def.subTitle) subEl.title = def.subTitle;
            left.appendChild(subEl);
        }
        foot.appendChild(left);
        if (def.spark) foot.appendChild(def.spark);
        tile.appendChild(foot);
        tile.title = def.tooltip || 'Klik untuk rincian';
        tile.addEventListener('click', def.onClick);
        container.appendChild(tile);
    }

    function monthlyTotals(rows, cats, f) {
        return activeMonths().map(function (ym) {
            return groupSum(rows, [ym], cats, f);
        });
    }
    // Monthly totals feed the KPI sparklines; the growth figure itself is
    // quarterly only — a monthly rate beside it was one number too many.


    /**
     * `qoqField` ('kwh' | 'rp') adds a per-company q-to-q column. Metrics with
     * no single underlying field (harga = Rp ÷ KWH) pass none and keep the
     * plain three-column table.
     */
    function companyBreakdownModal(title, rows, valueFn, fmt, qoqField) {
        openModal(title, filterPills(), function (body) {
            var s = sect(body, 'Rincian per perusahaan');
            var pair = qoqField ? qoqPair() : null;
            var cats = activeCats();
            var sorted = rows.slice().sort(function (a, b) { return (valueFn(b) || 0) - (valueFn(a) || 0); });
            simpleTable(s, pair ? ['Perusahaan', 'Pembangkit', 'Nilai', qoqHead('', pair)] : ['Perusahaan', 'Pembangkit', 'Nilai'],
                sorted.map(function (r) {
                    var cells = [r.perusahaan, r.jenisPembangkit || '—', fmt(valueFn(r))];
                    if (pair) cells.push(fmtSigned(rowQoq(r, cats, qoqField, pair)));
                    return cells;
                }), 2);
            if (pair) {
                s.appendChild(el('p', 'stx-note', 'Kolom q-to-q membandingkan total ' + quarterLabel(pair.cur) + ' dengan ' + quarterLabel(pair.prev)
                    + ' untuk perusahaan yang sama — selalu triwulan penuh, terlepas dari filter Bulan. "—" berarti triwulan pembanding kosong atau bernilai nol. ' + qoqPartial(pair)));
            }
        });
    }

    function renderKpis() {
        var wrap = document.getElementById('stx-kpis');
        clear(wrap);
        var rows = filteredRows();
        var months = activeMonths();
        var cats = activeCats();
        var win = monthWindowLabel();

        // 1 — companies reporting
        var done = rows.filter(function (r) { return r.selesai; }).length;
        kpiTile(wrap, {
            label: 'Perusahaan melapor',
            icon: 'melapor',
            value: fmtN(rows.length),
            sub: done + ' selesai · ' + (rows.length - done) + ' draf',
            onClick: function () {
                openModal('Perusahaan melapor', filterPills(), function (body) {
                    var s = sect(body, 'Daftar perusahaan');
                    simpleTable(s, ['Perusahaan', 'Pembangkit', 'Status', 'Diperbarui'],
                        rows.map(function (r) { return [r.perusahaan, r.jenisPembangkit || '—', r.selesai ? 'Selesai' : 'Draf', r.updatedAt || '—']; }), 99);
                });
            }
        });

        // 2 — total KWH
        var kwhSeries = monthlyTotals(rows, cats, 'kwh');
        var totKwh = groupSum(rows, months, cats, 'kwh');
        kpiTile(wrap, {
            label: 'Total produksi listrik',
            icon: 'kwh',
            value: totKwh === null ? '—' : nfCompact.format(totKwh) + ' KWH',
            tooltip: fmtKwhFull(totKwh),
            qoq: qoqLine(rows, cats, 'kwh'),
            sub: win.text,
            subTitle: win.title,
            spark: sparkline(kwhSeries, 64, 26),
            onClick: function () {
                companyBreakdownModal('Total produksi listrik (KWH)', rows,
                    function (r) { return rowSum(r, months, cats, 'kwh'); }, fmtKwhFull, 'kwh');
            }
        });

        // 3 — total Rp
        var rpSeries = monthlyTotals(rows, cats, 'rp');
        var totRp = groupSum(rows, months, cats, 'rp');
        kpiTile(wrap, {
            label: 'Total nilai produksi',
            icon: 'rp',
            value: fmtRp(totRp),
            tooltip: fmtRpFull(totRp),
            qoq: qoqLine(rows, cats, 'rp'),
            sub: win.text,
            subTitle: win.title,
            spark: sparkline(rpSeries, 64, 26),
            onClick: function () {
                companyBreakdownModal('Total nilai produksi (Rp)', rows,
                    function (r) { return rowSum(r, months, cats, 'rp'); }, fmtRpFull, 'rp');
            }
        });

        // 4 — average price per KWH
        var harga = (totKwh && totRp !== null) ? totRp / totKwh : null;
        kpiTile(wrap, {
            label: 'Harga rata-rata per KWH',
            icon: 'harga',
            value: harga === null ? '—' : 'Rp ' + nfCompact.format(harga),
            tooltip: harga === null ? '' : 'Rp ' + nfFull.format(Math.round(harga)) + ' per KWH',
            sub: 'total nilai ÷ total KWH',
            onClick: function () {
                companyBreakdownModal('Harga rata-rata per KWH', rows, function (r) {
                    var k = rowSum(r, months, cats, 'kwh'), p = rowSum(r, months, cats, 'rp');
                    return (k && p !== null) ? p / k : null;
                }, function (v) { return v === null ? '—' : 'Rp ' + nfFull.format(Math.round(v)); });
            }
        });

        // 5 — largest category (by KWH, always across all categories)
        var catTotals = CATS.map(function (cat) {
            return { cat: cat, v: groupSum(rows, months, [cat], 'kwh') || 0 };
        }).sort(function (a, b) { return b.v - a.v; });
        var totalAll = catTotals.reduce(function (a, c) { return a + c.v; }, 0);
        var top = catTotals[0];
        kpiTile(wrap, {
            label: 'Kategori terbesar (KWH)',
            icon: 'kategori',
            value: top && top.v > 0 ? DATA.categories[top.cat] : '—',
            sub: top && top.v > 0 && totalAll > 0 ? nfPct.format(top.v / totalAll * 100) + '% dari total produksi' : 'belum ada data',
            onClick: function () {
                openModal('Produksi per kategori pelanggan', filterPills(), function (body) {
                    var s = sect(body, 'Total per kategori dan pertumbuhan q-to-q');
                    var pair = qoqPair();
                    simpleTable(s, ['Kategori', 'Produksi (KWH)', qoqHead('KWH', pair), 'Nilai (Rp)', qoqHead('Rp', pair), 'Porsi KWH'],
                        catTotals.map(function (c) {
                            return [DATA.categories[c.cat], fmtKwhFull(c.v), fmtSigned(groupQoq(rows, [c.cat], 'kwh', pair)),
                                fmtRpFull(groupSum(rows, months, [c.cat], 'rp')), fmtSigned(groupQoq(rows, [c.cat], 'rp', pair)),
                                totalAll ? nfPct.format(c.v / totalAll * 100) + '%' : '—'];
                        }), 1);
                    s.appendChild(el('p', 'stx-note', pair
                        ? 'Pertumbuhan q-to-q membandingkan total ' + quarterLabel(pair.cur) + ' dengan ' + quarterLabel(pair.prev) + ' — selalu triwulan penuh, terlepas dari filter Bulan. ' + qoqPartial(pair)
                        : 'Belum ada triwulan pembanding, sehingga pertumbuhan q-to-q belum dapat dihitung.'));
                });
            }
        });

        // 6 — latest month with any data
        var lastMonth = null, lastCount = 0;
        for (var i = months.length - 1; i >= 0; i--) {
            var ym = months[i];
            var n = rows.filter(function (r) { return rowSum(r, [ym], cats, 'kwh') !== null || rowSum(r, [ym], cats, 'rp') !== null; }).length;
            if (n > 0) { lastMonth = ym; lastCount = n; break; }
        }
        kpiTile(wrap, {
            label: 'Bulan terakhir terisi',
            icon: 'bulan',
            value: lastMonth ? monthLabel(lastMonth) : '—',
            sub: lastMonth ? lastCount + ' perusahaan mengisi bulan ini' : 'belum ada data',
            onClick: function () {
                if (!lastMonth) return;
                var lm = lastMonth;
                openModal('Data bulan ' + monthLabel(lm), filterPills(), function (body) {
                    var s = sect(body, 'Per perusahaan');
                    simpleTable(s, ['Perusahaan', 'Produksi (KWH)', 'Nilai (Rp)'],
                        rows.map(function (r) {
                            return [r.perusahaan, fmtKwhFull(rowSum(r, [lm], cats, 'kwh')), fmtRpFull(rowSum(r, [lm], cats, 'rp'))];
                        }), 1);
                });
            }
        });
    }

    /* ═══════════════ CHART 1 — monthly line ═══════════════ */

    function renderMonthly() {
        var unit = state.unitMonthly;
        var shell = cardShell('card-monthly',
            'Produksi listrik bulanan',
            'Per bulan pada irisan filter aktif — arahkan kursor untuk rincian',
            { unitKey: 'unitMonthly' });
        var t = theme();
        var rows = filteredRows();
        var months = activeMonths();
        var fmtC = unit === 'kwh' ? function (v) { return nfCompact.format(v); } : function (v) { return nfCompact.format(v); };
        var fmtF = fmtUnitFull(unit);

        // series: single kategori → one series; all → top 3 + Lainnya
        var series = [];
        function seriesOf(name, cats) {
            return {
                name: name,
                values: months.map(function (ym) { return groupSum(rows, [ym], cats, unit); })
            };
        }
        var selCats = activeCats();
        if (selCats.length === 1) {
            series.push(seriesOf(DATA.categories[selCats[0]], [selCats[0]]));
        } else if (selCats.length <= 4) {
            selCats.forEach(function (c) { series.push(seriesOf(DATA.categories[c], [c])); });
        } else {
            var ranked = selCats.map(function (cat) {
                return { cat: cat, total: groupSum(rows, months, [cat], unit) || 0 };
            }).sort(function (a, b) { return b.total - a.total; });
            var tops = ranked.slice(0, 3).filter(function (x) { return x.total > 0; });
            tops.forEach(function (x) { series.push(seriesOf(DATA.categories[x.cat], [x.cat])); });
            var rest = ranked.slice(3).map(function (x) { return x.cat; });
            if (rest.length) series.push(seriesOf('Lainnya', rest));
            if (!series.length) series.push(seriesOf('Total', CATS));
        }

        var hasData = series.some(function (s) { return s.values.some(function (v) { return v !== null; }); });

        simpleTable(shell.tablePane,
            ['Bulan'].concat(series.map(function (s) { return s.name; })),
            months.map(function (m, mi) {
                return [monthLabel(m)].concat(series.map(function (s) { return fmtF(s.values[mi]); }));
            }), 1);

        if (!hasData || !months.length) {
            emptyState(shell.chartPane, 'Belum ada data pada irisan filter ini.');
            return;
        }

        var colors = [t.s1, t.s2, t.s3, t.s4];
        if (series.length >= 2) {
            series.forEach(function (s, i) { legendItem(shell.legend, 'line', colors[i], s.name); });
        }

        var W = Math.max(320, shell.chartPane.clientWidth || shell.card.clientWidth - 40);
        var H = 270, padL = 66, padR = 24, padT = 14, padB = 30;
        var maxV = 0;
        series.forEach(function (s) { s.values.forEach(function (v) { if (v !== null && v > maxV) maxV = v; }); });
        var scale = niceTicks(maxV);
        var plotW = W - padL - padR, plotH = H - padT - padB;
        var x = function (i) { return padL + (months.length === 1 ? plotW / 2 : plotW * i / (months.length - 1)); };
        var y = function (v) { return padT + plotH * (1 - v / scale.top); };

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Grafik garis bulanan');

        scale.ticks.forEach(function (tv) {
            svg.appendChild(svgEl('line', { x1: padL, x2: W - padR, y1: y(tv), y2: y(tv), stroke: tv === 0 ? t.axis : t.grid, 'stroke-width': 1 }));
            var lbl = svgEl('text', { x: padL - 8, y: y(tv) + 3.5, 'text-anchor': 'end', 'font-size': 11, fill: t.muted });
            lbl.textContent = fmtC(tv);
            svg.appendChild(lbl);
        });
        var labelEvery = Math.max(1, Math.ceil(months.length / 13));
        months.forEach(function (m, i) {
            if (i % labelEvery !== 0 && i !== months.length - 1) return;
            var lbl = svgEl('text', { x: x(i), y: H - 8, 'text-anchor': 'middle', 'font-size': 10.5, fill: t.muted });
            lbl.textContent = monthShort(m);
            svg.appendChild(lbl);
        });

        if (series.length === 1) {
            var s0 = series[0], area = '', started = false, firstIdx = null, lastIdx = null;
            s0.values.forEach(function (v, i) {
                if (v === null) return;
                if (firstIdx === null) firstIdx = i;
                lastIdx = i;
                area += (started ? ' L' : 'M') + x(i) + ',' + y(v);
                started = true;
            });
            if (started) {
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
                svg.appendChild(svgEl('circle', { cx: x(i), cy: y(v), r: 3.5, fill: colors[si], stroke: t.surface, 'stroke-width': 2 }));
            });
        });
        // endpoint label for the first (largest) series only
        var sTop = series[0];
        for (var i = sTop.values.length - 1; i >= 0; i--) {
            if (sTop.values[i] !== null) {
                var lblE = svgEl('text', { x: Math.min(x(i) + 9, W - 4), y: y(sTop.values[i]) + 4, 'font-size': 11, 'font-weight': 700, fill: t.ink2 });
                lblE.textContent = fmtC(sTop.values[i]);
                svg.appendChild(lblE);
                break;
            }
        }

        var cross = svgEl('line', { y1: padT, y2: padT + plotH, stroke: t.axis, 'stroke-width': 1, visibility: 'hidden' });
        svg.appendChild(cross);
        var overlay = svgEl('rect', { x: padL, y: padT, width: plotW, height: plotH, fill: 'transparent', tabindex: 0 });
        overlay.style.cursor = 'crosshair';
        var focusIdx = null;
        function showAt(i, cx, cy) {
            cross.setAttribute('x1', x(i)); cross.setAttribute('x2', x(i));
            cross.setAttribute('visibility', 'visible');
            tipShow(cx, cy, monthLabel(months[i]), series.map(function (s, si) {
                return { color: colors[si], label: s.name, value: fmtF(s.values[i]) };
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

    /* ═══════════════ CHART 2 — per-category bars ═══════════════ */

    function renderKategori() {
        var unit = state.unitKategori;
        var pair = qoqPair();
        var shell = cardShell('card-kategori',
            'Per kategori pelanggan',
            'Pertumbuhan q-to-q per kategori' + (pair ? ' (' + qoqPairLabel(pair) + ')' : '') + ' — klik baris untuk memfokuskan seluruh dashboard pada kategori itu',
            { unitKey: 'unitKategori' });
        var t = theme();
        var rows = filteredRows();
        var months = activeMonths();
        var fmt = fmtUnit(unit), fmtF = fmtUnitFull(unit);

        // Growth is carried for both units at once: the unit toggle decides what
        // the bars show, but "pertumbuhan per kategori" is asked of KWH and
        // rupiah together, so the Tabel view always lists both.
        var list = CATS.map(function (cat) {
            return {
                cat: cat,
                label: DATA.categories[cat],
                v: groupSum(rows, months, [cat], unit),
                kwh: groupSum(rows, months, [cat], 'kwh'),
                rp: groupSum(rows, months, [cat], 'rp'),
                gKwh: groupQoq(rows, [cat], 'kwh', pair),
                gRp: groupQoq(rows, [cat], 'rp', pair)
            };
        });
        var total = list.reduce(function (a, c) { return a + (c.v || 0); }, 0);

        simpleTable(shell.tablePane, ['Kategori', 'Produksi (KWH)', qoqHead('KWH', pair), 'Nilai (Rp)', qoqHead('Rp', pair), 'Porsi'],
            list.map(function (g) {
                return [g.label, fmtKwhFull(g.kwh), fmtSigned(g.gKwh), fmtRpFull(g.rp), fmtSigned(g.gRp),
                    total ? nfPct.format((g.v || 0) / total * 100) + '%' : '—'];
            }), 1);
        shell.tablePane.appendChild(el('p', 'stx-note', pair
            ? 'Pertumbuhan q-to-q membandingkan total ' + quarterLabel(pair.cur) + ' dengan ' + quarterLabel(pair.prev) + ' — selalu triwulan penuh, terlepas dari filter Bulan. Porsi dihitung atas satuan yang sedang aktif (' + (unit === 'kwh' ? 'KWH' : 'rupiah') + '). ' + qoqPartial(pair)
            : 'Belum ada triwulan pembanding, sehingga pertumbuhan q-to-q belum dapat dihitung.'));

        if (total <= 0) { emptyState(shell.chartPane, 'Belum ada data pada irisan filter ini.'); return; }

        var W = Math.max(300, shell.chartPane.clientWidth || 460);
        var rowH = 38, padT2 = 4;
        var labelW = Math.min(150, Math.max(110, W * 0.28));
        var valueW = 86;
        var plotW = W - labelW - valueW - 12;
        var H = padT2 + list.length * rowH + 4;
        var maxV = Math.max.apply(null, list.map(function (g) { return g.v || 0; }).concat([1]));

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Per kategori pelanggan');

        list.forEach(function (g, i) {
            var cy = padT2 + i * rowH + rowH / 2;
            var dim = !katSelected(g.cat);
            var name = svgEl('text', { x: 0, y: cy + 4, 'font-size': 11.5, 'font-weight': (katSelCount() && state.katSel[g.cat]) ? 800 : 600, fill: dim ? t.muted : t.ink2 });
            name.textContent = g.label.length > 18 ? g.label.slice(0, 17) + '…' : g.label;
            svg.appendChild(name);

            var bw = (g.v || 0) / maxV * plotW;
            var barH = 16;
            svg.appendChild(svgEl('rect', { x: labelW, y: cy - barH / 2, width: plotW, height: barH, rx: 4, fill: t.grid, opacity: 0.45 }));
            if (bw > 0) svg.appendChild(svgEl('path', { d: roundRightBarPath(labelW, cy - barH / 2, Math.max(bw, 2), barH, 4), fill: t.s1, opacity: dim ? 0.3 : 1 }));
            // level on top, its q-to-q growth right underneath — the growth of the
            // unit currently on the bars; the Tabel view carries both units
            var gPct = unit === 'kwh' ? g.gKwh : g.gRp;
            var val = svgEl('text', { x: labelW + plotW + 8, y: cy + (gPct === null ? 4 : -1), 'font-size': 11, 'font-weight': 700, fill: dim ? t.muted : t.ink });
            val.textContent = fmtNc(g.v);
            svg.appendChild(val);
            if (gPct !== null) {
                var gTxt = svgEl('text', { x: labelW + plotW + 8, y: cy + 11, 'font-size': 9.5, 'font-weight': 700, fill: dim ? t.muted : qoqColor(t, gPct) });
                gTxt.textContent = fmtSigned(gPct);
                svg.appendChild(gTxt);
            }

            var hit = svgEl('rect', { x: 0, y: padT2 + i * rowH, width: W, height: rowH, fill: 'transparent' });
            hit.style.cursor = 'pointer';
            hit.setAttribute('tabindex', 0);
            hit.setAttribute('role', 'button');
            hit.setAttribute('aria-label', g.label);
            hit.addEventListener('pointermove', function (e) {
                var lines = [
                    { color: t.s1, label: unit === 'kwh' ? 'Produksi' : 'Nilai', value: fmtF(g.v) },
                    { label: 'Porsi', value: total ? nfPct.format((g.v || 0) / total * 100) + '%' : '—' }
                ];
                // the pair is stated once on its own line — repeating it inside
                // both growth labels would wrap the tooltip onto four lines
                if (pair) {
                    lines.push({ label: 'Pertumbuhan', value: qoqPairLabelShort(pair) });
                    lines.push({ label: 'q-to-q KWH', value: fmtSigned(g.gKwh) });
                    lines.push({ label: 'q-to-q Rupiah', value: fmtSigned(g.gRp) });
                }
                tipShow(e.clientX, e.clientY, g.label, lines);
            });
            hit.addEventListener('pointerleave', tipHide);
            function focusCat() {
                tipHide();
                if (state.katSel[g.cat]) delete state.katSel[g.cat]; else state.katSel[g.cat] = true;
                rerender();
            }
            hit.addEventListener('click', focusCat);
            hit.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); focusCat(); } });
            svg.appendChild(hit);
        });

        shell.chartPane.appendChild(svg);
    }

    /* ═══════════════ CHART 3 — price per KWH per category ═══════════════ */

    function renderHarga() {
        var shell = cardShell('card-harga',
            'Harga rata-rata per KWH',
            'Total nilai ÷ total produksi per kategori (rupiah per KWH)');
        var t = theme();
        var rows = filteredRows();
        var months = activeMonths();

        var list = CATS.map(function (cat) {
            var k = groupSum(rows, months, [cat], 'kwh');
            var p = groupSum(rows, months, [cat], 'rp');
            return { cat: cat, label: DATA.categories[cat], v: (k && p !== null) ? p / k : null };
        });

        simpleTable(shell.tablePane, ['Kategori', 'Rp per KWH'],
            list.map(function (g) { return [g.label, g.v === null ? '—' : 'Rp ' + nfFull.format(Math.round(g.v))]; }), 1);

        var hasData = list.some(function (g) { return g.v !== null; });
        if (!hasData) { emptyState(shell.chartPane, 'Belum ada data pada irisan filter ini.'); return; }

        var W = Math.max(300, shell.chartPane.clientWidth || 460);
        var rowH = 38, padT2 = 4;
        var labelW = Math.min(150, Math.max(110, W * 0.28));
        var valueW = 86;
        var plotW = W - labelW - valueW - 12;
        var H = padT2 + list.length * rowH + 4;
        var maxV = Math.max.apply(null, list.map(function (g) { return g.v || 0; }).concat([1]));

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Harga rata-rata per KWH per kategori');

        list.forEach(function (g, i) {
            var cy = padT2 + i * rowH + rowH / 2;
            var dim = !katSelected(g.cat);
            var name = svgEl('text', { x: 0, y: cy + 4, 'font-size': 11.5, 'font-weight': (katSelCount() && state.katSel[g.cat]) ? 800 : 600, fill: dim ? t.muted : t.ink2 });
            name.textContent = g.label.length > 18 ? g.label.slice(0, 17) + '…' : g.label;
            svg.appendChild(name);

            var bw = (g.v || 0) / maxV * plotW;
            var barH = 16;
            svg.appendChild(svgEl('rect', { x: labelW, y: cy - barH / 2, width: plotW, height: barH, rx: 4, fill: t.grid, opacity: 0.45 }));
            if (bw > 0) svg.appendChild(svgEl('path', { d: roundRightBarPath(labelW, cy - barH / 2, Math.max(bw, 2), barH, 4), fill: t.s1, opacity: dim ? 0.3 : 1 }));
            var val = svgEl('text', { x: labelW + plotW + 8, y: cy + 4, 'font-size': 11, 'font-weight': 700, fill: dim ? t.muted : t.ink });
            val.textContent = g.v === null ? '—' : nfCompact.format(g.v);
            svg.appendChild(val);

            var hit = svgEl('rect', { x: 0, y: padT2 + i * rowH, width: W, height: rowH, fill: 'transparent' });
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, g.label, [
                    { color: t.s1, label: 'Harga per KWH', value: g.v === null ? '—' : 'Rp ' + nfFull.format(Math.round(g.v)) }
                ]);
            });
            hit.addEventListener('pointerleave', tipHide);
            svg.appendChild(hit);
        });

        shell.chartPane.appendChild(svg);
    }

    /* ═══════════════ CHART 4 — wilayah tujuan breakdown ═══════════════ */

    function wilayahSum(r, months, cats, label, f) {
        var any = false, total = 0;
        months.forEach(function (ym) {
            var wm = r.wilayahMonthly && r.wilayahMonthly[ym] ? r.wilayahMonthly[ym][label] : null;
            if (!wm) return;
            cats.forEach(function (cat) {
                var v = wm[cat] ? wm[cat][f] : null;
                if (v !== null && v !== undefined) { any = true; total += v; }
            });
        });
        return any ? total : null;
    }

    function renderWilayah() {
        var unit = state.unitWilayah;
        var shell = cardShell('card-wilayah',
            'Produksi per wilayah tujuan',
            'Penyaluran listrik menurut wilayah tujuan (dalam negeri per kab/kota, luar negeri per negara) — klik baris untuk rincian per perusahaan',
            { unitKey: 'unitWilayah' });
        var t = theme();
        var rows = filteredRows();
        var months = activeMonths();
        var cats = activeCats();
        var fmtF = fmtUnitFull(unit);

        // collect all wilayah labels present in scope
        var labels = {};
        rows.forEach(function (r) {
            months.forEach(function (ym) {
                var wm = r.wilayahMonthly ? r.wilayahMonthly[ym] : null;
                if (!wm) return;
                Object.keys(wm).forEach(function (label) { labels[label] = true; });
            });
        });
        var list = Object.keys(labels).map(function (label) {
            return {
                label: label,
                kwh: rows.reduce(function (a, r) { var v = wilayahSum(r, months, cats, label, 'kwh'); return v === null ? a : (a || 0) + v; }, null),
                rp:  rows.reduce(function (a, r) { var v = wilayahSum(r, months, cats, label, 'rp');  return v === null ? a : (a || 0) + v; }, null)
            };
        }).sort(function (a, b) { return (b[unit] || 0) - (a[unit] || 0); });

        var total = list.reduce(function (a, g) { return a + (g[unit] || 0); }, 0);

        simpleTable(shell.tablePane, ['Wilayah tujuan', 'Produksi (KWH)', 'Nilai (Rp)', 'Porsi'],
            list.map(function (g) {
                return [g.label, fmtKwhFull(g.kwh), fmtRpFull(g.rp),
                    total ? nfPct.format((g[unit] || 0) / total * 100) + '%' : '—'];
            }), 1);

        if (!list.length || total <= 0) {
            emptyState(shell.chartPane, 'Belum ada data wilayah tujuan pada irisan filter ini.');
            return;
        }

        var W = Math.max(360, shell.chartPane.clientWidth || 640);
        var rowH = 40, padT2 = 4;
        var labelW = Math.min(240, Math.max(150, W * 0.28));
        var valueW = 92;
        var plotW = W - labelW - valueW - 14;
        var H = padT2 + list.length * rowH + 4;
        var maxV = Math.max.apply(null, list.map(function (g) { return g[unit] || 0; }).concat([1]));

        var svg = svgEl('svg', { class: 'stx-svg', viewBox: '0 0 ' + W + ' ' + H, height: H, role: 'img' });
        svg.setAttribute('aria-label', 'Produksi per wilayah tujuan');

        list.forEach(function (g, i) {
            var cy = padT2 + i * rowH + rowH / 2;
            var name = svgEl('text', { x: 0, y: cy + 1, 'font-size': 11.5, 'font-weight': 600, fill: t.ink2 });
            name.textContent = g.label.length > 30 ? g.label.slice(0, 29) + '…' : g.label;
            svg.appendChild(name);
            var sub = svgEl('text', { x: 0, y: cy + 13, 'font-size': 9.5, fill: t.muted });
            sub.textContent = total ? nfPct.format((g[unit] || 0) / total * 100) + '% dari total' : '';
            svg.appendChild(sub);

            var bw = (g[unit] || 0) / maxV * plotW;
            var barH = 16;
            svg.appendChild(svgEl('rect', { x: labelW, y: cy - barH / 2, width: plotW, height: barH, rx: 4, fill: t.grid, opacity: 0.4 }));
            if (bw > 0) svg.appendChild(svgEl('path', { d: roundRightBarPath(labelW, cy - barH / 2, Math.max(bw, 2), barH, 4), fill: t.s1 }));
            var val = svgEl('text', { x: labelW + plotW + 8, y: cy + 4, 'font-size': 11, 'font-weight': 700, fill: t.ink });
            val.textContent = fmtNc(g[unit]);
            svg.appendChild(val);

            var hit = svgEl('rect', { x: 0, y: padT2 + i * rowH, width: W, height: rowH, fill: 'transparent' });
            hit.style.cursor = 'pointer';
            hit.setAttribute('tabindex', 0);
            hit.setAttribute('role', 'button');
            hit.setAttribute('aria-label', 'Rincian ' + g.label);
            hit.addEventListener('pointermove', function (e) {
                tipShow(e.clientX, e.clientY, g.label, [
                    { color: t.s1, label: 'Produksi', value: fmtKwhFull(g.kwh) },
                    { label: 'Nilai', value: fmtRpFull(g.rp) },
                    { label: 'Porsi', value: total ? nfPct.format((g[unit] || 0) / total * 100) + '%' : '—' }
                ]);
            });
            hit.addEventListener('pointerleave', tipHide);
            function openW() {
                tipHide();
                openModal(g.label, filterPills(), function (body) {
                    var s1 = sect(body, 'Ringkasan wilayah');
                    moneyRows(s1, [
                        { label: 'Total produksi listrik', value: fmtKwhFull(g.kwh) },
                        { label: 'Total nilai produksi', value: fmtRpFull(g.rp), total: true }
                    ]);
                    var s2 = sect(body, 'Per perusahaan');
                    var comp = rows.map(function (r) {
                        return { r: r, kwh: wilayahSum(r, months, cats, g.label, 'kwh'), rp: wilayahSum(r, months, cats, g.label, 'rp') };
                    }).filter(function (x) { return x.kwh !== null || x.rp !== null; })
                      .sort(function (a, b) { return (b.kwh || 0) - (a.kwh || 0); });
                    simpleTable(s2, ['Perusahaan', 'Produksi (KWH)', 'Nilai (Rp)'],
                        comp.map(function (x) { return [x.r.perusahaan, fmtKwhFull(x.kwh), fmtRpFull(x.rp)]; }), 1);
                });
            }
            hit.addEventListener('click', openW);
            hit.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openW(); } });
            svg.appendChild(hit);
        });

        shell.chartPane.appendChild(svg);
    }

    /* ═══════════════ TABLE — per company ═══════════════ */

    /**
     * Everything the detail table can be ranked by. Every entry is also a
     * standing column, so the explicit control and a column-heading click are
     * two doors into the same state.
     */
    var SORT_OPTS = [
        { key: 'perusahaan', label: 'Nama perusahaan',      num: false, sub: 'urut abjad' },
        { key: 'kwh',        label: 'Produksi listrik',     num: true,  sub: 'total KWH pada bulan terpilih' },
        { key: 'rp',         label: 'Nilai produksi',       num: true,  sub: 'total rupiah pada bulan terpilih' },
        { key: 'harga',      label: 'Harga rata-rata/KWH',  num: true,  sub: 'nilai ÷ produksi' },
        { key: 'nBulan',     label: 'Jumlah bulan terisi',  num: true,  sub: 'kelengkapan pengisian' },
        { key: 'pembangkit', label: 'Jenis pembangkit',     num: false, sub: 'urut abjad' },
        { key: 'selesai',    label: 'Status pengisian',     num: true,  sub: 'draf lebih dulu / selesai lebih dulu' },
        { key: 'updated',    label: 'Terakhir diperbarui',  num: true,  sub: 'tanggal simpan terakhir' }
    ];
    function sortOpt(key) {
        for (var i = 0; i < SORT_OPTS.length; i++) if (SORT_OPTS[i].key === key) return SORT_OPTS[i];
        return null;
    }
    // Direction wording follows the metric — "Terbesar" is nonsense for a name.
    function dirLabels(key) {
        if (key === 'updated') return ['Terbaru', 'Terlama'];
        if (key === 'selesai') return ['Selesai dulu', 'Draf dulu'];
        var o = sortOpt(key);
        return (o && !o.num) ? ['Z → A', 'A → Z'] : ['Terbesar', 'Terkecil'];
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
        var cur = sortOpt(state.sortKey);
        btn.appendChild(el('span', null, cur ? cur.label : state.sortKey));
        btn.appendChild(el('span', 'caret', '▼'));
        btn.setAttribute('aria-label', 'Pilih kolom pengurutan');
        btn.addEventListener('click', function () {
            togglePop(wrapEl, btn, function (panel) {
                panel.style.width = 'min(20rem, 90vw)';
                var head = el('div', 'stx-pop-head');
                head.appendChild(el('div', 'stx-pop-title', 'Urutkan perusahaan menurut'));
                head.appendChild(el('div', 'stx-pop-sub', 'Nilai dihitung pada irisan filter yang sedang aktif.'));
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

    var TABLE_PAGE = 10;

    // Growth line under a number inside a table cell. Nothing is drawn when
    // there is no rate to state — an endless column of "—" is noise, not data.
    function qoqCell(td, pct, pair) {
        if (pct === null) return;
        // names its baseline rather than saying "q-to-q": the reader should not
        // have to look up which transition the filter is on
        var q = el('div', 'stx-qoq-cell ' + qoqClass(pct), fmtSigned(pct) + ' vs ' + quarterLabel(pair.prev));
        q.title = 'Pertumbuhan q-to-q ' + quarterLabel(pair.cur) + ' vs ' + quarterLabel(pair.prev);
        td.appendChild(q);
    }

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
        var pair = qoqPair();
        var head = el('div', 'stx-chart-head');
        var titles = el('div');
        titles.appendChild(el('div', 'stx-chart-title', 'Rincian per perusahaan'));
        titles.appendChild(el('div', 'stx-chart-sub', 'Produksi dan nilai membawa pertumbuhan q-to-q perusahaan itu' + (pair ? ' (' + qoqPairLabel(pair) + ')' : '') + ' — klik baris untuk detail per bulan'));
        head.appendChild(titles);
        sortTools(head);
        card.appendChild(head);

        var rows = filteredRows();
        var months = activeMonths();
        var cats = activeCats();

        var enriched = rows.map(function (r) {
            var kwh = rowSum(r, months, cats, 'kwh');
            var rp = rowSum(r, months, cats, 'rp');
            var filledMonths = months.filter(function (ym) {
                return rowSum(r, [ym], cats, 'kwh') !== null || rowSum(r, [ym], cats, 'rp') !== null;
            }).length;
            return { r: r, kwh: kwh, rp: rp, harga: (kwh && rp !== null) ? rp / kwh : null, nBulan: filledMonths };
        });

        // Companies with nothing reported for the sort metric always sink to the
        // bottom — in either direction "terkecil" must not mean "belum mengisi".
        function sortValue(x, key) {
            if (key === 'perusahaan') return x.r.perusahaan;
            if (key === 'pembangkit') return x.r.jenisPembangkit || null;
            if (key === 'selesai') return x.r.selesai ? 1 : 0;
            if (key === 'updated') return x.r.updatedTs === undefined ? null : x.r.updatedTs;
            var v = x[key];
            return v === undefined ? null : v;
        }
        enriched.sort(function (a, b) {
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
        [['perusahaan', 'Perusahaan', false], ['pembangkit', 'Pembangkit', false], ['selesai', 'Status', false],
         ['kwh', 'Produksi (KWH)', true], ['rp', 'Nilai (Rp)', true], ['harga', 'Rp/KWH', true],
         ['nBulan', 'Bulan terisi', true], ['updated', 'Diperbarui', false]].forEach(function (c) {
            var sorted = state.sortKey === c[0];
            var th = el('th', (c[2] ? 'num' : '') + (sorted ? ' sorted' : '') || null, c[1]);
            if (sorted) {
                th.setAttribute('aria-sort', state.sortDir === 1 ? 'ascending' : 'descending');
                th.appendChild(el('span', 'arrow', state.sortDir === 1 ? '▲' : '▼'));
            }
            th.addEventListener('click', function () { setSort(c[0]); });
            trh.appendChild(th);
        });
        thead.appendChild(trh);
        table.appendChild(thead);

        var tb = el('tbody');
        if (!enriched.length) {
            var tr0 = el('tr'), td0 = el('td', null, 'Tidak ada data pada irisan filter ini.');
            td0.colSpan = 8;
            td0.style.textAlign = 'center';
            td0.style.padding = '2rem';
            tr0.appendChild(td0); tb.appendChild(tr0);
        }
        var limit = Math.min(Math.max(state.tableLimit, TABLE_PAGE), enriched.length);
        enriched.slice(0, limit).forEach(function (x) {
            var r = x.r;
            var tr = el('tr');
            tr.setAttribute('tabindex', 0);
            tr.appendChild(el('td', 'strong', r.perusahaan));
            tr.appendChild(el('td', null, r.jenisPembangkit || '—'));
            var tdS = el('td');
            tdS.appendChild(el('span', 'stx-badge ' + (r.selesai ? 'ok' : 'draft'), r.selesai ? 'Selesai' : 'Draf'));
            tr.appendChild(tdS);
            var tdK = el('td', 'num', x.kwh === null ? '—' : nfCompact.format(x.kwh));
            tdK.title = fmtKwhFull(x.kwh);
            qoqCell(tdK, rowQoq(r, cats, 'kwh', pair), pair);
            tr.appendChild(tdK);
            var tdR = el('td', 'num', fmtRp(x.rp));
            tdR.title = fmtRpFull(x.rp);
            qoqCell(tdR, rowQoq(r, cats, 'rp', pair), pair);
            tr.appendChild(tdR);
            tr.appendChild(el('td', 'num', x.harga === null ? '—' : nfFull.format(Math.round(x.harga))));
            tr.appendChild(el('td', 'num', x.nBulan + '/' + months.length));
            tr.appendChild(el('td', null, r.updatedAt || '—'));
            function openCompany() { companyModal(r); }
            tr.addEventListener('click', openCompany);
            tr.addEventListener('keydown', function (e) { if (e.key === 'Enter') openCompany(); });
            tb.appendChild(tr);
        });
        table.appendChild(tb);
        wrapT.appendChild(table);
        card.appendChild(wrapT);
        pagerFoot(card, enriched.length, limit);
    }

    /* ═══════════════ company drill-down modal ═══════════════ */

    function companyModal(r) {
        var months = activeMonths();
        var cats = activeCats();
        var pair = qoqPair();
        openModal(r.perusahaan, [
            r.jenisPembangkit || 'Pembangkit —',
            state.tahun === 'all' ? 'Semua tahun' : 'Tahun ' + state.tahun,
            state.triwulan === 'all' ? 'Semua triwulan' : 'Triwulan ' + TW_ROMAN[state.triwulan],
            r.selesai ? 'Selesai' : 'Draf'
        ], function (body) {
            var s1 = sect(body, 'Identitas');
            kvGrid(s1, [
                ['Kabupaten/Kota', r.kabupaten],
                ['Jenis pembangkit', r.jenisPembangkit],
                ['Daya terpasang', r.dayaKw === null ? null : nfFull.format(r.dayaKw) + ' KW'],
                ['Terakhir disimpan', r.updatedAt]
            ]);

            var kwh = rowSum(r, months, cats, 'kwh');
            var rp = rowSum(r, months, cats, 'rp');
            var s2 = sect(body, 'Ringkasan ' + monthWindowLabel().text);
            moneyRows(s2, [
                { label: 'Total produksi listrik', value: fmtKwhFull(kwh) },
                { label: 'Total nilai produksi', value: fmtRpFull(rp) },
                { label: 'Harga rata-rata per KWH', value: (kwh && rp !== null) ? 'Rp ' + nfFull.format(Math.round(rp / kwh)) : '—', total: true }
            ]);

            if (pair) {
                var s2b = sect(body, 'Pertumbuhan q-to-q (' + qoqPairLabel(pair) + ')');
                simpleTable(s2b, ['Komponen', quarterLabel(pair.prev), quarterLabel(pair.cur), qoqHead('', pair)], [
                    ['Produksi listrik (KWH)', 'kwh', fmtKwhFull],
                    ['Nilai produksi (Rp)', 'rp', fmtRpFull]
                ].map(function (m) {
                    var pv = rowSum(r, pair.prev.months, cats, m[1]), cv = rowSum(r, pair.cur.months, cats, m[1]);
                    return [m[0], m[2](pv), m[2](cv), fmtSigned(pctChange(cv, pv))];
                }), 1);
                s2b.appendChild(el('p', 'stx-note', 'Dihitung dari total triwulan penuh, terlepas dari filter Bulan yang sedang aktif. ' + qoqPartial(pair)));

                var s2c = sect(body, 'Pertumbuhan q-to-q per kategori pelanggan');
                simpleTable(s2c, ['Kategori', 'KWH ' + quarterLabel(pair.cur), qoqHead('KWH', pair), 'Rp ' + quarterLabel(pair.cur), qoqHead('Rp', pair)],
                    CATS.map(function (cat) {
                        var pk = rowSum(r, pair.prev.months, [cat], 'kwh'), ck = rowSum(r, pair.cur.months, [cat], 'kwh');
                        var pr = rowSum(r, pair.prev.months, [cat], 'rp'), cr = rowSum(r, pair.cur.months, [cat], 'rp');
                        return [DATA.categories[cat], fmtKwhFull(ck), fmtSigned(pctChange(ck, pk)), fmtRpFull(cr), fmtSigned(pctChange(cr, pr))];
                    }), 1);
            }

            var s3 = sect(body, 'Per bulan');
            var scr = el('div');
            simpleTable(scr, ['Bulan', 'Produksi (KWH)', 'Nilai (Rp)'],
                months.map(function (ym) {
                    return [monthLabel(ym), fmtKwhFull(rowSum(r, [ym], cats, 'kwh')), fmtRpFull(rowSum(r, [ym], cats, 'rp'))];
                }), 1);
            s3.appendChild(scr);

            var s4 = sect(body, 'Per kategori pelanggan');
            simpleTable(s4, ['Kategori', 'Produksi (KWH)', 'Nilai (Rp)'],
                CATS.map(function (cat) {
                    return [DATA.categories[cat], fmtKwhFull(rowSum(r, months, [cat], 'kwh')), fmtRpFull(rowSum(r, months, [cat], 'rp'))];
                }), 1);

            if (r.catatan) {
                var s5 = sect(body, 'Catatan responden');
                var p = el('p', null, r.catatan);
                p.style.fontSize = '0.8125rem';
                p.style.color = 'var(--stx-ink-2)';
                p.style.lineHeight = '1.6';
                s5.appendChild(p);
            }
        });
    }

    /* ═══════════════ orchestration ═══════════════ */

    state.unitMonthly = 'kwh';
    state.unitKategori = 'kwh';
    state.unitWilayah = 'kwh';

    function rerenderData() {
        renderKpis();
        renderMonthly();
        renderKategori();
        renderHarga();
        renderWilayah();
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
