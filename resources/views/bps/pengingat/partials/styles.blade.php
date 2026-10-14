{{-- Page CSS for /bps/pengingat. Plain CSS so the page needs no Vite rebuild. --}}
<style>
.pg-panel { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; box-shadow:0 1px 2px rgba(0,0,0,.05); padding:1.25rem; }
.dark .pg-panel { background:#1f2937; border-color:#374151; }
.pg-flush { padding:0; overflow:hidden; }
.pg-empty { padding:3rem 1.5rem; text-align:center; color:#6b7280; }

.pg-cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; }
.pg-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1rem 1.25rem; }
.dark .pg-card { background:#1f2937; border-color:#374151; }
.pg-card-label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; }
.pg-card-value { margin-top:.35rem; font-size:1.125rem; font-weight:700; color:#111827; }
.dark .pg-card-value { color:#f9fafb; }
.pg-card-sub { margin-top:.25rem; font-size:.8125rem; color:#6b7280; }
.dark .pg-card-sub, .dark .pg-card-label { color:#9ca3af; }

.pg-alert { border-radius:.5rem; padding:.875rem 1rem; font-size:.875rem; border:1px solid; }
.pg-alert code { font-size:.8125rem; }
.pg-alert-warn { background:#fffbeb; border-color:#fde68a; color:#92400e; }
.dark .pg-alert-warn { background:rgba(120,53,15,.25); border-color:#92400e; color:#fcd34d; }
.pg-alert-error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
.dark .pg-alert-error { background:rgba(127,29,29,.25); border-color:#991b1b; color:#fca5a5; }

.pg-filter { display:flex; flex-wrap:wrap; gap:1rem 1.5rem; align-items:flex-end; }
.pg-field { display:flex; flex-direction:column; gap:.375rem; min-width:0; }
.pg-field-end { margin-left:auto; }
.pg-label { font-size:.875rem; font-weight:500; color:#374151; }
.dark .pg-label { color:#d1d5db; }
.pg-input { width:100%; padding:.5rem .75rem; background:#fff; border:1px solid #d1d5db; border-radius:.375rem; font-size:.875rem; color:#111827; }
.pg-input:focus { outline:2px solid #3b82f6; outline-offset:-1px; border-color:transparent; }
.dark .pg-input { background:#111827; border-color:#4b5563; color:#f3f4f6; }
.pg-hint { font-size:.75rem; color:#6b7280; }
.dark .pg-hint { color:#9ca3af; }
.pg-checks { display:flex; flex-wrap:wrap; gap:.5rem 1.25rem; }
.pg-check { display:inline-flex; align-items:center; gap:.4rem; font-size:.875rem; color:#374151; cursor:pointer; }
.dark .pg-check { color:#d1d5db; }

.pg-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1rem; border-radius:.5rem; font-size:.875rem; font-weight:500; border:1px solid transparent; cursor:pointer; text-decoration:none; transition:background .15s; }
.pg-btn-primary { background:#2563eb; color:#fff; }
.pg-btn-primary:hover { background:#1d4ed8; }
.pg-btn-primary:disabled { background:#9ca3af; cursor:not-allowed; }
.pg-btn-ghost { background:#fff; color:#374151; border-color:#d1d5db; }
.pg-btn-ghost:hover { background:#f9fafb; }
.dark .pg-btn-ghost { background:#1f2937; color:#e5e7eb; border-color:#4b5563; }

.pg-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.pg-table th { background:#f9fafb; padding:.65rem 1rem; text-align:left; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
.pg-table td { padding:.65rem 1rem; border-bottom:1px solid #e5e7eb; color:#374151; vertical-align:top; }
.dark .pg-table th { background:#111827; color:#9ca3af; border-color:#374151; }
.dark .pg-table td { color:#d1d5db; border-color:#374151; }
.pg-table tbody tr:last-child td { border-bottom:0; }
.pg-col-check { width:2.5rem; }
.pg-row-muted td { opacity:.6; }
.pg-muted { font-size:.75rem; color:#6b7280; margin-top:.15rem; }
.pg-break { word-break:break-all; }
.pg-link { color:#2563eb; font-weight:500; }
.pg-link:hover { text-decoration:underline; }
.dark .pg-link { color:#60a5fa; }

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

.pg-send-footer { display:flex; flex-wrap:wrap; gap:1rem 1.5rem; align-items:flex-end; padding:1rem 1.25rem; border-top:1px solid #e5e7eb; background:#f9fafb; }
.dark .pg-send-footer { border-color:#374151; background:#111827; }
.pg-send-footer .pg-field { flex:1 1 320px; }
.pg-send-footer .pg-field-narrow { flex:0 1 260px; }
.pg-send-actions { display:flex; flex-direction:column; gap:.375rem; align-items:flex-end; }

.pg-panel-head { padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; }
.dark .pg-panel-head { border-color:#374151; }
.pg-panel-head h2 { font-size:1rem; font-weight:600; color:#111827; }
.dark .pg-panel-head h2 { color:#f9fafb; }
.pg-panel-head p { margin-top:.2rem; font-size:.8125rem; color:#6b7280; }

.pg-settings section { padding:1.1rem 0; border-bottom:1px solid #e5e7eb; }
.dark .pg-settings section { border-color:#374151; }
.pg-settings section:first-child { padding-top:0; }
.pg-settings h3 { font-size:.9375rem; font-weight:600; color:#111827; margin-bottom:.15rem; }
.dark .pg-settings h3 { color:#f9fafb; }
.pg-settings .pg-hint + .pg-days, .pg-settings .pg-hint + .pg-checks { margin-top:.65rem; }
.pg-grid-2 { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1rem 1.5rem; }
.pg-switch { display:flex; gap:.75rem; align-items:flex-start; cursor:pointer; color:#111827; font-size:.9375rem; }
.dark .pg-switch { color:#f9fafb; }
.pg-switch input[type=checkbox] { margin-top:.25rem; width:1.1rem; height:1.1rem; }
.pg-days { display:grid; grid-template-columns:repeat(auto-fill, minmax(2.6rem, 1fr)); gap:.4rem; max-width:28rem; }
.pg-day { position:relative; cursor:pointer; }
.pg-day input { position:absolute; opacity:0; pointer-events:none; }
.pg-day span { display:flex; align-items:center; justify-content:center; height:2.4rem; border:1px solid #d1d5db; border-radius:.4rem; font-size:.875rem; color:#374151; background:#fff; user-select:none; }
.dark .pg-day span { background:#111827; border-color:#4b5563; color:#d1d5db; }
.pg-day input:checked + span { background:#2563eb; border-color:#2563eb; color:#fff; font-weight:600; }
.pg-day input:focus-visible + span { outline:2px solid #93c5fd; outline-offset:1px; }
.pg-settings-footer { display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; padding-top:1.1rem; }

.pg-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; }
.pg-message { white-space:pre-line; font-size:.875rem; color:#374151; }
.dark .pg-message { color:#d1d5db; }
.pg-pagination { padding:.75rem 1rem; border-top:1px solid #e5e7eb; }
.dark .pg-pagination { border-color:#374151; }

/* Template editor */
.pg-editor { display:grid; grid-template-columns:minmax(0, 1fr) 22rem; gap:1.5rem; align-items:start; }
@media (max-width: 1100px) { .pg-editor { grid-template-columns:minmax(0, 1fr); } }
.pg-editor-main, .pg-editor-side .pg-panel { display:flex; flex-direction:column; gap:1rem; }
.pg-editor-side { display:flex; flex-direction:column; gap:1.5rem; }
.pg-editor-actions { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; }
.pg-mono { font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.8125rem; line-height:1.55; }
.pg-side-title { font-size:.9375rem; font-weight:600; color:#111827; }
.dark .pg-side-title { color:#f9fafb; }
.pg-placeholders { display:flex; flex-direction:column; gap:.5rem; }
.pg-placeholders li { display:flex; flex-direction:column; gap:.1rem; }
.pg-chip { align-self:flex-start; font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.8125rem; padding:.15rem .5rem; border-radius:.375rem; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; cursor:pointer; }
.pg-chip:hover { background:#dbeafe; }
.dark .pg-chip { background:#1e3a8a; color:#bfdbfe; border-color:#1e40af; }
.pg-btn-block { justify-content:center; width:100%; }
.pg-actions { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
.pg-actions form { display:inline; }
.pg-btn-sm { padding:.3rem .65rem; font-size:.8125rem; }
.pg-btn-danger { background:#fff; color:#b91c1c; border-color:#fecaca; }
.pg-btn-danger:hover { background:#fef2f2; }
.dark .pg-btn-danger { background:#1f2937; color:#fca5a5; border-color:#7f1d1d; }
.pg-template-snippet { margin-top:.25rem; font-size:.8125rem; color:#6b7280; max-width:42rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.pg-toolbar { display:flex; flex-wrap:wrap; gap:.75rem; justify-content:space-between; align-items:center; }
</style>
