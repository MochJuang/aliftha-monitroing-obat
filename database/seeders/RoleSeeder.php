<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('roles')->upsert([
            [
                'name' => 'admin',
                'description' => 'Mengelola seluruh data dan pengguna',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'petugas_gudang',
                'description' => 'Mengelola stok masuk, stok keluar, dan monitoring',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'pimpinan',
                'description' => 'Melihat dashboard dan laporan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['description', 'updated_at']);
    }
}
