{{--
  Back / continue buttons at the foot of a blok form.
  Expects: $prevUrl (nullable), $nextLabel.
--}}
<div class="k-actions">
  @if(!empty($prevUrl))
    <a href="{{ $prevUrl }}" class="k-btn k-btn-ghost">&larr; Blok sebelumnya</a>
  @else
    <a href="{{ route('survey.konstruksi.entry') }}" class="k-btn k-btn-ghost">&larr; Kembali</a>
  @endif
  <button type="submit" class="k-btn k-btn-primary">{{ $nextLabel }} &rarr;</button>
</div>
