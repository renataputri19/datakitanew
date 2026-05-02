@php
$routeName = request()->route()?->getName() ?? '';
preg_match('/ub\.(blok\w+)/', $routeName, $m);
$currentKey = $m[1] ?? '';

$isUnitPembantu = (int)($response?->jaringan_usaha) === 6;

// Always render all items — JS controls visibility for jaringan_usaha=6 in real-time.
$navBlocks = [
    ['key' => 'blok1a', 'route' => 'survey.ub.blok1a', 'label' => 'Blok I-A', 'sub' => 'Identitas & Lokasi',       'done' => $response?->blok1a_completed],
    ['key' => 'blok1b', 'route' => 'survey.ub.blok1b', 'label' => 'Blok I-B', 'sub' => 'Kegiatan & Digital',       'done' => $response?->blok1b_completed],
    ['key' => 'blok1c', 'route' => 'survey.ub.blok1c', 'label' => 'Blok I-C', 'sub' => 'Sertifikasi & Kemitraan', 'done' => $response?->blok1c_completed],
    ['key' => 'blok1d', 'route' => 'survey.ub.blok1d', 'label' => 'Blok I-D', 'sub' => 'Pekerja & Keuangan',      'done' => $response?->blok1d_completed],
    ['key' => 'blok2',  'route' => 'survey.ub.blok2',  'label' => 'Blok II',  'sub' => 'Catatan',                  'done' => $response?->blok2_completed],
    ['key' => 'blok3',  'route' => 'survey.ub.blok3',  'label' => 'Blok III', 'sub' => 'Keterangan Petugas',       'done' => $response?->blok3_completed],
];

foreach ($navBlocks as $i => &$blk) {
    $blk['active']   = $blk['key'] === $currentKey;
    $blk['canVisit'] = true;
}
unset($blk);
@endphp

<aside class="hidden lg:flex flex-col w-48 flex-shrink-0 self-start sticky top-4">
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-3 py-2.5 bg-blue-50 dark:bg-blue-950/30 border-b border-blue-100 dark:border-blue-800">
      <p class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Navigasi Blok</p>
      <p class="text-[9px] text-blue-500 dark:text-blue-400 mt-0.5">SE2026-L.UB</p>
    </div>
    <nav class="p-1.5 space-y-0.5">
      @foreach($navBlocks as $idx => $blk)
      <a href="{{ route($blk['route']) }}"
         data-ub-nav-key="{{ $blk['key'] }}"
         data-ub-nav-index="{{ $idx + 1 }}"
         data-ub-nav-active="{{ $blk['active'] ? '1' : '0' }}"
         class="ub-nav-item flex items-center gap-2 w-full px-2.5 py-2 rounded-xl transition
                {{ $blk['active']
                   ? 'bg-blue-600 text-white shadow-sm'
                   : ($blk['done']
                      ? 'text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/30'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700') }}">
        <div class="ub-nav-bubble w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold
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
      <a href="{{ route('survey.ub.entry') }}"
         class="flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition font-medium">
        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Dashboard Survei
      </a>
    </div>
  </div>
</aside>

<script>
(function () {
  const CHECK_SVG = '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
  const currentKey = @json($currentKey);

  const INDEX_MAP = {};
  document.querySelectorAll('[data-ub-nav-key]').forEach(function (el) {
    INDEX_MAP[el.getAttribute('data-ub-nav-key')] = el.getAttribute('data-ub-nav-index');
  });

  // Keys hidden for unit pembantu/penunjang (jaringan_usaha = 6)
  const PEMBANTU_HIDDEN = ['blok1c', 'blok1d', 'blok2'];

  function setNavVisible(key, visible) {
    const el = document.querySelector('[data-ub-nav-key="' + key + '"]');
    if (!el) return;
    // Re-number visible items after show/hide
    el.style.display = visible ? '' : 'none';
  }

  function applyJaringanVisibility(jaringanValue) {
    const isUnitPembantu = String(jaringanValue) === '6';
    PEMBANTU_HIDDEN.forEach(function (key) {
      setNavVisible(key, !isUnitPembantu);
    });
  }

  // Apply on page load based on server-rendered state
  applyJaringanVisibility(@json((int)($response?->jaringan_usaha ?? 0)));

  document.addEventListener('ub:autosave', function (e) {
    // Update jaringan_usaha-driven visibility in real time
    if (e.detail.field === 'jaringan_usaha') {
      applyJaringanVisibility(e.detail.value);
    }

    const completed = e.detail.blok_completed;
    const link = document.querySelector('[data-ub-nav-key="' + currentKey + '"]');
    if (!link) return;

    const isActive = link.getAttribute('data-ub-nav-active') === '1';
    const bubble   = link.querySelector('.ub-nav-bubble');

    if (completed) {
      // Mark green + checkmark
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
      // Revert to gray + number
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
</script>
