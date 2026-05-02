<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicineCategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('medicine_categories')->upsert([
            [
                'name' => 'Pil KB',
                'description' => 'Obat kontrasepsi oral',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Suntik KB',
                'description' => 'Obat kontrasepsi injeksi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Implant',
                'description' => 'Alat atau obat kontrasepsi tanam',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'IUD',
                'description' => 'Alat kontrasepsi dalam rahim',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['description', 'updated_at']);
    }
}
