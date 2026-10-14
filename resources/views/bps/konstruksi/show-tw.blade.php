@extends('layouts.bps')

@section('title', 'Detail Survei Konstruksi Triwulanan - BPS Dashboard')
@section('description', 'Rincian jawaban satu responden Survei Perusahaan Konstruksi Triwulanan')

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
.kd-table tr.total td { color:#b45309; }
.dark .kd-table tr.total td { color:#fbbf24; }
.kd-wide { min-width:1700px; }
.kd-wide td, .kd-wide th { white-space:nowrap; }
.kd-wide td.wrap { white-space:normal; min-width:15rem; }
.kd-rencana { background:rgba(59,130,246,.08); }

.kd-chip { display:inline-flex; align-items:center; padding:.15rem .55rem; border-radius:9999px; font-size:.68rem; font-weight:700; }
.kd-ok { background:#d1fae5; color:#065f46; }
.dark .kd-ok { background:#064e3b; color:#6ee7b7; }
.kd-no { background:#fee2e2; color:#991b1b; }
.dark .kd-no { background:#7f1d1d; color:#fca5a5; }
.kd-blok-list { display:flex; flex-wrap:wrap; gap:.4rem; }
.kd-empty { font-size:.82rem; color:#9ca3af; font-style:italic; }
.kd-warn { font-size:.82rem; padding:.7rem .9rem; border-radius:.5rem; background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.dark .kd-warn { background:rgba(120,53,15,.3); color:#fcd34d; border-color:#78350f; }
</style>
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  use App\Support\SibstrFormat;
  $dash = fn ($v) => trim((string) $v) === '' ? '—' : $v;
  $pekerja = is_array($response->tw_pekerja) ? $response->tw_pekerja : [];
  $bj      = is_array($response->tw_balas_jasa) ? $response->tw_balas_jasa : [];
@endphp

@section('content')
<div class="space-y-4">

  {{-- Header --}}
  <div class="flex items-start justify-between gap-4 flex-wrap">
    <div>
      <a href="{{ route('bps.konstruksi.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&larr; Kembali ke daftar</a>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $dash($response->nama_perusahaan) }}</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Survei Perusahaan Konstruksi Triwulanan (SKTR) · {{ $periode }} ·
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

  {{-- Kelengkapan --}}
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

  {{-- Updating status --}}
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
          <div style="grid-column:1/-1;"><dt>Alamat Baru</dt><dd>
            {{ $dash($response->pindah_alamat) }}
            @if($response->pindah_rt || $response->pindah_rw) · RT {{ $dash($response->pindah_rt) }} / RW {{ $dash($response->pindah_rw) }} @endif
            @if($response->pindah_kode_pos) · {{ $response->pindah_kode_pos }} @endif
          </dd></div>
        </dl>
      @endif

      @if($response->stoppedAtStatus())
        <p class="kd-warn" style="margin-top:1rem;">
          Pencacahan berhenti pada blok ini — perusahaan tidak aktif atau responden tidak memberikan jawaban.
          Blok I sampai Blok III tidak diisi.
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
          <div><dt>No. Handphone</dt><dd>{{ $dash($response->hp_pengusaha) }}</dd></div>
          <div style="grid-column:1/-1;"><dt>Alamat</dt><dd>
            {{ $dash($response->alamat_perusahaan) }}
            @if($response->rt || $response->rw) · RT {{ $dash($response->rt) }} / RW {{ $dash($response->rw) }} @endif
            @if($response->kode_pos) · {{ $response->kode_pos }} @endif
          </dd></div>
          <div><dt>Telepon</dt><dd>{{ $dash($response->nomor_telepon) }}</dd></div>
          <div><dt>Website</dt><dd>{{ $dash($response->website) }}</dd></div>
          <div><dt>E-mail</dt><dd>{{ $dash($response->email_perusahaan) }}</dd></div>
          <div><dt>Badan Hukum / Usaha</dt><dd>
            {{ K::BADAN_HUKUM[$response->badan_hukum] ?? '—' }}{{ $response->badan_hukum === '9' ? ' — ' . $dash($response->badan_hukum_lainnya) : '' }}
          </dd></div>
          <div><dt>Status Permodalan</dt><dd>{{ K::STATUS_MODAL[$response->status_penanaman_modal] ?? '—' }}</dd></div>
          <div><dt>Kualifikasi</dt><dd>{{ K::KUALIFIKASI[$response->kualifikasi] ?? '—' }}</dd></div>
        </dl>
      </div>
    </div>

    {{-- Blok II --}}
    <div class="kd-card">
      <div class="kd-head">
        <h2>Blok II — Tenaga Kerja &amp; Balas Jasa</h2>
        <p>Tidak termasuk pekerja tidak tetap/harian · balas jasa dalam ribuan rupiah</p>
      </div>
      <div class="kd-body kd-scroll">
        <table class="kd-table">
          <thead>
            <tr>
              <th style="text-align:left;">Triwulan</th>
              <th style="width:14rem;">Pekerja Tetap &amp; Kontrak (orang)</th>
              <th style="width:16rem;">Balas Jasa (000 Rp)</th>
            </tr>
          </thead>
          <tbody>
            @foreach(K::TW_SLOTS as $slot => $delta)
              <tr>
                <td>{{ $quarterLabels[$slot]['range'] ?? $slot }}</td>
                <td class="num">{{ SibstrFormat::idr($pekerja[$slot] ?? null) }}</td>
                <td class="num">{{ SibstrFormat::idr($bj[$slot] ?? null) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Blok III --}}
    <div class="kd-card">
      <div class="kd-head">
        <h2>Blok III — Nilai Kontrak &amp; Realisasi Fisik Pekerjaan/Proyek</h2>
        <p>
          {{ count($proyekTwRows) }} proyek dilaporkan · nilai dalam ribuan rupiah ·
          kolom bertanda <em>rencana</em> adalah perkiraan triwulan berikutnya
        </p>
      </div>
      <div class="kd-body">
        {{-- Three different reasons for an empty Blok III, and they mean very
             different things to a petugas. hasProyek() alone conflated them:
             it is also false while the preamble is still blank, so an untouched
             submission was reported as "menyatakan tidak ada proyek". --}}
        @if($response->stoppedAtStatus())
          <p class="kd-warn">Pengisian berhenti di Updating Status Perusahaan, sehingga Blok III tidak berlaku.</p>
        @elseif(!$response->proyekBlokApplies())
          <p class="kd-warn">Responden menyatakan <b>tidak ada proyek konstruksi</b> pada triwulan ini, sehingga Blok III tidak diisi.</p>
        @elseif($proyekTwRows === [])
          <p class="kd-empty">Belum ada proyek yang diisi.</p>
        @else
          <div class="kd-scroll">
            <table class="kd-table kd-wide">
              <thead>
                <tr>
                  <th rowspan="2">No</th>
                  <th rowspan="2">Nama Pekerjaan / KBLI (2)</th>
                  <th rowspan="2">Lokasi (3)</th>
                  <th rowspan="2">Sumber Dana (4)</th>
                  <th rowspan="2">Nilai Kontrak (5)</th>
                  <th rowspan="2">% Disubkon (6)</th>
                  <th rowspan="2">Mulai (7)</th>
                  <th rowspan="2">Selesai (8)</th>
                  <th colspan="4">% Kumulatif Realisasi Fisik s/d</th>
                  <th colspan="2">Upah Pekerja Harian</th>
                  <th colspan="2">Rata-rata Upah / Orang / Hari</th>
                  <th rowspan="2">Nilai Konstruksi Triwulan Ini</th>
                </tr>
                <tr>
                  <th>{{ $quarterLabels['realisasi_2lalu']['short'] ?? '' }} (9)</th>
                  <th>{{ $quarterLabels['realisasi_lalu']['short'] ?? '' }} (10)</th>
                  <th>{{ $quarterLabels['realisasi_ini']['short'] ?? '' }} (11)</th>
                  <th class="kd-rencana">{{ $quarterLabels['realisasi_depan']['short'] ?? '' }} (12) rencana</th>
                  <th>{{ $quarterLabels['upah_lalu']['short'] ?? '' }} (13)</th>
                  <th>{{ $quarterLabels['upah_ini']['short'] ?? '' }} (14)</th>
                  <th>{{ $quarterLabels['upah_rata_lalu']['short'] ?? '' }} (15)</th>
                  <th>{{ $quarterLabels['upah_rata_ini']['short'] ?? '' }} (16)</th>
                </tr>
              </thead>
              <tbody>
                @foreach($proyekTwRows as $i => $row)
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
                    <td class="num">{{ SibstrFormat::idr($row['nilai_kontrak'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::pct($row['pct_subkontrak'] ?? null) }}</td>
                    <td>{{ $dash($row['mulai'] ?? '') }}</td>
                    <td>{{ $dash($row['selesai'] ?? '') }}</td>
                    <td class="num">{{ SibstrFormat::pct($row['realisasi_2lalu'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::pct($row['realisasi_lalu'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::pct($row['realisasi_ini'] ?? null) }}</td>
                    <td class="num kd-rencana">{{ SibstrFormat::pct($row['realisasi_depan'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::idr($row['upah_lalu'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::idr($row['upah_ini'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::idr($row['upah_rata_lalu'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::idr($row['upah_rata_ini'] ?? null) }}</td>
                    <td class="num">{{ SibstrFormat::idr(K::nilaiKonstruksiTw($row)) }}</td>
                  </tr>
                @endforeach
                <tr class="total">
                  <td colspan="4" style="text-align:right;">JUMLAH</td>
                  <td class="num">{{ SibstrFormat::idr($proyekTwTotal['nilai_kontrak']) }}</td>
                  <td colspan="7"></td>
                  <td class="num">{{ SibstrFormat::idr($proyekTwTotal['upah_lalu']) }}</td>
                  <td class="num">{{ SibstrFormat::idr($proyekTwTotal['upah_ini']) }}</td>
                  <td colspan="2"></td>
                  <td class="num">{{ SibstrFormat::idr($proyekTwTotal['nilai_konstruksi']) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p style="margin-top:.7rem;font-size:.75rem;color:#6b7280;">
            Nilai konstruksi triwulan ini dihitung sebagai
            <code>nilai kontrak × (1 − % disubkontrakkan) × (kolom 11 − kolom 10) ÷ 100</code>;
            SKTR tidak mencetak kolom ini.
          </p>
        @endif
      </div>
    </div>
  @endif

  {{-- Penutup --}}
  <div class="kd-card">
    <div class="kd-head"><h2>Penutup — Metode Pendataan, Catatan &amp; Pemberi Jawaban</h2></div>
    <div class="kd-body">
      <dl class="kd-kv">
        <div><dt>Metode Pendataan Tahun {{ (int) $response->tahun + 1 }}</dt><dd>{{ K::METODE_PENDATAAN[$response->metode_pendataan] ?? '—' }}</dd></div>
        <div><dt>Nama Pemberi Jawaban</dt><dd>{{ $dash($response->pj_nama) }}</dd></div>
        <div><dt>Jabatan</dt><dd>{{ $dash($response->pj_jabatan) }}</dd></div>
        <div><dt>Nomor Telepon / HP</dt><dd>{{ $dash($response->pj_hp) }}</dd></div>
        <div><dt>E-mail</dt><dd>{{ $dash($response->pj_email) }}</dd></div>
        <div><dt>Akun Pengisi</dt><dd>{{ $responden->name ?? '—' }}<br><span style="font-weight:400;font-size:.75rem;color:#6b7280;">{{ $responden->email ?? '' }}</span></dd></div>
        <div style="grid-column:1/-1;"><dt>Catatan</dt><dd style="white-space:pre-wrap;">{{ $dash($response->catatan) }}</dd></div>
      </dl>
    </div>
  </div>

</div>

@include('bps.partials.delete-survey-modal')
@endsection
