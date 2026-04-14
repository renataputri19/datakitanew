{{--
  Reference Panel — shown when a previous period response is available.
  Displays read-only data from $referenceResponse for comparison.
  copyable = true  → show "Salin" buttons (Blok 1 & 2 fields)
  copyable = false → read-only only (Blok 3+)

  Usage:
    @include('survey.sibstr.partials.reference-panel', [
        'referenceResponse' => $referenceResponse,
        'tahun'             => $tahun,
        'triwulan'          => $triwulan,
        'fields'            => [['name' => 'field_name', 'label' => 'Label', 'copyable' => true], ...],
    ])
--}}

@if(!empty($referenceResponse))
  @php
    $refTahun    = $referenceResponse->tahun;
    $refTriwulan = $referenceResponse->triwulan;
    $refLabel    = $refTriwulan == 0
                    ? "Tahunan {$refTahun}"
                    : \App\Models\SurveyResponse::triwulanLabel($refTriwulan) . " {$refTahun}";
    $currentTwLabel = ($triwulan ?? 0) == 0
                    ? "Tahunan {$tahun}"
                    : \App\Models\SurveyResponse::triwulanLabel($triwulan) . " {$tahun}";
    $hasCopyable = collect($fields ?? [])->contains('copyable', true);
  @endphp

  <div id="ref-panel"
       class="reference-panel mb-6 rounded-xl border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-950/30 p-4"
       aria-label="Data referensi periode sebelumnya">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4 mb-3">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
            Data Referensi: {{ $refLabel }}
          </p>
          <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
            Hanya untuk perbandingan. Tidak dapat diedit di sini.
          </p>
        </div>
      </div>

      @if($hasCopyable)
        <button type="button"
                id="copy-all-btn"
                class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          Salin Semua ke {{ $currentTwLabel }}
        </button>
      @endif
    </div>

    <!-- Reference field grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      @foreach($fields ?? [] as $field)
        @php
          $refValue = $referenceResponse->{$field['name']} ?? null;
          $displayValue = is_array($refValue) ? json_encode($refValue) : ($refValue ?? '—');
        @endphp
        <div class="ref-field-row flex items-center justify-between gap-2 rounded-lg bg-white dark:bg-gray-900/50 px-3 py-2 border border-blue-100 dark:border-blue-800"
             data-field="{{ $field['name'] }}"
             data-value="{{ is_array($refValue) ? json_encode($refValue) : ($refValue ?? '') }}">
          <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $field['label'] }}</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $displayValue }}</p>
          </div>
          @if($field['copyable'] ?? false)
            <button type="button"
                    class="copy-field-btn flex-shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900/40 dark:hover:bg-blue-900/70 dark:text-blue-300 transition-colors"
                    data-target="{{ $field['name'] }}"
                    data-value="{{ is_array($refValue) ? json_encode($refValue) : ($refValue ?? '') }}"
                    title="Salin ke formulir saat ini">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              Salin
            </button>
          @endif
        </div>
      @endforeach
    </div>
  </div>

  <script>
  (function () {
      'use strict';

      function copyValueToField(fieldName, value) {
          // Try common form element types in priority order
          const el = document.querySelector(
              '[name="' + fieldName + '"],' +
              '[name="' + fieldName + '[]"]'
          );

          if (!el) return false;

          if (el.tagName === 'TEXTAREA' || (el.tagName === 'INPUT' && el.type !== 'radio' && el.type !== 'checkbox')) {
              el.value = value;
              el.dispatchEvent(new Event('input', { bubbles: true }));
              el.dispatchEvent(new Event('change', { bubbles: true }));
              return true;
          }

          if (el.type === 'radio' || el.type === 'checkbox') {
              const radios = document.querySelectorAll('[name="' + fieldName + '"]');
              radios.forEach(r => { r.checked = (r.value === value); });
              el.dispatchEvent(new Event('change', { bubbles: true }));
              return true;
          }

          return false;
      }

      document.addEventListener('DOMContentLoaded', function () {
          // Individual copy buttons
          document.querySelectorAll('.copy-field-btn').forEach(function (btn) {
              btn.addEventListener('click', function () {
                  const target = btn.dataset.target;
                  const value  = btn.dataset.value;
                  const copied = copyValueToField(target, value);

                  if (copied) {
                      btn.textContent = '✓';
                      btn.classList.remove('bg-blue-100', 'dark:bg-blue-900/40');
                      btn.classList.add('bg-green-100', 'dark:bg-green-900/40', 'text-green-700', 'dark:text-green-300');
                      setTimeout(function () {
                          btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin';
                          btn.classList.remove('bg-green-100', 'dark:bg-green-900/40', 'text-green-700', 'dark:text-green-300');
                          btn.classList.add('bg-blue-100', 'dark:bg-blue-900/40', 'text-blue-700', 'dark:text-blue-300');
                      }, 1500);
                  }
              });
          });

          // Copy-all button
          const copyAllBtn = document.getElementById('copy-all-btn');
          if (copyAllBtn) {
              copyAllBtn.addEventListener('click', function () {
                  document.querySelectorAll('.copy-field-btn').forEach(function (btn) {
                      btn.click();
                  });
              });
          }
      });
  }());
  </script>
@endif
