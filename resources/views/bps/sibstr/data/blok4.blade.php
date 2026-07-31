{{--
  Blok IV — Fenomena & Catatan per triwulan, read-only.
  Expects: $surveyResponse
--}}
@php
    use App\Support\SibstrFormat as F;

    $b4    = $surveyResponse->blok4_data ?? [];
    $yr4   = (int) ($surveyResponse->tahun ?? 2025);
    $rows4 = [
        ['401', 'Triwulan I',   'Jan–Mar', 'triwulan1'],
        ['402', 'Triwulan II',  'Apr–Jun', 'triwulan2'],
        ['403', 'Triwulan III', 'Jul–Sep', 'triwulan3'],
        ['404', 'Triwulan IV',  'Okt–Des', 'triwulan4'],
    ];
@endphp

<table class="kv">
    @foreach($rows4 as [$no, $label, $months, $key])
    <tr>
        <td class="k">{{ $no }}. {{ $label }} {{ $yr4 }} <span class="hint">({{ $months }})</span></td>
        <td class="v wrap">{{ F::plain($b4[$key] ?? null) }}</td>
    </tr>
    @endforeach
</table>
