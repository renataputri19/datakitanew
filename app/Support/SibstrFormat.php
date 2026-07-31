<?php

namespace App\Support;

/**
 * Value formatters for the read-only SIBSTR data views (BPS detail page, Mitra
 * detail page and the PDF). Replaces the per-partial `function_exists()`
 * helpers those views used to declare, so one dash convention and one number
 * format apply everywhere.
 */
class SibstrFormat
{
    public const DASH = '—';

    /**
     * Parse a stored figure into a float, or null when there is nothing to show.
     *
     * The survey forms format currency/quantity inputs in the browser with
     * Indonesian separators ("12.312.312.213.234") and persist the *formatted*
     * string, so the stored JSON mixes plain numbers with grouped ones. A plain
     * is_numeric() check rejects the grouped values and a (float) cast truncates
     * them at the first dot — both make filled answers read as empty here.
     *
     * $grouped=false keeps a single dot group ("213.213") as a decimal point
     * instead of a thousand separator; used for figures that can legitimately
     * carry decimals (unit prices, percentages).
     */
    public static function num(mixed $v, bool $grouped = true): ?float
    {
        $s = self::canonical($v, $grouped);

        return $s === null ? null : (float) $s;
    }

    /**
     * The same parse as num(), but kept as a plain "-123.45" string so callers
     * that only reformat digits (idr()) don't push a 20-digit rupiah figure
     * through a float and lose its tail.
     */
    private static function canonical(mixed $v, bool $grouped = true): ?string
    {
        if ($v === null || is_bool($v) || is_array($v)) {
            return null;
        }

        if (is_int($v)) {
            return (string) $v;
        }

        if (is_float($v)) {
            return is_finite($v) ? sprintf('%.10F', $v) : null;
        }

        $s = trim((string) $v);
        if ($s === '' || !preg_match('/^-?[0-9][0-9.,]*$/', $s)) {
            return null;
        }

        $sign = $s[0] === '-' ? '-' : '';
        $s    = ltrim($s, '-');

        if (str_contains($s, ',')) {
            // id-ID: the comma is the decimal separator, dots group thousands.
            $cut = strrpos($s, ',');
            $s   = str_replace(['.', ','], '', substr($s, 0, $cut)) . '.' . str_replace([',', '.'], '', substr($s, $cut + 1));
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $s) && ($grouped || substr_count($s, '.') > 1)) {
            // 12.345.678 — thousand separators only when every group is 3 digits.
            $s = str_replace('.', '', $s);
        }

        return is_numeric($s) ? $sign . $s : null;
    }

    /** Rupiah / whole-number figure with Indonesian thousand separators. */
    public static function idr(mixed $v): string
    {
        $s = self::canonical($v);
        if ($s === null) {
            return self::DASH;
        }

        // Whole numbers are grouped digit-by-digit so figures beyond float
        // precision (rupiah totals run long) come out exactly as entered.
        if (!preg_match('/^(-?)(\d+)$/', $s, $m)) {
            return number_format((float) $s, 0, ',', '.');
        }

        $digits = ltrim($m[2], '0');
        $digits = $digits === '' ? '0' : $digits;

        return ($digits === '0' ? '' : $m[1]) . strrev(implode('.', str_split(strrev($digits), 3)));
    }

    /** Two-decimal figure (unit prices, percentage totals). */
    public static function dec(mixed $v): string
    {
        $n = self::num($v, false);

        return $n === null ? self::DASH : number_format($n, 2, ',', '.');
    }

    /** Raw value, dash when empty. */
    public static function plain(mixed $v): string
    {
        if ($v === null || $v === '') {
            return self::DASH;
        }

        return (string) $v;
    }

    /** Percentage with a trailing sign, dash when empty. */
    public static function pct(mixed $v): string
    {
        $n = self::num($v, false);
        if ($n === null) {
            return self::DASH;
        }

        return number_format($n, floor($n) == $n ? 0 : 2, ',', '.') . ' %';
    }

    /** 'ya' / 'tidak' answers. */
    public static function yaTidak(mixed $v): string
    {
        return match ((string) $v) {
            'ya'    => 'Ya',
            'tidak' => 'Tidak',
            default => self::DASH,
        };
    }

    /** '1' / '2' answers used by the Blok IIIC prospek questions. */
    public static function satuDua(mixed $v): string
    {
        return match ((string) $v) {
            '1'     => 'Ya',
            '2'     => 'Tidak',
            default => self::DASH,
        };
    }

    /** Checkbox flags stored as 0/1. */
    public static function checkbox(mixed $v): string
    {
        return $v ? 'Ya' : 'Tidak';
    }

    public static function kondisiPerusahaan(mixed $v): string
    {
        return [
            'masih_aktif'            => 'Masih Aktif',
            'belum_beroperasi'       => 'Belum Beroperasi',
            'tutup'                  => 'Tutup',
            'pindah'                 => 'Pindah',
            'tidak_ditemukan'        => 'Tidak Ditemukan',
            'double_ganda_duplikat'  => 'Double / Ganda / Duplikat',
        ][(string) $v] ?? self::plain($v);
    }

    public static function jaringanUnit(mixed $v): string
    {
        return [
            'tunggal'                              => 'Tunggal',
            'pabrik_unit_produksi'                 => 'Pabrik/Unit Produksi, Cabang atau Perwakilan',
            'pusat_ada_kegiatan_produksi'          => 'Pusat, ada kegiatan produksi',
            'kantor_pusat_administrasi_perwakilan' => 'Kantor Pusat/Administrasi/Perwakilan',
            'unit_pembantu_penunjang'              => 'Unit Pembantu/Penunjang',
        ][(string) $v] ?? self::plain($v);
    }

    public static function jenisKelamin(mixed $v): string
    {
        return match ((string) $v) {
            'laki_laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            default     => self::DASH,
        };
    }

    public static function produksiRamahLingkungan(mixed $v): string
    {
        return match ((string) $v) {
            'ya_seluruh'  => 'Ya, seluruhnya',
            'ya_sebagian' => 'Ya, sebagian',
            'tidak'       => 'Tidak sama sekali',
            default       => self::DASH,
        };
    }

    /** Q318c1 asset-value bracket. */
    public static function rentangAset(mixed $v): string
    {
        return [
            '1' => '1 s.d. Rp 500 juta',
            '2' => 'Lebih dari Rp 500 juta s.d. Rp 1 miliar',
            '3' => 'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',
            '4' => 'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',
            '5' => 'Lebih dari Rp 10 miliar',
        ][(string) $v] ?? self::plain($v);
    }

    /**
     * Sum a set of keys from an array, used where a stored total may be 0/empty
     * but the components are present (Q318c total assets, Q319i total capital).
     */
    public static function sumOrStored(array $data, string $storedKey, array $partKeys): ?float
    {
        $stored = self::num($data[$storedKey] ?? null);
        if ($stored !== null && $stored > 0) {
            return $stored;
        }

        $sum = 0.0;
        foreach ($partKeys as $key) {
            $sum += self::num($data[$key] ?? null) ?? 0.0;
        }

        return $sum > 0 ? $sum : null;
    }
}
