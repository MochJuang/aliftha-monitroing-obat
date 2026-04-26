<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('units')->upsert([
            [
                'name' => 'Strip',
                'symbol' => 'strip',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Vial',
                'symbol' => 'vial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Set',
                'symbol' => 'set',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['symbol', 'updated_at']);
    }
}
