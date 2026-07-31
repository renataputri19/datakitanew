<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Base sheet for the BPS "monitoring" exports of the three surveys
 * (SIBSTR / UB / Listrik).
 *
 * Each row is one submission: the Blok I identity answers plus the columns BPS
 * actually monitors on — which blocks are filled and whether the submission is
 * finished or still in progress. Subclasses supply the headings and the per-row
 * mapping; the header styling, freeze pane and autofilter are shared so the
 * three files open the same way.
 *
 * Empty answers are written as blank cells (not a dash) so Excel's own filters
 * and "count blanks" work as a completeness check.
 */
abstract class SurveyMonitoringExport implements FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize
{
    /** @param Collection<int, Model> $records */
    public function __construct(protected Collection $records)
    {
    }

    /** Column values for one submission, in the same order as headings(). */
    abstract protected function row(Model $record, int $no): array;

    public function array(): array
    {
        $rows = [];
        $no   = 0;

        foreach ($this->records as $record) {
            $rows[] = $this->row($record, ++$no);
        }

        return $rows;
    }

    /**
     * Cell the sheet is frozen at — everything above and to the left stays put
     * while scrolling. Subclasses widen it to keep the company name in view.
     */
    protected function freezeCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
                    'alignment' => ['vertical' => 'center', 'wrapText' => true],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->freezePane($this->freezeCell());
                $sheet->setAutoFilter("A1:{$lastCol}1");

                if ($lastRow > 1) {
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                        'alignment' => ['vertical' => 'top'],
                    ]);
                }
            },
        ];
    }

    /** Blank cell for a missing answer, so completeness reads at a glance. */
    protected function val(mixed $v): string
    {
        return ($v === null || $v === '') ? '' : (string) $v;
    }

    /** Label a coded answer, keeping the raw code when it is unknown. */
    protected function label(mixed $v, array $map): string
    {
        if ($v === null || $v === '') {
            return '';
        }

        return $map[(string) $v] ?? (string) $v;
    }

    protected function flag(mixed $done): string
    {
        return $done ? 'Lengkap' : 'Belum';
    }

    protected function datetime(mixed $dt): string
    {
        return $dt ? $dt->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') : '';
    }
}
