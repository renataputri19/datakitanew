<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'user_type' => ['required', 'string', 'in:personal,instansi,akademisi'],
            'institution_type' => ['required_if:user_type,instansi', 'nullable', 'string', 'in:pemerintah,swasta,bumn,bumd,lainnya'],
            'institution_name' => ['required_if:user_type,instansi,akademisi', 'nullable', 'string', 'max:255'],
            'other_institution_name' => ['required_if:institution_name,Lainnya', 'nullable', 'string', 'max:255'],
            'institution_address' => ['required_if:user_type,instansi,akademisi', 'nullable', 'string', 'max:255'],
            'institution_phone' => ['required_if:user_type,instansi,akademisi', 'nullable', 'string', 'max:20'],
            'institution_website' => ['nullable', 'string', 'max:255', 'url'],
        ])->validate();

        // Create or find institution
        $institutionId = null;

        if (!empty($input['user_type'])) {
            // Only create institution for non-personal users
            if ($input['user_type'] !== 'personal') {
                // Determine institution name
                $institutionName = '';
                $academicType = null;

                if ($input['user_type'] === 'instansi') {
                    $institutionName = $input['institution_name'];
                } else if ($input['user_type'] === 'akademisi') {
                    if ($input['institution_name'] === 'Lainnya' && !empty($input['other_institution_name'])) {
                        $institutionName = $input['other_institution_name'];
                    } else {
                        $institutionName = $input['institution_name'];
                    }

                    // Determine academic type based on the selected institution
                    // This is a simple approach - you might want to create a more sophisticated mapping
                    if (strpos($institutionName, 'Universitas') !== false) {
                        $academicType = 'university';
                    } elseif (strpos($institutionName, 'Politeknik') !== false) {
                        $academicType = 'polytechnic';
                    } elseif (strpos($institutionName, 'Sekolah Tinggi') !== false || strpos($institutionName, 'STMIK') !== false || strpos($institutionName, 'STIE') !== false || strpos($institutionName, 'STAIN') !== false) {
                        $academicType = 'college';
                    } elseif (strpos($institutionName, 'Akademi') !== false) {
                        $academicType = 'academy';
                    } else {
                        $academicType = 'other';
                    }
                }

                // Create a new institution
                $institution = Institution::create([
                    'name' => $institutionName,
                    'type' => $input['user_type'],
                    'institution_type' => $input['user_type'] === 'instansi' ? $input['institution_type'] : null,
                    'academic_type' => $academicType,
                    'address' => $input['institution_address'] ?? null,
                    'phone' => $input['institution_phone'] ?? null,
                    'website' => $input['institution_website'] ?? null,
                ]);

                $institutionId = $institution->id;
            }
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'institution_id' => $institutionId,
            'is_bps' => false,
            'is_admin' => false,
            'is_superadmin' => false,
        ]);
    }
}
