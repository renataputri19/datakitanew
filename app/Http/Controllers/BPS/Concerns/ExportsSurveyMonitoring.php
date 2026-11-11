<?php

namespace App\Http\Controllers\BPS\Concerns;

use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel as ExcelWriter;

/**
 * Shared pieces of the "Export Excel" dialog behind the three BPS survey data
 * pages (SIBSTR / UB / Listrik): the writer format and the last-updated date
 * range, which every dialog offers.
 */
trait ExportsSurveyMonitoring
{
    /** Writer the dialog asked for; anything but an explicit "csv" is xlsx. */
    protected function exportFormat(?string $format): string
    {
        return $format === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX;
    }

    protected function exportExtension(string $writer): string
    {
        return $writer === ExcelWriter::CSV ? '.csv' : '.xlsx';
    }

    /**
     * Narrow a query to submissions last updated within the dialog's date
     * range. The dates are picked in WIB, so they are converted before they hit
     * the UTC-stored timestamps.
     */
    protected function applyUpdatedAtRange($query, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->where('updated_at', '>=', Carbon::parse($from, 'Asia/Jakarta')->startOfDay()->utc());
        }
        if ($to) {
            $query->where('updated_at', '<=', Carbon::parse($to, 'Asia/Jakarta')->endOfDay()->utc());
        }
    }

    /** Timestamp fragment every export filename ends with. */
    protected function exportStamp(): string
    {
        return now()->setTimezone('Asia/Jakarta')->format('Ymd_Hi');
    }

    /**
     * Write the superadmin audit row for an export. Called before the file is
     * built, and lets a failure propagate: an export that cannot be logged is
     * not served, so the trail at /superadmin/log-unduhan has no gaps.
     */
    protected function logExport(Request $request, string $survey, string $filename, string $writer, int $rows): void
    {
        ExportLog::record($request, $survey, $filename, $writer === ExcelWriter::CSV ? 'csv' : 'xlsx', $rows);
    }
}
