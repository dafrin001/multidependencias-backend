<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Equipo de Cómputo', 'code' => 'COMP-001'],
            ['name' => 'Vehículos', 'code' => 'VEH-001'],
            ['name' => 'Muebles de Oficina', 'code' => 'MUB-001'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['code' => $category['code']], $category);
        }
    }
}
