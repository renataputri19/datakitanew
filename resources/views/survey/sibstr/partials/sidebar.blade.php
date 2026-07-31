{{--
  SIBSTR blok navigation rail + mobile sheet.

  Presentation only — the ordered path, completion flags and lock state all come
  from App\Support\SibstrBlokPath, which mirrors the server-side guard, so a link
  is never offered that the guard would bounce back.

  Expects: $surveyResponse, $tahun, $triwulan, $period (+ optional $isEditMode)
--}}
@php
    $_r       = $surveyResponse ?? null;
    $_tw      = (int) ($triwulan ?? 0);
    $_tahunan = $_tw === 0;
    $_params  = [
        'year'   => $tahun ?? ($_r->tahun ?? 2025),
        'period' => $period ?? ($_tahunan ? 'tahunan' : (string) $_tw),
    ];

    $_currentKey = \App\Support\SibstrBlokPath::keyFromRouteName(request()->route()?->getName());
    $_navBlocks  = \App\Support\SibstrBlokPath::rows($_r, $_tw, !empty($isEditMode), $_params, $_currentKey);

    $_b2Done         = \App\Support\SibstrBlokPath::isComplete($_r, 'blok2');
    $_activeBlock    = collect($_navBlocks)->firstWhere('key', $_currentKey);
    $_currentLabel   = $_activeBlock['label'] ?? 'Blok';
    $_completedCount = count(array_filter($_navBlocks, fn ($b) => $b['done']));
    $_totalCount     = count($_navBlocks);
    $_periodLabel    = $_tahunan
        ? 'Tahunan ' . $_params['year']
        : \App\Models\SurveyResponse::triwulanLabel($_tw) . ' ' . $_params['year'];
    $_exitUrl        = route('survey.sibstr.entry');

    // Endpoint the rail re-fetches itself from after each autosave.
    $_navUrl = route('survey.sibstr.nav', $_params + [
        'current' => $_currentKey,
        'edit'    => !empty($isEditMode) ? 1 : null,
    ]);
@endphp

{{-- ── Desktop rail ───────────────────────────────────────────────────────── --}}
<aside class="hidden lg:flex flex-col w-52 flex-shrink-0 self-start sticky top-4">
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-3 py-2.5 bg-blue-50 dark:bg-blue-950/30 border-b border-blue-100 dark:border-blue-800">
      <p class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Navigasi Blok</p>
      <p class="text-[9px] text-blue-600 dark:text-blue-400 mt-0.5">SIBSTR · {{ $_periodLabel }}</p>
    </div>

    <nav class="p-1.5 space-y-0.5" id="sibstr-nav-rail">
      @include('survey.sibstr.partials.sidebar-rows', ['rows' => $_navBlocks, 'size' => 'sm'])
    </nav>

    <div class="px-3 pb-2.5 -mt-0.5" id="sibstr-nav-hint" @class(['hidden' => $_b2Done])>
      <p class="text-[9px] leading-snug text-gray-400 dark:text-gray-500">
        Blok berikutnya terbuka setelah <strong>Blok II</strong> tersimpan lengkap.
      </p>
    </div>

    <div class="border-t border-gray-100 dark:border-gray-700 px-3 py-2">
      <a href="{{ $_exitUrl }}"
         class="flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Dashboard Survei
      </a>
    </div>
  </div>
</aside>

{{-- ── Mobile FAB + bottom sheet ──────────────────────────────────────────── --}}
<div class="lg:hidden">
  <button id="sibstr-mob-fab" type="button" aria-label="Buka navigasi blok"
    class="fixed z-40 bottom-5 right-4 flex items-center gap-2.5 pl-3 pr-4 h-12 rounded-full bg-blue-600 text-white shadow-xl hover:bg-blue-700 active:scale-95 transition-transform">
    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
      </svg>
    </div>
    <div class="text-left leading-tight">
      <p class="text-[9px] font-semibold uppercase tracking-wider opacity-80">Navigasi Blok</p>
      <p class="text-xs font-bold">{{ $_currentLabel }}</p>
    </div>
    <span id="sibstr-nav-count"
          class="ml-0.5 flex-shrink-0 text-[10px] font-bold bg-white/25 rounded-full px-1.5 py-0.5 leading-none">
      {{ $_completedCount }}/{{ $_totalCount }}
    </span>
  </button>

  <div id="sibstr-mob-sheet"
    class="fixed bottom-20 right-4 z-50 w-72 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out origin-bottom-right"
    role="dialog" aria-modal="true" aria-label="Navigasi Blok">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
      <div>
        <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Navigasi Blok</p>
        <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-0.5">SIBSTR · {{ $_periodLabel }}</p>
      </div>
      <button id="sibstr-mob-close" type="button" aria-label="Tutup"
        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <nav class="px-3 py-2 space-y-0.5 overflow-y-auto" style="max-height:55vh;">
      <div id="sibstr-nav-sheet" class="space-y-0.5">
        @include('survey.sibstr.partials.sidebar-rows', ['rows' => $_navBlocks, 'size' => 'lg'])
      </div>
      <p id="sibstr-nav-hint-mob"
         class="px-3 pt-1 pb-2 text-[10px] leading-snug text-gray-400 dark:text-gray-500 {{ $_b2Done ? 'hidden' : '' }}">
        Blok berikutnya terbuka setelah <strong>Blok II</strong> tersimpan lengkap.
      </p>
    </nav>
    <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3">
      <a href="{{ $_exitUrl }}"
         class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Dashboard Survei
      </a>
    </div>
  </div>
</div>

<script>
/* ── Live blok navigation ───────────────────────────────────────────────────
 * survey.js fires 'survey:autosaved' after every successful field autosave.
 * We re-fetch the rail from the server rather than recomputing it here, because
 * a Blok II answer can change the *shape* of the path (tutup / unit penunjang
 * collapse it to I·II·VI; KBLI 10–33 switches to the Industri chain) and that
 * resolution lives in App\Support\SibstrBlokPath — duplicating it in JS is how
 * the two would drift apart.
 * ------------------------------------------------------------------------- */
(function () {
  const NAV_URL = @json($_navUrl);

  const rail     = document.getElementById('sibstr-nav-rail');
  const sheet    = document.getElementById('sibstr-nav-sheet');
  const hint     = document.getElementById('sibstr-nav-hint');
  const hintMob  = document.getElementById('sibstr-nav-hint-mob');
  const countEl  = document.getElementById('sibstr-nav-count');
  if (!rail && !sheet) return;

  let timer   = null;
  let inFlight = false;
  let queued  = false;

  async function refresh() {
    if (inFlight) { queued = true; return; }
    inFlight = true;

    try {
      const res = await fetch(NAV_URL, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      if (!res.ok) return;

      const data = await res.json();
      if (!data || data.success !== true) return;

      if (rail  && typeof data.rail  === 'string') rail.innerHTML  = data.rail;
      if (sheet && typeof data.sheet === 'string') sheet.innerHTML = data.sheet;
      if (countEl) countEl.textContent = data.completed + '/' + data.total;

      [hint, hintMob].forEach(function (el) {
        if (el) el.classList.toggle('hidden', !!data.blok2_complete);
      });
    } catch (_e) {
      /* Offline or a transient error — the rail simply keeps its last state. */
    } finally {
      inFlight = false;
      if (queued) { queued = false; schedule(); }
    }
  }

  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(refresh, 500);
  }

  document.addEventListener('survey:autosaved', schedule);
})();

(function () {
  const fab      = document.getElementById('sibstr-mob-fab');
  const sheet    = document.getElementById('sibstr-mob-sheet');
  const closeBtn = document.getElementById('sibstr-mob-close');
  if (!fab || !sheet) return;
  var open = false;

  function openSheet() {
    open = true;
    sheet.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
    sheet.classList.add('opacity-100', 'scale-100');
  }
  function closeSheet() {
    open = false;
    sheet.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
    sheet.classList.remove('opacity-100', 'scale-100');
  }

  fab.addEventListener('click', function (e) { e.stopPropagation(); open ? closeSheet() : openSheet(); });
  if (closeBtn) closeBtn.addEventListener('click', closeSheet);
  document.querySelectorAll('[data-open-blok-nav]').forEach(function (btn) {
    btn.addEventListener('click', function (e) { e.stopPropagation(); openSheet(); });
  });
  sheet.addEventListener('click', function (e) { e.stopPropagation(); });
  document.addEventListener('click', function () { if (open) closeSheet(); });
})();
</script>
