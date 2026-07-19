@php
$routeName = request()->route()?->getName() ?? '';
preg_match('/listrik\.(blok\w+)/', $routeName, $m);
$currentKey = $m[1] ?? '';

$navBlocks = [
    ['key' => 'blok1', 'route' => 'survey.listrik.blok1', 'label' => 'Blok I',   'sub' => 'Identitas & Lokasi',        'done' => $response?->blok1_completed],
    ['key' => 'blok2', 'route' => 'survey.listrik.blok2', 'label' => 'Blok II',  'sub' => 'Produksi Listrik Bulanan',  'done' => $response?->blok2_completed],
    ['key' => 'blok3', 'route' => 'survey.listrik.blok3', 'label' => 'Blok III', 'sub' => 'Catatan & Selesai',         'done' => $response?->blok3_completed],
];

foreach ($navBlocks as $i => &$blk) {
    $blk['active'] = $blk['key'] === $currentKey;
}
unset($blk);

$_activeBlock    = collect($navBlocks)->firstWhere('key', $currentKey);
$_currentLabel   = $_activeBlock ? $_activeBlock['label'] : 'Blok';
$_completedCount = count(array_filter($navBlocks, fn($b) => $b['done']));
$_totalCount     = count($navBlocks);
@endphp

<aside class="hidden lg:flex flex-col w-48 flex-shrink-0 self-start sticky top-4">
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-3 py-2.5 bg-amber-50 dark:bg-amber-950/30 border-b border-amber-100 dark:border-amber-800">
      <p class="text-[10px] font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Navigasi Blok</p>
      <p class="text-[9px] text-amber-600 dark:text-amber-400 mt-0.5">Survei Listrik</p>
    </div>
    <nav class="p-1.5 space-y-0.5">
      @foreach($navBlocks as $idx => $blk)
      <a href="{{ route($blk['route']) }}"
         data-listrik-nav-key="{{ $blk['key'] }}"
         data-listrik-nav-index="{{ $idx + 1 }}"
         data-listrik-nav-active="{{ $blk['active'] ? '1' : '0' }}"
         class="listrik-nav-item flex items-center gap-2 w-full px-2.5 py-2 rounded-xl transition
                {{ $blk['active']
                   ? 'bg-blue-600 text-white shadow-sm'
                   : ($blk['done']
                      ? 'text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/30'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700') }}">
        <div class="listrik-nav-bubble w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold
                    {{ $blk['done'] ? ($blk['active'] ? 'bg-white/30 text-white' : 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400') : ($blk['active'] ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500') }}">
          @if($blk['done'])
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
          @else
            {{ $idx + 1 }}
          @endif
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-[11px] font-bold leading-tight">{{ $blk['label'] }}</p>
          <p class="text-[9px] truncate leading-tight mt-0.5 opacity-75">{{ $blk['sub'] }}</p>
        </div>
      </a>
      @endforeach
    </nav>
    <div class="border-t border-gray-100 dark:border-gray-700 px-3 py-2">
      <a href="{{ route('survey.listrik.entry') }}"
         class="flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Dashboard Survei
      </a>
    </div>
  </div>
</aside>

{{-- ── Mobile FAB & bottom-sheet navigation (lg:hidden) ── --}}
<div class="lg:hidden">
  <button id="listrik-mob-fab" type="button" aria-label="Buka navigasi blok"
    class="fixed z-40 bottom-5 right-4 flex items-center gap-2.5 pl-3 pr-4 h-12 rounded-full bg-blue-600 text-white shadow-xl hover:bg-blue-700 active:scale-95 transition-transform">
    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <div class="text-left leading-tight">
      <p class="text-[9px] font-semibold uppercase tracking-wider opacity-80">Navigasi Blok</p>
      <p class="text-xs font-bold">{{ $_currentLabel }}</p>
    </div>
    <span class="ml-0.5 flex-shrink-0 text-[10px] font-bold bg-white/25 rounded-full px-1.5 py-0.5 leading-none">
      {{ $_completedCount }}/{{ $_totalCount }}
    </span>
  </button>

  <div id="listrik-mob-sheet"
    class="fixed bottom-20 right-4 z-50 w-72 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out origin-bottom-right"
    role="dialog" aria-modal="true" aria-label="Navigasi Blok">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
      <div>
        <p class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Navigasi Blok</p>
        <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">Survei Listrik</p>
      </div>
      <button id="listrik-mob-close" type="button" aria-label="Tutup"
        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <nav class="px-3 py-2 space-y-0.5 overflow-y-auto" style="max-height:55vh;">
      @foreach($navBlocks as $idx => $blk)
      <a href="{{ route($blk['route']) }}"
         class="flex items-center gap-3 w-full px-3 py-3 rounded-xl transition
                {{ $blk['active']
                   ? 'bg-blue-600 text-white shadow-sm'
                   : ($blk['done']
                      ? 'text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/30'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700') }}">
        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                    {{ $blk['done'] ? ($blk['active'] ? 'bg-white/30 text-white' : 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400') : ($blk['active'] ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500') }}">
          @if($blk['done'])
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
          @else
            {{ $idx + 1 }}
          @endif
        </div>
        <div>
          <p class="text-sm font-bold leading-tight">{{ $blk['label'] }}</p>
          <p class="text-xs leading-tight mt-0.5 opacity-75">{{ $blk['sub'] }}</p>
        </div>
      </a>
      @endforeach
    </nav>
    <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3">
      <a href="{{ route('survey.listrik.entry') }}"
         class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Dashboard Survei
      </a>
    </div>
  </div>
</div>

<script>
(function () {
  const CHECK_SVG = '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
  const currentKey = @json($currentKey);

  const INDEX_MAP = {};
  document.querySelectorAll('[data-listrik-nav-key]').forEach(function (el) {
    INDEX_MAP[el.getAttribute('data-listrik-nav-key')] = el.getAttribute('data-listrik-nav-index');
  });

  // survey.js and the blok2 grid both dispatch 'ub:autosave' with blok_completed
  document.addEventListener('ub:autosave', function (e) {
    const completed = e.detail.blok_completed;
    const link = document.querySelector('[data-listrik-nav-key="' + currentKey + '"]');
    if (!link) return;

    const isActive = link.getAttribute('data-listrik-nav-active') === '1';
    const bubble   = link.querySelector('.listrik-nav-bubble');

    if (completed) {
      if (!isActive) {
        link.className = link.className
          .replace(/\btext-gray-\S+/g, '').replace(/\bhover:bg-gray-\S+/g, '')
          .replace(/\bdark:text-gray-\S+/g, '').replace(/\bdark:hover:bg-gray-\S+/g, '');
        link.classList.add('text-green-700', 'dark:text-green-300', 'hover:bg-green-50', 'dark:hover:bg-green-950/30');
      }
      if (bubble) {
        bubble.className = bubble.className
          .replace(/\bbg-gray-\S+/g, '').replace(/\bdark:bg-gray-\S+/g, '')
          .replace(/\btext-gray-\S+/g, '');
        bubble.classList.add(isActive ? 'bg-white/30' : 'bg-green-100',
                             isActive ? 'text-white'   : 'text-green-600',
                             'dark:bg-green-900/50', 'dark:text-green-400');
        bubble.innerHTML = CHECK_SVG;
      }
    } else {
      if (!isActive) {
        link.className = link.className
          .replace(/\btext-green-\S+/g, '').replace(/\bhover:bg-green-\S+/g, '')
          .replace(/\bdark:text-green-\S+/g, '').replace(/\bdark:hover:bg-green-\S+/g, '');
        link.classList.add('text-gray-700', 'dark:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
      }
      if (bubble) {
        bubble.className = bubble.className
          .replace(/\bbg-green-\S+/g, '').replace(/\bdark:bg-green-\S+/g, '')
          .replace(/\btext-green-\S+/g, '').replace(/\bdark:text-green-\S+/g, '')
          .replace(/\bbg-white\/\S+/g, '');
        bubble.classList.add(isActive ? 'bg-white/20' : 'bg-gray-100',
                             isActive ? 'text-white'   : 'text-gray-500',
                             'dark:bg-gray-700');
        bubble.innerHTML = INDEX_MAP[currentKey] || '';
      }
    }
  });
})();

(function () {
  const fab      = document.getElementById('listrik-mob-fab');
  const sheet    = document.getElementById('listrik-mob-sheet');
  const closeBtn = document.getElementById('listrik-mob-close');
  if (!fab || !sheet) return;
  var _open = false;

  function openSheet() {
    _open = true;
    sheet.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
    sheet.classList.add('opacity-100', 'scale-100');
  }
  function closeSheet() {
    _open = false;
    sheet.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
    sheet.classList.remove('opacity-100', 'scale-100');
  }

  fab.addEventListener('click', function (e) { e.stopPropagation(); _open ? closeSheet() : openSheet(); });
  if (closeBtn) closeBtn.addEventListener('click', closeSheet);
  document.querySelectorAll('[data-open-sidebar]').forEach(function (btn) {
    btn.addEventListener('click', function (e) { e.stopPropagation(); openSheet(); });
  });
  sheet.addEventListener('click', function (e) { e.stopPropagation(); });
  document.addEventListener('click', function () { if (_open) closeSheet(); });
})();
</script>
