{{--
  Cross-Fill Drawer — slide-over panel that copies overlapping Blok I answers
  from the *other* survey (UB ↔ SIBSTR). Mirrors the previous-period reference
  drawer but pulls from a different questionnaire. Manual: nothing is copied
  until the respondent clicks a "Salin" button.

  Props:
    $items        — array of ['label','target','value','display','copyable'] (from App\Support\SurveyCrossFill)
    $sourceBadge  — short source label, e.g. "Survei UB" or "SIBSTR"
    $sourceLabel  — longer caption, e.g. "Data dari Survei UB SE2026 (sudah terisi)"
    $offsetBottom — optional CSS bottom offset for the floating button (default 1.75rem)
--}}
@php
    $cfItems       = $items ?? [];
    $cfCopyable    = collect($cfItems)->where('copyable', true)->count();
    $cfSourceBadge = $sourceBadge ?? 'Survei lain';
    $cfSourceLabel = $sourceLabel ?? 'Data dari survei lain yang sudah terisi';
    $cfOffset      = $offsetBottom ?? '1.75rem';
@endphp
@if($cfCopyable > 0)

{{-- Floating trigger button (emerald, to distinguish from the blue reference drawer) --}}
<div style="position:fixed;bottom:{{ $cfOffset }};right:1.75rem;z-index:38;">
    <button type="button"
            id="cross-open-btn"
            onclick="openCrossDrawer()"
            title="Salin jawaban yang sama dari {{ $cfSourceBadge }}"
            aria-label="Buka panel salin data dari {{ $cfSourceBadge }}"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.1rem;border-radius:9999px;
                   background:linear-gradient(135deg,#047857,#059669);color:#fff;
                   font-size:0.8125rem;font-weight:600;letter-spacing:0.01em;
                   box-shadow:0 4px 14px rgba(5,150,105,0.45);border:none;cursor:pointer;
                   transition:box-shadow 0.2s,transform 0.2s;">
        <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        <span>Salin dari {{ $cfSourceBadge }}</span>
        <span style="display:inline-flex;align-items:center;justify-content:center;width:1.2rem;height:1.2rem;
                     border-radius:9999px;background:rgba(255,255,255,0.25);font-size:0.7rem;font-weight:700;">
            {{ $cfCopyable }}
        </span>
    </button>
</div>

{{-- Backdrop --}}
<div id="cross-backdrop"
     onclick="closeCrossDrawer()"
     style="display:none;position:fixed;inset:0;top:4rem;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);z-index:48;
            opacity:0;transition:opacity 0.3s ease;"
     aria-hidden="true"></div>

{{-- Drawer --}}
<aside id="cross-drawer"
       role="complementary"
       aria-label="Panel salin data dari {{ $cfSourceBadge }}"
       style="position:fixed;top:4rem;bottom:0;right:0;z-index:49;
              width:100%;max-width:36rem;display:flex;flex-direction:column;
              background:#fff;box-shadow:-8px 0 32px rgba(0,0,0,0.18);
              transform:translateX(100%);transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);">

    {{-- Header --}}
    <div style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;
                padding:1rem 1.25rem;
                background:linear-gradient(135deg,#065f46,#059669);
                border-bottom:1px solid rgba(255,255,255,0.1);">
        <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;">
            <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;
                        width:2.25rem;height:2.25rem;border-radius:0.625rem;
                        background:rgba(255,255,255,0.18);">
                <svg style="width:1.125rem;height:1.125rem;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div style="min-width:0;">
                <p style="margin:0;font-size:0.9375rem;font-weight:700;color:#fff;line-height:1.2;">Salin dari {{ $cfSourceBadge }}</p>
                <p style="margin:0.15rem 0 0;font-size:0.7rem;color:#d1fae5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $cfSourceLabel }}
                </p>
            </div>
        </div>
        <button type="button"
                onclick="closeCrossDrawer()"
                aria-label="Tutup panel salin data"
                style="flex-shrink:0;margin-left:0.75rem;padding:0.4rem;border-radius:0.5rem;
                       background:rgba(255,255,255,0.15);border:none;cursor:pointer;color:#ecfdf5;
                       transition:background 0.15s;">
            <svg style="width:1.125rem;height:1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Info + copy-all bar --}}
    <div style="flex-shrink:0;padding:0.625rem 1.25rem;border-bottom:1px solid #e5e7eb;background:#f0fdf4;
                display:flex;align-items:center;justify-content:space-between;gap:0.75rem;">
        <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.3rem 0.75rem;border-radius:9999px;
                     background:#dcfce7;color:#15803d;font-size:0.75rem;font-weight:700;">
            <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $cfCopyable }} pertanyaan sama
        </span>
        <button type="button"
                id="cross-copy-all-btn"
                style="flex-shrink:0;display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.875rem;
                       border-radius:0.5rem;background:#059669;color:#fff;font-size:0.75rem;font-weight:600;
                       border:none;cursor:pointer;transition:background 0.15s;">
            <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Salin Semua
        </button>
    </div>

    {{-- Scrollable content --}}
    <div style="flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:1.25rem;display:flex;flex-direction:column;gap:0.5rem;">
        <p style="margin:0 0 0.25rem;font-size:0.73rem;color:#6b7280;line-height:1.5;">
            Tekan <strong>Salin</strong> untuk menyalin satu jawaban ke formulir ini. Data tidak berubah otomatis —
            Anda yang menentukan jawaban mana yang ingin disalin, dan tetap dapat menyuntingnya setelah disalin.
        </p>
        @foreach($cfItems as $field)
        @php
            $fCopyable = $field['copyable'] ?? false;
            $fValue    = $field['value'] ?? '';
            $fDisplay  = $field['display'] ?? '—';
            $fTarget   = $field['target'] ?? '';
        @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;
                    padding:0.625rem 0.875rem;border-radius:0.5rem;
                    background:{{ $fCopyable ? '#fff' : '#f9fafb' }};border:1px solid #e5e7eb;">
            <div style="min-width:0;flex:1;">
                <p style="margin:0;font-size:0.7rem;color:#6b7280;">{{ $field['label'] ?? '' }}</p>
                <p style="margin:0.15rem 0 0;font-size:0.875rem;font-weight:500;color:{{ $fCopyable ? '#111827' : '#9ca3af' }};word-break:break-word;">{{ $fDisplay }}</p>
            </div>
            @if($fCopyable)
            <button type="button"
                    class="cross-copy-field-btn"
                    data-target="{{ $fTarget }}"
                    data-value="{{ $fValue }}"
                    data-also='@json($field['also'] ?? [])'
                    title="Salin ke formulir"
                    style="flex-shrink:0;display:inline-flex;align-items:center;gap:0.25rem;padding:0.3rem 0.625rem;
                           border-radius:0.375rem;background:#ecfdf5;color:#059669;font-size:0.75rem;font-weight:600;
                           border:1px solid #a7f3d0;cursor:pointer;transition:background 0.15s,color 0.15s;white-space:nowrap;">
                <svg style="width:0.7rem;height:0.7rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Salin
            </button>
            @else
            <span style="flex-shrink:0;font-size:0.7rem;color:#9ca3af;white-space:nowrap;">Tidak tersedia</span>
            @endif
        </div>
        @endforeach
    </div>
</aside>

<script>
(function () {
    'use strict';

    function openCrossDrawer() {
        var drawer   = document.getElementById('cross-drawer');
        var backdrop = document.getElementById('cross-backdrop');
        if (!drawer || !backdrop) return;
        backdrop.style.display = 'block';
        requestAnimationFrame(function () {
            backdrop.style.opacity = '1';
            drawer.style.transform = 'translateX(0)';
        });
        document.body.style.overflow = 'hidden';
    }

    function closeCrossDrawer() {
        var drawer   = document.getElementById('cross-drawer');
        var backdrop = document.getElementById('cross-backdrop');
        if (!drawer || !backdrop) return;
        drawer.style.transform = 'translateX(100%)';
        backdrop.style.opacity = '0';
        setTimeout(function () {
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }, 320);
        var btn = document.getElementById('cross-open-btn');
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
            var matched = false;
            document.querySelectorAll('[name="' + fieldName + '"]').forEach(function (r) {
                r.checked = (String(r.value) === String(value));
                if (r.checked) matched = true;
            });
            // Fire change on the now-checked control so dependent toggles / autosave run.
            var checked = document.querySelector('[name="' + fieldName + '"]:checked');
            if (checked) checked.dispatchEvent(new Event('change', { bubbles: true }));
            return matched;
        }
        return false;
    }

    function flashCopied(btn) {
        btn.innerHTML = '<svg style="width:0.7rem;height:0.7rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Disalin';
        btn.style.background = '#d1fae5';
        btn.style.color = '#065f46';
        btn.style.borderColor = '#6ee7b7';
        setTimeout(function () {
            btn.innerHTML = '<svg style="width:0.7rem;height:0.7rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin';
            btn.style.background = '#ecfdf5';
            btn.style.color = '#059669';
            btn.style.borderColor = '#a7f3d0';
        }, 1500);
    }

    document.addEventListener('DOMContentLoaded', function () {
        /* Individual copy buttons */
        document.querySelectorAll('.cross-copy-field-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (copyValueToField(btn.dataset.target, btn.dataset.value)) {
                    // Apply any companion fields (e.g. flip "Punya NIB? → Ya" so NIB shows).
                    var also = [];
                    try { also = JSON.parse(btn.dataset.also || '[]'); } catch (e) { also = []; }
                    also.forEach(function (c) {
                        if (c && c.target != null) copyValueToField(c.target, String(c.value));
                    });
                    flashCopied(btn);
                }
            });
        });

        /* Copy-all button */
        var copyAllBtn = document.getElementById('cross-copy-all-btn');
        if (copyAllBtn) {
            copyAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.cross-copy-field-btn').forEach(function (btn) { btn.click(); });
            });
        }

        /* Hover effect for the open button */
        var openBtn = document.getElementById('cross-open-btn');
        if (openBtn) {
            openBtn.addEventListener('mouseenter', function () {
                openBtn.style.boxShadow = '0 6px 20px rgba(5,150,105,0.55)';
                openBtn.style.transform = 'translateY(-1px)';
            });
            openBtn.addEventListener('mouseleave', function () {
                openBtn.style.boxShadow = '0 4px 14px rgba(5,150,105,0.45)';
                openBtn.style.transform = 'translateY(0)';
            });
        }
    });

    /* Escape closes drawer */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var drawer = document.getElementById('cross-drawer');
            if (drawer && drawer.style.transform !== 'translateX(100%)') closeCrossDrawer();
        }
    });

    window.openCrossDrawer  = openCrossDrawer;
    window.closeCrossDrawer = closeCrossDrawer;
}());
</script>
@endif
