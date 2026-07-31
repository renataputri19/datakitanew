{{--
  Shared "Export Excel" dialog for the BPS survey data pages (SIBSTR / UB /
  Listrik). BPS picks the slice to download here rather than inheriting the
  page's own filters, so the export can be wider or narrower than what is on
  screen.

  Include it once per page with the survey's own filter set:

      @include('bps.partials.export-excel-modal', [
          'exportAction' => route('bps.ub.export'),
          'exportTitle'  => 'Ekspor Data Survei UB',
          'exportFields' => [
              ['type' => 'select', 'name' => 'status', 'label' => 'Status',
               'options' => ['' => 'Semua Status', 'completed' => 'Selesai']],
              ['type' => 'date', 'name' => 'date_from', 'label' => 'Diperbarui Dari'],
          ],
      ])

  then open it from the page header:

      <button type="button" class="btn-export-excel" onclick="bpsOpenExportModal()">Export Excel</button>

  Field keys: type (select|date|text), name, label, options (select only),
  value, hint, full (span both columns), show_when (see below).

  A field carrying show_when => ['field' => 'type', 'in' => ['', 'triwulanan']]
  is hidden — and its value cleared — while the named field holds a value
  outside that list. Used for Triwulan, which is meaningless on Tahunan rows.

  Styles are self-contained rather than Tailwind utilities so the dialog renders
  correctly without depending on a fresh `npm run build`.
--}}
@php
    $exportTitle    = $exportTitle    ?? 'Ekspor Data';
    $exportSubtitle = $exportSubtitle ?? 'Pilih data yang ingin diunduh. Kosongkan filter untuk mengunduh semua data.';
    $exportFields   = $exportFields   ?? [];
@endphp

@once
@push('styles')
<style>
.btn-export-excel {
    padding: 0.5rem 1rem;
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    font-family: inherit;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: background .2s;
}
.btn-export-excel:hover { background: #15803d; }

.bps-export-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 9999;
    overflow-y: auto;
}
.bps-export-overlay[hidden] { display: none; }

.bps-export-modal {
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,.25);
    max-width: 40rem;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.dark .bps-export-modal { background: #1f2937; }

.bps-export-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.dark .bps-export-header { border-color: #374151; }

.bps-export-header-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    background: rgba(22, 163, 74, .12);
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bps-export-title { font-size: 1rem; font-weight: 600; color: #111827; margin: 0; }
.dark .bps-export-title { color: #f9fafb; }
.bps-export-subtitle { font-size: .8125rem; color: #6b7280; margin: .25rem 0 0; line-height: 1.5; }
.dark .bps-export-subtitle { color: #9ca3af; }

.bps-export-body { padding: 1.5rem; overflow-y: auto; }

.bps-export-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}
@media (max-width: 640px) {
    .bps-export-grid { grid-template-columns: 1fr; }
}
.bps-export-field.is-full { grid-column: 1 / -1; }
.bps-export-field[hidden] { display: none; }

.bps-export-label {
    display: block;
    font-size: .8125rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: .25rem;
}
.dark .bps-export-label { color: #d1d5db; }

.bps-export-input {
    width: 100%;
    padding: .5rem .75rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: .375rem;
    font-size: .875rem;
    font-family: inherit;
    color: #111827;
}
.dark .bps-export-input { background: #111827; border-color: #4b5563; color: #f3f4f6; }
.bps-export-input:focus { outline: 2px solid #16a34a; outline-offset: -1px; border-color: transparent; }

.bps-export-hint { font-size: .75rem; color: #6b7280; margin: .25rem 0 0; }
.dark .bps-export-hint { color: #9ca3af; }

.bps-export-footer {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    flex-wrap: wrap;
}
.dark .bps-export-footer { background: #111827; border-color: #374151; }

.bps-export-btn {
    padding: .5rem 1rem;
    border-radius: .5rem;
    font-size: .875rem;
    font-weight: 500;
    font-family: inherit;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .375rem;
    transition: background .2s;
}
.bps-export-btn-cancel { background: #e5e7eb; color: #374151; }
.bps-export-btn-cancel:hover { background: #d1d5db; }
.dark .bps-export-btn-cancel { background: #374151; color: #e5e7eb; }
.dark .bps-export-btn-cancel:hover { background: #4b5563; }
.bps-export-btn-reset { background: transparent; color: #6b7280; margin-right: auto; }
.bps-export-btn-reset:hover { color: #374151; text-decoration: underline; }
.dark .bps-export-btn-reset { color: #9ca3af; }
.bps-export-btn-submit { background: #16a34a; color: #fff; }
.bps-export-btn-submit:hover { background: #15803d; }
</style>
@endpush
@endonce

<div id="bpsExportOverlay" class="bps-export-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="bpsExportTitle">
    <div class="bps-export-modal">
        <form method="GET" action="{{ $exportAction }}" id="bpsExportForm">
            <div class="bps-export-header">
                <div class="bps-export-header-icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="bps-export-title" id="bpsExportTitle">{{ $exportTitle }}</h2>
                    <p class="bps-export-subtitle">{{ $exportSubtitle }}</p>
                </div>
            </div>

            <div class="bps-export-body">
                <div class="bps-export-grid">
                    @foreach($exportFields as $field)
                        @php
                            $name  = $field['name'];
                            $type  = $field['type'] ?? 'text';
                            $value = $field['value'] ?? '';
                        @endphp
                        <div class="bps-export-field {{ ($field['full'] ?? false) ? 'is-full' : '' }}"
                             data-export-field="{{ $name }}"
                             @isset($field['show_when'])
                                 data-export-show-when="{{ json_encode($field['show_when']) }}"
                             @endisset>
                            <label class="bps-export-label" for="bps_export_{{ $name }}">{{ $field['label'] }}</label>

                            @if($type === 'select')
                                <select class="bps-export-input" id="bps_export_{{ $name }}" name="{{ $name }}">
                                    @foreach($field['options'] as $optValue => $optLabel)
                                        <option value="{{ $optValue }}" {{ (string) $optValue === (string) $value ? 'selected' : '' }}>
                                            {{ $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif($type === 'date')
                                <input class="bps-export-input" type="date"
                                       id="bps_export_{{ $name }}" name="{{ $name }}" value="{{ $value }}">
                            @else
                                <input class="bps-export-input" type="text"
                                       id="bps_export_{{ $name }}" name="{{ $name }}" value="{{ $value }}"
                                       placeholder="{{ $field['placeholder'] ?? '' }}">
                            @endif

                            @isset($field['hint'])
                                <p class="bps-export-hint">{{ $field['hint'] }}</p>
                            @endisset
                        </div>
                    @endforeach

                    <div class="bps-export-field">
                        <label class="bps-export-label" for="bps_export_format">Format Berkas</label>
                        <select class="bps-export-input" id="bps_export_format" name="format">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bps-export-footer">
                <button type="button" class="bps-export-btn bps-export-btn-reset" onclick="bpsResetExportModal()">
                    Reset filter
                </button>
                <button type="button" class="bps-export-btn bps-export-btn-cancel" onclick="bpsCloseExportModal()">
                    Batal
                </button>
                <button type="submit" class="bps-export-btn bps-export-btn-submit">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Data
                </button>
            </div>
        </form>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    var overlay = document.getElementById('bpsExportOverlay');
    var form    = document.getElementById('bpsExportForm');
    if (!overlay || !form) return;

    // Remembered so "Reset filter" restores the defaults the page rendered
    // with, not an empty form.
    var defaults = {};
    form.querySelectorAll('[name]').forEach(function (el) { defaults[el.name] = el.value; });

    /**
     * Apply every data-export-show-when rule. A hidden field is also disabled
     * so it stays out of the query string entirely — an unwanted `triwulan=2`
     * would otherwise narrow a Tahunan export to nothing.
     */
    function applyConditions() {
        overlay.querySelectorAll('[data-export-show-when]').forEach(function (wrapper) {
            var rule;
            try {
                rule = JSON.parse(wrapper.getAttribute('data-export-show-when'));
            } catch (e) {
                return;
            }

            var source = form.querySelector('[name="' + rule.field + '"]');
            if (!source) return;

            var visible = (rule.in || []).indexOf(source.value) !== -1;
            wrapper.hidden = !visible;

            wrapper.querySelectorAll('[name]').forEach(function (input) {
                input.disabled = !visible;
                if (!visible) input.value = '';
            });
        });
    }

    form.addEventListener('change', applyConditions);

    window.bpsOpenExportModal = function () {
        applyConditions();
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        var first = form.querySelector('select, input');
        if (first) first.focus();
    };

    window.bpsCloseExportModal = function () {
        overlay.hidden = true;
        document.body.style.overflow = '';
    };

    window.bpsResetExportModal = function () {
        form.querySelectorAll('[name]').forEach(function (el) {
            el.value = defaults.hasOwnProperty(el.name) ? defaults[el.name] : '';
        });
        applyConditions();
    };

    // The download leaves the page in place, so close the dialog once the
    // browser has taken the request.
    form.addEventListener('submit', function () {
        setTimeout(window.bpsCloseExportModal, 400);
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) window.bpsCloseExportModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.hidden) window.bpsCloseExportModal();
    });

    applyConditions();
})();
</script>
@endpush
@endonce
