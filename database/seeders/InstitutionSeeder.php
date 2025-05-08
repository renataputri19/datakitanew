<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        // Sample institutions
        $institutions = [
            [
                'name' => 'BPS Kota Batam',
                'type' => 'Government',
                'institution_type' => 'Statistical Office',
                'academic_type' => null,
                'address' => 'Jl. Raya Batam, Kota Batam, Kepulauan Riau',
                'phone' => '+62 778 123456',
                'website' => 'https://bps.go.id/batam',
                'description' => 'Badan Pusat Statistik Kota Batam provides statistical data and analysis.',
            ],
            [
                'name' => 'UMKM Association Batam',
                'type' => 'Non-Government',
                'institution_type' => 'Association',
                'academic_type' => null,
                'address' => 'Jl. Industri Batam, Kota Batam, Kepulauan Riau',
                'phone' => '+62 778 654321',
                'website' => 'https://umkm-batam.org',
                'description' => 'Association supporting micro, small, and medium enterprises in Batam.',
            ],
        ];

        // Insert institutions with UUIDs
        foreach ($institutions as $institution) {
            Institution::create($institution);
        }
    }
}