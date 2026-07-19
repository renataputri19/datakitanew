<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListrikSurveyResponse extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'listrik_survey_responses';

    /**
     * Anchor year for the single response row per user (the monthly grid
     * itself spans multiple calendar years — see availableMonths()).
     */
    public const TAHUN = 2026;

    /** First month covered by the questionnaire grid. */
    public const START_YEAR  = 2025;
    public const START_MONTH = 1;

    /** Customer categories (kolom kuesioner) in fixed display order. */
    public const CATEGORIES = [
        'rt'  => 'Rumah Tangga',
        'ind' => 'Industri',
        'sos' => 'Sosial',
        'bis' => 'Bisnis',
        'pem' => 'Pemerintah',
        'mul' => 'Multiguna/T/L',
    ];

    /** Kab/kota pilihan untuk wilayah tujuan dalam Provinsi Kepri. */
    public const KEPRI_KABKOTA = [
        'Kota Batam',
        'Kota Tanjungpinang',
        'Kab. Bintan',
        'Kab. Karimun',
        'Kab. Lingga',
        'Kab. Natuna',
        'Kab. Kepulauan Anambas',
    ];

    /** Default wilayah tujuan for a new row (spec: default Batam). */
    public static function defaultWilayah(): array
    {
        return ['jenis' => 'dn', 'area' => 'kepri', 'kabkota' => 'Kota Batam', 'negara' => null];
    }

    /**
     * A month's data is a LIST of wilayah-tujuan rows:
     *   [ { "w": {jenis, area, kabkota, negara}, "rt": {kwh, rp}, ... }, ... ]
     * Legacy single-object months (pre-wilayah) are wrapped into one row
     * with the default wilayah so old drafts keep working.
     */
    public static function normalizeMonthRows(mixed $monthData): array
    {
        if (!is_array($monthData) || $monthData === []) {
            return [];
        }
        // legacy shape: {"rt": {...}, "ind": {...}} — associative, no 'w', has a category key
        if (!array_is_list($monthData)) {
            $row = ['w' => self::defaultWilayah()];
            foreach (array_keys(self::CATEGORIES) as $cat) {
                $row[$cat] = $monthData[$cat] ?? ['kwh' => null, 'rp' => null];
            }
            return [$row];
        }
        return $monthData;
    }

    public static function wilayahValid(mixed $w): bool
    {
        if (!is_array($w)) {
            return false;
        }
        $jenis = $w['jenis'] ?? null;
        if ($jenis === 'ln') {
            return trim((string) ($w['negara'] ?? '')) !== '';
        }
        if ($jenis === 'dn') {
            $area = $w['area'] ?? null;
            if (!in_array($area, ['kepri', 'luar_kepri'], true)) {
                return false;
            }
            return trim((string) ($w['kabkota'] ?? '')) !== '';
        }
        return false;
    }

    /** Human label for a wilayah tujuan (used in dashboards/tables). */
    public static function wilayahLabel(mixed $w): string
    {
        if (!is_array($w)) {
            return 'Tidak diketahui';
        }
        if (($w['jenis'] ?? null) === 'ln') {
            $n = trim((string) ($w['negara'] ?? ''));
            return $n !== '' ? $n . ' (Luar Negeri)' : 'Luar Negeri';
        }
        $k = trim((string) ($w['kabkota'] ?? ''));
        if (($w['area'] ?? null) === 'luar_kepri') {
            return $k !== '' ? $k . ' (Luar Kepri)' : 'Luar Kepri';
        }
        return $k !== '' ? $k : 'Dalam Negeri';
    }

    protected $fillable = [
        'user_id', 'tahun', 'survey_section', 'is_completed', 'last_saved_at',
        // Blok I
        'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa',
        'nama_perusahaan', 'nama_komersial', 'alamat_perusahaan', 'rt', 'rw',
        'kode_pos', 'nomor_telepon', 'nomor_hp', 'email_perusahaan',
        'jenis_pembangkit', 'daya_terpasang_kw',
        'nama_pengusaha', 'jenis_kelamin', 'umur', 'nik',
        'blok1_completed',
        // Blok II
        'data_listrik', 'blok2_completed',
        // Blok III
        'catatan', 'blok3_completed',
    ];

    protected $casts = [
        'is_completed'      => 'boolean',
        'last_saved_at'     => 'datetime',
        'blok1_completed'   => 'boolean',
        'blok2_completed'   => 'boolean',
        'blok3_completed'   => 'boolean',
        'data_listrik'      => 'array',
        'daya_terpasang_kw' => 'decimal:2',
        'umur'              => 'integer',
        'jenis_kelamin'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getOrCreateForUser(string $userId, string $section): static
    {
        $response = static::firstOrCreate(
            ['user_id' => $userId, 'tahun' => self::TAHUN],
            ['survey_section' => $section]
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

    /**
     * Months the grid must cover: January 2025 up to and including the
     * current calendar month. Keys "YYYY_M" grouped per year:
     * [2025 => ['2025_1', …, '2025_12'], 2026 => ['2026_1', …, '2026_7']].
     * The window extends automatically as real time passes (2027, …).
     */
    public static function availableMonths(): array
    {
        $now      = now();
        $curYear  = (int) $now->format('Y');
        $curMonth = (int) $now->format('n');

        $byYear = [];
        for ($y = self::START_YEAR; $y <= $curYear; $y++) {
            $lastMonth = $y === $curYear ? $curMonth : 12;
            $first     = $y === self::START_YEAR ? self::START_MONTH : 1;
            for ($m = $first; $m <= $lastMonth; $m++) {
                $byYear[$y][] = "{$y}_{$m}";
            }
        }
        return $byYear;
    }

    /** Flat list of all month keys in chronological order. */
    public static function availableMonthKeys(): array
    {
        return array_merge(...array_values(self::availableMonths()));
    }

    public static function monthLabel(string $key): string
    {
        [$y, $m] = explode('_', $key);
        $names = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return ($names[(int) $m] ?? $m) . ' ' . $y;
    }

    /**
     * True when every available month has at least one wilayah-tujuan row,
     * every row has a valid wilayah, and every category cell (kwh + rp) in
     * every row holds a numeric value (0 counts as filled — null/'' does not).
     */
    public function isBlok2GridComplete(): bool
    {
        $data = is_array($this->data_listrik) ? $this->data_listrik : [];
        foreach (self::availableMonthKeys() as $ym) {
            $rows = self::normalizeMonthRows($data[$ym] ?? null);
            if ($rows === []) {
                return false;
            }
            foreach ($rows as $row) {
                if (!self::wilayahValid($row['w'] ?? null)) {
                    return false;
                }
                foreach (array_keys(self::CATEGORIES) as $cat) {
                    foreach (['kwh', 'rp'] as $f) {
                        $v = $row[$cat][$f] ?? null;
                        if ($v === null || $v === '' || !is_numeric($v)) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function completionPercent(): int
    {
        $blocks = [$this->blok1_completed, $this->blok2_completed, $this->blok3_completed];
        return (int) round(count(array_filter($blocks)) / count($blocks) * 100);
    }

    /** Roman numeral for a quarter index, e.g. 3 => "III". */
    public static function romanQuarter(int $q): string
    {
        return [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'][$q] ?? (string) $q;
    }

    /** Empty per-category accumulator: ['rt' => ['kwh' => 0, 'rp' => 0], …]. */
    private static function emptyTotals(): array
    {
        $t = [];
        foreach (array_keys(self::CATEGORIES) as $cat) {
            $t[$cat] = ['kwh' => 0.0, 'rp' => 0.0];
        }
        $t['kwh'] = 0.0;
        $t['rp']  = 0.0;
        return $t;
    }

    private static function addInto(array &$totals, string $cat, float $kwh, float $rp): void
    {
        $totals[$cat]['kwh'] += $kwh;
        $totals[$cat]['rp']  += $rp;
        $totals['kwh']       += $kwh;
        $totals['rp']        += $rp;
    }

    /**
     * Blok II regrouped as quarters, each keeping its individual months and
     * their wilayah-tujuan rows, plus per-month and per-quarter subtotals.
     * The monthly grid runs to dozens of rows, so the BPS detail page and the
     * data PDF both render it quarter by quarter instead of as one long table.
     *
     * Returns a list of:
     *   ['label','year','quarter','months'=>[['key','label','rows','totals']],'totals']
     * where a row is ['wilayah' => string, 'cells' => [cat => ['kwh','rp']]].
     */
    public function quarterlyBreakdown(): array
    {
        $data     = is_array($this->data_listrik) ? $this->data_listrik : [];
        $quarters = [];

        foreach (self::availableMonthKeys() as $ym) {
            [$year, $month] = array_map('intval', explode('_', $ym));
            $q   = (int) ceil($month / 3);
            $qid = "{$year}_{$q}";

            if (!isset($quarters[$qid])) {
                $quarters[$qid] = [
                    'label'   => 'Triwulan ' . self::romanQuarter($q) . ' ' . $year,
                    'year'    => $year,
                    'quarter' => $q,
                    'months'  => [],
                    'totals'  => self::emptyTotals(),
                ];
            }

            $monthTotals = self::emptyTotals();
            $rows        = [];

            foreach (self::normalizeMonthRows($data[$ym] ?? null) as $row) {
                $cells = [];
                foreach (array_keys(self::CATEGORIES) as $cat) {
                    $kwh = (float) ($row[$cat]['kwh'] ?? 0);
                    $rp  = (float) ($row[$cat]['rp']  ?? 0);
                    $cells[$cat] = ['kwh' => $kwh, 'rp' => $rp];
                    self::addInto($monthTotals, $cat, $kwh, $rp);
                    self::addInto($quarters[$qid]['totals'], $cat, $kwh, $rp);
                }
                $rows[] = [
                    'wilayah' => self::wilayahLabel($row['w'] ?? null),
                    'cells'   => $cells,
                ];
            }

            $quarters[$qid]['months'][] = [
                'key'    => $ym,
                'label'  => self::monthLabel($ym),
                'rows'   => $rows,
                'totals' => $monthTotals,
            ];
        }

        return array_values($quarters);
    }
}
