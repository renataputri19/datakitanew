{{-- Page CSS for /bps/pengingat. Plain CSS so the page needs no Vite rebuild.
     Colours are variables on .pg, redefined once for dark mode. --}}
<style>
.pg {
    --pg-bg:#fff; --pg-subtle:#f8fafc; --pg-border:#e5e7eb; --pg-border-strong:#d1d5db;
    --pg-text:#111827; --pg-text-2:#374151; --pg-muted:#6b7280;
    --pg-primary:#2563eb; --pg-primary-hover:#1d4ed8; --pg-primary-soft:#eff6ff; --pg-primary-text:#1d4ed8;
    --pg-track:#e5e7eb;
    display:flex; flex-direction:column; gap:1.25rem; color:var(--pg-text-2);
}
.dark .pg {
    --pg-bg:#1f2937; --pg-subtle:#111827; --pg-border:#374151; --pg-border-strong:#4b5563;
    --pg-text:#f9fafb; --pg-text-2:#d1d5db; --pg-muted:#9ca3af;
    --pg-primary:#3b82f6; --pg-primary-hover:#2563eb; --pg-primary-soft:rgba(59,130,246,.14); --pg-primary-text:#93c5fd;
    --pg-track:#374151;
}
.pg svg { flex:none; }
.pg-ico { width:1.1rem; height:1.1rem; }
.pg code { font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.8125em; padding:.05rem .3rem; border-radius:.25rem; background:var(--pg-subtle); border:1px solid var(--pg-border); color:var(--pg-text); }
.pg-sr { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }

/* ── Header ─────────────────────────────────────────── */
.pg-head { display:flex; flex-wrap:wrap; gap:1rem; justify-content:space-between; align-items:flex-start; }
.pg-title { font-size:1.5rem; line-height:1.3; font-weight:700; color:var(--pg-text); }
.pg-subtitle { margin-top:.25rem; max-width:48rem; font-size:.875rem; color:var(--pg-muted); }
.pg-back { display:inline-flex; align-items:center; gap:.3rem; font-size:.875rem; margin-bottom:.25rem; }

/* ── Surfaces ───────────────────────────────────────── */
.pg-panel { background:var(--pg-bg); border:1px solid var(--pg-border); border-radius:.75rem; box-shadow:0 1px 2px rgba(0,0,0,.04); padding:1.25rem; }
.pg-flush { padding:0; overflow:hidden; }
.pg-empty { display:flex; flex-direction:column; align-items:center; gap:.5rem; padding:3rem 1.5rem; text-align:center; color:var(--pg-muted); font-size:.875rem; }
.pg-empty svg { width:2.75rem; height:2.75rem; color:var(--pg-border-strong); margin-bottom:.25rem; }
.pg-empty-title { font-size:1rem; font-weight:600; color:var(--pg-text); }
.pg-empty .pg-btn { margin-top:.75rem; }
.pg-empty-sm { padding:2rem 1.25rem; text-align:center; font-size:.875rem; color:var(--pg-muted); }

.pg-alert { display:flex; gap:.75rem; align-items:flex-start; border-radius:.6rem; padding:.875rem 1rem; font-size:.875rem; border:1px solid; }
.pg-alert svg { width:1.25rem; height:1.25rem; margin-top:.05rem; }
.pg-alert-warn { background:#fffbeb; border-color:#fde68a; color:#92400e; }
.dark .pg-alert-warn { background:rgba(120,53,15,.25); border-color:#92400e; color:#fcd34d; }
.pg-alert-error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
.dark .pg-alert-error { background:rgba(127,29,29,.25); border-color:#991b1b; color:#fca5a5; }

/* ── Status overview ────────────────────────────────── */
.pg-overview { display:grid; grid-template-columns:repeat(auto-fit, minmax(270px, 1fr)); gap:1rem; }
.pg-overview-item { display:flex; gap:.875rem; align-items:flex-start; background:var(--pg-bg); border:1px solid var(--pg-border); border-radius:.75rem; padding:1rem 1.125rem; }
.pg-icon-circle { display:grid; place-items:center; width:2.5rem; height:2.5rem; border-radius:9999px; }
.pg-icon-circle svg { width:1.25rem; height:1.25rem; }
.pg-icon-blue  { background:var(--pg-primary-soft); color:var(--pg-primary-text); }
.pg-icon-green { background:#d1fae5; color:#047857; }
.pg-icon-gray  { background:var(--pg-subtle); color:var(--pg-muted); border:1px solid var(--pg-border); }
.dark .pg-icon-green { background:rgba(16,185,129,.15); color:#6ee7b7; }
.pg-overview-label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--pg-muted); }
.pg-overview-value { margin-top:.15rem; display:flex; align-items:center; gap:.45rem; font-size:1.0625rem; font-weight:700; color:var(--pg-text); }
.pg-overview-sub { margin-top:.2rem; font-size:.8125rem; color:var(--pg-muted); }

/* ── "How it works" ─────────────────────────────────── */
.pg-help { border:1px dashed var(--pg-border-strong); border-radius:.75rem; padding:.7rem 1rem; }
.pg-help > summary, .pg-disclosure > summary, .pg-more > summary { list-style:none; cursor:pointer; }
.pg-help > summary::-webkit-details-marker, .pg-disclosure > summary::-webkit-details-marker, .pg-more > summary::-webkit-details-marker { display:none; }
.pg-help > summary { display:flex; align-items:center; gap:.5rem; font-size:.875rem; font-weight:500; color:var(--pg-primary-text); }
.pg-chevron { width:1rem; height:1rem; margin-left:auto; transition:transform .15s; }
details[open] > summary .pg-chevron { transform:rotate(180deg); }
.pg-help-list { display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:.75rem; margin-top:.875rem; }
.pg-help-list li { background:var(--pg-bg); border:1px solid var(--pg-border); border-radius:.6rem; padding:.75rem .875rem; font-size:.8125rem; line-height:1.5; color:var(--pg-muted); }
.pg-help-list strong { display:block; margin-bottom:.15rem; font-size:.875rem; color:var(--pg-text); }

/* ── Tabs ───────────────────────────────────────────── */
.pg-tabs { display:flex; gap:.25rem; border-bottom:1px solid var(--pg-border); overflow-x:auto; scrollbar-width:none; }
.pg-tabs::-webkit-scrollbar { display:none; }
.pg-tab { display:inline-flex; align-items:center; gap:.5rem; padding:.75rem 1rem; margin-bottom:-1px; border-bottom:2px solid transparent; font-size:.875rem; font-weight:500; white-space:nowrap; color:var(--pg-muted); text-decoration:none; transition:color .15s, border-color .15s; }
.pg-tab:hover { color:var(--pg-text); border-color:var(--pg-border-strong); }
.pg-tab.is-active { color:var(--pg-primary-text); border-color:var(--pg-primary); }

/* ── Numbered steps (Kirim) ─────────────────────────── */
.pg-step { background:var(--pg-bg); border:1px solid var(--pg-border); border-radius:.75rem; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden; }
.pg-step-head { display:flex; gap:.875rem; align-items:flex-start; padding:1rem 1.25rem; border-bottom:1px solid var(--pg-border); }
.pg-step-num { display:grid; place-items:center; flex:none; width:1.75rem; height:1.75rem; border-radius:9999px; background:var(--pg-primary); color:#fff; font-size:.875rem; font-weight:700; }
.pg-step-head h2, .pg-section-head h2 { font-size:1rem; font-weight:600; color:var(--pg-text); }
.pg-step-head p, .pg-section-head p { margin-top:.15rem; font-size:.8125rem; color:var(--pg-muted); }
.pg-step-body { display:flex; flex-direction:column; gap:1.125rem; padding:1.25rem; }

.pg-choice { display:flex; flex-direction:column; gap:.5rem; }
.pg-segmented { display:flex; flex-wrap:wrap; gap:.5rem; }
.pg-seg { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .875rem; border:1px solid var(--pg-border-strong); border-radius:.5rem; background:var(--pg-bg); font-size:.875rem; font-weight:500; color:var(--pg-text-2); text-decoration:none; cursor:pointer; transition:border-color .15s, color .15s, background .15s; }
.pg-seg:hover { border-color:var(--pg-primary); color:var(--pg-primary-text); }
.pg-seg.is-active { background:var(--pg-primary-soft); border-color:var(--pg-primary); box-shadow:inset 0 0 0 1px var(--pg-primary); color:var(--pg-primary-text); }
.pg-seg-sm { padding:.3rem .7rem; font-size:.8125rem; }
.pg-seg-note { padding:.05rem .45rem; border-radius:9999px; background:#d1fae5; color:#065f46; font-size:.6875rem; font-weight:600; }
.dark .pg-seg-note { background:#064e3b; color:#6ee7b7; }
.pg-count { min-width:1.5rem; padding:.05rem .45rem; border-radius:9999px; border:1px solid var(--pg-border); background:var(--pg-subtle); font-size:.75rem; font-weight:600; text-align:center; color:var(--pg-text-2); }
.pg-seg.is-active .pg-count { background:var(--pg-primary); border-color:transparent; color:#fff; }
.pg-select-auto { width:auto; min-width:16rem; }
.pg-checkbox { display:inline-grid; place-items:center; flex:none; width:1.1rem; height:1.1rem; border:1.5px solid var(--pg-border-strong); border-radius:.3rem; background:var(--pg-bg); transition:background .12s, border-color .12s; }
.is-active > .pg-checkbox { background:var(--pg-primary); border-color:var(--pg-primary); }
.is-active > .pg-checkbox::after { content:''; width:.3rem; height:.55rem; border:solid #fff; border-width:0 2px 2px 0; transform:translateY(-1px) rotate(45deg); }

/* Source cards: tick one or more */
.pg-sources { display:grid; grid-template-columns:repeat(auto-fill, minmax(215px, 1fr)); gap:.625rem; }
.pg-source { display:flex; align-items:center; gap:.75rem; padding:.75rem .875rem; border:1px solid var(--pg-border-strong); border-radius:.6rem; background:var(--pg-bg); color:var(--pg-text-2); text-decoration:none; cursor:pointer; user-select:none; transition:border-color .12s, background .12s, box-shadow .12s; }
.pg-source:hover { border-color:var(--pg-primary); }
.pg-source:focus-visible { outline:2px solid #93c5fd; outline-offset:2px; }
.pg-source.is-active { border-color:var(--pg-primary); background:var(--pg-primary-soft); box-shadow:inset 0 0 0 1px var(--pg-primary); }
.pg-source-text { display:flex; flex:1; flex-direction:column; min-width:0; }
.pg-source-title { font-size:.875rem; font-weight:600; color:var(--pg-text); }
.pg-source-sub { font-size:.75rem; color:var(--pg-muted); }
.pg-source-started .pg-source-sub { color:#b45309; }
.dark .pg-source-started .pg-source-sub { color:#fcd34d; }
.pg-source-count { font-size:1.125rem; font-weight:700; font-variant-numeric:tabular-nums; color:var(--pg-text); }
.pg-source.is-active .pg-source-count { color:var(--pg-primary-text); }
.pg-source.is-zero .pg-source-count { font-weight:500; color:var(--pg-muted); }

.pg-result { display:flex; flex-wrap:wrap; align-items:baseline; gap:.3rem .6rem; padding:.7rem .9rem; border:1px solid var(--pg-border); border-radius:.6rem; background:var(--pg-subtle); font-size:.875rem; color:var(--pg-text-2); }
.pg-result b { font-size:1.0625rem; font-variant-numeric:tabular-nums; color:var(--pg-text); }
.pg-result-arrow { color:var(--pg-muted); }
.pg-result.is-prompt { flex-wrap:nowrap; align-items:flex-start; gap:.65rem; border-color:var(--pg-primary); border-style:dashed; background:var(--pg-primary-soft); line-height:1.55; color:var(--pg-text); }
.pg-result.is-prompt svg { width:1.15rem; height:1.15rem; margin-top:.15rem; color:var(--pg-primary-text); }
.pg-result.is-prompt b { font-size:inherit; }

/* In-place reload of the Kirim section */
.pg-loading { display:none; align-items:center; gap:.45rem; margin-left:auto; font-size:.75rem; color:var(--pg-muted); }
.pg-loading::before { content:''; width:.9rem; height:.9rem; border:2px solid var(--pg-border-strong); border-top-color:var(--pg-primary); border-radius:9999px; animation:pg-spin .7s linear infinite; }
@keyframes pg-spin { to { transform:rotate(360deg); } }
@media (prefers-reduced-motion: reduce) { .pg-loading::before { animation-duration:2.5s; } }
#pg-kirim[aria-busy="true"] .pg-loading { display:inline-flex; }
#pg-kirim[aria-busy="true"] #pg-send-form, #pg-kirim[aria-busy="true"] .pg-result { opacity:.5; pointer-events:none; transition:opacity .15s .1s; }
#pg-kirim[data-switching] .pg-sources { opacity:.5; pointer-events:none; }
.pg-tags { display:flex; flex-wrap:wrap; gap:.25rem; }
.pg-tag { padding:.05rem .45rem; border:1px solid var(--pg-border); border-radius:.3rem; background:var(--pg-subtle); font-size:.75rem; white-space:nowrap; color:var(--pg-text-2); }
.pg-row-click .pg-badge, .pg-row-muted .pg-badge { margin-top:.2rem; }

/* Recipient table */
.pg-table-tools { display:flex; flex-wrap:wrap; gap:.75rem 1rem; align-items:center; justify-content:space-between; padding:.875rem 1.25rem; border-bottom:1px solid var(--pg-border); background:var(--pg-subtle); }
.pg-search { position:relative; flex:1 1 260px; max-width:26rem; }
.pg-search svg { position:absolute; left:.7rem; top:50%; width:1rem; height:1rem; transform:translateY(-50%); color:var(--pg-muted); pointer-events:none; }
.pg-search .pg-input { padding-left:2.2rem; }
.pg-selected { font-size:.875rem; color:var(--pg-muted); }
.pg-selected strong { color:var(--pg-text); }
.pg-table-scroll { max-height:28rem; overflow:auto; }
.pg-scroll-x { overflow-x:auto; }
.pg-table-note { padding:.75rem 1.25rem; border-top:1px solid var(--pg-border); font-size:.8125rem; color:var(--pg-muted); }

.pg-disclosure { border-top:1px solid var(--pg-border); }
.pg-disclosure > summary { display:flex; gap:.6rem; align-items:flex-start; padding:.875rem 1.25rem; background:#fffbeb; color:#92400e; font-size:.875rem; }
.dark .pg-disclosure > summary { background:rgba(120,53,15,.2); color:#fcd34d; }
.pg-disclosure > summary svg:first-child { width:1.15rem; height:1.15rem; margin-top:.1rem; }
.pg-disclosure[open] > summary { border-bottom:1px solid var(--pg-border); }

.pg-sendbar { display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-top:1px solid var(--pg-border); background:var(--pg-subtle); }
.pg-sendbar-text { font-size:.9375rem; color:var(--pg-text); }
.pg-sendbar-text .pg-hint { display:block; margin-top:.15rem; }
.pg-sendbar-actions { display:flex; flex-wrap:wrap; gap:.5rem; }

/* ── Form controls ──────────────────────────────────── */
.pg-field { display:flex; flex-direction:column; gap:.375rem; min-width:0; }
.pg-label { font-size:.875rem; font-weight:500; color:var(--pg-text-2); }
.pg-optional { font-weight:400; color:var(--pg-muted); }
.pg-input { width:100%; padding:.5rem .75rem; background:var(--pg-bg); border:1px solid var(--pg-border-strong); border-radius:.4rem; font-size:.875rem; color:var(--pg-text); }
.pg-input:focus { outline:2px solid #3b82f6; outline-offset:-1px; border-color:transparent; }
.dark .pg-input { background:#111827; }
.pg-hint { font-size:.75rem; line-height:1.45; color:var(--pg-muted); }
.pg-checks { display:flex; flex-wrap:wrap; gap:.5rem 1.25rem; }
.pg-check { display:inline-flex; align-items:center; gap:.45rem; font-size:.875rem; color:var(--pg-text-2); cursor:pointer; }
.pg-grid-2 { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem 1.5rem; }
.pg-span-all { grid-column:1 / -1; }

.pg-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:.55rem 1rem; border:1px solid transparent; border-radius:.5rem; font-size:.875rem; font-weight:500; text-decoration:none; cursor:pointer; transition:background .15s, border-color .15s; }
.pg-btn svg { width:1rem; height:1rem; }
.pg-btn-primary { background:var(--pg-primary); color:#fff; }
.pg-btn-primary:hover { background:var(--pg-primary-hover); }
.pg-btn-primary:disabled { background:#9ca3af; cursor:not-allowed; }
.pg-btn-ghost { background:var(--pg-bg); border-color:var(--pg-border-strong); color:var(--pg-text-2); }
.pg-btn-ghost:hover { background:var(--pg-subtle); }
.pg-btn-danger { background:var(--pg-bg); border-color:#fecaca; color:#b91c1c; }
.pg-btn-danger:hover { background:#fef2f2; }
.dark .pg-btn-danger { border-color:#7f1d1d; color:#fca5a5; }
.dark .pg-btn-danger:hover { background:rgba(127,29,29,.25); }
.pg-btn-sm { padding:.35rem .7rem; font-size:.8125rem; }
.pg-btn-lg { padding:.65rem 1.25rem; font-size:.9375rem; }
.pg-btn-block { width:100%; }

/* ── Tables ─────────────────────────────────────────── */
.pg-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.pg-table th { position:sticky; top:0; z-index:1; padding:.65rem 1rem; background:var(--pg-subtle); border-bottom:1px solid var(--pg-border); text-align:left; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; color:var(--pg-muted); }
.pg-table td { padding:.7rem 1rem; border-bottom:1px solid var(--pg-border); vertical-align:top; color:var(--pg-text-2); }
.pg-table tbody tr:last-child td { border-bottom:0; }
.pg-table tbody tr:hover td { background:var(--pg-subtle); }
.pg-col-check { width:2.75rem; }
.pg-row-click { cursor:pointer; }
.pg-row-muted td { opacity:.55; }
.pg-strong { font-weight:600; color:var(--pg-text); }
.pg-muted { margin-top:.15rem; font-size:.75rem; color:var(--pg-muted); }
.pg-break { word-break:break-all; }
.pg-nowrap { white-space:nowrap; }
.pg-link { color:var(--pg-primary-text); font-weight:500; text-decoration:none; }
.pg-link:hover { text-decoration:underline; }
.pg-pagination { padding:.75rem 1rem; border-top:1px solid var(--pg-border); }

.pg-badge { display:inline-flex; align-items:center; padding:.15rem .6rem; border-radius:9999px; font-size:.75rem; font-weight:500; white-space:nowrap; }
.pg-badge-green { background:#d1fae5; color:#065f46; }
.pg-badge-amber { background:#fef3c7; color:#92400e; }
.pg-badge-blue  { background:#dbeafe; color:#1e40af; }
.pg-badge-red   { background:#fee2e2; color:#991b1b; }
.pg-badge-gray  { background:#f3f4f6; color:#374151; }
.dark .pg-badge-green { background:#064e3b; color:#6ee7b7; }
.dark .pg-badge-amber { background:#78350f; color:#fcd34d; }
.dark .pg-badge-blue  { background:#1e3a8a; color:#bfdbfe; }
.dark .pg-badge-red   { background:#7f1d1d; color:#fecaca; }
.dark .pg-badge-gray  { background:#374151; color:#e5e7eb; }

.pg-dot { display:inline-block; flex:none; width:.55rem; height:.55rem; border-radius:9999px; background:#9ca3af; }
.pg-dot-green { background:#10b981; }
.pg-dot-blue  { background:#60a5fa; }
.pg-dot-red   { background:#ef4444; }

/* ── Progress (Riwayat, detail) ─────────────────────── */
.pg-progress { display:flex; min-width:8rem; height:.5rem; border-radius:9999px; overflow:hidden; background:var(--pg-track); }
.pg-progress-lg { height:.75rem; }
.pg-progress-sent   { background:#10b981; }
.pg-progress-failed { background:#ef4444; }
.pg-progress-queued { background:#60a5fa; }
.pg-legend { display:flex; flex-wrap:wrap; gap:.2rem .9rem; margin-top:.45rem; font-size:.75rem; color:var(--pg-muted); }
.pg-legend span { display:inline-flex; align-items:center; gap:.35rem; }
.pg-legend b { font-weight:600; color:var(--pg-text-2); }
.pg-col-result { min-width:15rem; }

/* ── Settings ───────────────────────────────────────── */
.pg-stack { display:flex; flex-direction:column; gap:1.25rem; }
.pg-section { display:flex; flex-direction:column; gap:1.25rem; }
.pg-section-head { display:flex; flex-wrap:wrap; gap:1rem; justify-content:space-between; align-items:flex-start; }
.pg-section-head h2 { font-size:1.0625rem; }
.pg-block h3 { font-size:.875rem; font-weight:600; color:var(--pg-text); }
.pg-block .pg-hint { margin-top:.15rem; }
.pg-block .pg-checks, .pg-block .pg-days { margin-top:.7rem; }
.pg-auto-fields { display:flex; flex-direction:column; gap:1.5rem; transition:opacity .15s; }
.pg-section.is-off .pg-auto-fields { opacity:.55; }

.pg-toggle { position:relative; display:inline-flex; align-items:center; gap:.6rem; cursor:pointer; user-select:none; }
.pg-toggle input[type=checkbox] { position:absolute; opacity:0; width:1px; height:1px; }
.pg-toggle-track { position:relative; width:2.75rem; height:1.5rem; border-radius:9999px; background:var(--pg-border-strong); transition:background .15s; }
.pg-toggle-thumb { position:absolute; top:.1875rem; left:.1875rem; width:1.125rem; height:1.125rem; border-radius:9999px; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.25); transition:transform .15s; }
.pg-toggle input:checked + .pg-toggle-track { background:#10b981; }
.pg-toggle input:checked + .pg-toggle-track .pg-toggle-thumb { transform:translateX(1.25rem); }
.pg-toggle input:focus-visible + .pg-toggle-track { outline:2px solid #93c5fd; outline-offset:2px; }
.pg-toggle-label { font-size:.875rem; font-weight:600; color:var(--pg-text); }

.pg-summary { display:flex; gap:.75rem; align-items:flex-start; padding:.9rem 1rem; border-radius:.6rem; background:var(--pg-primary-soft); font-size:.9rem; line-height:1.6; color:var(--pg-text); }
.pg-summary svg { width:1.25rem; height:1.25rem; margin-top:.15rem; color:var(--pg-primary-text); }
.pg-summary.is-off { background:var(--pg-subtle); color:var(--pg-muted); }
.pg-summary.is-off svg { color:var(--pg-muted); }
.pg-summary.is-invalid { background:#fef2f2; color:#991b1b; }
.dark .pg-summary.is-invalid { background:rgba(127,29,29,.25); color:#fca5a5; }
.pg-summary-next { display:block; margin-top:.2rem; font-size:.8125rem; color:var(--pg-muted); }

.pg-days { display:grid; grid-template-columns:repeat(7, minmax(0, 2.6rem)); gap:.375rem; }
.pg-day { position:relative; cursor:pointer; }
.pg-day input { position:absolute; opacity:0; pointer-events:none; }
.pg-day span { display:flex; align-items:center; justify-content:center; height:2.4rem; border:1px solid var(--pg-border-strong); border-radius:.4rem; background:var(--pg-bg); font-size:.875rem; color:var(--pg-text-2); user-select:none; transition:background .1s; }
.pg-day:hover span { border-color:var(--pg-primary); }
.pg-day input:checked + span { background:var(--pg-primary); border-color:var(--pg-primary); color:#fff; font-weight:600; }
.pg-day input:focus-visible + span { outline:2px solid #93c5fd; outline-offset:1px; }
.pg-savebar { display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; }

/* ── Templates ──────────────────────────────────────── */
.pg-toolbar { display:flex; flex-wrap:wrap; gap:.75rem 1rem; justify-content:space-between; align-items:center; }
.pg-intro { max-width:46rem; font-size:.875rem; color:var(--pg-muted); }
.pg-template-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1rem; }
.pg-template-card { display:flex; flex-direction:column; gap:.6rem; }
.pg-template-card h3 { font-size:1rem; font-weight:600; color:var(--pg-text); }
.pg-template-title { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
.pg-template-subject { font-size:.8125rem; color:var(--pg-text-2); }
.pg-template-excerpt { flex:1; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; font-size:.8125rem; line-height:1.5; color:var(--pg-muted); }
.pg-actions { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
.pg-actions form { display:inline; }
.pg-template-card .pg-actions { padding-top:.75rem; border-top:1px solid var(--pg-border); }

/* ── Blast detail ───────────────────────────────────── */
.pg-result-head { display:flex; flex-wrap:wrap; gap:1rem; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; }
.pg-result-title { margin-top:.15rem; display:flex; align-items:center; gap:.5rem; font-size:1.25rem; font-weight:700; color:var(--pg-text); }
.pg-result-title svg { width:1.4rem; height:1.4rem; }
.pg-ok { color:#059669; }
.pg-bad { color:#dc2626; }
.dark .pg-ok { color:#34d399; }
.dark .pg-bad { color:#f87171; }
.pg-filters { display:flex; flex-wrap:wrap; gap:.5rem; padding:.875rem 1.25rem; border-bottom:1px solid var(--pg-border); }
.pg-dl { display:grid; grid-template-columns:max-content minmax(0, 1fr); gap:.6rem 1.25rem; font-size:.875rem; }
.pg-dl dt { color:var(--pg-muted); }
.pg-dl dd { color:var(--pg-text); }
.pg-message { white-space:pre-line; }

/* ── Template editor ────────────────────────────────── */
.pg-editor { display:grid; grid-template-columns:minmax(0, 1fr) 22rem; gap:1.5rem; align-items:start; }
@media (max-width: 1100px) { .pg-editor { grid-template-columns:minmax(0, 1fr); } }
.pg-editor-main, .pg-editor-side .pg-panel { display:flex; flex-direction:column; gap:1rem; }
.pg-editor-side { display:flex; flex-direction:column; gap:1.5rem; }
.pg-editor-actions { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; }
.pg-mono { font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.8125rem; line-height:1.55; }
.pg-side-title { font-size:.9375rem; font-weight:600; color:var(--pg-text); }
.pg-placeholders { display:flex; flex-direction:column; gap:.5rem; }
.pg-placeholders li { display:flex; flex-direction:column; gap:.1rem; }
.pg-chip { align-self:flex-start; padding:.15rem .5rem; border:1px solid #bfdbfe; border-radius:.375rem; background:#eff6ff; font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.8125rem; color:#1d4ed8; cursor:pointer; }
.pg-chip:hover { background:#dbeafe; }
.dark .pg-chip { background:#1e3a8a; border-color:#1e40af; color:#bfdbfe; }
.pg-more > summary { display:flex; align-items:center; gap:.4rem; font-size:.8125rem; font-weight:500; color:var(--pg-primary-text); }
.pg-more[open] > summary { margin-bottom:.75rem; }
.pg-more-body { display:flex; flex-direction:column; gap:1rem; }

@media (max-width: 640px) {
    .pg-title { font-size:1.3rem; }
    .pg-step-head, .pg-step-body, .pg-table-tools, .pg-sendbar, .pg-disclosure > summary { padding-left:1rem; padding-right:1rem; }
    .pg-sendbar-actions, .pg-sendbar-actions .pg-btn { width:100%; }
    .pg-select-auto { width:100%; min-width:0; }
}
</style>
