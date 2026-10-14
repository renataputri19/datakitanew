{{--
  A "rincian → nilai" table, which is the shape of Blok III, IV and VIII.

  Expects:
    $field      JSON column the inputs write into (e.g. 'blok4_pengeluaran')
    $rows       [key => label] in questionnaire order
    $values     stored map for that column
    $valueHead  column-2 heading, e.g. 'Nilai (000 Rp)'
    $totalLabel JUMLAH row label
    $sumKey     data-k-sum key the page's recompute() writes the total into
    $decimal    optional: allow a decimal comma (percentages)
--}}
<div class="k-scroll">
  <table class="k-table">
    <thead>
      <tr>
        <th style="text-align:left;">Rincian (1)</th>
        <th style="width:16rem;">{{ $valueHead }} (2)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $key => $label)
        <tr>
          <td>{{ $loop->iteration }}. {{ $label }}</td>
          <td>
            <input class="k-input k-num" inputmode="decimal"
                   data-k-field="{{ $field }}" data-k-path="{{ $key }}"
                   @if(!empty($decimal)) data-k-decimal @endif
                   aria-label="{{ $label }}"
                   value="{{ $values[$key] ?? '' }}">
          </td>
        </tr>
      @endforeach
      <tr class="k-total-row">
        <td>{{ $totalLabel }}</td>
        <td class="k-readonly" data-k-sum="{{ $sumKey }}">0</td>
      </tr>
    </tbody>
  </table>
</div>
