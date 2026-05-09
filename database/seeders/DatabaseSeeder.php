<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // Monalisa structure (domains/aspects/indikators)
            // MonalisaStructureSeeder::class,
            // Users (BPS + Kominfo sample users)
            // UserSeeder::class,
            // Mitra user for SIBSTR results access
            // MitraUserSeeder::class,
            // NewsSeeder::class,
            // VideoSeeder::class,
            // AcademicInstitutionSeeder::class,
            // InstitutionSeeder::class,
            // SystemSeeder::class,
            // QueueLoketSeeder::class,
        ]);
    }
}
