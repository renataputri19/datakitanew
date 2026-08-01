<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

/**
 * SIBSTR Triwulanan statistics dashboard (BPS/admin only).
 *
 * Flattens each respondent's quarterly SurveyResponse into a compact
 * row (identity + derived financial metrics + Blok V sentiment) and
 * embeds the whole payload in the view; all filtering/aggregation then
 * happens client-side so the dashboard responds instantly.
 */
class StatistikController extends Controller
{
    /**
     * Nama golongan pokok KBLI (2 digit) yang relevan dengan responden SIBSTR.
     * Kode di luar peta ini tampil sebagai "KBLI XX".
     */
    private const KBLI_GROUP_NAMES = [
        '10' => 'Industri Makanan',
        '11' => 'Industri Minuman',
        '12' => 'Industri Pengolahan Tembakau',
        '13' => 'Industri Tekstil',
        '14' => 'Industri Pakaian Jadi',
        '15' => 'Industri Kulit dan Alas Kaki',
        '16' => 'Industri Kayu dan Gabus',
        '17' => 'Industri Kertas',
        '18' => 'Industri Pencetakan dan Media Rekaman',
        '19' => 'Industri Batu Bara dan Pengilangan Migas',
        '20' => 'Industri Bahan Kimia',
        '21' => 'Industri Farmasi dan Obat',
        '22' => 'Industri Karet dan Plastik',
        '23' => 'Industri Galian Bukan Logam',
        '24' => 'Industri Logam Dasar',
        '25' => 'Industri Barang Logam',
        '26' => 'Industri Komputer, Elektronik dan Optik',
        '27' => 'Industri Peralatan Listrik',
        '28' => 'Industri Mesin dan Perlengkapan',
        '29' => 'Industri Kendaraan Bermotor',
        '30' => 'Industri Alat Angkutan Lainnya',
        '31' => 'Industri Furnitur',
        '32' => 'Industri Pengolahan Lainnya',
        '33' => 'Reparasi dan Pemasangan Mesin',
        '35' => 'Pengadaan Listrik dan Gas',
        '43' => 'Konstruksi Khusus',
        '47' => 'Perdagangan Eceran',
        '52' => 'Pergudangan dan Penunjang Angkutan',
        '58' => 'Aktivitas Penerbitan',
        '68' => 'Real Estat',
        '74' => 'Aktivitas Profesional Lainnya',
        '77' => 'Penyewaan dan Sewa Guna',
        '85' => 'Pendidikan',
        '91' => 'Perpustakaan, Arsip dan Museum',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    public function index(Request $request)
    {
        $availableYears = SurveyResponse::where('survey_type', 'sibstr')
            ->where('triwulan', '>', 0)
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->values();

        $tahun = (int) $request->input('tahun', $availableYears->first() ?? now()->year);

        // Latest record per (user, triwulan) — UUID ids are not orderable,
        // so dedupe on updated_at instead of MAX(id).
        $rows = SurveyResponse::with('user:id,name,email')
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', '>', 0)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique(fn ($r) => $r->user_id . '|' . $r->triwulan)
            ->values();

        $payload = [
            'tahun'          => $tahun,
            'availableYears' => $availableYears->isEmpty() ? [$tahun] : $availableYears->all(),
            'quarters'       => $rows->pluck('triwulan')->unique()->sort()->values()->all(),
            'openQuarters'   => SurveyResponse::availableTriwulan($tahun),
            'generatedAt'    => now()->locale('id')->translatedFormat('j F Y H.i'),
            'rows'           => $rows->map(fn ($r) => $this->buildRow($r))->all(),
        ];

        return view('bps.statistik.index', [
            'user'    => $request->user(),
            'payload' => $payload,
        ]);
    }

    /**
     * Flatten one quarterly SurveyResponse into the dashboard row shape.
     */
    private function buildRow(SurveyResponse $r): array
    {
        $tahun    = (int) $r->tahun;
        $triwulan = (int) $r->triwulan;

        // Month keys of this quarter + the December bridge month the form carries.
        $quarterMonths = array_keys(SurveyResponse::triwulanMonthKeys($tahun, $triwulan));
        $bridgeKey     = ($tahun - 1) . '_des';

        $kbli   = $r->kbli_utama ? preg_replace('/\D/', '', $r->kbli_utama) : null;
        $kbli2  = $kbli && strlen($kbli) >= 2 ? substr($kbli, 0, 2) : null;
        $industri = $r->isKbliIndustri();

        // ── Blok IIIA (industri): production revenue per month ──
        $rawProducts = $r->getAttributes()['blok3a_products'] ?? null;
        $products    = $rawProducts ? json_decode($rawProducts, true) : [];
        $products    = is_array($products) ? $products : [];

        $rawLainnya = $r->getAttributes()['blok3a_lainnya'] ?? null;
        $lainnya    = $rawLainnya ? json_decode($rawLainnya, true) : [];
        $lainnyaNilai = is_array($lainnya['nilai'] ?? null) ? $lainnya['nilai'] : [];

        $produkPerMonth  = [];   // Σ product nilai per month (301)
        $productDetails  = [];
        foreach ($products as $p) {
            $detail = [
                'jenis'  => (string) ($p['jenis_barang'] ?? ''),
                'satuan' => (string) ($p['satuan'] ?? ''),
                'banyak' => [],
                'nilai'  => [],
            ];
            foreach (array_merge([$bridgeKey], $quarterMonths) as $m) {
                $detail['banyak'][$m] = $this->toNum($p['banyaknya'][$m] ?? null);
                $detail['nilai'][$m]  = $this->toNum($p['nilai'][$m] ?? null);
            }
            foreach ($quarterMonths as $m) {
                $v = $this->toNum($p['nilai'][$m] ?? null);
                if ($v !== null) {
                    $produkPerMonth[$m] = ($produkPerMonth[$m] ?? 0) + $v;
                }
            }
            $productDetails[] = $detail;
        }

        $lainnyaPerMonth = [];
        foreach ($quarterMonths as $m) {
            $v = $this->toNum($lainnyaNilai[$m] ?? null);
            if ($v !== null) {
                $lainnyaPerMonth[$m] = $v;
            }
        }

        // Monthly total (301 + 302) incl. bridge month, for the trend chart.
        $monthlyNilai = [];
        foreach (array_merge([$bridgeKey], $quarterMonths) as $m) {
            $prod = null;
            foreach ($products as $p) {
                $v = $this->toNum($p['nilai'][$m] ?? null);
                if ($v !== null) {
                    $prod = ($prod ?? 0) + $v;
                }
            }
            $lain = $this->toNum($lainnyaNilai[$m] ?? null);
            $monthlyNilai[$m] = ($prod === null && $lain === null) ? null : (float) ($prod ?? 0) + (float) ($lain ?? 0);
        }

        $pendapatanProduk  = $this->sumOrNull(array_values($produkPerMonth));
        $pendapatanLainnya = $this->sumOrNull(array_values($lainnyaPerMonth));

        // ── Blok IIIB: the sektor decides which JSON blob applies ──
        $b3b = $industri
            ? (is_array($r->blok3b_industri_data) ? $r->blok3b_industri_data : [])
            : (is_array($r->blok3b_nonindustri_data) ? $r->blok3b_nonindustri_data : []);

        $num = fn (string $key) => $this->toNum($b3b[$key] ?? null);

        $royalti = $num('q304');
        if ($industri) {
            $penjualan  = null; // industri: sales come from Blok IIIA
            $upah       = $num('q310');
            $capex      = $num('q311');
        } else {
            $penjualan  = $num('q303');
            $upah       = $num('q310_tw');
            $capex      = $num('q311_tw');
        }
        $biayaProduksi    = $num('q312_tw');
        $biayaOperasional = $num('q313_tw');

        $pendapatanTotal = $industri
            ? $this->sumOrNull([$pendapatanProduk, $pendapatanLainnya, $royalti])
            : $this->sumOrNull([$penjualan, $royalti]);

        $pengeluaranTotal = $this->sumOrNull([$upah, $biayaProduksi, $biayaOperasional]);

        $surplus = ($pendapatanTotal !== null && $pengeluaranTotal !== null)
            ? $pendapatanTotal - $pengeluaranTotal
            : null;

        // ── Kategori C metrics (definisi revisi) ──
        // Nilai Produksi = output Blok IIIA (301 + 302); non-industri: penjualan (q303).
        // Biaya Produksi = biaya produksi + biaya operasional + pembelian aset (q311).
        // Nilai Tambah   = Nilai Produksi − Biaya Produksi.
        $nilaiProduksi = $industri
            ? $this->sumOrNull([$pendapatanProduk, $pendapatanLainnya])
            : $penjualan;
        $biayaTotal = $this->sumOrNull([$biayaProduksi, $biayaOperasional, $capex]);
        $nilaiTambah = ($nilaiProduksi !== null && $biayaTotal !== null)
            ? $nilaiProduksi - $biayaTotal
            : null;

        // ── Blok V sentiment (kondisi p1 / prospek p2) ──
        $blok5 = [];
        $b5 = is_array($r->blok5_data) ? $r->blok5_data : [];
        foreach (['501', '502', '503', '504', '505', '506', '507'] as $k) {
            $blok5[$k] = [
                'p1' => $b5[$k]['p1'] ?? null,
                'p2' => $b5[$k]['p2'] ?? null,
            ];
        }

        return [
            'id'         => $r->id,
            'uid'        => $r->user_id,
            'triwulan'   => $triwulan,
            'perusahaan' => $r->nama_perusahaan ?: ($r->user->name ?? 'Tanpa nama'),
            'kabupaten'  => $r->kabupaten_kota,
            'kip'        => $r->kip,
            'kbli'       => $kbli,
            'kbli2'      => $kbli2,
            'kbliGroup'  => $kbli2 ? ($kbli2 . ' · ' . (self::KBLI_GROUP_NAMES[$kbli2] ?? 'KBLI ' . $kbli2)) : 'Belum mengisi KBLI',
            'sektor'     => $industri ? 'industri' : 'nonindustri',
            'kegiatan'   => $r->kegiatan_utama_perusahaan,
            'kondisi'    => $r->kondisi_perusahaan,
            'selesai'    => (bool) $r->is_completed,
            'updatedAt'  => optional($r->updated_at)->locale('id')->translatedFormat('j M Y'),
            // sortable twin of updatedAt — the formatted string sorts wrong
            'updatedTs'  => optional($r->updated_at)->getTimestamp(),

            'tenagaKerja' => $r->rata_rata_tenaga_kerja !== null ? (int) $r->rata_rata_tenaga_kerja : null,

            'pendapatanProduk'  => $pendapatanProduk,
            'pendapatanLainnya' => $pendapatanLainnya,
            'pendapatanRoyalti' => $royalti,
            'penjualan'         => $penjualan,
            'pendapatanTotal'   => $pendapatanTotal,

            'upah'              => $upah,
            'biayaProduksi'     => $biayaProduksi,
            'biayaOperasional'  => $biayaOperasional,
            'pengeluaranTotal'  => $pengeluaranTotal,
            'surplus'           => $surplus,
            'capex'             => $capex,

            'nilaiProduksi' => $nilaiProduksi,
            'biayaTotal'    => $biayaTotal,
            'nilaiTambah'   => $nilaiTambah,

            'persediaanAwal'  => $num('q309_awal'),
            'persediaanAkhir' => $num('q309_akhir'),
            'eksporPct'       => $num('q314_tw'),
            'imporPct'        => $num('q315_tw'),

            'monthlyNilai' => $monthlyNilai,
            'products'     => $productDetails,
            'blok5'        => $blok5,
            'catatan'      => is_array($r->blok6_data) ? ($r->blok6_data['catatan'] ?? null) : null,
        ];
    }

    /**
     * "1.00" / 1 / "" / null → float|null. Empty string and null mean
     * "not filled in" and must stay null (0 is a legitimate answer).
     */
    private function toNum($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
            if (!is_numeric($value)) {
                return null;
            }
        }
        return (float) $value;
    }

    /**
     * Sum ignoring nulls; null when every part is null (nothing reported).
     */
    private function sumOrNull(array $parts): ?float
    {
        $filled = array_filter($parts, fn ($v) => $v !== null);
        return $filled === [] ? null : (float) array_sum($filled);
    }
}
