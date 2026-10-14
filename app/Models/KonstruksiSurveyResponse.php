<?php

namespace App\Models;

use App\Support\KonstruksiBlokPath;
use App\Support\SibstrFormat;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Survei Perusahaan Konstruksi Tahunan (SKTH).
 *
 * Numbers arrive from the browser already formatted id-ID ("12.312.312"), the
 * same as every other survey here, so every read goes through
 * SibstrFormat::num() — never is_numeric() or (float).
 *
 * Blok IX is not stored: the questionnaire derives it from Blok II, IV, VI,
 * VII and VIII, so ringkasan() recomputes it on read and a later correction in
 * an upstream blok can never leave a stale summary behind.
 */
class KonstruksiSurveyResponse extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'konstruksi_survey_responses';

    /** Annual questionnaire currently in the field (SKTH-2025). */
    public const TAHUN_TAHUNAN = 2025;

    /** Quarterly questionnaire currently in the field (SKTR-2026, TW1–TW4). */
    public const TAHUN_TRIWULANAN = 2026;

    /** triwulan value that means "annual". */
    public const TAHUNAN = 0;

    /** Blok VI accepts at most this many project rows (form prints 13). */
    public const MAX_PROYEK = 13;

    /** Blok VII prints 10 named materials + "lainnya". */
    public const MAX_BAHAN = 11;

    /** Blok II — jenjang pendidikan rows, in questionnaire order. */
    public const PENDIDIKAN = [
        'smp' => 'SMP dan Sederajat atau lebih rendah',
        'sma' => 'SMA dan Sederajat',
        'pt'  => 'Perguruan Tinggi',
    ];

    /** Blok II rincian B — balas jasa pekerja tetap & kontrak. */
    public const BALAS_JASA = [
        'gaji'            => 'Gaji dan Tunjangan Rutin',
        'lembur'          => 'Upah Lembur',
        'transport_makan' => 'Uang Transportasi dan Uang Makan',
        'hadiah_bonus'    => 'Hadiah, Bonus, dan Sejenisnya',
        'dana_pensiun'    => 'Iuran Dana Pensiun dan Tunjangan Sosial',
        'asuransi'        => 'Asuransi Tenaga Kerja',
    ];

    /** Blok III — sumber modal. Percentages, must total 100. */
    public const SUMBER_MODAL = [
        'pemerintah_pusat'  => 'Pemerintah Pusat',
        'pemerintah_daerah' => 'Pemerintah Daerah',
        'swasta_nasional'   => 'Swasta Nasional/Perorangan',
        'asing'             => 'Asing',
    ];

    /** Blok IV — pengeluaran operasional. */
    public const PENGELUARAN = [
        'bahan_bakar'      => 'Bahan Bakar dan Pelumas',
        'angkutan'         => 'Angkutan, Pengiriman, Ekspedisi, Pergudangan, Pos, dan Jasa Kurir',
        'sewa'             => 'Sewa Lahan, Bangunan, Kendaraan, Mesin, Peralatan, dan Alat Perlengkapan',
        'diklat'           => 'Pendidikan dan Pelatihan',
        'pajak'            => 'Pajak',
        'penyusutan'       => 'Penyusutan dan Amortisasi',
        'jasa_intelektual' => 'Pengeluaran Jasa Intelektual (Hak Paten, Merek, Hak Cipta, Desain Industri dll)',
        'lainnya'          => 'Pengeluaran Lain',
    ];

    /** Blok V — jenis barang modal, each with penambahan + pengurangan. */
    public const BARANG_MODAL = [
        'tanah'      => 'Tanah',
        'bangunan'   => 'Bangunan',
        'alat_berat' => 'Alat Berat',
        'kendaraan'  => 'Kendaraan',
        'software'   => 'Software',
        'lainnya'    => 'Lainnya',
    ];

    /** Blok VI rincian 4 — sumber modal pekerjaan, must total 100%. */
    public const MODAL_PROYEK = [
        'modal_sendiri' => 'Modal sendiri',
        'pinjaman'      => 'Pinjaman lembaga keuangan',
        'pengguna_jasa' => 'Pengguna jasa/pemilik pekerjaan',
    ];

    /** Blok VIII — jenis pendapatan lain. */
    public const PENDAPATAN_LAIN = [
        'konsultan_dn'     => 'Pendapatan Konsultan Perencanaan dan/atau Pengawasan — Dalam Negeri',
        'konsultan_ln'     => 'Pendapatan Konsultan Perencanaan dan/atau Pengawasan — Luar Negeri',
        'sewa'             => 'Pendapatan dari Sewa (Gedung, Ruangan, Lahan, Alat dan Perlengkapan, dll)',
        'jasa_intelektual' => 'Pendapatan Jasa Intelektual (Hak Paten, Hak Merek, Hak Cipta, Desain Industri, dll)',
        'lainnya'          => 'Lainnya',
    ];

    /** Blok I — badan hukum/badan usaha codes. */
    public const BADAN_HUKUM = [
        '1' => 'PT. Persero',
        '2' => 'PT',
        '3' => 'CV',
        '4' => 'Koperasi',
        '5' => 'Kantor Perwakilan BUJKA',
        '9' => 'Lainnya',
    ];

    public const STATUS_MODAL = [
        '1' => 'Penanaman Modal Dalam Negeri',
        '2' => 'Penanaman Modal Asing',
        '9' => 'Lainnya',
    ];

    public const KUALIFIKASI = [
        '1' => 'Spesialis',
        '2' => 'Kecil',
        '3' => 'Menengah',
        '4' => 'Besar',
        '9' => 'Tidak Memiliki SBU Aktif',
    ];

    /** Blok VI kolom 4 — sumber dana utama pembiayaan proyek. */
    public const SUMBER_DANA = [
        '1' => 'APBN / APBD',
        '2' => 'BUMN / BUMD',
        '3' => 'Swasta',
        '4' => 'Luar Negeri',
    ];

    public const METODE_PENDATAAN = [
        '1' => 'Mandiri (online via website)',
        '2' => 'Wawancara petugas',
    ];

    // ── Quarterly questionnaire (SKTR) ────────────────────────────────────

    /** Updating Status Perusahaan — rincian 1. */
    public const STATUS_PERUSAHAAN = [
        '1' => 'Aktif',
        '2' => 'Tutup Sementara',
        '3' => 'Tutup',
        '4' => 'Tidak Ditemukan',
        '5' => 'Bukan Konstruksi',
        '6' => 'Pindah Kabupaten/Kota',
    ];

    /**
     * The status codes a responden can report about themselves online.
     *
     * "Tidak Ditemukan" (4) is left out on purpose: it is what a petugas writes
     * when they could not locate the company, which nobody filling the form
     * can truthfully select. The full STATUS_PERUSAHAAN map above still labels
     * it, so a 4 recorded through any other channel still displays correctly.
     */
    public const STATUS_PERUSAHAAN_MANDIRI = ['1', '2', '3', '5', '6'];

    /** Only an active company continues into Blok I and beyond. */
    public const STATUS_AKTIF = '1';

    /** Status 6 is the only one that asks for a new address. */
    public const STATUS_PINDAH = '6';

    /** Updating Status Perusahaan — rincian 2. */
    public const KESEDIAAN_RESPONDEN = [
        '1' => 'Bersedia',
        '2' => 'Menolak',
        '3' => 'Non Respon',
    ];

    public const KESEDIAAN_BERSEDIA = '1';

    /** Updating Status Perusahaan — rincian 3. */
    public const KEBERADAAN_PROYEK = [
        '1' => 'Ada Proyek Konstruksi',
        '2' => 'Tidak Ada Proyek Konstruksi',
    ];

    public const ADA_PROYEK = '1';

    /** Blok III (SKTR) prints 10 project rows. */
    public const MAX_PROYEK_TW = 10;

    /**
     * The two quarters SKTR's Blok II asks about, as slot names.
     * 'prev' = the quarter before the survey quarter, 'cur' = the survey quarter.
     */
    public const TW_SLOTS = ['prev' => -1, 'cur' => 0];

    /**
     * Blok III's four cumulative-realisation columns, as slot name => offset
     * from the survey quarter. The window rolls every quarter but never changes
     * width — TW1 and TW4 both have exactly these four.
     */
    public const REALISASI_SLOTS = [
        'realisasi_2lalu' => -2,
        'realisasi_lalu'  => -1,
        'realisasi_ini'   => 0,
        'realisasi_depan' => 1,   // rencana/perkiraan
    ];

    /** Blok III's upah columns, two quarters each. */
    public const UPAH_SLOTS      = ['upah_lalu' => -1, 'upah_ini' => 0];
    public const UPAH_RATA_SLOTS = ['upah_rata_lalu' => -1, 'upah_rata_ini' => 0];

    protected $fillable = [
        'user_id', 'tahun', 'triwulan', 'survey_section', 'is_completed', 'last_saved_at',
        // Blok I
        'kip', 'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa', 'nus',
        'nama_perusahaan', 'punya_nib', 'nib', 'nama_pengusaha', 'hp_pengusaha',
        'alamat_perusahaan', 'rt', 'rw', 'kode_pos', 'nomor_telepon', 'website', 'email_perusahaan',
        'alamat_operasional', 'rt_operasional', 'rw_operasional', 'kode_pos_operasional',
        'badan_hukum', 'badan_hukum_lainnya', 'status_penanaman_modal', 'kualifikasi',
        'blok1_completed',
        // Blok II
        'blok2_pekerja', 'blok2_balas_jasa', 'blok2_completed',
        // Blok III
        'blok3_modal', 'modal_asing_negara', 'blok3_completed',
        // Blok IV
        'blok4_pengeluaran', 'blok4_completed',
        // Blok V
        'blok5_barang_modal', 'barang_modal_lainnya', 'blok5_completed',
        // Blok VI
        'data_proyek', 'pct_bahan', 'pct_upah', 'blok6_sumber_modal', 'blok6_completed',
        // Blok VII
        'blok7_bahan', 'blok7_completed',
        // Blok VIII
        'blok8_pendapatan_lain', 'pendapatan_lain_lainnya', 'blok8_completed',
        // Blok IX
        'blok9_completed',
        // Blok X
        'metode_pendataan', 'catatan', 'pj_nama', 'pj_hp', 'pj_jabatan', 'pj_email',
        'blok10_completed',
        // Quarterly (SKTR)
        'status_perusahaan', 'kesediaan_responden', 'keberadaan_proyek',
        'pindah_kabupaten_kota', 'pindah_kecamatan', 'pindah_kelurahan_desa',
        'pindah_alamat', 'pindah_rt', 'pindah_rw', 'pindah_kode_pos',
        'status_completed', 'tw_pekerja', 'tw_balas_jasa', 'data_proyek_tw',
        'penutup_completed',
    ];

    protected $casts = [
        'is_completed'          => 'boolean',
        'last_saved_at'         => 'datetime',
        'tahun'                 => 'integer',
        'triwulan'              => 'integer',
        'blok2_pekerja'         => 'array',
        'blok2_balas_jasa'      => 'array',
        'blok3_modal'           => 'array',
        'blok4_pengeluaran'     => 'array',
        'blok5_barang_modal'    => 'array',
        'data_proyek'           => 'array',
        'blok6_sumber_modal'    => 'array',
        'blok7_bahan'           => 'array',
        'blok8_pendapatan_lain' => 'array',
        'blok1_completed'       => 'boolean',
        'blok2_completed'       => 'boolean',
        'blok3_completed'       => 'boolean',
        'blok4_completed'       => 'boolean',
        'blok5_completed'       => 'boolean',
        'blok6_completed'       => 'boolean',
        'blok7_completed'       => 'boolean',
        'blok8_completed'       => 'boolean',
        'blok9_completed'       => 'boolean',
        'blok10_completed'      => 'boolean',
        'tw_pekerja'            => 'array',
        'tw_balas_jasa'         => 'array',
        'data_proyek_tw'        => 'array',
        'status_completed'      => 'boolean',
        'penutup_completed'     => 'boolean',
    ];

    /** True when this row is the quarterly questionnaire rather than the annual one. */
    public function isTriwulanan(): bool
    {
        return (int) $this->triwulan !== self::TAHUNAN;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getOrCreateForUser(
        string $userId,
        string $section,
        int $tahun = self::TAHUN_TAHUNAN,
        int $triwulan = self::TAHUNAN
    ): static {
        $defaults = ['survey_section' => $section];

        // The preamble's first two questions are enumerator codes carried over
        // from the paper flow: a petugas records whether the company was found
        // and whether it agreed to answer. Neither is a question a responden can
        // meaningfully answer about themselves here — somebody logged in and
        // filling the form IS an operating company that IS willing. So both are
        // seeded on creation; status_perusahaan stays editable because a
        // company can legitimately report that it has closed or moved.
        //
        // Both instruments now open on this preamble, so both are seeded.
        // keberadaan_proyek is deliberately NOT seeded — it is the one question
        // here only the responden can answer, and a default would decide the
        // shape of their questionnaire for them.
        $defaults['status_perusahaan']   = self::STATUS_AKTIF;
        $defaults['kesediaan_responden'] = self::KESEDIAAN_BERSEDIA;

        $response = static::firstOrCreate(
            ['user_id' => $userId, 'tahun' => $tahun, 'triwulan' => $triwulan],
            $defaults
        );
        $response->update(['survey_section' => $section]);

        return $response;
    }

    public function updateWithAutoSave(array $data): static
    {
        $this->fill($data);
        $this->last_saved_at = now();
        $this->save();

        return $this;
    }

    // ── Number helpers ────────────────────────────────────────────────────

    /** Parse one stored figure. Inputs persist as id-ID strings. */
    public static function n(mixed $v): float
    {
        return SibstrFormat::num($v) ?? 0.0;
    }

    /**
     * The valid codes of a code => label map, as strings.
     *
     * PHP silently casts numeric array keys to int, so array_keys(BADAN_HUKUM)
     * returns [1, 2, …] and a strict in_array('2', …, true) against it is
     * always false. Answers arrive from the form as strings, so every code
     * check goes through here.
     */
    public static function codes(array $map): array
    {
        return array_map('strval', array_keys($map));
    }

    /** Sum the given keys out of a stored JSON map. */
    public static function sumOf(mixed $map, array $keys): float
    {
        $map = is_array($map) ? $map : [];
        $sum = 0.0;
        foreach ($keys as $k) {
            $sum += self::n($map[$k] ?? null);
        }

        return $sum;
    }

    /** True when every listed key holds a parseable figure (0 counts as filled). */
    private static function allFilled(mixed $map, array $keys): bool
    {
        $map = is_array($map) ? $map : [];
        foreach ($keys as $k) {
            if (SibstrFormat::num($map[$k] ?? null) === null) {
                return false;
            }
        }

        return true;
    }

    // ── Blok totals (the "JUMLAH" rows the paper form prints) ─────────────

    public function totalPekerja(): float
    {
        $d   = is_array($this->blok2_pekerja) ? $this->blok2_pekerja : [];
        $sum = 0.0;
        foreach (array_keys(self::PENDIDIKAN) as $jenjang) {
            $sum += self::n($d[$jenjang]['l'] ?? null) + self::n($d[$jenjang]['p'] ?? null);
        }

        return $sum;
    }

    public function totalBalasJasa(): float
    {
        return self::sumOf($this->blok2_balas_jasa, array_keys(self::BALAS_JASA));
    }

    public function totalModal(): float
    {
        return self::sumOf($this->blok3_modal, array_keys(self::SUMBER_MODAL));
    }

    public function totalPengeluaran(): float
    {
        return self::sumOf($this->blok4_pengeluaran, array_keys(self::PENGELUARAN));
    }

    /** Blok V totals: ['tambah' => x, 'kurang' => y]. */
    public function totalBarangModal(): array
    {
        $d = is_array($this->blok5_barang_modal) ? $this->blok5_barang_modal : [];
        $t = ['tambah' => 0.0, 'kurang' => 0.0];
        foreach (array_keys(self::BARANG_MODAL) as $jenis) {
            $t['tambah'] += self::n($d[$jenis]['tambah'] ?? null);
            $t['kurang'] += self::n($d[$jenis]['kurang'] ?? null);
        }

        return $t;
    }

    public function totalModalProyek(): float
    {
        return self::sumOf($this->blok6_sumber_modal, array_keys(self::MODAL_PROYEK));
    }

    public function totalBahan(): float
    {
        $sum = 0.0;
        foreach ($this->bahanRows() as $row) {
            $sum += self::n($row['nilai'] ?? null);
        }

        return $sum;
    }

    public function totalPendapatanLain(): float
    {
        return self::sumOf($this->blok8_pendapatan_lain, array_keys(self::PENDAPATAN_LAIN));
    }

    /** Blok VII rows, dropping the blank trailing ones the form prints. */
    public function bahanRows(): array
    {
        $rows = is_array($this->blok7_bahan) ? $this->blok7_bahan : [];

        return array_values(array_filter($rows, fn ($r) => is_array($r)
            && (trim((string) ($r['nama'] ?? '')) !== '' || SibstrFormat::num($r['nilai'] ?? null) !== null)));
    }

    // ── Blok VI : project rows ────────────────────────────────────────────

    /** The 17 questionnaire columns, keyed. Column 17 is derived. */
    public static function emptyProyekRow(): array
    {
        return [
            'nama_pekerjaan'   => '',   // (2)
            'kbli'             => '',
            'provinsi_lokasi'  => '',   // (3)
            'kabkota_lokasi'   => '',
            'kode_kabkota'     => '',
            'sumber_dana'      => '',   // (4)
            'volume'           => '',   // (5)
            'satuan'           => '',   // (6)
            'nilai_kontrak'    => '',   // (7)
            'nilai_subkontrak' => '',   // (8)
            'mulai'            => '',   // (9)  mm/yyyy
            'selesai'          => '',   // (10) mm/yyyy
            'realisasi_awal'   => '',   // (11) % s/d 31/12 tahun sebelumnya
            'realisasi_akhir'  => '',   // (12) % s/d 31/12 tahun survei
            'bahan_perusahaan' => '',   // (13)
            'bahan_pemilik'    => '',   // (14)
            'upah_harian'      => '',   // (15)
            'upah_rata_rata'   => '',   // (16)
        ];
    }

    /**
     * Column (17), exactly as the questionnaire prints it:
     *   {[(7) - (8)] * (12 - 11) / 100} + (14)
     *
     * Never negative — a realisasi that moved backwards is a data-entry slip,
     * and a negative here would silently subtract from the Blok IX pendapatan.
     */
    public static function nilaiKonstruksi(array $row): float
    {
        $kontrak = self::n($row['nilai_kontrak'] ?? null);
        $subkon  = self::n($row['nilai_subkontrak'] ?? null);
        $awal    = self::n($row['realisasi_awal'] ?? null);
        $akhir   = self::n($row['realisasi_akhir'] ?? null);
        $pemilik = self::n($row['bahan_pemilik'] ?? null);

        return max(0.0, (($kontrak - $subkon) * ($akhir - $awal) / 100) + $pemilik);
    }

    /**
     * Project rows that carry any answer at all.
     *
     * Mirrors proyekTwRows(): a submission whose preamble says there were no
     * projects reports none, even if `data_proyek` still holds rows typed
     * before the answer was changed. Hiding rather than deleting means one rule
     * covers every reader, and the rows come back if they switch to "Ada".
     */
    public function proyekRows(): array
    {
        if (!$this->proyekBlokApplies()) {
            return [];
        }

        $rows = is_array($this->data_proyek) ? $this->data_proyek : [];

        return array_values(array_filter($rows, function ($r) {
            if (!is_array($r)) {
                return false;
            }
            foreach ($r as $v) {
                if (trim((string) $v) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }

    /** Blok VI "JUMLAH" row — the columns the form actually totals. */
    public function totalProyek(): array
    {
        $t = [
            'nilai_kontrak'    => 0.0,
            'bahan_perusahaan' => 0.0,
            'bahan_pemilik'    => 0.0,
            'upah_harian'      => 0.0,
            'nilai_konstruksi' => 0.0,
        ];

        foreach ($this->proyekRows() as $row) {
            $t['nilai_kontrak']    += self::n($row['nilai_kontrak'] ?? null);
            $t['bahan_perusahaan'] += self::n($row['bahan_perusahaan'] ?? null);
            $t['bahan_pemilik']    += self::n($row['bahan_pemilik'] ?? null);
            $t['upah_harian']      += self::n($row['upah_harian'] ?? null);
            $t['nilai_konstruksi'] += self::nilaiKonstruksi($row);
        }

        return $t;
    }

    // ── Blok IX : ringkasan, derived ──────────────────────────────────────

    /**
     * The summary block, recomputed from its sources every read.
     *
     * The paper form's formulas cite "Blok VII" because its Blok VI grid
     * carries over onto the Blok VII sheet; the figures it means are the
     * project-grid totals, which is what totalProyek() returns.
     */
    public function ringkasan(): array
    {
        $proyek = $this->totalProyek();

        $pengeluaran = [
            'balas_jasa'  => $this->totalBalasJasa() + $proyek['upah_harian'],
            'bahan'       => $proyek['bahan_perusahaan'] + $proyek['bahan_pemilik'],
            'operasional' => $this->totalPengeluaran(),
        ];
        $totalA = array_sum($pengeluaran);

        $pendapatan = [
            'nilai_konstruksi' => $proyek['nilai_konstruksi'],
            'pendapatan_lain'  => $this->totalPendapatanLain(),
        ];
        $totalB = array_sum($pendapatan);

        return [
            'pengeluaran' => $pengeluaran,
            'total_a'     => $totalA,
            'pendapatan'  => $pendapatan,
            'total_b'     => $totalB,
            // (B/A − 1) × 100 %. Undefined with no pengeluaran at all — the
            // paper form prints #DIV/0! there; we show a dash instead.
            'margin_pct'  => $totalA > 0 ? (($totalB / $totalA) - 1) * 100 : null,
        ];
    }

    // ── Completion predicates ─────────────────────────────────────────────

    public function isBlok1Complete(): bool
    {
        $required = [
            'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa', 'nus',
            'nama_perusahaan', 'punya_nib', 'nama_pengusaha', 'hp_pengusaha',
            'alamat_perusahaan', 'badan_hukum', 'status_penanaman_modal', 'kualifikasi',
        ];
        foreach ($required as $field) {
            if (trim((string) $this->{$field}) === '') {
                return false;
            }
        }
        if ($this->punya_nib === 'ya' && trim((string) $this->nib) === '') {
            return false;
        }
        if ($this->badan_hukum === '9' && trim((string) $this->badan_hukum_lainnya) === '') {
            return false;
        }

        return true;
    }

    public function isBlok2Complete(): bool
    {
        $pekerja = is_array($this->blok2_pekerja) ? $this->blok2_pekerja : [];
        foreach (array_keys(self::PENDIDIKAN) as $jenjang) {
            if (!self::allFilled($pekerja[$jenjang] ?? null, ['l', 'p'])) {
                return false;
            }
        }

        return self::allFilled($this->blok2_balas_jasa, array_keys(self::BALAS_JASA));
    }

    /** Percentages must be present and total exactly 100. */
    public function isBlok3Complete(): bool
    {
        if (!self::allFilled($this->blok3_modal, array_keys(self::SUMBER_MODAL))) {
            return false;
        }
        if (abs($this->totalModal() - 100) > 0.001) {
            return false;
        }
        $asing = self::n(($this->blok3_modal ?? [])['asing'] ?? null);

        return $asing <= 0 || trim((string) $this->modal_asing_negara) !== '';
    }

    public function isBlok4Complete(): bool
    {
        return self::allFilled($this->blok4_pengeluaran, array_keys(self::PENGELUARAN));
    }

    public function isBlok5Complete(): bool
    {
        $d = is_array($this->blok5_barang_modal) ? $this->blok5_barang_modal : [];
        foreach (array_keys(self::BARANG_MODAL) as $jenis) {
            if (!self::allFilled($d[$jenis] ?? null, ['tambah', 'kurang'])) {
                return false;
            }
        }
        $lainnya = self::n($d['lainnya']['tambah'] ?? null) + self::n($d['lainnya']['kurang'] ?? null);

        return $lainnya <= 0 || trim((string) $this->barang_modal_lainnya) !== '';
    }

    /**
     * At least one project, each with the columns the Blok IX formulas read,
     * plus the two averages and a sumber-modal split totalling 100 %.
     *
     * A company with no construction work at all still has to answer Blok VI,
     * and this rejects an empty grid. That is deliberate: the annual form has
     * no "tidak ada proyek" option, so an empty grid is an unfinished one.
     */
    public function isBlok6Complete(): bool
    {
        $rows = $this->proyekRows();
        if ($rows === []) {
            return false;
        }

        foreach ($rows as $row) {
            if (trim((string) ($row['nama_pekerjaan'] ?? '')) === '') {
                return false;
            }
            if (!in_array((string) ($row["sumber_dana"] ?? ""), self::codes(self::SUMBER_DANA), true)) {
                return false;
            }
            $needed = ['nilai_kontrak', 'nilai_subkontrak', 'realisasi_awal', 'realisasi_akhir',
                'bahan_perusahaan', 'bahan_pemilik', 'upah_harian', 'upah_rata_rata'];
            if (!self::allFilled($row, $needed)) {
                return false;
            }
            foreach (['mulai', 'selesai'] as $f) {
                if (!preg_match('#^(0[1-9]|1[0-2])/[0-9]{4}$#', (string) ($row[$f] ?? ''))) {
                    return false;
                }
            }
        }

        if (SibstrFormat::num($this->pct_bahan) === null || SibstrFormat::num($this->pct_upah) === null) {
            return false;
        }
        if (!self::allFilled($this->blok6_sumber_modal, array_keys(self::MODAL_PROYEK))) {
            return false;
        }

        return abs($this->totalModalProyek() - 100) <= 0.001;
    }

    /** Every listed material needs both a name and a value. */
    public function isBlok7Complete(): bool
    {
        $rows = $this->bahanRows();
        if ($rows === []) {
            return false;
        }
        foreach ($rows as $row) {
            if (trim((string) ($row['nama'] ?? '')) === '' || SibstrFormat::num($row['nilai'] ?? null) === null) {
                return false;
            }
        }

        return true;
    }

    public function isBlok8Complete(): bool
    {
        if (!self::allFilled($this->blok8_pendapatan_lain, array_keys(self::PENDAPATAN_LAIN))) {
            return false;
        }
        $lainnya = self::n(($this->blok8_pendapatan_lain ?? [])['lainnya'] ?? null);

        return $lainnya <= 0 || trim((string) $this->pendapatan_lain_lainnya) !== '';
    }

    /** Blok IX is derived; it is complete once everything it reads is. */
    public function isBlok9Complete(): bool
    {
        return $this->isBlok2Complete()
            && $this->isBlok4Complete()
            && $this->isBlok6Complete()
            && $this->isBlok7Complete()
            && $this->isBlok8Complete();
    }

    public function isBlok10Complete(): bool
    {
        if (!in_array((string) $this->metode_pendataan, self::codes(self::METODE_PENDATAAN), true)) {
            return false;
        }
        foreach (['pj_nama', 'pj_hp', 'pj_jabatan'] as $field) {
            if (trim((string) $this->{$field}) === '') {
                return false;
            }
        }

        return trim((string) $this->catatan) !== '';
    }

    public function completionPercent(): int
    {
        $keys = KonstruksiBlokPath::path((int) $this->triwulan, $this);
        $done = 0;
        foreach ($keys as $key) {
            $done += KonstruksiBlokPath::isComplete($this, $key) ? 1 : 0;
        }

        return $keys === [] ? 0 : (int) round($done / count($keys) * 100);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Quarterly questionnaire (SKTR)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Shift a (tahun, triwulan) pair by a number of quarters.
     *
     * The Blok II and Blok III columns are a window relative to the survey
     * quarter, so every label on those grids is derived from here rather than
     * hard-coded per quarter. TW1's window reaches back into the previous year
     * and TW4's forecast column reaches into the next, which this handles by
     * counting in absolute quarters.
     *
     * @return array{tahun:int, triwulan:int}
     */
    public static function shiftQuarter(int $tahun, int $triwulan, int $delta): array
    {
        $index = ($tahun * 4) + ($triwulan - 1) + $delta;

        return [
            'tahun'    => intdiv($index, 4),
            'triwulan' => ($index % 4) + 1,
        ];
    }

    public static function romanQuarter(int $q): string
    {
        return [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'][$q] ?? (string) $q;
    }

    /** Last calendar day of a quarter, as the questionnaire prints it. */
    private static function quarterEndDate(int $tahun, int $q): string
    {
        return [
            1 => "31 Mar {$tahun}",
            2 => "30 Jun {$tahun}",
            3 => "30 Sep {$tahun}",
            4 => "31 Des {$tahun}",
        ][$q] ?? '';
    }

    /** Month span of a quarter, as the questionnaire prints it. */
    private static function quarterMonths(int $tahun, int $q): string
    {
        return [
            1 => "Jan–Mar {$tahun}",
            2 => "Apr–Jun {$tahun}",
            3 => "Jul–Sep {$tahun}",
            4 => "Okt–Des {$tahun}",
        ][$q] ?? '';
    }

    /**
     * Labels for one slot offset from this row's survey quarter.
     *
     * @return array{short:string, end:string, range:string}
     *   short — "TW IV-2025"
     *   end   — "TW IV-2025 (31 Des 2025)"   for cumulative realisation columns
     *   range — "TW IV-2025 (Okt–Des 2025)"  for the per-quarter upah columns
     */
    public function quarterLabel(int $delta): array
    {
        $q     = self::shiftQuarter((int) $this->tahun, (int) $this->triwulan, $delta);
        $short = 'TW ' . self::romanQuarter($q['triwulan']) . '-' . $q['tahun'];

        return [
            'short' => $short,
            'end'   => $short . ' (' . self::quarterEndDate($q['tahun'], $q['triwulan']) . ')',
            'range' => $short . ' (' . self::quarterMonths($q['tahun'], $q['triwulan']) . ')',
        ];
    }

    /**
     * Every quarterly slot resolved to its labels, keyed by slot name.
     * Views read this instead of computing offsets themselves.
     *
     * @return array<string, array{short:string, end:string, range:string}>
     */
    public function quarterLabels(): array
    {
        $out = [];
        foreach (self::TW_SLOTS as $slot => $delta) {
            $out[$slot] = $this->quarterLabel($delta);
        }
        foreach (self::REALISASI_SLOTS as $slot => $delta) {
            $out[$slot] = $this->quarterLabel($delta);
        }
        foreach (array_merge(self::UPAH_SLOTS, self::UPAH_RATA_SLOTS) as $slot => $delta) {
            $out[$slot] = $this->quarterLabel($delta);
        }

        return $out;
    }

    /** The 16 questionnaire columns of SKTR's Blok III, keyed. */
    public static function emptyProyekTwRow(): array
    {
        return [
            'nama_pekerjaan'   => '',   // (2)
            'kbli'             => '',
            'provinsi_lokasi'  => '',   // (3)
            'kabkota_lokasi'   => '',
            'kode_kabkota'     => '',
            'sumber_dana'      => '',   // (4)
            'nilai_kontrak'    => '',   // (5)
            'pct_subkontrak'   => '',   // (6) — a PERCENTAGE here, unlike SKTH's rupiah figure
            'mulai'            => '',   // (7)  mm/yyyy
            'selesai'          => '',   // (8)  mm/yyyy
            'realisasi_2lalu'  => '',   // (9)
            'realisasi_lalu'   => '',   // (10)
            'realisasi_ini'    => '',   // (11)
            'realisasi_depan'  => '',   // (12) rencana
            'upah_lalu'        => '',   // (13)
            'upah_ini'         => '',   // (14)
            'upah_rata_lalu'   => '',   // (15)
            'upah_rata_ini'    => '',   // (16)
        ];
    }

    /**
     * Value of work done during the survey quarter, in 000 Rp.
     *
     * SKTR has no printed "nilai konstruksi" column — its grid stops at the
     * cumulative percentages — so this derives the quarter's own progress the
     * same way SKTH's column (17) does: contract value net of the
     * subcontracted share, times the percentage completed during the quarter.
     * Never negative; a realisation that moved backwards is a data-entry slip.
     */
    public static function nilaiKonstruksiTw(array $row): float
    {
        $kontrak = self::n($row['nilai_kontrak'] ?? null);
        $subPct  = self::n($row['pct_subkontrak'] ?? null);
        $lalu    = self::n($row['realisasi_lalu'] ?? null);
        $ini     = self::n($row['realisasi_ini'] ?? null);

        $netto = $kontrak * max(0.0, 1 - ($subPct / 100));

        return max(0.0, $netto * ($ini - $lalu) / 100);
    }

    /**
     * SKTR project rows that carry any answer at all.
     *
     * A submission whose preamble says there are no projects reports none, even
     * if `data_proyek_tw` still holds rows typed before the answer was changed.
     * Hiding them here rather than deleting them means one rule covers every
     * reader — the grid, the totals, the BPS listing, the PDF, the export — and
     * the rows come back untouched if the responden switches back to "Ada".
     */
    public function proyekTwRows(): array
    {
        if (!$this->proyekBlokApplies()) {
            return [];
        }

        $rows = is_array($this->data_proyek_tw) ? $this->data_proyek_tw : [];

        return array_values(array_filter($rows, function ($r) {
            if (!is_array($r)) {
                return false;
            }
            foreach ($r as $v) {
                if (trim((string) $v) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }

    /** Blok III (SKTR) totals. */
    public function totalProyekTw(): array
    {
        $t = [
            'nilai_kontrak'    => 0.0,
            'upah_lalu'        => 0.0,
            'upah_ini'         => 0.0,
            'nilai_konstruksi' => 0.0,
        ];

        foreach ($this->proyekTwRows() as $row) {
            $t['nilai_kontrak']    += self::n($row['nilai_kontrak'] ?? null);
            $t['upah_lalu']        += self::n($row['upah_lalu'] ?? null);
            $t['upah_ini']         += self::n($row['upah_ini'] ?? null);
            $t['nilai_konstruksi'] += self::nilaiKonstruksiTw($row);
        }

        return $t;
    }

    // ── SKTR completion predicates ────────────────────────────────────────

    /** The preamble: status, kesediaan, and — when moving — the new address. */
    public function isStatusComplete(): bool
    {
        if (!in_array((string) $this->status_perusahaan, self::codes(self::STATUS_PERUSAHAAN), true)) {
            return false;
        }
        if (!in_array((string) $this->kesediaan_responden, self::codes(self::KESEDIAAN_RESPONDEN), true)) {
            return false;
        }

        if ((string) $this->status_perusahaan === self::STATUS_PINDAH) {
            foreach (['pindah_kabupaten_kota', 'pindah_kecamatan', 'pindah_kelurahan_desa', 'pindah_alamat'] as $f) {
                if (trim((string) $this->{$f}) === '') {
                    return false;
                }
            }
        }

        // Keberadaan proyek is only asked of a company that is still reporting.
        if ($this->continuesPastStatus()) {
            return in_array((string) $this->keberadaan_proyek, self::codes(self::KEBERADAAN_PROYEK), true);
        }

        return true;
    }

    /**
     * Does this submission carry on past the preamble?
     * A company that is not active, or that refused / did not respond, is
     * recorded and finished right there — the rest of the form does not apply.
     *
     * Both instruments open on the same preamble, so this is period-agnostic:
     * SKTR ends at Penutup, SKTH at Blok X, but the rule is one rule.
     */
    public function continuesPastStatus(): bool
    {
        return (string) $this->status_perusahaan === self::STATUS_AKTIF
            && (string) $this->kesediaan_responden === self::KESEDIAAN_BERSEDIA;
    }

    /**
     * Did the preamble actually END the questionnaire?
     *
     * The negation of continuesPastStatus() is NOT this: a row whose preamble is
     * still blank does not continue either, but nothing has been ruled out yet
     * and its full path is still on offer. Views that announce "Pengisian
     * Dihentikan", or hide the blocks behind it, must ask this instead — the
     * looser test told an untouched submission its form had been cut short.
     */
    public function stoppedAtStatus(): bool
    {
        return trim((string) $this->status_perusahaan) !== ''
            && !$this->continuesPastStatus();
    }

    /** Did the responden report having construction projects in this period? */
    public function hasProyek(): bool
    {
        return $this->continuesPastStatus()
            && (string) $this->keberadaan_proyek === self::ADA_PROYEK;
    }

    /**
     * Is the project block part of THIS submission's questionnaire?
     * (Blok III on SKTR, Blok VI on SKTH — and on SKTH a "no" ends the survey,
     * so it gates Blok II–IX as well. KonstruksiBlokPath::path() owns that.)
     *
     * hasProyek() answers "did they report projects", which is false while the
     * preamble is still blank. That is the wrong question for gating a page: an
     * unanswered preamble must leave the block reachable, or the responden's
     * first visit bounces before they have said anything. So a blank answer
     * keeps the block, and only a recorded "no" — or a company that stops at the
     * preamble entirely — takes it away.
     *
     * This is the single source of truth behind KonstruksiBlokPath::path(), the
     * page and save guards, and the project row accessors.
     */
    public function proyekBlokApplies(): bool
    {
        if (trim((string) $this->status_perusahaan) === '') {
            return true;
        }
        if (!$this->continuesPastStatus()) {
            return false;
        }

        return trim((string) $this->keberadaan_proyek) === ''
            || $this->hasProyek();
    }

    /**
     * SKTR's Blok I is SKTH's minus alamat operasional, and the quarterly form
     * does not print the KIP-only fields, so this is a slightly smaller check.
     */
    public function isBlok1TwComplete(): bool
    {
        $required = [
            'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa', 'nus',
            'nama_perusahaan', 'punya_nib', 'nama_pengusaha', 'hp_pengusaha',
            'alamat_perusahaan', 'badan_hukum', 'status_penanaman_modal', 'kualifikasi',
        ];
        foreach ($required as $field) {
            if (trim((string) $this->{$field}) === '') {
                return false;
            }
        }
        if ($this->punya_nib === 'ya' && trim((string) $this->nib) === '') {
            return false;
        }

        return !($this->badan_hukum === '9' && trim((string) $this->badan_hukum_lainnya) === '');
    }

    /** Blok II (SKTR): a worker count and a balas jasa figure for both quarters. */
    public function isBlok2TwComplete(): bool
    {
        return self::allFilled($this->tw_pekerja, array_keys(self::TW_SLOTS))
            && self::allFilled($this->tw_balas_jasa, array_keys(self::TW_SLOTS));
    }

    /**
     * Blok III (SKTR). A company that reported no projects has nothing to fill,
     * so the block counts as complete — unlike SKTH, the quarterly form has an
     * explicit "Tidak Ada Proyek Konstruksi" answer and this honours it.
     *
     * That exemption only applies once the preamble has actually been answered.
     * Keberadaan proyek starts blank, and treating blank as "no projects" made
     * the block show a green tick on the nav rail before the responden had
     * filled anything at all.
     */
    public function isBlok3TwComplete(): bool
    {
        if (trim((string) $this->keberadaan_proyek) === '') {
            return false;
        }

        if (!$this->hasProyek()) {
            return true;
        }

        $rows = $this->proyekTwRows();
        if ($rows === []) {
            return false;
        }

        $needed = array_merge(
            ['nilai_kontrak', 'pct_subkontrak'],
            array_keys(self::REALISASI_SLOTS),
            array_keys(self::UPAH_SLOTS),
            array_keys(self::UPAH_RATA_SLOTS)
        );

        foreach ($rows as $row) {
            if (trim((string) ($row['nama_pekerjaan'] ?? '')) === '') {
                return false;
            }
            if (!in_array((string) ($row['sumber_dana'] ?? ''), self::codes(self::SUMBER_DANA), true)) {
                return false;
            }
            if (!self::allFilled($row, $needed)) {
                return false;
            }
            foreach (['mulai', 'selesai'] as $f) {
                if (!preg_match('#^(0[1-9]|1[0-2])/[0-9]{4}$#', (string) ($row[$f] ?? ''))) {
                    return false;
                }
            }
        }

        return true;
    }

    /** SKTR's closing block, same questions as SKTH's Blok X. */
    public function isPenutupComplete(): bool
    {
        return $this->isBlok10Complete();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Period-agnostic accessors
    // ══════════════════════════════════════════════════════════════════════
    //
    //  The two instruments keep their projects in different columns, so any
    //  caller that just wants "this submission's projects" — the BPS listing,
    //  a dashboard tile — must not reach for proyekRows() directly or a
    //  quarterly row silently reports zero. These pick the right side.

    /** Project rows for whichever instrument this row belongs to. */
    public function proyekRowsForPeriod(): array
    {
        return $this->isTriwulanan() ? $this->proyekTwRows() : $this->proyekRows();
    }

    public function proyekCount(): int
    {
        return count($this->proyekRowsForPeriod());
    }

    /**
     * Value of construction completed, in 000 Rp — for the whole year on SKTH,
     * for the survey quarter on SKTR.
     */
    public function nilaiKonstruksiTotal(): float
    {
        return $this->isTriwulanan()
            ? $this->totalProyekTw()['nilai_konstruksi']
            : $this->totalProyek()['nilai_konstruksi'];
    }

    /** Contract value of every project listed, in 000 Rp. */
    public function nilaiKontrakTotal(): float
    {
        return $this->isTriwulanan()
            ? $this->totalProyekTw()['nilai_kontrak']
            : $this->totalProyek()['nilai_kontrak'];
    }

    /** Short instrument name for listings and exports. */
    public function instrumen(): string
    {
        return $this->isTriwulanan() ? 'SKTR' : 'SKTH';
    }
}
