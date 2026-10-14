@php
  $doneCount   = count(array_filter($navBlocks, fn ($b) => $b['done']));
  $totalCount  = count($navBlocks);
  $activeBlock = collect($navBlocks)->firstWhere('active', true);
@endphp

{{--
  The two data-k-nav-* attributes are the whole wiring for the live rail:
  konstruksi.js reads them on load and re-fetches these rows after an autosave
  that could have changed them (see navWatcher there). Keeping it declarative
  means every blok page gets it without adding a line to its own script block.
--}}
<aside class="k-rail" id="k-rail"
       data-k-nav-url="{{ route('survey.konstruksi.nav', $routeParams) }}"
       data-k-nav-current="{{ $activeBlock['key'] ?? '' }}">
  <div class="k-rail-head">
    <p>Navigasi Blok</p>
    <p>Survei Konstruksi · {{ $periodeLabel ?? '' }}</p>
  </div>
  <nav class="k-rail-nav" id="k-rail-nav">
    @include('survey.konstruksi.partials.nav-rows')
  </nav>
  <div class="k-rail-foot">
    <a href="{{ route('survey.konstruksi.entry') }}">&larr; Dashboard Survei</a>
  </div>
</aside>

{{-- Mobile blok picker --}}
<div class="k-mob">
  {{-- The class is what styles this as a floating button; the id is only the
       JS handle. Without the class it renders inline and pushes the form
       sideways on phones. --}}
  <button type="button" class="k-fab" id="k-fab" aria-label="Buka navigasi blok">
    <b data-k-fab-label>{{ $activeBlock['label'] ?? 'Blok' }}</b>
    <span data-k-fab-count>{{ $doneCount }}/{{ $totalCount }}</span>
  </button>
  <div class="k-sheet" id="k-sheet" hidden role="dialog" aria-label="Navigasi Blok">
    <div class="k-rail-head">
      <p>Navigasi Blok</p>
      <p>Survei Konstruksi · {{ $periodeLabel ?? '' }}</p>
    </div>
    <nav class="k-rail-nav">
      @include('survey.konstruksi.partials.nav-rows')
    </nav>
    <div class="k-rail-foot">
      <a href="{{ route('survey.konstruksi.entry') }}">&larr; Dashboard Survei</a>
    </div>
  </div>
</div>
