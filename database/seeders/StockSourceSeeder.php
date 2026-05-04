<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSourceSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('stock_sources')->upsert([
            [
                'name' => 'BKKBN Provinsi Jawa Barat',
                'source_type' => 'bkkbn',
                'address' => 'Bandung',
                'phone' => '022-7000001',
                'contact_person' => 'Rina',
                'notes' => 'Sumber pengadaan utama dari distribusi provinsi untuk program KB.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dinas Kesehatan Kota Sukabumi',
                'source_type' => 'dinkes',
                'address' => 'Sukabumi',
                'phone' => '0266-200001',
                'contact_person' => 'Dedi',
                'notes' => 'Sumber pengadaan pendukung dari alokasi dinas kesehatan kota.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PT Supplier Sehat Sentosa',
                'source_type' => 'supplier',
                'address' => 'Jakarta',
                'phone' => '021-8000001',
                'contact_person' => 'Taufik',
                'notes' => 'Supplier rekanan untuk pemenuhan cadangan bila stok program belum cukup.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['source_type', 'address', 'phone', 'contact_person', 'notes', 'is_active', 'updated_at']);
    }
}
