<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundingSourceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('funding_sources')->upsert([
            [
                'code' => 'APBD',
                'name' => 'Anggaran Pendapatan dan Belanja Daerah',
                'source_type' => 'Pemerintah Daerah',
                'notes' => 'Sumber dana dari APBD Kota Sukabumi.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'APBN',
                'name' => 'Anggaran Pendapatan dan Belanja Negara',
                'source_type' => 'Pemerintah Pusat',
                'notes' => 'Sumber dana dari APBN melalui program nasional.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BOKB',
                'name' => 'Bantuan Operasional Keluarga Berencana',
                'source_type' => 'Dana Khusus',
                'notes' => 'Sumber dana operasional program keluarga berencana.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'DAK',
                'name' => 'Dana Alokasi Khusus',
                'source_type' => 'Transfer Pemerintah',
                'notes' => 'Sumber dana transfer khusus sektor kesehatan.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code'], ['name', 'source_type', 'notes', 'is_active', 'updated_at']);
    }
}
