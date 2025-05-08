<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class AcademicInstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing academic institutions with their types
        $this->updateExistingInstitutions();
    }

    /**
     * Update existing academic institutions with their types.
     */
    private function updateExistingInstitutions(): void
    {
        $institutions = Institution::where('type', 'akademisi')->get();

        foreach ($institutions as $institution) {
            $academicType = $this->determineAcademicType($institution->name);
            $institution->academic_type = $academicType;
            $institution->save();
        }
    }

    /**
     * Determine the academic type based on the institution name.
     *
     * @param string $name
     * @return string
     */
    private function determineAcademicType(string $name): string
    {
        if (strpos($name, 'Universitas') !== false) {
            return 'university';
        } elseif (strpos($name, 'Politeknik') !== false) {
            return 'polytechnic';
        } elseif (
            strpos($name, 'Sekolah Tinggi') !== false || 
            strpos($name, 'STMIK') !== false || 
            strpos($name, 'STIE') !== false || 
            strpos($name, 'STAIN') !== false
        ) {
            return 'college';
        } elseif (strpos($name, 'Akademi') !== false) {
            return 'academy';
        } else {
            return 'other';
        }
    }
}
