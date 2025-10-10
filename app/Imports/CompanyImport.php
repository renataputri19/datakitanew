<?php

namespace App\Imports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CompanyImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip empty rows - only check if nama_perusahaan is empty since alamat can be null
        if (empty($row['nama_perusahaan'])) {
            return null;
        }

        // Check if company already exists
        $existingCompany = Company::where('nama_perusahaan', $row['nama_perusahaan'])->first();
        
        if ($existingCompany) {
            // Update existing company - alamat can be null/empty
            $existingCompany->update([
                'alamat' => $row['alamat'] ?? null,
            ]);
            return null; // Don't create new model
        }

        return new Company([
            'nama_perusahaan' => $row['nama_perusahaan'],
            'alamat' => $row['alamat'] ?? null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
        ];
    }
}
