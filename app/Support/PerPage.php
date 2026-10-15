<?php

namespace App\Support;

/**
 * Page sizes for the BPS data tables.
 *
 * The "Tampilkan" dropdown offers OPTIONS, and the controllers read the
 * request through {@see self::from()} so a hand-edited `?per_page=100000`
 * cannot pull a whole survey table into one response.
 */
final class PerPage
{
    public const DEFAULT = 15;

    public const OPTIONS = [15, 25, 50, 100];

    public static function from(mixed $value): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        return in_array($value, self::OPTIONS, true) ? $value : self::DEFAULT;
    }
}
