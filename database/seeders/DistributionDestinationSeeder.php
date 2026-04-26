<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistributionDestinationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('distribution_destinations')->upsert([
            [
                'code' => 'PKM001',
                'name' => 'Puskesmas Cikole',
                'destination_type' => 'puskesmas',
                'address' => 'Cikole, Sukabumi',
                'phone' => '0266-210001',
                'contact_person' => 'Siti',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PKM002',
                'name' => 'Puskesmas Warudoyong',
                'destination_type' => 'puskesmas',
                'address' => 'Warudoyong, Sukabumi',
                'phone' => '0266-210002',
                'contact_person' => 'Lina',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'KLN001',
                'name' => 'Klinik Keluarga Sehat',
                'destination_type' => 'klinik',
                'address' => 'Sukaraja, Sukabumi',
                'phone' => '0266-210003',
                'contact_person' => 'Rudi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BDN001',
                'name' => 'Bidan Praktik Mandiri Siti',
                'destination_type' => 'bidan',
                'address' => 'Citamiang, Sukabumi',
                'phone' => '081300000001',
                'contact_person' => 'Siti Aminah',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code'], ['name', 'destination_type', 'address', 'phone', 'contact_person', 'is_active', 'updated_at']);
    }
}
