{{--
  Shared pagination bar for the BPS lists:
      {{ $rows->links('bps.partials.pagination') }}
      {{ $rows->links('bps.partials.pagination', ['label' => 'pengguna']) }}

  The page window always has 7 slots (first, last, current ±1 and the gaps),
  so the buttons never shift under the cursor while paging through:
      1 2 3 4 5 … 34   ·   1 … 9 10 11 … 34   ·   1 … 30 31 32 33 34
  Past 7 pages a "Ke halaman" box jumps straight to any page. Under 640px the
  numbers collapse to "6 / 34" between the arrows.

  Styles are scoped and inline so no Vite rebuild is needed.
--}}
@if ($paginator->total() > 0)
@php
    $label   = $label ?? 'data';
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();
    $fmt     = fn (int $n) => number_format($n, 0, ',', '.');

    if ($last <= 7) {
        $slots = range(1, $last);
    } elseif ($current <= 4) {
        $slots = [1, 2, 3, 4, 5, null, $last];
    } elseif ($current >= $last - 3) {
        $slots = [1, null, $last - 4, $last - 3, $last - 2, $last - 1, $last];
    } else {
        $slots = [1, null, $current - 1, $current, $current + 1, null, $last];
    }

    // The jump box is a plain GET form, so it must resend every filter the
    // page links carry. Read them off the paginator's own URL: it is already
    // flattened (a%5B0%5D=x), so each pair maps onto one hidden input.
    $keep = [];
    foreach (explode('&', (string) parse_url($paginator->url(1), PHP_URL_QUERY)) as $pair) {
        [$name, $value] = array_map('urldecode', explode('=', $pair, 2) + [1 => '']);
        if ($name !== '' && $name !== $paginator->getPageName()) {
            $keep[] = [$name, $value];
        }
    }
@endphp

@once
@push('styles')
<style>
.bps-pg { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem 1.25rem; width:100%; }
.bps-pg-summary { font-size:.875rem; color:#6b7280; }
.bps-pg-summary b { font-weight:600; color:#111827; font-variant-numeric:tabular-nums; }
.bps-pg-controls { display:flex; align-items:center; flex-wrap:wrap; gap:.75rem 1rem; }
.bps-pg-nav { display:flex; align-items:center; gap:.25rem; }
.bps-pg-btn {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:2.25rem; height:2.25rem; padding:0 .5rem;
    border:1px solid #e5e7eb; border-radius:.5rem; background:#fff;
    font-size:.875rem; font-weight:500; color:#374151; text-decoration:none;
    font-variant-numeric:tabular-nums; line-height:1;
    transition:background-color .15s, border-color .15s, color .15s;
}
a.bps-pg-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }
a.bps-pg-btn:focus-visible, .bps-pg-jump input:focus-visible, .bps-pg-jump button:focus-visible {
    outline:2px solid #3b82f6; outline-offset:2px;
}
.bps-pg-btn[aria-current="page"] { background:#2563eb; border-color:#2563eb; color:#fff; font-weight:600; box-shadow:0 1px 2px rgba(37,99,235,.35); }
.bps-pg-btn[aria-disabled="true"] { color:#d1d5db; background:#f9fafb; cursor:not-allowed; }
.bps-pg-btn svg { width:1rem; height:1rem; }
.bps-pg-gap { display:inline-flex; align-items:flex-end; justify-content:center; width:1.5rem; height:2.25rem; padding-bottom:.6rem; color:#9ca3af; letter-spacing:.1em; user-select:none; }
.bps-pg-compact { display:none; padding:0 .75rem; font-size:.875rem; color:#4b5563; font-variant-numeric:tabular-nums; white-space:nowrap; }
.bps-pg-compact b { color:#111827; font-weight:600; }
.bps-pg-jump { display:flex; align-items:center; gap:.375rem; margin:0; font-size:.875rem; color:#6b7280; }
.bps-pg-jump label { display:flex; align-items:center; gap:.375rem; white-space:nowrap; }
.bps-pg-jump input {
    width:4rem; height:2.25rem; padding:0 .5rem; text-align:center;
    border:1px solid #d1d5db; border-radius:.5rem; background:#fff; color:#111827;
    font-size:.875rem; font-variant-numeric:tabular-nums; -moz-appearance:textfield;
}
.bps-pg-jump input::-webkit-outer-spin-button, .bps-pg-jump input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
.bps-pg-jump button {
    height:2.25rem; padding:0 .875rem; border:none; border-radius:.5rem;
    background:#111827; color:#fff; font-size:.8125rem; font-weight:600; cursor:pointer;
    transition:background-color .15s;
}
.bps-pg-jump button:hover { background:#374151; }

.dark .bps-pg-summary { color:#9ca3af; }
.dark .bps-pg-summary b, .dark .bps-pg-compact b { color:#f3f4f6; }
.dark .bps-pg-btn { background:#1f2937; border-color:#374151; color:#d1d5db; }
.dark a.bps-pg-btn:hover { background:rgba(37,99,235,.18); border-color:#3b82f6; color:#93c5fd; }
.dark .bps-pg-btn[aria-current="page"] { background:#2563eb; border-color:#2563eb; color:#fff; box-shadow:none; }
.dark .bps-pg-btn[aria-disabled="true"] { background:#111827; border-color:#1f2937; color:#4b5563; }
.dark .bps-pg-gap { color:#6b7280; }
.dark .bps-pg-compact { color:#9ca3af; }
.dark .bps-pg-jump { color:#9ca3af; }
.dark .bps-pg-jump input { background:#1f2937; border-color:#4b5563; color:#f3f4f6; }
.dark .bps-pg-jump button { background:#2563eb; }
.dark .bps-pg-jump button:hover { background:#1d4ed8; }

@media (max-width: 639px) {
    .bps-pg { justify-content:center; text-align:center; }
    .bps-pg-controls { justify-content:center; width:100%; }
    .bps-pg-num, .bps-pg-gap { display:none; }
    .bps-pg-compact { display:inline; }
}
</style>
@endpush
@endonce

<div class="bps-pg">
    <p class="bps-pg-summary">
        @if ($paginator->count() > 0)
            Menampilkan <b>{{ $fmt($paginator->firstItem()) }}–{{ $fmt($paginator->lastItem()) }}</b>
            dari <b>{{ $fmt($paginator->total()) }}</b> {{ $label }}
        @else
            {{-- A hand-typed ?page= past the end: no rows, but the links still lead back. --}}
            Halaman ini kosong · total <b>{{ $fmt($paginator->total()) }}</b> {{ $label }}
        @endif
    </p>

    @if ($paginator->hasPages())
    <div class="bps-pg-controls">
        <nav class="bps-pg-nav" role="navigation" aria-label="Navigasi halaman">
            @if ($paginator->onFirstPage())
                <span class="bps-pg-btn" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                </span>
            @else
                <a class="bps-pg-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                </a>
            @endif

            @foreach ($slots as $page)
                @if ($page === null)
                    <span class="bps-pg-gap" aria-hidden="true">…</span>
                @elseif ($page === $current)
                    <span class="bps-pg-btn bps-pg-num" aria-current="page">{{ $fmt($page) }}</span>
                @else
                    <a class="bps-pg-btn bps-pg-num" href="{{ $paginator->url($page) }}" aria-label="Halaman {{ $page }}">{{ $fmt($page) }}</a>
                @endif
            @endforeach

            <span class="bps-pg-compact">Hal. <b>{{ $fmt($current) }}</b> / {{ $fmt($last) }}</span>

            @if ($paginator->hasMorePages())
                <a class="bps-pg-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" title="Halaman berikutnya">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </a>
            @else
                <span class="bps-pg-btn" aria-disabled="true" aria-label="Halaman berikutnya">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </span>
            @endif
        </nav>

        @if ($last > 7)
        <form class="bps-pg-jump" method="GET" action="{{ $paginator->path() }}">
            @foreach ($keep as [$name, $value])
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <label>
                Ke halaman
                <input type="number" name="{{ $paginator->getPageName() }}" min="1" max="{{ $last }}"
                       inputmode="numeric" placeholder="{{ $current }}" required>
            </label>
            <button type="submit">Buka</button>
        </form>
        @endif
    </div>
    @endif
</div>
@endif
