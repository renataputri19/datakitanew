{{--
  In-page confirmation dialog for /bps/pengingat, replacing the browser's
  confirm(). Include once, inside .pg so it inherits the page colours.

  A static question goes on the form itself:

      <form method="POST" data-confirm="Hapus template?"
            data-confirm-text="…" data-confirm-ok="Ya, Hapus" data-confirm-tone="danger">

  A dynamic one calls the JS directly; it resolves true only on the OK button:

      pgConfirm({ title, text, facts: [['Periode', 'Triwulan III 2026']], note, ok, tone })

  Built on <dialog>.showModal(), which already traps focus, closes on Escape
  and makes the page behind it inert. Focus starts on "Batal": every action
  behind this dialog emails real respondents or deletes something.
--}}
@once
@push('styles')
<style>
.pg-dialog { padding:0; border:none; border-radius:.875rem; background:var(--pg-bg); color:var(--pg-text-2); width:min(30rem, calc(100vw - 2rem)); max-height:calc(100vh - 2rem); box-shadow:0 25px 50px -12px rgba(0,0,0,.35); overflow:auto; }
.pg-dialog::backdrop { background:rgba(15,23,42,.55); backdrop-filter:blur(2px); }
.pg-dialog [hidden] { display:none !important; } /* .pg-icon-circle and the note set display: */
.pg-dialog[open] { animation:pg-dialog-in .16s ease-out; }
.pg-dialog[open]::backdrop { animation:pg-dialog-fade .16s ease-out; }
@keyframes pg-dialog-in { from { opacity:0; transform:translateY(8px) scale(.98); } }
@keyframes pg-dialog-fade { from { opacity:0; } }
@media (prefers-reduced-motion: reduce) { .pg-dialog[open], .pg-dialog[open]::backdrop { animation:none; } }

.pg-dialog-box { margin:0; }
.pg-dialog-body { display:flex; gap:1rem; align-items:flex-start; padding:1.5rem 1.5rem 1.25rem; }
.pg-dialog-body > div { flex:1; min-width:0; }
.pg-dialog .pg-icon-circle { width:2.75rem; height:2.75rem; flex:none; }
.pg-icon-red { background:#fee2e2; color:#dc2626; }
.dark .pg-icon-red { background:rgba(127,29,29,.35); color:#fca5a5; }
.pg-dialog-title { font-size:1.0625rem; line-height:1.4; font-weight:700; color:var(--pg-text); margin:.15rem 0 0; }
.pg-dialog-text { margin-top:.35rem; font-size:.875rem; line-height:1.55; color:var(--pg-muted); }

.pg-dialog-facts { margin-top:1rem; border:1px solid var(--pg-border); border-radius:.6rem; background:var(--pg-subtle); font-size:.875rem; }
.pg-dialog-facts > div { display:grid; grid-template-columns:7.5rem 1fr; gap:.75rem; padding:.55rem .875rem; }
.pg-dialog-facts > div + div { border-top:1px solid var(--pg-border); }
.pg-dialog-facts dt { color:var(--pg-muted); }
.pg-dialog-facts dd { margin:0; color:var(--pg-text); font-weight:600; overflow-wrap:anywhere; }
.pg-dialog-facts dd.is-long { font-weight:400; white-space:pre-line; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }

.pg-dialog-note { display:flex; gap:.5rem; align-items:flex-start; margin-top:.875rem; padding:.6rem .75rem; border-radius:.5rem; background:var(--pg-primary-soft); color:var(--pg-primary-text); font-size:.8125rem; line-height:1.5; }
.pg-dialog-note svg { width:1rem; height:1rem; margin-top:.1rem; }

.pg-dialog-actions { display:flex; justify-content:flex-end; gap:.5rem; padding:1rem 1.5rem; background:var(--pg-subtle); border-top:1px solid var(--pg-border); }
.pg-btn-danger-solid { background:#dc2626; color:#fff; }
.pg-btn-danger-solid:hover { background:#b91c1c; }

@media (max-width: 480px) {
    .pg-dialog-body { flex-direction:column; gap:.75rem; padding:1.25rem; }
    .pg-dialog-facts > div { grid-template-columns:1fr; gap:.1rem; }
    .pg-dialog-actions { flex-direction:column-reverse; padding:1rem 1.25rem; }
    .pg-dialog-actions .pg-btn { width:100%; }
}
</style>
@endpush

<dialog id="pg-confirm" class="pg-dialog" aria-labelledby="pg-confirm-title" aria-describedby="pg-confirm-text">
    <form method="dialog" class="pg-dialog-box">
        <div class="pg-dialog-body">
            <span class="pg-icon-circle pg-icon-blue" data-tone-icon="primary">@include('bps.pengingat.partials.icon', ['name' => 'mail'])</span>
            <span class="pg-icon-circle pg-icon-red" data-tone-icon="danger" hidden>@include('bps.pengingat.partials.icon', ['name' => 'warn'])</span>
            <div>
                <h2 class="pg-dialog-title" id="pg-confirm-title"></h2>
                <p class="pg-dialog-text" id="pg-confirm-text"></p>
                <dl class="pg-dialog-facts" hidden></dl>
                <p class="pg-dialog-note" hidden>@include('bps.pengingat.partials.icon', ['name' => 'clock'])<span></span></p>
            </div>
        </div>
        <div class="pg-dialog-actions">
            <button type="submit" value="cancel" class="pg-btn pg-btn-ghost" autofocus>Batal</button>
            <button type="submit" value="ok" class="pg-btn pg-btn-primary" data-confirm-ok></button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
(function () {
    var dialog = document.getElementById('pg-confirm');
    if (!dialog) return;

    var title  = dialog.querySelector('#pg-confirm-title');
    var text   = dialog.querySelector('#pg-confirm-text');
    var facts  = dialog.querySelector('.pg-dialog-facts');
    var note   = dialog.querySelector('.pg-dialog-note');
    var okBtn  = dialog.querySelector('[data-confirm-ok]');
    var icons  = dialog.querySelectorAll('[data-tone-icon]');
    var settle = null;

    // Everything below is set with textContent: template names and the extra
    // message are typed by users.
    window.pgConfirm = function (opts) {
        var tone = opts.tone === 'danger' ? 'danger' : 'primary';

        if (typeof dialog.showModal !== 'function') {
            return Promise.resolve(window.confirm([opts.title, opts.text].filter(Boolean).join('\n\n')));
        }

        title.textContent = opts.title || '';
        text.textContent  = opts.text || '';
        text.hidden       = !opts.text;

        facts.textContent = '';
        (opts.facts || []).forEach(function (f) {
            var row = document.createElement('div');
            var dt  = document.createElement('dt');
            var dd  = document.createElement('dd');
            dt.textContent = f[0];
            dd.textContent = f[1];
            if (f[2]) dd.className = 'is-long';
            row.appendChild(dt);
            row.appendChild(dd);
            facts.appendChild(row);
        });
        facts.hidden = !facts.children.length;

        note.lastElementChild.textContent = opts.note || '';
        note.hidden = !opts.note;

        okBtn.textContent = opts.ok || 'Ya, Lanjutkan';
        okBtn.className   = 'pg-btn ' + (tone === 'danger' ? 'pg-btn-danger-solid' : 'pg-btn-primary');
        [].forEach.call(icons, function (i) { i.hidden = i.getAttribute('data-tone-icon') !== tone; });

        dialog.returnValue = '';
        dialog.showModal();
        document.documentElement.style.overflow = 'hidden';

        return new Promise(function (resolve) { settle = resolve; });
    };

    dialog.addEventListener('close', function () {
        document.documentElement.style.overflow = '';
        if (settle) settle(dialog.returnValue === 'ok');
        settle = null;
    });

    // A click on the dimmed backdrop lands on the <dialog> itself.
    dialog.addEventListener('click', function (e) {
        if (e.target === dialog) dialog.close('cancel');
    });

    // Forms carrying data-confirm ask first, then submit for real. The submit
    // button is disabled so a second click cannot queue the action twice.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.hasAttribute('data-confirm') || form.hasAttribute('data-confirmed')) return;
        e.preventDefault();

        pgConfirm({
            title: form.getAttribute('data-confirm'),
            text:  form.getAttribute('data-confirm-text'),
            ok:    form.getAttribute('data-confirm-ok'),
            tone:  form.getAttribute('data-confirm-tone'),
        }).then(function (ok) {
            if (!ok) return;
            form.setAttribute('data-confirmed', '');
            [].forEach.call(form.querySelectorAll('[type="submit"]'), function (b) { b.disabled = true; });
            form.submit();
        });
    });
})();
</script>
@endpush
@endonce
