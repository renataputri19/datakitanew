@extends('layouts.user-dashboard')

@section('title', 'Survei Perusahaan Konstruksi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
<style>
  .k-period-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(15rem,1fr)); gap:1rem; }
  .k-period {
    border:1px solid var(--k-border); border-radius:.9rem; padding:1rem 1.1rem;
    background:var(--k-surface); display:flex; flex-direction:column; gap:.6rem;
  }
  .k-period.is-current { border-color:var(--k-accent); box-shadow:0 0 0 2px rgba(180,83,9,.15); }
  .k-period-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.5rem; }
  .k-period-head b { font-size:.95rem; color:var(--k-text); }
  .k-period-head small { display:block; font-size:.68rem; color:var(--k-muted); margin-top:.1rem; }
  .k-period-bar { height:.4rem; border-radius:999px; background:var(--k-row-alt); overflow:hidden; }
  .k-period-bar > div { height:100%; background:var(--k-accent); border-radius:999px; }
  .k-period-foot { display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
  .k-period-foot span { font-size:.72rem; color:var(--k-muted); }
</style>
@endpush

@section('dashboard-content')
<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Survei Perusahaan Konstruksi</h1>
    <p class="ud-page-description">
      SKTH (tahunan) dan SKTR (triwulanan) — pilih periode yang ingin diisi
    </p>
  </div>
</div>

@foreach(['error' => 'k-flash-error', 'info' => 'k-flash-info', 'success' => 'k-flash-ok'] as $key => $cls)
  @if(session($key))
    <div class="k-flash {{ $cls }}">{{ session($key) }}</div>
  @endif
@endforeach

<div class="k-hero">
  <h2>Periode Pengisian</h2>
  <p>
    Survei ini mengumpulkan data statistik sektor konstruksi untuk perencanaan pembangunan.
    Kerahasiaan data dijamin Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Pasal 21),
    dan survei dibiayai APBN — tidak ada biaya yang dibebankan kepada responden.
    Setiap isian tersimpan otomatis, sehingga pengisian dapat dijeda kapan saja.
  </p>
</div>

@php
  $groups = collect($periods)->groupBy('instrumen');
@endphp

@foreach(['SKTH' => 'Tahunan (SKTH)', 'SKTR' => 'Triwulanan (SKTR)'] as $code => $groupLabel)
  @if($groups->has($code))
    <div class="k-card">
      <p class="k-card-title">{{ $groupLabel }}</p>

      <div class="k-period-grid">
        @foreach($groups->get($code) as $p)
          <div class="k-period {{ $p['isCurrent'] ? 'is-current' : '' }}">
            <div class="k-period-head">
              <div>
                <b>{{ $p['label'] }}</b>
                <small>
                  {{ $p['instrumen'] }}
                  @if($p['isCurrent']) · triwulan berjalan @endif
                </small>
              </div>
              @if($p['completed'])
                <span class="k-chip k-chip-ok">Selesai</span>
              @elseif($p['response'])
                <span class="k-chip k-chip-no">{{ $p['percent'] }}%</span>
              @else
                <span class="k-chip k-chip-no">Belum mulai</span>
              @endif
            </div>

            <div class="k-period-bar"><div style="width: {{ $p['percent'] }}%"></div></div>

            <div class="k-period-foot">
              <span>
                @if($p['response']?->last_saved_at)
                  {{ $p['response']->last_saved_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}
                @else
                  &nbsp;
                @endif
              </span>
              @if($p['completed'])
                <span style="display:flex;gap:.4rem;">
                  <a href="{{ route('survey.konstruksi.pdf.download', $p['routeParams']) }}" class="k-btn k-btn-ghost k-btn-sm">PDF</a>
                  <form method="POST" action="{{ route('survey.konstruksi.start-edit', $p['routeParams']) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="k-btn k-btn-ghost k-btn-sm">Perbaiki</button>
                  </form>
                </span>
              @else
                <a href="{{ route('survey.konstruksi.' . $p['nextBlok'], $p['routeParams']) }}" class="k-btn k-btn-primary k-btn-sm">
                  {{ $p['response'] ? 'Lanjutkan' : 'Mulai isi' }} &rarr;
                </a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
@endforeach

<div class="k-card">
  <p class="k-card-title">Informasi Survei</p>
  <ul style="margin:0;padding-left:1.1rem;font-size:.82rem;line-height:1.75;color:var(--k-muted);">
    <li><b>Tujuan:</b> memperoleh data statistik yang dapat dipercaya dan tepat waktu untuk perencanaan pembangunan sektor konstruksi.</li>
    <li><b>Dasar hukum:</b> Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Pasal 11).</li>
    <li><b>Kerahasiaan:</b> dijamin Undang-Undang Nomor 16 Tahun 1997 (Pasal 21).</li>
    <li><b>Kewajiban responden:</b> Undang-Undang Nomor 16 Tahun 1997 (Pasal 27).</li>
    <li><b>Biaya:</b> dibiayai APBN; tidak ada biaya yang dibebankan kepada responden.</li>
  </ul>
</div>
@endsection
