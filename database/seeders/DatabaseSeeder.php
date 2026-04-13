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
        // KODE BAWAAN LARAVEL DI BAWAH INI KITA HAPUS/KOMENTARI SAJA:
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // KITA GANTI DENGAN MEMANGGIL SEEDER BUATAN KITA:
        $this->call([
            UserSeeder::class,
        ]);
    }
}