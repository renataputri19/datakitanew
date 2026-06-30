<?php

namespace App\Support;

use App\Models\SurveyResponse;
use App\Models\UbSurveyResponse;

/**
 * Builds the "cross-fill" field lists that let a respondent copy the overlapping
 * Blok I answers between the SIBSTR survey and the SE2026 UB survey.
 *
 * Both surveys are keyed to the same user, so wherever the two questionnaires ask
 * the same thing we expose a manual "Salin" button (rendered by the cross-fill
 * drawer partial). Copying is one value per click and never automatic — the
 * respondent decides whether to pull a value.
 *
 * Each returned item is:
 *   label    — human label shown in the drawer row
 *   target   — the destination form field `name` to write into
 *   value    — value to set on that field (already code-translated; null when none)
 *   display  — human-readable text shown in the drawer row
 *   copyable — false when the source is empty or has no valid cross-survey mapping
 */
class SurveyCrossFill
{
    /** UB jenis_kawasan code → SIBSTR jenis_kawasan key (only the shared options map). */
    private const KAWASAN_UB_TO_SIBSTR = [
        1  => 'ekonomi_khusus',
        2  => 'industri',
        10 => 'luar_kawasan',
    ];

    /** SIBSTR jenis_kawasan key → UB jenis_kawasan code. */
    private const KAWASAN_SIBSTR_TO_UB = [
        'ekonomi_khusus' => 1,
        'industri'       => 2,
        'luar_kawasan'   => 10,
    ];

    /** Human labels for every UB jenis_kawasan code (for drawer display). */
    private const KAWASAN_UB_LABELS = [
        1  => 'Kawasan Ekonomi Khusus (KEK)',
        2  => 'Kawasan Industri (KI)',
        3  => 'Stasiun',
        4  => 'Bandara',
        5  => 'Pelabuhan',
        6  => 'Terminal',
        7  => 'Rest area jalan tol',
        8  => 'Kawasan sentra ekonomi perdesaan/kelurahan',
        9  => 'Kawasan usaha lainnya',
        10 => 'Di luar kawasan',
    ];

    /**
     * Build the items used to fill a SIBSTR Blok 1 form from the user's UB response.
     * `target` values are SIBSTR form field names.
     */
    public static function ubToSibstr(UbSurveyResponse $ub): array
    {
        $items = [
            self::text('Nama Perusahaan',        'nama_perusahaan', $ub->nama_perusahaan),
            self::text('Kabupaten / Kota',       'kabupaten_kota',  $ub->kabupaten_kota),
            self::text('Alamat',                 'alamat_pabrik',   $ub->alamat_perusahaan),
            self::text('Telepon',                'telepon_fax',     $ub->nomor_telepon),
            self::text('Email',                  'email',           $ub->email_perusahaan),
            self::text('Homepage / Website',     'homepage',        $ub->homepage),
            self::text('NIB',                    'nib',             $ub->nib),
            self::text('Nama Kawasan',           'nama_kawasan',    $ub->nama_kawasan),
            self::text('Nama Penanggung Jawab',  'legalisasi_nama', $ub->nama_pengusaha),
            self::text('NIK',                    'legalisasi_nik',  $ub->nik),
        ];

        // Jenis Kawasan — translate UB code → SIBSTR key (only KEK/KI/Luar map across)
        $ubKawasan  = $ub->jenis_kawasan !== null && $ub->jenis_kawasan !== '' ? (int) $ub->jenis_kawasan : null;
        $sibKawasan = $ubKawasan !== null ? (self::KAWASAN_UB_TO_SIBSTR[$ubKawasan] ?? null) : null;
        $items[] = [
            'label'    => 'Jenis Kawasan',
            'target'   => 'jenis_kawasan',
            'value'    => $sibKawasan,
            'display'  => self::kawasanDisplay($ubKawasan, $sibKawasan, SurveyResponse::getJenisKawasanOptions()),
            'copyable' => $sibKawasan !== null,
        ];

        // Jenis Kelamin penanggung jawab — UB 1/2 → SIBSTR laki_laki/perempuan
        $ubKelamin  = $ub->jenis_kelamin !== null && $ub->jenis_kelamin !== '' ? (int) $ub->jenis_kelamin : null;
        $sibKelamin = match ($ubKelamin) {
            1 => 'laki_laki',
            2 => 'perempuan',
            default => null,
        };
        $items[] = [
            'label'    => 'Jenis Kelamin (Penanggung Jawab)',
            'target'   => 'legalisasi_jenis_kelamin',
            'value'    => $sibKelamin,
            'display'  => self::kelaminDisplay($ubKelamin),
            'copyable' => $sibKelamin !== null,
        ];

        return $items;
    }

    /**
     * Build the items used to fill a UB Blok I-A form from the user's SIBSTR response.
     * `target` values are UB form field names.
     */
    public static function sibstrToUb(SurveyResponse $sibstr): array
    {
        $items = [
            self::text('Nama Perusahaan',     'nama_perusahaan',   $sibstr->nama_perusahaan),
            self::text('Kabupaten / Kota',    'kabupaten_kota',    $sibstr->kabupaten_kota),
            self::text('Alamat',              'alamat_perusahaan', $sibstr->alamat_pabrik),
            self::text('Telepon',             'nomor_telepon',     $sibstr->telepon_fax),
            self::text('Email',               'email_perusahaan',  $sibstr->email),
            self::text('Homepage / Website',  'homepage',          $sibstr->homepage),
            // Copying NIB also flips "Punya NIB? → Ya" so the (otherwise hidden) field shows.
            self::text('NIB',                 'nib',               $sibstr->nib, [['target' => 'has_nib', 'value' => 1]]),
            self::text('Nama Kawasan',        'nama_kawasan',      $sibstr->nama_kawasan),
            self::text('Nama Pengusaha',      'nama_pengusaha',    $sibstr->legalisasi_nama),
            self::text('NIK',                 'nik',               $sibstr->legalisasi_nik),
        ];

        // Jenis Kawasan — SIBSTR key → UB code (all three SIBSTR options map across)
        $sibKawasan = $sibstr->jenis_kawasan ?: null;
        $ubKawasan  = $sibKawasan !== null ? (self::KAWASAN_SIBSTR_TO_UB[$sibKawasan] ?? null) : null;
        $items[] = [
            'label'    => 'Jenis Kawasan',
            'target'   => 'jenis_kawasan',
            'value'    => $ubKawasan,
            'display'  => $ubKawasan !== null ? (self::KAWASAN_UB_LABELS[$ubKawasan] ?? '—') : '—',
            'copyable' => $ubKawasan !== null,
        ];

        // Jenis Kelamin pengusaha — SIBSTR laki_laki/perempuan → UB 1/2
        $sibKelamin = $sibstr->legalisasi_jenis_kelamin ?: null;
        $ubKelamin  = match ($sibKelamin) {
            'laki_laki' => 1,
            'perempuan' => 2,
            default => null,
        };
        $items[] = [
            'label'    => 'Jenis Kelamin',
            'target'   => 'jenis_kelamin',
            'value'    => $ubKelamin,
            'display'  => self::kelaminDisplay($ubKelamin),
            'copyable' => $ubKelamin !== null,
        ];

        return $items;
    }

    /** True when at least one item in the list can actually be copied. */
    public static function hasCopyable(array $items): bool
    {
        foreach ($items as $item) {
            if (!empty($item['copyable'])) {
                return true;
            }
        }
        return false;
    }

    // ── helpers ────────────────────────────────────────────────────────────────

    /**
     * A plain text field item; copyable only when the source has a value.
     *
     * @param array $also Companion fields to set alongside this one when copied,
     *                    each ['target' => string, 'value' => mixed]. Used e.g. to
     *                    flip a gating radio so the copied value becomes visible.
     */
    private static function text(string $label, string $target, $value, array $also = []): array
    {
        $clean = is_string($value) ? trim($value) : $value;
        $has   = $clean !== null && $clean !== '';
        return [
            'label'    => $label,
            'target'   => $target,
            'value'    => $has ? (string) $clean : null,
            'display'  => $has ? (string) $clean : '—',
            'copyable' => $has,
            'also'     => $has ? $also : [],
        ];
    }

    private static function kawasanDisplay(?int $ubCode, ?string $sibKey, array $sibOptions): string
    {
        if ($ubCode === null) {
            return '—';
        }
        if ($sibKey !== null) {
            return $sibOptions[$sibKey] ?? (self::KAWASAN_UB_LABELS[$ubCode] ?? '—');
        }
        // UB has a kawasan type (codes 3–9) with no SIBSTR equivalent.
        return (self::KAWASAN_UB_LABELS[$ubCode] ?? '—') . ' — tidak ada padanan di SIBSTR';
    }

    private static function kelaminDisplay(?int $code): string
    {
        return match ($code) {
            1 => 'Laki-laki',
            2 => 'Perempuan',
            default => '—',
        };
    }
}
