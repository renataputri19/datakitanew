{{--
  Blok VI pre-flight checklist — the same pattern Survei Listrik uses on its
  final block. Lists every blok that applies to this response and whether it is
  complete, so the responden sees what is missing BEFORE pressing "Selesaikan
  Survei" instead of only after the server bounces them back.

  Expects: $surveyResponse, $tahun, $triwulan, $period (+ optional $isEditMode)
--}}
@php
    $_csR      = $surveyResponse ?? null;
    $_csTw     = (int) ($triwulan ?? 0);
    $_csParams = [
        'year'   => $tahun ?? ($_csR->tahun ?? 2025),
        'period' => $period ?? ($_csTw === 0 ? 'tahunan' : (string) $_csTw),
    ];

    // Blok VI itself is the page doing the finishing — it is not a prerequisite.
    $_csRows = array_values(array_filter(
        \App\Support\SibstrBlokPath::rows($_csR, $_csTw, !empty($isEditMode), $_csParams),
        fn ($row) => $row['key'] !== 'blok6'
    ));

    $_csMissing = array_values(array_filter($_csRows, fn ($row) => !$row['done']));
@endphp

<div class="form-section">
    <div class="section-header">
        <h3 class="section-title">Ringkasan Kelengkapan</h3>
        <p class="section-subtitle">
            Periksa daftar di bawah sebelum menyelesaikan survei
        </p>
    </div>

    <div class="sibstr-check-list">
        @foreach($_csRows as $_csRow)
        <div class="sibstr-check {{ $_csRow['done'] ? 'ok' : 'bad' }}">
            <span class="ic">
                @if($_csRow['done'])
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                @endif
            </span>
            <p>
                {{ $_csRow['label'] }} — {{ $_csRow['sub'] }}
                <small>{{ $_csRow['done'] ? 'Lengkap' : 'Belum lengkap' }}</small>
            </p>
            @if(!$_csRow['done'] && $_csRow['url'])
            <a href="{{ $_csRow['url'] }}" class="fix">Lengkapi &rarr;</a>
            @endif
        </div>
        @endforeach
    </div>

    @if(count($_csMissing) > 0)
    <div class="sibstr-check-note bad">
        <strong>{{ count($_csMissing) }} blok belum lengkap.</strong>
        Saat tombol penyelesaian diklik, sistem memeriksa ulang seluruh blok dan
        akan mengarahkan Anda kembali ke blok pertama yang belum lengkap.
    </div>
    @else
    <div class="sibstr-check-note ok">
        Seluruh blok sudah lengkap. Isi catatan bila perlu, lalu selesaikan survei.
    </div>
    @endif
</div>
