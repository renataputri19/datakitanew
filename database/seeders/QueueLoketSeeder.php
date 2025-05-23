<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QueueLoket;

class QueueLoketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default loket data
        $lokets = [
            [
                'no_loket' => '1',
                'nama_loket' => 'Loket 1',
                'is_active' => true,
            ],
            [
                'no_loket' => '2',
                'nama_loket' => 'Loket 2',
                'is_active' => true,
            ],
            [
                'no_loket' => '3',
                'nama_loket' => 'Loket 3',
                'is_active' => true,
            ],
        ];

        // Insert loket data
        foreach ($lokets as $loket) {
            QueueLoket::create($loket);
        }
    }
}
