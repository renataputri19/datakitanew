@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok IX: Ringkasan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Support\SibstrFormat;
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok IX — Ringkasan Pengeluaran & Pendapatan',
  'subtitle' => 'Selama tahun ' . $tahun . ' · dihitung otomatis',
])

<div class="k-card">
  <p class="k-card-title">Ringkasan</p>
  <p class="k-note">
    Blok ini <b>tidak diisi</b> — seluruh angkanya dihitung dari Blok II, IV, VI, VII dan VIII.
    Periksa apakah nilainya wajar; bila ada yang keliru, perbaiki di blok asalnya dan angka di sini
    akan ikut berubah.
  </p>

  <div class="k-scroll">
    <table class="k-table">
      <thead>
        <tr>
          <th style="text-align:left;">Rincian (1)</th>
          <th style="width:16rem;">Nilai (000 Rp) (2)</th>
        </tr>
      </thead>
      <tbody>
        <tr class="k-total-row"><td colspan="2">A. RINCIAN PENGELUARAN</td></tr>
        <tr>
          <td>
            1. Balas Jasa dan Upah Pekerja
            <div class="k-hint">Blok II rincian B (JUMLAH) + Blok VI kolom (15) (JUMLAH)</div>
          </td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['pengeluaran']['balas_jasa']) }}</td>
        </tr>
        <tr>
          <td>
            2. Nilai Pemakaian Bahan/Material yang Digunakan
            <div class="k-hint">Blok VI kolom (13) + (14) (JUMLAH)</div>
          </td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['pengeluaran']['bahan']) }}</td>
        </tr>
        <tr>
          <td>
            3. Pengeluaran Operasional
            <div class="k-hint">Blok IV (JUMLAH)</div>
          </td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['pengeluaran']['operasional']) }}</td>
        </tr>
        <tr class="k-total-row">
          <td>JUMLAH A [ 1 + 2 + 3 ]</td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['total_a']) }}</td>
        </tr>

        <tr class="k-total-row"><td colspan="2">B. RINCIAN PENDAPATAN</td></tr>
        <tr>
          <td>
            1. Nilai Konstruksi yang Diselesaikan
            <div class="k-hint">Blok VI kolom (17) (JUMLAH)</div>
          </td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['pendapatan']['nilai_konstruksi']) }}</td>
        </tr>
        <tr>
          <td>
            2. Nilai dari Pendapatan Lain
            <div class="k-hint">Blok VIII (JUMLAH)</div>
          </td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['pendapatan']['pendapatan_lain']) }}</td>
        </tr>
        <tr class="k-total-row">
          <td>JUMLAH B [ 1 + 2 ]</td>
          <td class="k-readonly">{{ SibstrFormat::idr($ringkasan['total_b']) }}</td>
        </tr>

        <tr class="k-total-row">
          <td>C. Persentase Keuntungan Terhadap Pengeluaran &nbsp; <span style="font-weight:400;">(B/A − 1) × 100 %</span></td>
          <td class="k-readonly">
            {{ $ringkasan['margin_pct'] === null ? '—' : number_format($ringkasan['margin_pct'], 2, ',', '.') . ' %' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  @php
    $sumber = [
      'Blok II'   => ['blok2', $response->isBlok2Complete()],
      'Blok IV'   => ['blok4', $response->isBlok4Complete()],
      'Blok VI'   => ['blok6', $response->isBlok6Complete()],
      'Blok VII'  => ['blok7', $response->isBlok7Complete()],
      'Blok VIII' => ['blok8', $response->isBlok8Complete()],
    ];
    $kurang = array_filter($sumber, fn ($s) => !$s[1]);
  @endphp

  @if($kurang)
    <p class="k-note" style="margin-top:1rem;margin-bottom:0;border-left-color:var(--k-danger);">
      Angka di atas belum lengkap karena blok sumbernya masih kosong:
      @foreach($kurang as $label => $s)
        <a href="{{ route('survey.konstruksi.' . $s[0], $routeParams) }}">{{ $label }}</a>@if(!$loop->last), @endif
      @endforeach.
    </p>
  @endif
</div>

<div class="k-actions">
  <a href="{{ route('survey.konstruksi.blok8', $routeParams) }}" class="k-btn k-btn-ghost">&larr; Blok sebelumnya</a>
  <a href="{{ route('survey.konstruksi.blok10', $routeParams) }}" class="k-btn k-btn-primary">Lanjut ke Blok X &rarr;</a>
</div>

@include('survey.konstruksi.partials.page-close')
@endsection
