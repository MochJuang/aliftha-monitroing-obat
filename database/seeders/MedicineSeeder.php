<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicineSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = DB::table('medicine_categories')->pluck('id', 'name');
        $units = DB::table('units')->pluck('id', 'name');
        $now = now();

        DB::table('medicines')->upsert([
            [
                'category_id' => $categories['Pil KB'] ?? null,
                'unit_id' => $units['Strip'] ?? null,
                'code' => 'PIL001',
                'name' => 'Pil KB Andalan',
                'brand' => 'Andalan',
                'dosage' => '28 tablet',
                'minimum_stock' => 100,
                'description' => 'Pil kontrasepsi oral',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => $categories['Suntik KB'] ?? null,
                'unit_id' => $units['Vial'] ?? null,
                'code' => 'SNT001',
                'name' => 'Suntik KB 3 Bulan',
                'brand' => 'Depo Medroxyprogesterone',
                'dosage' => '150 mg/ml',
                'minimum_stock' => 80,
                'description' => 'Suntik kontrasepsi 3 bulanan',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => $categories['Implant'] ?? null,
                'unit_id' => $units['Set'] ?? null,
                'code' => 'IMP001',
                'name' => 'Implant 2 Batang',
                'brand' => 'Indoplant',
                'dosage' => '2 batang',
                'minimum_stock' => 100,
                'description' => 'Kontrasepsi implant 2 batang',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => $categories['IUD'] ?? null,
                'unit_id' => $units['Set'] ?? null,
                'code' => 'IUD001',
                'name' => 'IUD Copper T 380A',
                'brand' => 'Copper T',
                'dosage' => '380A',
                'minimum_stock' => 25,
                'description' => 'Alat kontrasepsi dalam rahim',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code'], [
            'category_id',
            'unit_id',
            'name',
            'brand',
            'dosage',
            'minimum_stock',
            'description',
            'is_active',
            'updated_at',
        ]);
    }
}
