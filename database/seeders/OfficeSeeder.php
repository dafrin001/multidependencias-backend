<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            'Hacienda',
            'Planeación',
            'Salud',
            'Gobierno',
            'Educación',
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(['name' => $office]);
        }
    }
}
