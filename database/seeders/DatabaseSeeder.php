<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AreaSeeder::class,
            OfficeSeeder::class,
            CategorySeeder::class,
            FullInventorySeeder::class,
            NewModulesSeeder::class,
            HrSeeder::class,
        ]);

        // Usuario administrador por defecto
        User::firstOrCreate(
            ['email' => 'admin@alcaldia.gov'],
            [
                'name'     => 'Administrador Municipal',
                'password' => Hash::make('password'),
            ]
        );
    }
}
