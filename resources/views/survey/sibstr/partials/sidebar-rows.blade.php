{{--
  The nav rows themselves, split out so the same markup serves both the initial
  server render and the live refresh (SurveyController::sibstrNav re-renders
  this partial and the sidebar swaps it in).

  Expects: $rows (from App\Support\SibstrBlokPath::rows), $size ('sm' | 'lg')
--}}
@foreach($rows as $idx => $blk)
    @include('survey.sibstr.partials.sidebar-item', ['blk' => $blk, 'idx' => $idx, 'size' => $size])
@endforeach
