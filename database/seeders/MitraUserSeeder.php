<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MitraUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotent: uses updateOrCreate so it is safe to run multiple times.
     */
    public function run(): void
    {
        // User::updateOrCreate(
        //     ['email' => 'akunmitra21713@gmail.com'],
        //     [
        //         'name'            => 'Mitra SIBSTR',
        //         'password'        => Hash::make('Mitra21713'),
        //         'is_mitra'        => true,
        //         'is_bps'          => false,
        //         'is_admin'        => false,
        //         'is_superadmin'   => false,
        //         'is_kominfo_user' => false,
        //     ]
        // );

        User::updateOrCreate(
            ['email' => 'aditprameswara@bps.go.id'],
            [
                'name'            => 'Adit Prameswara',
                'password'        => Hash::make('Bps-12345'),
                'is_mitra'        => true,
                'is_bps'          => false,
                'is_admin'        => false,
                'is_superadmin'   => false,
                'is_kominfo_user' => false,
            ]
        );
    }
}
