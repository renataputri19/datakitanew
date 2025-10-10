<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $companies = [
            [
                'nama_perusahaan' => 'PT. Astra International Tbk',
                'alamat' => 'Jl. Gaya Motor Raya No. 8, Sunter II, Jakarta Utara 14330, DKI Jakarta',
            ],
            [
                'nama_perusahaan' => 'PT. Bank Central Asia Tbk',
                'alamat' => 'Menara BCA, Grand Indonesia, Jl. M.H. Thamrin No. 1, Jakarta Pusat 10310, DKI Jakarta',
            ],
            [
                'nama_perusahaan' => 'PT. Telekomunikasi Indonesia Tbk',
                'alamat' => 'Jl. Japati No. 1, Bandung 40133, Jawa Barat',
            ],
            [
                'nama_perusahaan' => 'PT. Unilever Indonesia Tbk',
                'alamat' => 'Grha Unilever, Green Office Park Kav. 3, BSD City, Tangerang 15345, Banten',
            ],
            [
                'nama_perusahaan' => 'PT. Indofood Sukses Makmur Tbk',
                'alamat' => 'Sudirman Plaza, Indofood Tower, Jl. Jend. Sudirman Kav. 76-78, Jakarta 12910, DKI Jakarta',
            ],
            [
                'nama_perusahaan' => 'PT. Gudang Garam Tbk',
                'alamat' => 'Jl. Semampir II No. 1, Kediri 64121, Jawa Timur',
            ],
            [
                'nama_perusahaan' => 'PT. Semen Indonesia Tbk',
                'alamat' => 'Jl. Veteran, Gresik 61122, Jawa Timur',
            ],
            [
                'nama_perusahaan' => 'PT. Pertamina (Persero)',
                'alamat' => 'Jl. Medan Merdeka Timur No. 1A, Jakarta Pusat 10110, DKI Jakarta',
            ],
            [
                'nama_perusahaan' => 'PT. Garuda Indonesia (Persero) Tbk',
                'alamat' => 'Jl. Ir. H. Juanda No. 15, Bandung 40135, Jawa Barat',
            ],
            [
                'nama_perusahaan' => 'PT. Krakatau Steel (Persero) Tbk',
                'alamat' => 'Jl. Industri No. 5, Cilegon 42435, Banten',
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
