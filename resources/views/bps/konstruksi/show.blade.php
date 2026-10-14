@extends('layouts.bps')

@section('title', 'Detail Survei Konstruksi - BPS Dashboard')
@section('description', 'Rincian jawaban satu responden Survei Perusahaan Konstruksi')

@push('styles')
<style>
.kd-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; box-shadow:0 1px 2px 0 rgba(0,0,0,.05); margin-bottom:1.25rem; overflow:hidden; }
.dark .kd-card { background:#1f2937; border-color:#374151; }
.kd-head { padding:.85rem 1.25rem; background:#fffbeb; border-bottom:1px solid #fde68a; }
.dark .kd-head { background:rgba(120,53,15,.25); border-color:#78350f; }
.kd-head h2 { font-size:.8rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:#b45309; margin:0; }
.dark .kd-head h2 { color:#fbbf24; }
.kd-head p { font-size:.75rem; color:#92400e; margin:.15rem 0 0; }
.dark .kd-head p { color:#fcd34d; }
.kd-body { padding:1.25rem; }
.kd-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }

.kd-kv { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:.85rem 1.5rem; }
.kd-kv > div { min-width:0; }
.kd-kv dt { font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:.15rem; }
.dark .kd-kv dt { color:#9ca3af; }
.kd-kv dd { font-size:.875rem; font-weight:600; color:#111827; margin:0; word-break:break-word; }
.dark .kd-kv dd { color:#f9fafb; }

.kd-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.kd-table th, .kd-table td { border:1px solid #e5e7eb; padding:.45rem .6rem; color:#374151; vertical-align:top; }
.dark .kd-table th, .dark .kd-table td { border-color:#374151; color:#d1d5db; }
.kd-table thead th { background:#f9fafb; font-size:.7rem; font-weight:700; text-align:center; color:#6b7280; line-height:1.3; }
.dark .kd-table thead th { background:#111827; color:#9ca3af; }
.kd-table td.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
.kd-table tr.total { background:#fffbeb; font-weight:700; }
.dark .kd-table tr.total { background:rgba(120,53,15,.25); }
.dark .kd-table tr.total td { color:#fbbf24; }
.kd-table tr.total td { color:#b45309; }
.kd-wide { min-width:1750px; }
.kd-wide td, .kd-wide th { white-space:nowrap; }
.kd-wide td.wrap { white-space:normal; min-width:15rem; }

.kd-chip { display:inline-flex; align-items:center; padding:.15rem .55rem; border-radius:9999px; font-size:.68rem; font-weight:700; }
.kd-ok { background:#d1fae5; color:#065f46; }
.dark .kd-ok { background:#064e3b; color:#6ee7b7; }
.kd-no { background:#fee2e2; color:#991b1b; }
.dark .kd-no { background:#7f1d1d; color:#fca5a5; }
.kd-blok-list { display:flex; flex-wrap:wrap; gap:.4rem; }
.kd-blok-list span { font-size:.68rem; }
.kd-empty { font-size:.82rem; color:#9ca3af; font-style:italic; }
.kd-warn { font-size:.82rem; padding:.7rem .9rem; border-radius:.5rem; background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.dark .kd-warn { background:rgba(120,53,15,.3); color:#fcd34d; border-color:#78350f; }
</style>
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  use App\Support\SibstrFormat;
  $dash = fn ($v) => trim((string) $v) === '' ? '—' : $v;
  $pekerja = is_array($response->blok2_pekerja) ? $response->blok2_pekerja : [];
@endphp

@section('content')
<div class="space-y-4">

  {{-- Header --}}
  <div class="flex items-start justify-between gap-4 flex-wrap">
    <div>
      <a href="{{ route('bps.konstruksi.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&larr; Kembali ke daftar</a>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $dash($response->nama_perusahaan) }}</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Survei Perusahaan Konstruksi · {{ $periode }} ·
        {{ $response->is_completed ? 'Selesai' : 'Dalam proses (' . $response->completionPercent() . '%)' }} ·
        terakhir disimpan {{ $completedAt }}
      </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <a href="{{ route('bps.konstruksi.download', $response->id) }}"
         class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-white" style="background:#16a34a;">
        Unduh PDF Lengkap
      </a>
      <button type="button" class="btn-delete"
              onclick="bpsConfirmDelete(
                  '{{ route('bps.konstruksi.destroy', $response->id) }}',
                  @js($response->nama_perusahaan ?: ($responden->name ?? 'Responden')),
                  @js('Survei Konstruksi ' . $periode . ' — ' . ($responden->email ?? '')),
                  @js('Survei Konstruksi ' . $periode)
              )">
        Hapus Data
      </button>
    </div>
  </div>

  {{-- Kelengkapan per blok --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Kelengkapan Pengisian</h2></div>
    <div class="kd-body">
      <div class="kd-blok-list">
        @foreach($navBlocks as $blk)
          <span class="kd-chip {{ $blk['done'] ? 'kd-ok' : 'kd-no' }}">
            {{ $blk['label'] }} — {{ $blk['done'] ? 'Lengkap' : 'Belum' }}
          </span>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Updating Status Perusahaan --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Updating Status Perusahaan</h2></div>
    <div class="kd-body">
      <dl class="kd-kv">
        <div><dt>Status Perusahaan</dt><dd>{{ K::STATUS_PERUSAHAAN[$response->status_perusahaan] ?? '—' }}</dd></div>
        <div><dt>Kesediaan Responden</dt><dd>{{ K::KESEDIAAN_RESPONDEN[$response->kesediaan_responden] ?? '—' }}</dd></div>
        <div><dt>Keberadaan Proyek</dt><dd>{{ K::KEBERADAAN_PROYEK[$response->keberadaan_proyek] ?? '—' }}</dd></div>
      </dl>

      @if((string) $response->status_perusahaan === K::STATUS_PINDAH)
        <dl class="kd-kv" style="margin-top:1rem;">
          <div><dt>Kabupaten/Kota Baru</dt><dd>{{ $dash($response->pindah_kabupaten_kota) }}</dd></div>
          <div><dt>Kecamatan Baru</dt><dd>{{ $dash($response->pindah_kecamatan) }}</dd></div>
          <div><dt>Desa/Kelurahan Baru</dt><dd>{{ $dash($response->pindah_kelurahan_desa) }}</dd></div>
          <div style="grid-column:1/-1;"><dt>Alamat Baru</dt><dd>{{ $dash($response->pindah_alamat) }}</dd></div>
        </dl>
      @endif

      @if($response->stoppedAtStatus())
        <p class="kd-warn" style="margin-top:1rem;">
          Pencacahan berhenti pada blok ini — perusahaan tidak aktif atau responden tidak memberikan jawaban.
          Blok I sampai Blok IX tidak diisi.
        </p>
      @elseif(!$response->proyekBlokApplies())
        <p class="kd-warn" style="margin-top:1rem;">
          Responden menyatakan <b>tidak ada proyek konstruksi</b> sepanjang tahun ini, sehingga
          Blok III sampai Blok IX dilewati. Blok II tetap diisi karena menanyakan pekerja
          tetap dan kontrak, yang tidak bergantung pada ada tidaknya proyek.
        </p>
      @endif
    </div>
  </div>

@if(!$response->stoppedAtStatus())
  {{-- Blok I --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok I — Keterangan Umum Perusahaan</h2></div>
    <div class="kd-body">
      <dl class="kd-kv">
        <div><dt>KIP</dt><dd>{{ $dash($response->kip) }}</dd></div>
        <div><dt>Nomor Urut Sampel</dt><dd>{{ $dash($response->nus) }}</dd></div>
        <div><dt>Provinsi</dt><dd>{{ $dash($response->provinsi) }}</dd></div>
        <div><dt>Kabupaten/Kota</dt><dd>{{ $dash($response->kabupaten_kota) }}</dd></div>
        <div><dt>Kecamatan</dt><dd>{{ $dash($response->kecamatan) }}</dd></div>
        <div><dt>Desa/Kelurahan</dt><dd>{{ $dash($response->kelurahan_desa) }}</dd></div>
        <div><dt>NIB</dt><dd>{{ $response->punya_nib === 'ya' ? $dash($response->nib) : 'Tidak memiliki NIB' }}</dd></div>
        <div><dt>Penanggung Jawab</dt><dd>{{ $dash($response->nama_pengusaha) }}</dd></div>
        <div><dt>No. HP</dt><dd>{{ $dash($response->hp_pengusaha) }}</dd></div>
        <div style="grid-column:1/-1;"><dt>Alamat Perusahaan</dt><dd>
          {{ $dash($response->alamat_perusahaan) }}
          @if($response->rt || $response->rw) · RT {{ $dash($response->rt) }} / RW {{ $dash($response->rw) }} @endif
          @if($response->kode_pos) · {{ $response->kode_pos }} @endif
        </dd></div>
        @if(trim((string) $response->alamat_operasional) !== '')
          <div style="grid-column:1/-1;"><dt>Alamat Operasional</dt><dd>{{ $response->alamat_operasional }}</dd></div>
        @endif
        <div><dt>Telepon</dt><dd>{{ $dash($response->nomor_telepon) }}</dd></div>
        <div><dt>Website</dt><dd>{{ $dash($response->website) }}</dd></div>
        <div><dt>E-mail</dt><dd>{{ $dash($response->email_perusahaan) }}</dd></div>
        <div><dt>Badan Hukum / Usaha</dt><dd>
          {{ K::BADAN_HUKUM[$response->badan_hukum] ?? '—' }}{{ $response->badan_hukum === '9' ? ' — ' . $dash($response->badan_hukum_lainnya) : '' }}
        </dd></div>
        <div><dt>Status Penanaman Modal</dt><dd>{{ K::STATUS_MODAL[$response->status_penanaman_modal] ?? '—' }}</dd></div>
        <div><dt>Kualifikasi</dt><dd>{{ K::KUALIFIKASI[$response->kualifikasi] ?? '—' }}</dd></div>
      </dl>
    </div>
  </div>

  {{-- Blok II --}}
  <div class="kd-card">
    <div class="kd-head">
      <h2>Blok II — Tenaga Kerja &amp; Pengeluaran Balas Jasa</h2>
      <p>Kondisi 31 Desember {{ $response->tahun }} · nilai dalam ribuan rupiah</p>
    </div>
    <div class="kd-body kd-scroll">
      <table class="kd-table" style="margin-bottom:1.25rem;">
        <thead>
          <tr><th style="text-align:left;">Jenjang Pendidikan</th><th>Laki-Laki</th><th>Perempuan</th><th>Jumlah</th></tr>
        </thead>
        <tbody>
          @php $totL = 0; $totP = 0; @endphp
          @foreach(K::PENDIDIKAN as $key => $label)
            @php
              $l = K::n($pekerja[$key]['l'] ?? null);
              $p = K::n($pekerja[$key]['p'] ?? null);
              $totL += $l; $totP += $p;
            @endphp
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              <td class="num">{{ SibstrFormat::idr($l) }}</td>
              <td class="num">{{ SibstrFormat::idr($p) }}</td>
              <td class="num">{{ SibstrFormat::idr($l + $p) }}</td>
            </tr>
          @endforeach
          <tr class="total">
            <td>JUMLAH PEKERJA TETAP DAN KONTRAK</td>
            <td class="num">{{ SibstrFormat::idr($totL) }}</td>
            <td class="num">{{ SibstrFormat::idr($totP) }}</td>
            <td class="num">{{ SibstrFormat::idr($totL + $totP) }}</td>
          </tr>
        </tbody>
      </table>

      <table class="kd-table">
        <thead><tr><th style="text-align:left;">Rincian Balas Jasa</th><th style="width:14rem;">Nilai (000 Rp)</th></tr></thead>
        <tbody>
          @foreach(K::BALAS_JASA as $key => $label)
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              <td class="num">{{ SibstrFormat::idr(($response->blok2_balas_jasa ?? [])[$key] ?? null) }}</td>
            </tr>
          @endforeach
          <tr class="total"><td>JUMLAH [ 1 + 2 + … + 6 ]</td><td class="num">{{ SibstrFormat::idr($response->totalBalasJasa()) }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>

@if($response->proyekBlokApplies())
  {{-- Blok III --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok III — Kepemilikan Modal Perusahaan</h2></div>
    <div class="kd-body kd-scroll">
      <table class="kd-table">
        <thead><tr><th style="text-align:left;">Sumber Modal</th><th style="width:14rem;">Persentase</th></tr></thead>
        <tbody>
          @foreach(K::SUMBER_MODAL as $key => $label)
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              <td class="num">{{ SibstrFormat::pct(($response->blok3_modal ?? [])[$key] ?? null) }}</td>
            </tr>
          @endforeach
          <tr class="total"><td>JUMLAH</td><td class="num">{{ SibstrFormat::pct($response->totalModal()) }}</td></tr>
        </tbody>
      </table>
      @if(trim((string) $response->modal_asing_negara) !== '')
        <p style="margin-top:.75rem;font-size:.82rem;color:#6b7280;">
          Negara dengan kepemilikan modal asing terbesar: <strong>{{ $response->modal_asing_negara }}</strong>
        </p>
      @endif
    </div>
  </div>

  {{-- Blok IV --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok IV — Pengeluaran Operasional</h2><p>Nilai dalam ribuan rupiah</p></div>
    <div class="kd-body kd-scroll">
      <table class="kd-table">
        <thead><tr><th style="text-align:left;">Rincian</th><th style="width:14rem;">Nilai (000 Rp)</th></tr></thead>
        <tbody>
          @foreach(K::PENGELUARAN as $key => $label)
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              <td class="num">{{ SibstrFormat::idr(($response->blok4_pengeluaran ?? [])[$key] ?? null) }}</td>
            </tr>
          @endforeach
          <tr class="total"><td>JUMLAH [ 1 + 2 + … + 8 ]</td><td class="num">{{ SibstrFormat::idr($response->totalPengeluaran()) }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Blok V --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok V — Penambahan &amp; Pengurangan Barang Modal</h2><p>Nilai dalam ribuan rupiah</p></div>
    <div class="kd-body kd-scroll">
      @php $bm = $response->totalBarangModal(); @endphp
      <table class="kd-table">
        <thead><tr><th style="text-align:left;">Jenis Barang Modal</th><th style="width:12rem;">Penambahan</th><th style="width:12rem;">Pengurangan</th></tr></thead>
        <tbody>
          @foreach(K::BARANG_MODAL as $key => $label)
            <tr>
              <td>
                {{ $loop->iteration }}. {{ $label }}
                @if($key === 'lainnya' && trim((string) $response->barang_modal_lainnya) !== '')
                  <span style="color:#6b7280;">— {{ $response->barang_modal_lainnya }}</span>
                @endif
              </td>
              <td class="num">{{ SibstrFormat::idr(($response->blok5_barang_modal ?? [])[$key]['tambah'] ?? null) }}</td>
              <td class="num">{{ SibstrFormat::idr(($response->blok5_barang_modal ?? [])[$key]['kurang'] ?? null) }}</td>
            </tr>
          @endforeach
          <tr class="total">
            <td>JUMLAH [ 1 + 2 + … + 6 ]</td>
            <td class="num">{{ SibstrFormat::idr($bm['tambah']) }}</td>
            <td class="num">{{ SibstrFormat::idr($bm['kurang']) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Blok VI --}}
  <div class="kd-card">
    <div class="kd-head">
      <h2>Blok VI — Pendapatan &amp; Pengeluaran Pekerjaan Konstruksi</h2>
      <p>{{ count($proyekRows) }} proyek dilaporkan · nilai dalam ribuan rupiah · kolom (17) dihitung otomatis</p>
    </div>
    <div class="kd-body">
      @if($proyekRows === [])
        <p class="kd-empty">Belum ada proyek yang diisi.</p>
      @else
        <div class="kd-scroll">
          <table class="kd-table kd-wide">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Pekerjaan / KBLI (2)</th>
                <th>Lokasi (3)</th>
                <th>Sumber Dana (4)</th>
                <th>Volume (5)</th>
                <th>Satuan (6)</th>
                <th>Nilai Kontrak (7)</th>
                <th>Subkontrak (8)</th>
                <th>Mulai (9)</th>
                <th>Selesai (10)</th>
                <th>Realisasi s/d {{ (int) $response->tahun - 1 }} (11)</th>
                <th>Realisasi s/d {{ $response->tahun }} (12)</th>
                <th>Bahan Perusahaan (13)</th>
                <th>Bahan Pemilik (14)</th>
                <th>Upah Harian (15)</th>
                <th>Rata-rata Upah (16)</th>
                <th>Nilai Konstruksi (17)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($proyekRows as $i => $row)
                <tr>
                  <td class="num">{{ $i + 1 }}</td>
                  <td class="wrap">
                    {{ $dash($row['nama_pekerjaan'] ?? '') }}
                    @if(trim((string) ($row['kbli'] ?? '')) !== '')
                      <div style="font-size:.7rem;color:#6b7280;">KBLI {{ $row['kbli'] }}</div>
                    @endif
                  </td>
                  <td class="wrap">
                    {{ $dash($row['kabkota_lokasi'] ?? '') }}
                    <div style="font-size:.7rem;color:#6b7280;">
                      {{ $row['provinsi_lokasi'] ?? '' }}{{ trim((string) ($row['kode_kabkota'] ?? '')) !== '' ? ' · ' . $row['kode_kabkota'] : '' }}
                    </div>
                  </td>
                  <td>{{ K::SUMBER_DANA[$row['sumber_dana'] ?? ''] ?? '—' }}</td>
                  <td class="num">{{ SibstrFormat::dec($row['volume'] ?? null) }}</td>
                  <td>{{ $dash($row['satuan'] ?? '') }}</td>
                  <td class="num">{{ SibstrFormat::idr($row['nilai_kontrak'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::idr($row['nilai_subkontrak'] ?? null) }}</td>
                  <td>{{ $dash($row['mulai'] ?? '') }}</td>
                  <td>{{ $dash($row['selesai'] ?? '') }}</td>
                  <td class="num">{{ SibstrFormat::pct($row['realisasi_awal'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::pct($row['realisasi_akhir'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::idr($row['bahan_perusahaan'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::idr($row['bahan_pemilik'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::idr($row['upah_harian'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::idr($row['upah_rata_rata'] ?? null) }}</td>
                  <td class="num">{{ SibstrFormat::idr(K::nilaiKonstruksi($row)) }}</td>
                </tr>
              @endforeach
              <tr class="total">
                <td colspan="6" style="text-align:right;">JUMLAH</td>
                <td class="num">{{ SibstrFormat::idr($proyekTotal['nilai_kontrak']) }}</td>
                <td colspan="5"></td>
                <td class="num">{{ SibstrFormat::idr($proyekTotal['bahan_perusahaan']) }}</td>
                <td class="num">{{ SibstrFormat::idr($proyekTotal['bahan_pemilik']) }}</td>
                <td class="num">{{ SibstrFormat::idr($proyekTotal['upah_harian']) }}</td>
                <td></td>
                <td class="num">{{ SibstrFormat::idr($proyekTotal['nilai_konstruksi']) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      @endif

      <dl class="kd-kv" style="margin-top:1.25rem;">
        <div><dt>Rata-rata % bahan/material (rincian 2)</dt><dd>{{ SibstrFormat::pct($response->pct_bahan) }}</dd></div>
        <div><dt>Rata-rata % upah pekerja harian (rincian 3)</dt><dd>{{ SibstrFormat::pct($response->pct_upah) }}</dd></div>
        @foreach(K::MODAL_PROYEK as $key => $label)
          <div><dt>Sumber modal — {{ $label }}</dt><dd>{{ SibstrFormat::pct(($response->blok6_sumber_modal ?? [])[$key] ?? null) }}</dd></div>
        @endforeach
      </dl>
    </div>
  </div>

  {{-- Blok VII --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok VII — Struktur Bahan / Material Proyek Utama</h2><p>Nilai dalam ribuan rupiah</p></div>
    <div class="kd-body kd-scroll">
      @if($bahanRows === [])
        <p class="kd-empty">Belum ada bahan/material yang diisi.</p>
      @else
        <table class="kd-table">
          <thead><tr><th style="width:3rem;">No</th><th style="text-align:left;">Jenis Bahan / Material</th><th style="width:14rem;">Nilai (000 Rp)</th></tr></thead>
          <tbody>
            @foreach($bahanRows as $i => $row)
              <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td>{{ $dash($row['nama'] ?? '') }}</td>
                <td class="num">{{ SibstrFormat::idr($row['nilai'] ?? null) }}</td>
              </tr>
            @endforeach
            <tr class="total"><td colspan="2" style="text-align:right;">JUMLAH</td><td class="num">{{ SibstrFormat::idr($response->totalBahan()) }}</td></tr>
          </tbody>
        </table>
      @endif
    </div>
  </div>

  {{-- Blok VIII --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok VIII — Pendapatan Lain</h2><p>Nilai dalam ribuan rupiah</p></div>
    <div class="kd-body kd-scroll">
      <table class="kd-table">
        <thead><tr><th style="text-align:left;">Jenis Pendapatan</th><th style="width:14rem;">Nilai (000 Rp)</th></tr></thead>
        <tbody>
          @foreach(K::PENDAPATAN_LAIN as $key => $label)
            <tr>
              <td>
                {{ $loop->iteration }}. {{ $label }}
                @if($key === 'lainnya' && trim((string) $response->pendapatan_lain_lainnya) !== '')
                  <span style="color:#6b7280;">— {{ $response->pendapatan_lain_lainnya }}</span>
                @endif
              </td>
              <td class="num">{{ SibstrFormat::idr(($response->blok8_pendapatan_lain ?? [])[$key] ?? null) }}</td>
            </tr>
          @endforeach
          <tr class="total"><td>JUMLAH</td><td class="num">{{ SibstrFormat::idr($response->totalPendapatanLain()) }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Blok IX --}}
  <div class="kd-card">
    <div class="kd-head">
      <h2>Blok IX — Ringkasan Pengeluaran &amp; Pendapatan</h2>
      <p>Dihitung dari Blok II, IV, VI, VII dan VIII — tidak diisi responden</p>
    </div>
    <div class="kd-body kd-scroll">
      <table class="kd-table">
        <thead><tr><th style="text-align:left;">Rincian</th><th style="width:16rem;">Nilai (000 Rp)</th></tr></thead>
        <tbody>
          <tr class="total"><td colspan="2">A. RINCIAN PENGELUARAN</td></tr>
          <tr><td>1. Balas Jasa dan Upah Pekerja</td><td class="num">{{ SibstrFormat::idr($ringkasan['pengeluaran']['balas_jasa']) }}</td></tr>
          <tr><td>2. Nilai Pemakaian Bahan/Material</td><td class="num">{{ SibstrFormat::idr($ringkasan['pengeluaran']['bahan']) }}</td></tr>
          <tr><td>3. Pengeluaran Operasional</td><td class="num">{{ SibstrFormat::idr($ringkasan['pengeluaran']['operasional']) }}</td></tr>
          <tr class="total"><td>JUMLAH A [ 1 + 2 + 3 ]</td><td class="num">{{ SibstrFormat::idr($ringkasan['total_a']) }}</td></tr>

          <tr class="total"><td colspan="2">B. RINCIAN PENDAPATAN</td></tr>
          <tr><td>1. Nilai Konstruksi yang Diselesaikan</td><td class="num">{{ SibstrFormat::idr($ringkasan['pendapatan']['nilai_konstruksi']) }}</td></tr>
          <tr><td>2. Nilai dari Pendapatan Lain</td><td class="num">{{ SibstrFormat::idr($ringkasan['pendapatan']['pendapatan_lain']) }}</td></tr>
          <tr class="total"><td>JUMLAH B [ 1 + 2 ]</td><td class="num">{{ SibstrFormat::idr($ringkasan['total_b']) }}</td></tr>

          <tr class="total">
            <td>C. Persentase Keuntungan Terhadap Pengeluaran &nbsp;<span style="font-weight:400;">(B/A − 1) × 100 %</span></td>
            <td class="num">{{ $ringkasan['margin_pct'] === null ? '—' : number_format($ringkasan['margin_pct'], 2, ',', '.') . ' %' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

@endif {{-- proyekBlokApplies: Blok III–IX --}}
@endif {{-- continuesPastStatus: Blok I–IX --}}

  {{-- Blok X --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Blok X — Metode Pendataan, Catatan &amp; Pemberi Jawaban</h2></div>
    <div class="kd-body">
      <dl class="kd-kv">
        <div><dt>Metode Pendataan Diinginkan</dt><dd>{{ K::METODE_PENDATAAN[$response->metode_pendataan] ?? '—' }}</dd></div>
        <div><dt>Nama Pemberi Jawaban</dt><dd>{{ $dash($response->pj_nama) }}</dd></div>
        <div><dt>Jabatan</dt><dd>{{ $dash($response->pj_jabatan) }}</dd></div>
        <div><dt>Nomor HP / Telepon</dt><dd>{{ $dash($response->pj_hp) }}</dd></div>
        <div><dt>E-mail</dt><dd>{{ $dash($response->pj_email) }}</dd></div>
        <div><dt>Akun Pengisi</dt><dd>{{ $responden->name ?? '—' }}<br><span style="font-weight:400;font-size:.75rem;color:#6b7280;">{{ $responden->email ?? '' }}</span></dd></div>
        <div style="grid-column:1/-1;"><dt>Catatan</dt><dd style="white-space:pre-wrap;">{{ $dash($response->catatan) }}</dd></div>
      </dl>
    </div>
  </div>

</div>

@include('bps.partials.delete-survey-modal')
@endsection
