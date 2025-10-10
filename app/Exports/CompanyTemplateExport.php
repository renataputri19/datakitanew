<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Return sample data for template
        return [
            ['PT. Contoh Perusahaan 1', 'Jl. Contoh No. 1, Jakarta Pusat, DKI Jakarta'],
            ['CV. Contoh Perusahaan 2', 'Jl. Contoh No. 2, Bandung, Jawa Barat'],
            ['', ''], // Empty row for user input
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Perusahaan',
            'Alamat',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
