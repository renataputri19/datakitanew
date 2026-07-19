@extends('layouts.bps')

@section('title', 'Detail Survei Listrik – BPS Dashboard')
@section('description', 'Detail respons Survei Listrik dalam mode tampilan')

@php
    use App\Models\ListrikSurveyResponse;
    $cats = ListrikSurveyResponse::CATEGORIES;
    $fmt  = fn ($v) => number_format((float) $v, 0, ',', '.');
@endphp

@push('styles')
<style>
.lst-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,.05);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.dark .lst-card { background: #1f2937; border-color: #374151; }
.lst-card-head {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    display: flex; align-items: center; gap: .625rem;
}
.dark .lst-card-head { background: #111827; border-color: #374151; }
.lst-card-title { font-size: .95rem; font-weight: 700; color: #111827; }
.dark .lst-card-title { color: #f9fafb; }
.lst-card-body { padding: 1.25rem; }

.lst-kv { width: 100%; border-collapse: collapse; }
.lst-kv td { padding: .5rem .25rem; border-bottom: 1px solid #f3f4f6; font-size: .875rem; vertical-align: top; }
.dark .lst-kv td { border-color: #374151; }
.lst-kv tr:last-child td { border-bottom: none; }
.lst-kv .lbl { color: #6b7280; width: 38%; }
.dark .lst-kv .lbl { color: #9ca3af; }
.lst-kv .val { color: #111827; font-weight: 600; }
.dark .lst-kv .val { color: #f3f4f6; }

.lst-quarter { border: 1px solid #e5e7eb; border-radius: .625rem; margin-bottom: .75rem; overflow: hidden; }
.dark .lst-quarter { border-color: #374151; }
.lst-quarter > summary {
    padding: .75rem 1rem;
    background: #f9fafb;
    cursor: pointer;
    font-weight: 700; font-size: .875rem; color: #111827;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    list-style: none;
}
.lst-quarter > summary::-webkit-details-marker { display: none; }
.lst-quarter > summary::after { content: '▾'; color: #6b7280; font-size: .9rem; }
.lst-quarter[open] > summary::after { content: '▴'; }
.dark .lst-quarter > summary { background: #111827; color: #f9fafb; }
.lst-quarter-sum { font-weight: 500; font-size: .75rem; color: #6b7280; }
.dark .lst-quarter-sum { color: #9ca3af; }
.lst-quarter-body { padding: .875rem 1rem 1rem; overflow-x: auto; }

.lst-month-label {
    font-size: .8rem; font-weight: 700; color: #1e40af;
    margin: .875rem 0 .375rem;
}
.dark .lst-month-label { color: #93c5fd; }
.lst-month-label:first-child { margin-top: 0; }

.lst-grid { width: 100%; border-collapse: collapse; font-size: .75rem; min-width: 46rem; }
.lst-grid th, .lst-grid td { border: 1px solid #e5e7eb; padding: .3rem .45rem; }
.dark .lst-grid th, .dark .lst-grid td { border-color: #374151; }
.lst-grid thead th {
    background: #eff6ff; color: #1e3a8a; font-weight: 600; text-align: center; white-space: nowrap;
}
.dark .lst-grid thead th { background: #1e3a5f; color: #bfdbfe; }
.lst-grid td { text-align: right; color: #374151; white-space: nowrap; }
.dark .lst-grid td { color: #d1d5db; }
.lst-grid td.wil { text-align: left; font-weight: 600; color: #111827; white-space: normal; min-width: 9rem; }
.dark .lst-grid td.wil { color: #f3f4f6; }
.lst-grid tr.total td { background: #f9fafb; font-weight: 700; color: #111827; }
.dark .lst-grid tr.total td { background: #111827; color: #f9fafb; }
.lst-grid tr.qtotal td { background: #ecfdf5; font-weight: 700; color: #065f46; }
.dark .lst-grid tr.qtotal td { background: #064e3b; color: #a7f3d0; }
.lst-empty { font-size: .75rem; color: #9ca3af; font-style: italic; padding: .35rem 0; }

.bps-back-button {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem; border-radius: .5rem;
    background: #6b7280; color: #fff; font-size: .875rem; font-weight: 500;
    text-decoration: none;
}
.bps-back-button:hover { background: #4b5563; }
.lst-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.lst-btn-pdf {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem; border-radius: .5rem;
    background: #16a34a; color: #fff; font-size: .875rem; font-weight: 600;
    text-decoration: none;
}
.lst-btn-pdf:hover { background: #15803d; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Survei Listrik</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $response->nama_perusahaan ?: 'Tanpa nama perusahaan' }}
                &middot; {{ $response->user->name ?? '-' }}
            </p>
        </div>
        <div class="lst-actions">
            <a href="{{ route('bps.listrik.download', $response->id) }}" class="lst-btn-pdf">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Unduh PDF Data
            </a>
            <a href="{{ route('bps.listrik.index') }}" class="bps-back-button">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Daftar Survei Listrik
            </a>
        </div>
    </div>

    {{-- Status --}}
    <div class="lst-card">
        <div class="lst-card-body" style="display:flex; gap:1.5rem; flex-wrap:wrap; align-items:center;">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block">Status</span>
                @if($response->is_completed)
                    <span class="text-sm font-bold text-green-600 dark:text-green-400">Selesai</span>
                @else
                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400">Dalam Proses</span>
                @endif
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block">Kemajuan</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $response->completionPercent() }}%</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block">Terakhir Disimpan</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ $response->last_saved_at ? $response->last_saved_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '—' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Blok I --}}
    <div class="lst-card">
        <div class="lst-card-head">
            <span class="lst-card-title">Blok I — Identitas &amp; Lokasi</span>
        </div>
        <div class="lst-card-body">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8">
                <table class="lst-kv">
                    <tr><td class="lbl">Nama Perusahaan</td><td class="val">{{ $response->nama_perusahaan ?: '—' }}</td></tr>
                    <tr><td class="lbl">Nama Komersial</td><td class="val">{{ $response->nama_komersial ?: '—' }}</td></tr>
                    <tr><td class="lbl">Alamat</td><td class="val">{{ $response->alamat_perusahaan ?: '—' }}</td></tr>
                    <tr><td class="lbl">RT / RW</td><td class="val">{{ ($response->rt ?: '—') . ' / ' . ($response->rw ?: '—') }}</td></tr>
                    <tr><td class="lbl">Kelurahan / Desa</td><td class="val">{{ $response->kelurahan_desa ?: '—' }}</td></tr>
                    <tr><td class="lbl">Kecamatan</td><td class="val">{{ $response->kecamatan ?: '—' }}</td></tr>
                    <tr><td class="lbl">Kabupaten / Kota</td><td class="val">{{ $response->kabupaten_kota ?: '—' }}</td></tr>
                    <tr><td class="lbl">Provinsi</td><td class="val">{{ $response->provinsi ?: '—' }}</td></tr>
                    <tr><td class="lbl">Kode Pos</td><td class="val">{{ $response->kode_pos ?: '—' }}</td></tr>
                </table>
                <table class="lst-kv">
                    <tr><td class="lbl">Telepon</td><td class="val">{{ $response->nomor_telepon ?: '—' }}</td></tr>
                    <tr><td class="lbl">HP</td><td class="val">{{ $response->nomor_hp ?: '—' }}</td></tr>
                    <tr><td class="lbl">Email Perusahaan</td><td class="val">{{ $response->email_perusahaan ?: '—' }}</td></tr>
                    <tr><td class="lbl">Jenis Pembangkit</td><td class="val">{{ $response->jenis_pembangkit ?: '—' }}</td></tr>
                    <tr>
                        <td class="lbl">Daya Terpasang</td>
                        <td class="val">{{ $response->daya_terpasang_kw ? number_format((float) $response->daya_terpasang_kw, 2, ',', '.') . ' kW' : '—' }}</td>
                    </tr>
                    <tr><td class="lbl">Nama Pengusaha</td><td class="val">{{ $response->nama_pengusaha ?: '—' }}</td></tr>
                    <tr>
                        <td class="lbl">Jenis Kelamin</td>
                        <td class="val">{{ $response->jenis_kelamin == 1 ? 'Laki-laki' : ($response->jenis_kelamin == 2 ? 'Perempuan' : '—') }}</td>
                    </tr>
                    <tr><td class="lbl">Umur</td><td class="val">{{ $response->umur ? $response->umur . ' tahun' : '—' }}</td></tr>
                    <tr><td class="lbl">NIK</td><td class="val">{{ $response->nik ?: '—' }}</td></tr>
                    <tr><td class="lbl">Pengguna Sistem</td><td class="val">{{ $response->user->name ?? '—' }}<br><span style="font-weight:400;font-size:.75rem;color:#6b7280;">{{ $response->user->email ?? '' }}</span></td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Blok II — grouped per quarter --}}
    <div class="lst-card">
        <div class="lst-card-head">
            <span class="lst-card-title">Blok II — Produksi &amp; Nilai Produksi Listrik Bulanan</span>
        </div>
        <div class="lst-card-body">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                Data dikelompokkan per triwulan agar lebih mudah dibaca. Klik judul triwulan untuk membuka atau menutup rinciannya.
                Nilai KWH dalam satuan kWh, nilai produksi dalam rupiah.
            </p>

            @forelse($quarters as $qi => $q)
            <details class="lst-quarter" {{ $qi === 0 ? 'open' : '' }}>
                <summary>
                    <span>{{ $q['label'] }}</span>
                    <span class="lst-quarter-sum">
                        Total {{ $fmt($q['totals']['kwh']) }} kWh &middot; Rp {{ $fmt($q['totals']['rp']) }}
                    </span>
                </summary>
                <div class="lst-quarter-body">
                    @foreach($q['months'] as $m)
                        <div class="lst-month-label">{{ $m['label'] }}</div>
                        @if(count($m['rows']) === 0)
                            <div class="lst-empty">Belum ada data untuk bulan ini.</div>
                        @else
                        <table class="lst-grid">
                            <thead>
                                <tr>
                                    <th rowspan="2">Wilayah Tujuan</th>
                                    @foreach($cats as $label)
                                    <th colspan="2">{{ $label }}</th>
                                    @endforeach
                                    <th colspan="2">Jumlah</th>
                                </tr>
                                <tr>
                                    @foreach($cats as $label)
                                    <th>KWH</th><th>Rupiah</th>
                                    @endforeach
                                    <th>KWH</th><th>Rupiah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($m['rows'] as $row)
                                @php
                                    $rowKwh = collect($row['cells'])->sum('kwh');
                                    $rowRp  = collect($row['cells'])->sum('rp');
                                @endphp
                                <tr>
                                    <td class="wil">{{ $row['wilayah'] }}</td>
                                    @foreach(array_keys($cats) as $cat)
                                    <td>{{ $fmt($row['cells'][$cat]['kwh']) }}</td>
                                    <td>{{ $fmt($row['cells'][$cat]['rp']) }}</td>
                                    @endforeach
                                    <td>{{ $fmt($rowKwh) }}</td>
                                    <td>{{ $fmt($rowRp) }}</td>
                                </tr>
                                @endforeach
                                <tr class="total">
                                    <td class="wil">Total {{ $m['label'] }}</td>
                                    @foreach(array_keys($cats) as $cat)
                                    <td>{{ $fmt($m['totals'][$cat]['kwh']) }}</td>
                                    <td>{{ $fmt($m['totals'][$cat]['rp']) }}</td>
                                    @endforeach
                                    <td>{{ $fmt($m['totals']['kwh']) }}</td>
                                    <td>{{ $fmt($m['totals']['rp']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    @endforeach

                    {{-- Quarter subtotal --}}
                    <div class="lst-month-label">Subtotal {{ $q['label'] }}</div>
                    <table class="lst-grid">
                        <thead>
                            <tr>
                                <th rowspan="2">Periode</th>
                                @foreach($cats as $label)
                                <th colspan="2">{{ $label }}</th>
                                @endforeach
                                <th colspan="2">Jumlah</th>
                            </tr>
                            <tr>
                                @foreach($cats as $label)
                                <th>KWH</th><th>Rupiah</th>
                                @endforeach
                                <th>KWH</th><th>Rupiah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="qtotal">
                                <td class="wil">{{ $q['label'] }}</td>
                                @foreach(array_keys($cats) as $cat)
                                <td>{{ $fmt($q['totals'][$cat]['kwh']) }}</td>
                                <td>{{ $fmt($q['totals'][$cat]['rp']) }}</td>
                                @endforeach
                                <td>{{ $fmt($q['totals']['kwh']) }}</td>
                                <td>{{ $fmt($q['totals']['rp']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </details>
            @empty
                <div class="lst-empty">Belum ada data Blok II.</div>
            @endforelse
        </div>
    </div>

    {{-- Blok III --}}
    <div class="lst-card">
        <div class="lst-card-head">
            <span class="lst-card-title">Blok III — Catatan</span>
        </div>
        <div class="lst-card-body">
            <p class="text-sm text-gray-700 dark:text-gray-300" style="white-space:pre-wrap;">{{ $response->catatan ?: '—' }}</p>
        </div>
    </div>

    <div>
        <a href="{{ route('bps.listrik.index') }}" class="bps-back-button">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Daftar Survei Listrik
        </a>
    </div>
</div>
@endsection
