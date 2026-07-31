{{--
  One row of the SIBSTR blok navigation.
  Expects: $blk (key,label,sub,done,active,unlocked,url), $idx, $size ('sm' desktop rail | 'lg' mobile sheet)
--}}
@php
    $_lg = ($size ?? 'sm') === 'lg';

    $_rowPad    = $_lg ? 'px-3 py-3 gap-3' : 'px-2.5 py-2 gap-2';
    $_bubbleDim = $_lg ? 'w-7 h-7 text-xs'  : 'w-5 h-5 text-[10px]';
    $_tickDim   = $_lg ? 'w-3.5 h-3.5'      : 'w-2.5 h-2.5';
    $_lockDim   = $_lg ? 'w-3.5 h-3.5'      : 'w-2.5 h-2.5';
    $_labelSize = $_lg ? 'text-sm'          : 'text-[11px]';
    $_subSize   = $_lg ? 'text-xs'          : 'text-[9px]';

    $_state = $blk['active'] ? 'active' : (!$blk['unlocked'] ? 'locked' : ($blk['done'] ? 'done' : 'todo'));

    $_rowTone = match ($_state) {
        'active' => 'bg-blue-600 text-white shadow-sm',
        'done'   => 'text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/30',
        'locked' => 'text-gray-400 dark:text-gray-600 cursor-not-allowed',
        default  => 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
    };

    $_bubbleTone = match ($_state) {
        'active' => $blk['done'] ? 'bg-white/30 text-white' : 'bg-white/20 text-white',
        'done'   => 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400',
        'locked' => 'bg-gray-50 dark:bg-gray-800 text-gray-300 dark:text-gray-600',
        default  => 'bg-gray-100 dark:bg-gray-700 text-gray-500',
    };

    $_tag        = $blk['unlocked'] && $blk['url'] ? 'a' : 'div';
    $_lockedHint = 'Selesaikan blok sebelumnya untuk membuka ' . $blk['label'];
@endphp

<{{ $_tag }}
  @if($_tag === 'a') href="{{ $blk['url'] }}" @else aria-disabled="true" title="{{ $_lockedHint }}" @endif
  data-sibstr-nav-key="{{ $blk['key'] }}"
  data-sibstr-nav-index="{{ $idx + 1 }}"
  data-sibstr-nav-active="{{ $blk['active'] ? '1' : '0' }}"
  class="sibstr-nav-item flex items-center w-full rounded-xl transition {{ $_rowPad }} {{ $_rowTone }}">

  <div class="sibstr-nav-bubble rounded-full flex items-center justify-center flex-shrink-0 font-bold {{ $_bubbleDim }} {{ $_bubbleTone }}">
    @if($blk['done'])
      <svg class="{{ $_tickDim }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
    @elseif($_state === 'locked')
      <svg class="{{ $_lockDim }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
    @else
      {{ $idx + 1 }}
    @endif
  </div>

  <div class="min-w-0 flex-1">
    <p class="{{ $_labelSize }} font-bold leading-tight">{{ $blk['label'] }}</p>
    <p class="{{ $_subSize }} truncate leading-tight mt-0.5 opacity-75">{{ $blk['sub'] }}</p>
  </div>
</{{ $_tag }}>
