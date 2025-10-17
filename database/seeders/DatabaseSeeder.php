<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Anda bisa biarkan atau hapus bagian ini sesuai kebutuhan
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Daftarkan semua seeder Anda di dalam array ini
        $this->call([
            UserRoleSeeder::class,
            ProductSeeder::class, // <-- TAMBAHKAN BARIS INI
        ]);
    }
}