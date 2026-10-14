<?php

namespace App\Support;

/**
 * Renders a BPS-written reminder template (subject + body with {placeholders})
 * for one respondent and one survey period.
 *
 * The body is plain text, not HTML or Markdown: BPS staff type it in a
 * textarea. It is escaped first, so nothing typed can inject markup; then
 * placeholders are filled, **teks** becomes bold, a blank line starts a new
 * paragraph and a single Enter is a line break. {tombol} marks where the
 * "Isi Survei" button goes (after the body when absent).
 *
 * Placeholders carry the period, so one template serves every quarter —
 * TW I–III stragglers during TW IV collection get their own months.
 */
class ReminderTemplate
{
    public const DEFAULT_NAME = 'Pengingat standar';

    public const DEFAULT_SUBJECT = 'Pengingat Pengisian SIBSTR Triwulan {triwulan} {tahun} — BPS Kota Batam';

    public const DEFAULT_BODY = <<<'TXT'
        Yth. {penghubung}
        **{nama_perusahaan}**

        Pengisian **Survei Industri Besar dan Sedang Triwulanan (SIBSTR) {periode}** telah dibuka.

        {kalimat_status}

        {tombol}

        Silakan masuk menggunakan akun DataKita yang biasa digunakan untuk mengisi survei SIBSTR.

        Data yang Bapak/Ibu berikan dijamin kerahasiaannya dan hanya digunakan untuk keperluan statistik.

        Jika ada yang perlu dikonfirmasi terkait pengiriman email pengisian survei, Bapak/Ibu dapat menghubungi Sdri. **Ridha Amalia Hakim (085255557116)**.

        Terima kasih atas partisipasi Bapak/Ibu.

        Hormat kami,
        **BPS Kota Batam**
        TXT;

    private const ROMAN = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

    private const BULAN = [
        1 => 'Januari–Maret',
        2 => 'April–Juni',
        3 => 'Juli–September',
        4 => 'Oktober–Desember',
    ];

    /** Placeholder => what it becomes, for the editor's help panel. */
    public static function placeholders(): array
    {
        return [
            'penghubung'      => 'Nama penghubung (Blok I 105), atau "Bapak/Ibu" bila kosong',
            'nama_perusahaan' => 'Nama perusahaan (Blok I 101); baris dihapus bila kosong',
            'periode'         => 'Triwulan III 2026 (Juli–September)',
            'triwulan'        => 'III',
            'tahun'           => '2026',
            'bulan'           => 'Juli–September',
            'kalimat_status'  => 'Kalimat sesuai status: belum mulai mengisi / sudah mulai tapi belum selesai',
            'status_isian'    => 'belum mulai mengisi / sudah mulai mengisi tetapi belum selesai',
            'link'            => 'Alamat halaman pengisian SIBSTR',
            'tombol'          => 'Posisi tombol "Isi Survei SIBSTR" (hanya di isi email)',
        ];
    }

    public static function periodLabel(int $tahun, int $triwulan): string
    {
        return 'Triwulan ' . self::roman($triwulan) . " {$tahun} (" . (self::BULAN[$triwulan] ?? '') . ')';
    }

    public static function roman(int $triwulan): string
    {
        return self::ROMAN[$triwulan] ?? (string) $triwulan;
    }

    /** Plain-text values for one respondent and period. */
    public static function variables(int $tahun, int $triwulan, ?string $namaPerusahaan, ?string $penghubung, bool $inProgress, string $url): array
    {
        return [
            'penghubung'      => filled($penghubung) ? trim($penghubung) : 'Bapak/Ibu',
            'nama_perusahaan' => trim((string) $namaPerusahaan),
            'periode'         => self::periodLabel($tahun, $triwulan),
            'triwulan'        => self::roman($triwulan),
            'tahun'           => (string) $tahun,
            'bulan'           => self::BULAN[$triwulan] ?? '',
            'kalimat_status'  => $inProgress
                ? 'Isian perusahaan Bapak/Ibu untuk periode ini sudah dimulai tetapi **belum diselesaikan**. Mohon kesediaannya untuk melanjutkan dan menyelesaikan pengisian.'
                : 'Perusahaan Bapak/Ibu **belum mengisi** kuesioner untuk periode ini. Mohon kesediaannya untuk melakukan pengisian.',
            'status_isian'    => $inProgress ? 'sudah mulai mengisi tetapi belum selesai' : 'belum mulai mengisi',
            'link'            => $url,
        ];
    }

    /** Placeholders used in $text that do not exist — for validation. */
    public static function unknownPlaceholders(string $text): array
    {
        preg_match_all('/\{([^{}\s]+)\}/', $text, $m);

        return array_values(array_unique(array_diff($m[1], array_keys(self::placeholders()))));
    }

    public static function subject(string $template, array $vars): string
    {
        $text = strtr($template, self::pairs(array_diff_key($vars, ['tombol' => 1])) + ['{tombol}' => '']);
        $text = str_replace('**', '', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Body as HTML, split at {tombol}.
     *
     * @return array{0: string, 1: string}  [before button, after button]
     */
    public static function body(string $template, array $vars): array
    {
        $text  = str_replace(["\r\n", "\r"], "\n", $template);
        $parts = explode('{tombol}', $text, 2);

        return [
            self::paragraphs($parts[0], $vars),
            self::paragraphs(str_replace('{tombol}', '', $parts[1] ?? ''), $vars),
        ];
    }

    /**
     * One <p> per paragraph, every line at column 0 and no blank lines, so
     * the Markdown pass of Laravel's mail layout leaves it as a raw HTML block.
     */
    private static function paragraphs(string $text, array $vars): string
    {
        $url  = (string) ($vars['link'] ?? '');
        $html = strtr(e($text), self::pairs(array_map('e', array_diff_key($vars, ['link' => 1]))) + [
            '{link}' => '<a href="' . e($url) . '">' . e($url) . '</a>',
        ]);
        $html = preg_replace('/\*\*(.*?)\*\*/u', '<strong>$1</strong>', $html);

        return collect(preg_split('/\n\s*\n/', $html))
            ->map(function (string $para) {
                // A line emptied by a blank placeholder (no company name)
                // is dropped rather than left as a gap.
                $lines = collect(explode("\n", $para))
                    ->map(fn ($l) => trim($l))
                    ->filter(fn ($l) => trim(strip_tags($l)) !== '');

                return $lines->isEmpty() ? null : '<p>' . $lines->implode("<br>\n") . '</p>';
            })
            ->filter()
            ->implode("\n");
    }

    private static function pairs(array $vars): array
    {
        $pairs = [];
        foreach ($vars as $key => $value) {
            $pairs['{' . $key . '}'] = (string) $value;
        }

        return $pairs;
    }
}
