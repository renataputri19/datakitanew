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
            'institution_address' => ['nullable', 'string', 'max:255'],
            'institution_phone' => ['nullable', 'string', 'max:20'],
            'institution_website' => ['nullable', 'string', 'max:255', 'url'],
        ])->validate();

        // Create or find institution
        $institutionId = null;

        if (!empty($input['user_type'])) {
            // Determine institution name
            $institutionName = $input['name']; // Default to user's name for personal

            if ($input['user_type'] === 'instansi') {
                $institutionName = $input['institution_name'];
            } else if ($input['user_type'] === 'akademisi') {
                if ($input['institution_name'] === 'Lainnya' && !empty($input['other_institution_name'])) {
                    $institutionName = $input['other_institution_name'];
                } else {
                    $institutionName = $input['institution_name'];
                }
            }

            // Create a new institution
            $institution = Institution::create([
                'name' => $institutionName,
                'type' => $input['user_type'],
                'institution_type' => $input['user_type'] === 'instansi' ? $input['institution_type'] : null,
                'address' => $input['institution_address'] ?? null,
                'phone' => $input['institution_phone'] ?? null,
                'website' => $input['institution_website'] ?? null,
            ]);

            $institutionId = $institution->id;
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
