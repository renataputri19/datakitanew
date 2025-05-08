<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'renata@gmail.com',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('renata'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'user@gmail.com',
                'is_bps' => false,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('renata'),
            ],
            [
                'name' => 'Admin BPS',
                'email' => 'admin.bps@gmail.com',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => true,
                'password' => Hash::make('renata'),
            ],
        ];

        // Insert users into the database
        foreach ($users as $user) {
            User::create($user);
        }
    }
}