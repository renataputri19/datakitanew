{{--
  Blok IIIC — Bahan Baku & Bahan Penolong, read-only (industri tahunan).

  Also carries Nilai Aset (318), Kepemilikan Modal (319) and Prospek/Kendala
  (320–322) for the Industri path — on the survey form those questions live on
  this page, stored inside blok3b_industri_data.

  Expects: $surveyResponse
--}}
@php
    use App\Support\SibstrFormat as F;

    $materials = $surveyResponse->blok3a2_materials ?? [];
    $d3c       = $surveyResponse->blok3b_industri_data ?? [];
    $tahun3c   = (int) ($surveyResponse->tahun ?? 2025);

    $q318a = F::num($d3c['q318a'] ?? null);
    $q318b = F::num($d3c['q318b'] ?? null);
    $q318c = F::sumOrStored($d3c, 'q318c', ['q318a', 'q318b']);
    $q319i = F::sumOrStored($d3c, 'q319i', ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h']);

    $hasAset    = $q318a > 0 || $q318b > 0 || $q318c !== null
                    || !empty($d3c['q318c_range']) || !empty($d3c['q318d_area']);
    $hasModal   = collect(['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'])
                    ->contains(fn ($k) => !empty($d3c[$k]));
    $hasProspek = !empty($d3c['q320']) || !empty($d3c['q324']) || !empty($d3c['q325']);

    $totalDn = collect($materials)->sum(fn ($m) => F::num($m['dn_nilai'] ?? null) ?? 0);
    $totalLn = collect($materials)->sum(fn ($m) => F::num($m['ln_nilai'] ?? null) ?? 0);
@endphp

{{-- ── 304. Bahan baku & penolong ──────────────────────────────────────────── --}}
<div class="sub">304. Bahan Baku dan Bahan Penolong yang Digunakan</div>

@if(count($materials) === 0)
    <div class="empty">Belum ada data bahan baku untuk ditampilkan.</div>
@else
<div class="sx">
    <table class="dt wide">
        <thead>
            <tr>
                <th class="w-no" rowspan="2">(1)<br>No.</th>
                <th class="ta-l" rowspan="2">(2)<br>Nama Bahan Baku &amp; Penolong</th>
                <th rowspan="2">(3)<br>Satuan<br>Standar</th>
                <th class="th-dn" colspan="2">Dalam Negeri</th>
                <th class="th-ln" colspan="2">Luar Negeri *)</th>
                <th class="ta-l" rowspan="2">(8)<br>Negara Asal **)</th>
            </tr>
            <tr>
                <th class="th-dn">(4) Banyaknya</th>
                <th class="th-dn">(5) Nilai (Rp)</th>
                <th class="th-ln">(6) Banyaknya</th>
                <th class="th-ln">(7) Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $i => $mat)
            <tr>
                <td class="ta-c">{{ $i + 1 }}</td>
                <td class="ta-l">{{ F::plain($mat['nama_bahan'] ?? null) }}</td>
                <td class="ta-c">{{ F::plain($mat['satuan_standar'] ?? null) }}</td>
                <td class="num c-dn">{{ F::idr($mat['dn_banyaknya'] ?? null) }}</td>
                <td class="num c-dn">{{ F::idr($mat['dn_nilai'] ?? null) }}</td>
                <td class="num c-ln">{{ F::idr($mat['ln_banyaknya'] ?? null) }}</td>
                <td class="num c-ln">{{ F::idr($mat['ln_nilai'] ?? null) }}</td>
                <td class="ta-l">{{ F::plain($mat['negara_asal'] ?? null) }}</td>
            </tr>

            @if(!empty($mat['rincian_asal']) && is_array($mat['rincian_asal']))
                @foreach($mat['rincian_asal'] as $ra)
                    @php
                        $raJml = F::num($ra['jumlah'] ?? null);
                        $raNil = F::num($ra['nilai'] ?? null);
                    @endphp
                    @if(!empty($ra['provinsi']) || $raJml !== null || $raNil !== null)
                    <tr class="r-sub">
                        <td class="ta-c">&#8627;</td>
                        <td class="ta-l">{{ F::plain($ra['provinsi'] ?? null) }}</td>
                        <td></td>
                        <td class="num c-dn">{{ F::idr($raJml) }}</td>
                        <td class="num c-dn">{{ F::idr($raNil) }}</td>
                        <td class="c-ln"></td>
                        <td class="c-ln"></td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @endforeach

            <tr class="r-total">
                <td class="ta-l" colspan="3">Total nilai bahan baku &amp; penolong</td>
                <td></td>
                <td class="num">{{ F::idr($totalDn > 0 ? $totalDn : null) }}</td>
                <td></td>
                <td class="num">{{ F::idr($totalLn > 0 ? $totalLn : null) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
<p class="note">
    *) Termasuk yang diimpor oleh importir umum atau pihak lain.
    **) Jika negara asal impor lebih dari satu, dituliskan negara dengan nilai impor terbesar.
</p>
@endif

{{-- ── 318. Nilai aset ────────────────────────────────────────────────────── --}}
@if($hasAset)
<div class="sub">318. Nilai Aset per 31 Desember {{ $tahun3c }}</div>
<table class="kv">
    <tr><td class="k">318a. Tanah dan bangunan (Rp)</td><td class="v num">{{ F::idr($q318a > 0 ? $q318a : null) }}</td></tr>
    <tr><td class="k">318b. Selain tanah dan bangunan (Rp)</td><td class="v num">{{ F::idr($q318b > 0 ? $q318b : null) }}</td></tr>
    <tr><td class="k">318c. Nilai total aset — jumlah a + b (Rp)</td><td class="v num">{{ F::idr($q318c) }}</td></tr>
    <tr><td class="k">318c1. Rentang nilai <span class="hint">(jika c kosong)</span></td><td class="v">{{ F::rentangAset($d3c['q318c_range'] ?? null) }}</td></tr>
    <tr><td class="k">318d. Luas tanah untuk usaha (m&sup2;)</td><td class="v num">{{ F::plain($d3c['q318d_area'] ?? null) }}</td></tr>
</table>
@endif

{{-- ── 319. Kepemilikan modal ─────────────────────────────────────────────── --}}
@if($hasModal)
<div class="sub">319. Susunan Kepemilikan Modal (%)</div>
<table class="kv">
    <tr><td class="k">319a. Pribadi / Perorangan</td><td class="v num">{{ F::pct($d3c['q319a'] ?? null) }}</td></tr>
    <tr><td class="k">319b. Lembaga Nonprofit yang Melayani Rumah Tangga</td><td class="v num">{{ F::pct($d3c['q319b'] ?? null) }}</td></tr>
    <tr><td class="k">319c. Korporasi Publik</td><td class="v num">{{ F::pct($d3c['q319c'] ?? null) }}</td></tr>
    <tr><td class="k">319d. Korporasi Non Publik</td><td class="v num">{{ F::pct($d3c['q319d'] ?? null) }}</td></tr>
    <tr><td class="k">319e. Pemerintah Pusat</td><td class="v num">{{ F::pct($d3c['q319e'] ?? null) }}</td></tr>
    <tr><td class="k">319f. Pemerintah Daerah</td><td class="v num">{{ F::pct($d3c['q319f'] ?? null) }}</td></tr>
    <tr><td class="k">319g. Perusahaan Swasta Nasional</td><td class="v num">{{ F::pct($d3c['q319g'] ?? null) }}</td></tr>
    <tr><td class="k">319h. Asing</td><td class="v num">{{ F::pct($d3c['q319h'] ?? null) }}</td></tr>
    <tr class="r-total"><td class="k">319i. Total <span class="hint">(harus 100%)</span></td><td class="v num">{{ $q319i !== null ? F::dec($q319i) . ' %' : F::DASH }}</td></tr>
</table>
@endif

{{-- ── 320–322. Prospek & kendala ─────────────────────────────────────────── --}}
@if($hasProspek)
<div class="sub">Prospek dan Kendala Usaha</div>
<table class="kv">
    <tr><td class="k">320. Kendala selama {{ $tahun3c }} — a. Permodalan</td><td class="v">{{ F::satuDua($d3c['q320'] ?? null) }}</td></tr>
    <tr><td class="k">320. — b. Bahan baku</td><td class="v">{{ F::satuDua($d3c['q321'] ?? null) }}</td></tr>
    <tr><td class="k">320. — c. Pemasaran</td><td class="v">{{ F::satuDua($d3c['q322'] ?? null) }}</td></tr>
    <tr><td class="k">320. — d. Iklim usaha</td><td class="v">{{ F::satuDua($d3c['q323'] ?? null) }}</td></tr>
    <tr><td class="k">321. Rencana merekrut pegawai / mengembangkan usaha {{ $tahun3c + 1 }}</td><td class="v">{{ F::satuDua($d3c['q324'] ?? null) }}</td></tr>
    <tr><td class="k">322. Strategi daya saing — a. Inovasi barang &amp; jasa</td><td class="v">{{ F::satuDua($d3c['q325'] ?? null) }}</td></tr>
    <tr><td class="k">322. — b. Pengembangan teknologi</td><td class="v">{{ F::satuDua($d3c['q326'] ?? null) }}</td></tr>
    <tr><td class="k">322. — c. Pemasaran (marketing)</td><td class="v">{{ F::satuDua($d3c['q327'] ?? null) }}</td></tr>
    <tr><td class="k">322. — d. Kemitraan (UMKM, pemerintah, dll.)</td><td class="v">{{ F::satuDua($d3c['q328'] ?? null) }}</td></tr>
</table>
@endif
