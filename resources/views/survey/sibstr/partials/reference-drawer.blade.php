{{--
  Reference Drawer — slide-over panel showing previous-period data for copying.

  Props:
    $referenceResponse  — SurveyResponse model
    $fields             — array of ['name' => '', 'label' => '', 'copyable' => true, 'target' => ''] (target optional, defaults to name; supports dot-notation for nested JSON)
    $currentTwLabel     — string e.g. "Triwulan I (Jan–Mar) 2026"
--}}
@if(!empty($referenceResponse))
@php
    $refTahun    = $referenceResponse->tahun;
    $refTriwulan = $referenceResponse->triwulan;
    $refLabel    = $refTriwulan == 0
                    ? "Tahunan {$refTahun}"
                    : \App\Models\SurveyResponse::triwulanLabel($refTriwulan) . " {$refTahun}";
@endphp

{{-- Floating trigger button --}}
<div style="position:fixed;bottom:1.75rem;right:1.75rem;z-index:39;">
    <button type="button"
            id="ref-open-btn"
            onclick="openRefDrawer()"
            title="Lihat data referensi periode sebelumnya"
            aria-label="Buka panel data referensi"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.1rem;border-radius:9999px;
                   background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;
                   font-size:0.8125rem;font-weight:600;letter-spacing:0.01em;
                   box-shadow:0 4px 14px rgba(37,99,235,0.45);border:none;cursor:pointer;
                   transition:box-shadow 0.2s,transform 0.2s;">
        <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span>Data Referensi</span>
        <span style="display:inline-flex;align-items:center;justify-content:center;width:1.2rem;height:1.2rem;
                     border-radius:9999px;background:rgba(255,255,255,0.25);font-size:0.7rem;font-weight:700;">
            {{ count($fields ?? []) }}
        </span>
    </button>
</div>

{{-- Backdrop --}}
<div id="ref-backdrop"
     onclick="closeRefDrawer()"
     style="display:none;position:fixed;inset:0;top:4rem;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);z-index:48;
            opacity:0;transition:opacity 0.3s ease;"
     aria-hidden="true"></div>

{{-- Drawer --}}
<aside id="ref-drawer"
       role="complementary"
       aria-label="Panel data referensi untuk perbandingan"
       style="position:fixed;top:4rem;bottom:0;right:0;z-index:49;
              width:100%;max-width:36rem;display:flex;flex-direction:column;
              background:#fff;box-shadow:-8px 0 32px rgba(0,0,0,0.18);
              transform:translateX(100%);transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);">

    {{-- Header --}}
    <div style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;
                padding:1rem 1.25rem;
                background:linear-gradient(135deg,#1e40af,#2563eb);
                border-bottom:1px solid rgba(255,255,255,0.1);">
        <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;">
            <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;
                        width:2.25rem;height:2.25rem;border-radius:0.625rem;
                        background:rgba(255,255,255,0.18);">
                <svg style="width:1.125rem;height:1.125rem;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div style="min-width:0;">
                <p style="margin:0;font-size:0.9375rem;font-weight:700;color:#fff;line-height:1.2;">Data Referensi</p>
                <p style="margin:0.15rem 0 0;font-size:0.7rem;color:#bfdbfe;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $refLabel }} — Hanya untuk referensi
                </p>
            </div>
        </div>
        <button type="button"
                onclick="closeRefDrawer()"
                aria-label="Tutup panel data referensi"
                style="flex-shrink:0;margin-left:0.75rem;padding:0.4rem;border-radius:0.5rem;
                       background:rgba(255,255,255,0.15);border:none;cursor:pointer;color:#e0f2fe;
                       transition:background 0.15s;">
            <svg style="width:1.125rem;height:1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Period label bar --}}
    <div style="flex-shrink:0;padding:0.625rem 1.25rem;border-bottom:1px solid #e5e7eb;background:#f9fafb;
                display:flex;align-items:center;justify-content:space-between;gap:0.75rem;">
        <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.3rem 0.75rem;border-radius:9999px;
                     background:#dbeafe;color:#1d4ed8;font-size:0.75rem;font-weight:700;">
            <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $refLabel }}
        </span>
        @if(!empty($currentTwLabel))
        <button type="button"
                id="ref-copy-all-btn"
                style="flex-shrink:0;display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.875rem;
                       border-radius:0.5rem;background:#2563eb;color:#fff;font-size:0.75rem;font-weight:600;
                       border:none;cursor:pointer;transition:background 0.15s;">
            <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Salin Semua ke {{ $currentTwLabel }}
        </button>
        @endif
    </div>

    {{-- Scrollable content --}}
    <div style="flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:1.25rem;display:flex;flex-direction:column;gap:0.5rem;">
        @foreach($fields ?? [] as $field)
        @php
            $fVal     = data_get($referenceResponse, $field['name']);
            $fDisplay = is_array($fVal) ? implode(', ', array_filter(array_values($fVal), fn($v) => !is_array($v))) ?: json_encode($fVal) : ($fVal ?? '—');
            $fRaw     = is_array($fVal) ? json_encode($fVal) : ($fVal ?? '');
            $fTarget  = $field['target'] ?? $field['name'];
        @endphp
        <div class="ref-drawer-field"
             data-field="{{ $fTarget }}"
             data-value="{{ $fRaw }}"
             style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;
                    padding:0.625rem 0.875rem;border-radius:0.5rem;
                    background:#fff;border:1px solid #e5e7eb;">
            <div style="min-width:0;flex:1;">
                <p style="margin:0;font-size:0.7rem;color:#6b7280;">{{ $field['label'] }}</p>
                <p style="margin:0.15rem 0 0;font-size:0.875rem;font-weight:500;color:#111827;word-break:break-word;">{{ $fDisplay }}</p>
            </div>
            @if($field['copyable'] ?? false)
            <button type="button"
                    class="ref-copy-field-btn"
                    data-target="{{ $fTarget }}"
                    data-value="{{ $fRaw }}"
                    title="Salin ke formulir"
                    style="flex-shrink:0;display:inline-flex;align-items:center;gap:0.25rem;padding:0.3rem 0.625rem;
                           border-radius:0.375rem;background:#eff6ff;color:#2563eb;font-size:0.75rem;font-weight:600;
                           border:1px solid #bfdbfe;cursor:pointer;transition:background 0.15s,color 0.15s;white-space:nowrap;">
                <svg style="width:0.7rem;height:0.7rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Salin
            </button>
            @endif
        </div>
        @endforeach
    </div>
</aside>

<script>
(function () {
    'use strict';

    function openRefDrawer() {
        var drawer   = document.getElementById('ref-drawer');
        var backdrop = document.getElementById('ref-backdrop');
        if (!drawer || !backdrop) return;
        backdrop.style.display = 'block';
        requestAnimationFrame(function () {
            backdrop.style.opacity = '1';
            drawer.style.transform = 'translateX(0)';
        });
        document.body.style.overflow = 'hidden';
    }

    function closeRefDrawer() {
        var drawer   = document.getElementById('ref-drawer');
        var backdrop = document.getElementById('ref-backdrop');
        if (!drawer || !backdrop) return;
        drawer.style.transform = 'translateX(100%)';
        backdrop.style.opacity = '0';
        setTimeout(function () {
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }, 320);
        var btn = document.getElementById('ref-open-btn');
        if (btn) btn.focus();
    }

    function copyValueToField(fieldName, value) {
        var el = document.querySelector('[name="' + fieldName + '"],[name="' + fieldName + '[]"]');
        if (!el) return false;
        if (el.tagName === 'TEXTAREA' || (el.tagName === 'INPUT' && el.type !== 'radio' && el.type !== 'checkbox')) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }
        if (el.type === 'radio' || el.type === 'checkbox') {
            document.querySelectorAll('[name="' + fieldName + '"]').forEach(function (r) {
                r.checked = (r.value === value);
            });
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }
        return false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        /* Individual copy buttons */
        document.querySelectorAll('.ref-copy-field-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var copied = copyValueToField(btn.dataset.target, btn.dataset.value);
                if (copied) {
                    btn.textContent = '✓ Disalin';
                    btn.style.background = '#d1fae5';
                    btn.style.color = '#065f46';
                    btn.style.borderColor = '#6ee7b7';
                    setTimeout(function () {
                        btn.innerHTML = '<svg style="width:0.7rem;height:0.7rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin';
                        btn.style.background = '#eff6ff';
                        btn.style.color = '#2563eb';
                        btn.style.borderColor = '#bfdbfe';
                    }, 1500);
                }
            });
        });

        /* Copy-all button */
        var copyAllBtn = document.getElementById('ref-copy-all-btn');
        if (copyAllBtn) {
            copyAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.ref-copy-field-btn').forEach(function (btn) { btn.click(); });
            });
        }

        /* Hover effect for open button */
        var openBtn = document.getElementById('ref-open-btn');
        if (openBtn) {
            openBtn.addEventListener('mouseenter', function () {
                openBtn.style.boxShadow = '0 6px 20px rgba(37,99,235,0.55)';
                openBtn.style.transform = 'translateY(-1px)';
            });
            openBtn.addEventListener('mouseleave', function () {
                openBtn.style.boxShadow = '0 4px 14px rgba(37,99,235,0.45)';
                openBtn.style.transform = 'translateY(0)';
            });
        }
    });

    /* Escape closes drawer */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var drawer = document.getElementById('ref-drawer');
            if (drawer && drawer.style.transform !== 'translateX(100%)') closeRefDrawer();
        }
    });

    window.openRefDrawer  = openRefDrawer;
    window.closeRefDrawer = closeRefDrawer;
}());
</script>
@endif
