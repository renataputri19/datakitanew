{{--
  Blok VI — Catatan, read-only.
  Expects: $surveyResponse
--}}
@php use App\Support\SibstrFormat as F; @endphp

<table class="kv">
    <tr>
        <td class="k">601. Catatan Tambahan</td>
        <td class="v wrap">{{ F::plain($surveyResponse->catatan) }}</td>
    </tr>
</table>
