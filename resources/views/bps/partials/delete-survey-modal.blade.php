{{--
  Shared delete confirmation for the BPS survey data pages (SIBSTR / UB / Listrik).

  Include this once per page, then trigger it from a row action:

      <button type="button" class="btn-delete"
              onclick="bpsConfirmDelete('{{ route('bps.listrik.destroy', $resp->id) }}', @js($label), @js($sublabel))">
        Hapus
      </button>

  Deletion is soft — the row leaves the list and the responden may re-fill the
  survey, but the answers remain recoverable in the database.

  Styles are self-contained rather than Tailwind utilities so the modal renders
  correctly without depending on a fresh `npm run build`.
--}}
@once
@push('styles')
<style>
.btn-delete {
    padding: 0.375rem 0.75rem;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    cursor: pointer;
    transition: background .2s;
    font-family: inherit;
}
.btn-delete:hover { background: #b91c1c; }

.bps-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 9999;
}
.bps-modal-overlay[hidden] { display: none; }

.bps-modal {
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,.25);
    max-width: 30rem;
    width: 100%;
    overflow: hidden;
}
.dark .bps-modal { background: #1f2937; }

.bps-modal-body { padding: 1.5rem; display: flex; gap: 1rem; align-items: flex-start; }
.bps-modal-icon {
    width: 2.75rem; height: 2.75rem; flex-shrink: 0;
    border-radius: 9999px;
    background: #fee2e2; color: #dc2626;
    display: flex; align-items: center; justify-content: center;
}
.dark .bps-modal-icon { background: #7f1d1d; color: #fca5a5; }
.bps-modal-title { font-size: 1.05rem; font-weight: 700; color: #111827; margin-bottom: 0.35rem; }
.dark .bps-modal-title { color: #f9fafb; }
.bps-modal-text { font-size: 0.875rem; color: #4b5563; line-height: 1.5; }
.dark .bps-modal-text { color: #d1d5db; }
.bps-modal-target {
    margin-top: 0.75rem; padding: 0.625rem 0.75rem;
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem;
    font-size: 0.875rem; color: #111827; font-weight: 600;
    word-break: break-word;
}
.dark .bps-modal-target { background: #111827; border-color: #374151; color: #f9fafb; }
.bps-modal-sub { display: block; font-weight: 400; font-size: 0.75rem; color: #6b7280; margin-top: 0.15rem; }
.dark .bps-modal-sub { color: #9ca3af; }
.bps-modal-note {
    margin-top: 0.75rem; padding: 0.5rem 0.75rem;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.5rem;
    font-size: 0.75rem; color: #92400e;
}
.dark .bps-modal-note { background: #78350f33; border-color: #b45309; color: #fcd34d; }
.bps-modal-actions {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 1rem 1.5rem;
    background: #f9fafb; border-top: 1px solid #e5e7eb;
}
.dark .bps-modal-actions { background: #111827; border-color: #374151; }
.bps-btn-cancel {
    padding: 0.5rem 1rem; border-radius: 0.5rem;
    background: #fff; color: #374151; border: 1px solid #d1d5db;
    font-size: 0.875rem; font-weight: 500; cursor: pointer; font-family: inherit;
}
.bps-btn-cancel:hover { background: #f3f4f6; }
.dark .bps-btn-cancel { background: #374151; color: #e5e7eb; border-color: #4b5563; }
.dark .bps-btn-cancel:hover { background: #4b5563; }
.bps-btn-confirm {
    padding: 0.5rem 1rem; border-radius: 0.5rem;
    background: #dc2626; color: #fff; border: none;
    font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit;
}
.bps-btn-confirm:hover { background: #b91c1c; }
</style>
@endpush

<div id="bps-delete-modal" class="bps-modal-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="bps-delete-title">
    <div class="bps-modal">
        <div class="bps-modal-body">
            <div class="bps-modal-icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div style="flex:1; min-width:0;">
                <h3 class="bps-modal-title" id="bps-delete-title">Hapus Data Survei?</h3>
                <p class="bps-modal-text">
                    Data survei berikut akan dihapus dari daftar. Tindakan ini hanya memengaruhi
                    <strong id="bps-delete-scope">survei ini</strong> &mdash; survei lain milik responden yang sama tidak ikut terhapus.
                </p>
                <div class="bps-modal-target">
                    <span id="bps-delete-name">&mdash;</span>
                    <span class="bps-modal-sub" id="bps-delete-sub"></span>
                </div>
                <p class="bps-modal-note">
                    Responden dapat mengisi ulang survei ini setelah data dihapus.
                </p>
            </div>
        </div>
        <div class="bps-modal-actions">
            <button type="button" class="bps-btn-cancel" onclick="bpsCloseDelete()">Batal</button>
            <form method="POST" id="bps-delete-form" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="bps-btn-confirm">Ya, Hapus Data</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('bps-delete-modal');
    var form  = document.getElementById('bps-delete-form');

    window.bpsConfirmDelete = function (url, name, sub, scope) {
        form.setAttribute('action', url);
        document.getElementById('bps-delete-name').textContent = name || '—';
        document.getElementById('bps-delete-sub').textContent  = sub || '';
        document.getElementById('bps-delete-scope').textContent = scope || 'survei ini';
        modal.hidden = false;
    };

    window.bpsCloseDelete = function () {
        modal.hidden = true;
    };

    // Dismiss on backdrop click and on Escape, so a mis-opened dialog is easy
    // to back out of without reaching for the Batal button.
    modal.addEventListener('click', function (e) {
        if (e.target === modal) bpsCloseDelete();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) bpsCloseDelete();
    });
})();
</script>
@endpush
@endonce
