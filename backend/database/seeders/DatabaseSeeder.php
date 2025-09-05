<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Agrega aquí todos los seeders que quieras ejecutar
        $this->call([
            AdminUserSeeder::class, // crea admin / admin
            // OtrosSeeders::class,
        ]);
    }
}
