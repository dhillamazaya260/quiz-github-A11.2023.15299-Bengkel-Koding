<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan penting: Poli harus ada sebelum Dokter.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PoliSeeder::class,
            DokterSeeder::class,
            PasienSeeder::class,
            ObatSeeder::class,
        ]);
    }
}