<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'admin')->value('id');

        if (! $roleId) {
            $this->command?->warn('Role admin belum tersedia. Jalankan RoleSeeder terlebih dahulu.');

            return;
        }

        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@dppkb.go.id'],
            [
                'role_id' => $roleId,
                'name' => 'Super Admin',
                'username' => 'admin',
                'phone' => '081200000001',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
