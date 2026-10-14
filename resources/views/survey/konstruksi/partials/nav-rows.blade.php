{{--
  The blok rows themselves. Rendered inside both the desktop rail and the
  mobile sheet, and returned on its own by survey.konstruksi.nav so the rail
  can refresh after an autosave without a page reload.

  $navBlocks comes from KonstruksiBlokPath::rows().
--}}
@foreach($navBlocks as $blk)
  @php
    // Every block is reachable, as in Survei UB — answers autosave, so moving
    // away mid-block loses nothing. Completeness is enforced at submit.
    $classes = 'k-rail-item'
      . ($blk['active'] ? ' is-active' : '')
      . ($blk['done'] ? ' is-done' : '');
  @endphp
  <a href="{{ $blk['url'] ?? '#' }}"
     class="{{ $classes }}"
     data-k-nav="{{ $blk['key'] }}"
     @if($blk['active']) aria-current="step" @endif
     title="{{ $blk['label'] }} — {{ $blk['done'] ? 'sudah lengkap' : 'belum lengkap' }}">
    <span class="k-rail-bubble">
      @if($blk['done'])
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
             stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
      @else
        {{ $blk['badge'] }}
      @endif
    </span>
    <span class="k-rail-label">
      <b>{{ $blk['label'] }}</b>
      <span>{{ $blk['sub'] }}</span>
    </span>
  </a>
@endforeach
