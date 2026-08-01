<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\UbSurveyResponse;
use Illuminate\Http\Request;

/**
 * Statistik UB — BPS-only dashboard over Survei Usaha/Perusahaan (SE2026-L.UB).
 *
 * Same architecture as the SIBSTR and Listrik statistik dashboards: the
 * controller flattens every respondent into one embedded JSON payload and the
 * page filters/aggregates client-side, so every control responds instantly.
 *
 * UB is an annual snapshot (one row per user per tahun), not a monthly or
 * quarterly series — the derived metrics below are therefore per-usaha totals.
 */
class StatistikUbController extends Controller
{
    /** Kategori lapangan usaha (KBLI 2020, kategori A–U). */
    private const KATEGORI = [
        'A' => 'Pertanian, Kehutanan dan Perikanan',
        'B' => 'Pertambangan dan Penggalian',
        'C' => 'Industri Pengolahan',
        'D' => 'Pengadaan Listrik, Gas, Uap dan Udara Dingin',
        'E' => 'Pengelolaan Air, Limbah dan Daur Ulang Sampah',
        'F' => 'Konstruksi',
        'G' => 'Perdagangan Besar dan Eceran; Reparasi Mobil dan Sepeda Motor',
        'H' => 'Pengangkutan dan Pergudangan',
        'I' => 'Penyediaan Akomodasi dan Makan Minum',
        'J' => 'Informasi dan Komunikasi',
        'K' => 'Aktivitas Keuangan dan Asuransi',
        'L' => 'Real Estat',
        'M' => 'Aktivitas Profesional, Ilmiah dan Teknis',
        'N' => 'Aktivitas Penyewaan, Ketenagakerjaan dan Penunjang Usaha',
        'O' => 'Administrasi Pemerintahan dan Jaminan Sosial Wajib',
        'P' => 'Pendidikan',
        'Q' => 'Aktivitas Kesehatan Manusia dan Aktivitas Sosial',
        'R' => 'Kesenian, Hiburan dan Rekreasi',
        'S' => 'Aktivitas Jasa Lainnya',
        'T' => 'Aktivitas Rumah Tangga sebagai Pemberi Kerja',
        'U' => 'Aktivitas Badan Internasional dan Badan Ekstra Internasional',
    ];

    /**
     * KBLI 2-digit ranges → kategori letter. Q9h is filled by BPS and is often
     * still blank while Q9g (kode KBLI) is already there, so the kategori is
     * derived from the KBLI code whenever the letter itself is missing.
     */
    private const KBLI_RANGES = [
        ['A', 1, 3], ['B', 5, 9], ['C', 10, 33], ['D', 35, 35], ['E', 36, 39],
        ['F', 41, 43], ['G', 45, 47], ['H', 49, 53], ['I', 55, 56], ['J', 58, 63],
        ['K', 64, 66], ['L', 68, 68], ['M', 69, 75], ['N', 77, 82], ['O', 84, 84],
        ['P', 85, 85], ['Q', 86, 88], ['R', 90, 93], ['S', 94, 96], ['T', 97, 98],
        ['U', 99, 99],
    ];

    private const BADAN_USAHA = [
        1 => 'Perseroan (PT/NV/Persero/Tbk)',
        2 => 'Yayasan',
        3 => 'Koperasi',
        4 => 'Dana Pensiun',
        5 => 'Perum/Perumda',
        6 => 'BUM Desa',
        7 => 'Persekutuan Komanditer (CV)',
        8 => 'Persekutuan Firma (Fa)',
        9 => 'Persekutuan Perdata (Maatschap)',
        10 => 'Kantor Perwakilan Luar Negeri',
        11 => 'Badan Usaha Luar Negeri',
        12 => 'Badan Usaha Lainnya (BLU, PTN-BH dll)',
        13 => 'Bukan Badan Usaha',
    ];

    private const RANGE_ASET = [
        1 => 's.d. Rp 500 juta',
        2 => '> Rp 500 juta s.d. Rp 1 miliar',
        3 => '> Rp 1 miliar s.d. Rp 5 miliar',
        4 => '> Rp 5 miliar s.d. Rp 10 miliar',
        5 => '> Rp 10 miliar',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    public function index(Request $request)
    {
        $rows = UbSurveyResponse::with('user:id,name,email')
            ->orderBy('updated_at', 'desc')
            ->get();

        $years = $rows->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->unique()
            ->sortDesc()
            ->values();

        $payload = [
            'years'       => $years->isEmpty() ? [(int) now()->year] : $years->all(),
            'kategori'    => self::KATEGORI,
            'rangeAset'   => self::RANGE_ASET,
            'generatedAt' => now()->locale('id')->translatedFormat('j F Y H.i'),
            'rows'        => $rows->map(fn ($r) => $this->buildRow($r))->values()->all(),
        ];

        return view('bps.statistik.ub', [
            'user'    => $request->user(),
            'payload' => $payload,
        ]);
    }

    /**
     * Flatten one annual UB response into the dashboard row shape.
     */
    private function buildRow(UbSurveyResponse $r): array
    {
        $kat      = $this->kategoriOf($r);
        $pekerjaL = $this->int($r->pekerja_laki);
        $pekerjaP = $this->int($r->pekerja_perempuan);
        $pekerjaSum = $this->sumOrNull([$pekerjaL, $pekerjaP]);
        $pekerja  = $pekerjaSum !== null ? (int) $pekerjaSum : $this->int($r->total_pekerja);

        // ── Blok I-D: pengeluaran ──
        $upah        = $this->num($r->pengeluaran_upah_gaji);
        $biayaProd   = $this->num($r->pengeluaran_biaya_produksi);
        $pembelian   = $this->num($r->pengeluaran_pembelian_barang);
        $operasional = $this->num($r->pengeluaran_operasional);
        $nonOps      = $this->num($r->pengeluaran_nonoperasional);
        // Totals are only stamped when Blok I-D is finalised, so drafts fall
        // back to the component sum (and vice versa for legacy rows).
        $pengeluaran = $this->sumOrNull([$upah, $biayaProd, $pembelian, $operasional, $nonOps])
            ?? $this->num($r->total_pengeluaran);

        // ── Blok I-D: pendapatan / produksi ──
        $produksiBJ  = $this->num($r->nilai_produksi_barang_jasa);
        $pendLainnya = $this->num($r->pendapatan_lainnya);
        $produksi    = $this->sumOrNull([$produksiBJ, $pendLainnya])
            ?? $this->num($r->total_nilai_produksi);

        $surplus = ($produksi !== null && $pengeluaran !== null) ? $produksi - $pengeluaran : null;

        // ── Blok I-D: aset ──
        $asetTB    = $this->num($r->nilai_aset_tanah_bangunan);
        $asetLain  = $this->num($r->nilai_aset_lainnya);
        $asetTotal = $this->sumOrNull([$asetTB, $asetLain]) ?? $this->num($r->nilai_total_aset);

        $rangeAset = $r->range_total_aset !== null ? (int) $r->range_total_aset : $this->rangeOf($asetTotal);

        return [
            'id'         => $r->id,
            'uid'        => $r->user_id,
            'tahun'      => (int) $r->tahun,
            'perusahaan' => $r->nama_perusahaan ?: ($r->user->name ?? 'Tanpa nama'),
            'komersial'  => $r->nama_komersial,
            'kabupaten'  => $r->kabupaten_kota,
            'kecamatan'  => $r->kecamatan,
            'kbli'       => $r->kode_kbli,
            'kat'        => $kat ?? '—',
            'katLabel'   => $kat ? ($kat . ' · ' . (self::KATEGORI[$kat] ?? 'Kategori ' . $kat)) : 'Belum dikategorikan',
            'badanUsaha' => $r->status_badan_usaha !== null
                ? (self::BADAN_USAHA[(int) $r->status_badan_usaha] ?? 'Badan usaha lainnya')
                : 'Belum diisi',
            'kegiatan'   => $r->kegiatan_utama,
            'produk'     => $r->produk_utama,
            'selesai'    => (bool) $r->is_completed,
            'progress'   => $r->completionPercent(),
            'updatedAt'  => optional($r->updated_at)->locale('id')->translatedFormat('j M Y'),
            'updatedTs'  => optional($r->updated_at)->getTimestamp(),

            'pekerjaL'     => $pekerjaL,
            'pekerjaP'     => $pekerjaP,
            'pekerja'      => $pekerja,
            'tahunOperasi' => $this->int($r->tahun_beroperasi),

            'upah'           => $upah,
            'biayaProduksi'  => $biayaProd,
            'pembelian'      => $pembelian,
            'operasional'    => $operasional,
            'nonOperasional' => $nonOps,
            'pengeluaran'    => $pengeluaran,

            'produksiBarangJasa' => $produksiBJ,
            'pendapatanLainnya'  => $pendLainnya,
            'produksi'           => $produksi,
            'surplus'            => $surplus,
            'persenOnline'       => $this->num($r->persen_pendapatan_online),

            'asetTanahBangunan' => $asetTB,
            'asetLainnya'       => $asetLain,
            'aset'              => $asetTotal,
            'rangeAset'         => $rangeAset,
            'luasTanah'         => $this->num($r->luas_tanah),

            'modal' => [
                'pribadi'            => $this->num($r->modal_pribadi),
                'nonprofit'          => $this->num($r->modal_nonprofit),
                'korporasiPublik'    => $this->num($r->modal_korporasi_publik),
                'korporasiNonPublik' => $this->num($r->modal_korporasi_nonpublik),
                'pemerintah'         => $this->num($r->modal_pemerintah),
                'asing'              => $this->num($r->modal_asing),
            ],

            // Yes/no profile indicators. null = pertanyaan belum dijawab, which
            // must stay distinct from "tidak" or the percentages lie.
            'flags' => [
                'nib'             => $this->yn($r->has_nib),
                'laporanKeuangan' => $this->yn($r->has_laporan_keuangan),
                'internet'        => $this->yn($r->uses_internet),
                'digital'         => $this->yn($r->uses_teknologi_digital),
                'halal'           => $r->sertifikat_halal === null ? null : ((int) $r->sertifikat_halal === 1),
                'izinEdar'        => $r->izin_edar === null ? null : ((int) $r->izin_edar === 1),
                'eksporBarang'    => $this->yn($r->ekspor_impor_barang),
                'eksporJasa'      => $this->yn($r->ekspor_impor_jasa),
                'ramahLingkungan' => $r->produk_ramah_lingkungan === null ? null : ((int) $r->produk_ramah_lingkungan !== 3),
                'inputLingkungan' => $this->yn($r->uses_input_lingkungan),
                'karyaSeni'       => $this->yn($r->uses_karya_seni),
                'mitraKdkmp'      => $this->yn($r->bermitra_kdkmp),
                'mbg'             => $r->terlibat_mbg === null ? null : ((int) $r->terlibat_mbg !== 5),
            ],

            'pengusaha' => [
                'nama' => $r->nama_pengusaha,
                'jk'   => $r->jenis_kelamin !== null ? ((int) $r->jenis_kelamin === 1 ? 'Laki-laki' : 'Perempuan') : null,
                'umur' => $this->int($r->umur),
            ],

            'catatan' => $r->catatan,
        ];
    }

    /** Q9h when BPS filled it, otherwise derived from the KBLI code (Q9g). */
    private function kategoriOf(UbSurveyResponse $r): ?string
    {
        $kat = strtoupper(trim((string) $r->kategori_lapangan_usaha));
        if ($kat !== '' && isset(self::KATEGORI[$kat])) {
            return $kat;
        }

        $kbli = preg_replace('/\D/', '', (string) $r->kode_kbli);
        if ($kbli === '' || strlen($kbli) < 2) {
            return null;
        }
        $two = (int) substr($kbli, 0, 2);
        foreach (self::KBLI_RANGES as [$letter, $from, $to]) {
            if ($two >= $from && $two <= $to) {
                return $letter;
            }
        }
        return null;
    }

    /** Asset band (Q24c1) derived from the nominal total when unanswered. */
    private function rangeOf(?float $aset): ?int
    {
        if ($aset === null) {
            return null;
        }
        if ($aset <= 500_000_000) return 1;
        if ($aset <= 1_000_000_000) return 2;
        if ($aset <= 5_000_000_000) return 3;
        if ($aset <= 10_000_000_000) return 4;
        return 5;
    }

    /** 1 = Ya, 2 = Tidak; anything else means the question is unanswered. */
    private function yn($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value === 1;
    }

    /**
     * Decimal columns come back as strings ("1250000.00"); "" and null mean
     * "belum diisi" and must stay null (0 is a legitimate answer).
     */
    private function num($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    private function int($value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        return (int) $value;
    }

    /** Sum ignoring nulls; null when every part is null (nothing reported). */
    private function sumOrNull(array $parts): ?float
    {
        $filled = array_filter($parts, fn ($v) => $v !== null);
        return $filled === [] ? null : (float) array_sum($filled);
    }
}
