{{--
  Blok I — Keterangan Umum, read-only.

  Shared by the BPS detail page, the Mitra detail page and the PDF. Values are
  printed as plain text in label/value tables so long entries wrap instead of
  being clipped the way the old readonly <input> rendering did.

  Markup contract (styled by each host):
    table.kv > tr > td.k (label) + td.v (value)     — two-column facts
    div.sub                                        — sub-section heading
    div.sx > table.dt                              — wide scrollable data table
    p.note / div.empty                             — footnotes / empty state

  Expects: $surveyResponse, $bpsRiData, $jenisKawasanOptions
--}}
@php use App\Support\SibstrFormat as F; @endphp

<div class="sub">Identitas Perusahaan</div>
<table class="kv">
    <tr><td class="k">KIP</td><td class="v">{{ F::plain($surveyResponse->kip) }}</td></tr>
    <tr><td class="k">IDSBR</td><td class="v">{{ F::plain($surveyResponse->idsbr) }}</td></tr>
    <tr><td class="k">101. Nama Perusahaan</td><td class="v">{{ F::plain($surveyResponse->nama_perusahaan) }}</td></tr>
    <tr><td class="k">102. Alamat Pabrik/Tempat Usaha</td><td class="v">{{ F::plain($surveyResponse->alamat_pabrik) }}</td></tr>
    <tr><td class="k">103. Kabupaten/Kota</td><td class="v">{{ F::plain($surveyResponse->kabupaten_kota) }}</td></tr>
    <tr><td class="k">104. Telepon/Fax</td><td class="v">{{ F::plain($surveyResponse->telepon_fax) }}</td></tr>
    <tr><td class="k">105. Penghubung</td><td class="v">{{ F::plain($surveyResponse->penghubung) }}</td></tr>
    <tr><td class="k">106. Email</td><td class="v">{{ F::plain($surveyResponse->email) }}</td></tr>
    <tr><td class="k">107. Homepage/Website</td><td class="v">{{ F::plain($surveyResponse->homepage) }}</td></tr>
    <tr><td class="k">108. Tahun Mulai Beroperasi Komersial</td><td class="v">{{ F::plain($surveyResponse->tahun_mulai_beroperasi) }}</td></tr>
    <tr><td class="k">NIB (Nomor Induk Berusaha)</td><td class="v">{{ F::plain($surveyResponse->nib) }}</td></tr>
    <tr>
        <td class="k">Jenis Kawasan</td>
        <td class="v">{{ $jenisKawasanOptions[$surveyResponse->jenis_kawasan ?? ''] ?? F::plain($surveyResponse->jenis_kawasan) }}</td>
    </tr>
    <tr><td class="k">Nama Kawasan</td><td class="v">{{ F::plain($surveyResponse->nama_kawasan) }}</td></tr>
    <tr><td class="k">Nama Perusahaan Pengelola Kawasan</td><td class="v">{{ F::plain($surveyResponse->nama_pengelola_kawasan) }}</td></tr>
</table>

<div class="sub">Legalisasi Perusahaan</div>
<p class="note">Diketahui oleh yang bertanggung jawab di perusahaan.</p>
<table class="kv">
    <tr><td class="k">Nama</td><td class="v">{{ F::plain($surveyResponse->legalisasi_nama) }}</td></tr>
    <tr><td class="k">Jabatan</td><td class="v">{{ F::plain($surveyResponse->legalisasi_jabatan) }}</td></tr>
    <tr><td class="k">Jenis Kelamin</td><td class="v">{{ F::jenisKelamin($surveyResponse->legalisasi_jenis_kelamin) }}</td></tr>
    <tr><td class="k">NIK</td><td class="v">{{ F::plain($surveyResponse->legalisasi_nik) }}</td></tr>
</table>

<div class="sub">Kontak BPS RI</div>
<table class="kv">
    <tr><td class="k">Penghubung</td><td class="v">{{ $bpsRiData['penghubung'] }}</td></tr>
    <tr><td class="k">Telepon</td><td class="v">{{ $bpsRiData['telepon'] }}</td></tr>
    <tr><td class="k">Fax</td><td class="v">{{ $bpsRiData['fax'] }}</td></tr>
    <tr><td class="k">Email</td><td class="v">{{ $bpsRiData['email'] }}</td></tr>
    <tr><td class="k">Alamat</td><td class="v">{{ $bpsRiData['alamat'] }}</td></tr>
</table>
