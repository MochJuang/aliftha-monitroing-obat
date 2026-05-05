<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RkoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userId = DB::table('users')->where('email', 'admin@dppkb.go.id')->value('id');

        if (! $userId) {
            $this->command?->warn('User admin belum tersedia. Jalankan AdminUserSeeder terlebih dahulu.');

            return;
        }

        $medicineIds = DB::table('medicines')->pluck('id', 'code');

        if ($medicineIds->isEmpty()) {
            $this->command?->warn('Master data obat belum tersedia. Jalankan MedicineSeeder terlebih dahulu.');

            return;
        }

        $fundingSourceIds = DB::table('funding_sources')->pluck('id', 'code');

        if ($fundingSourceIds->isEmpty()) {
            $this->command?->warn('Master sumber dana belum tersedia. Jalankan FundingSourceSeeder terlebih dahulu.');

            return;
        }

        DB::transaction(function () use ($userId, $medicineIds, $fundingSourceIds) {
            Schema::disableForeignKeyConstraints();

            try {
                DB::table('stock_mutation_items')->delete();
                DB::table('stock_mutations')->delete();
                DB::table('medicine_stocks')->delete();
                DB::table('procurement_realizations')->delete();
                DB::table('rko_details')->delete();
                DB::table('rko_headers')->delete();
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            $headers = [
                [
                    'rko_number' => 'RKO-202604-0001',
                    'period_month' => 4,
                    'period_year' => 2026,
                    'funding_source_code' => 'APBD',
                    'total_budget' => 19860000,
                    'status' => 'approved',
                    'submitted_at' => '2026-04-01',
                    'approved_at' => '2026-04-02',
                    'notes' => 'Rencana kebutuhan obat untuk penerimaan awal April dari provinsi.',
                    'items' => [
                        ['medicine_code' => 'PIL001', 'planned_quantity' => 275, 'approved_quantity' => 250, 'estimated_unit_price' => 12000, 'priority' => 'tinggi', 'notes' => 'Kebutuhan pil KB pelayanan rutin.'],
                        ['medicine_code' => 'SNT001', 'planned_quantity' => 200, 'approved_quantity' => 180, 'estimated_unit_price' => 18500, 'priority' => 'tinggi', 'notes' => 'Kebutuhan suntik KB layanan reguler.'],
                        ['medicine_code' => 'IMP001', 'planned_quantity' => 140, 'approved_quantity' => 120, 'estimated_unit_price' => 76000, 'priority' => 'sedang', 'notes' => 'Cadangan implant awal bulan.'],
                    ],
                ],
                [
                    'rko_number' => 'RKO-202604-0002',
                    'period_month' => 4,
                    'period_year' => 2026,
                    'funding_source_code' => 'APBN',
                    'total_budget' => 6930000,
                    'status' => 'approved',
                    'submitted_at' => '2026-04-08',
                    'approved_at' => '2026-04-09',
                    'notes' => 'Rencana tambahan pertengahan April untuk penguatan stok fasilitas kesehatan.',
                    'items' => [
                        ['medicine_code' => 'PIL001', 'planned_quantity' => 220, 'approved_quantity' => 200, 'estimated_unit_price' => 12100, 'priority' => 'tinggi', 'notes' => 'Tambahan pil KB untuk distribusi lanjutan.'],
                        ['medicine_code' => 'IUD001', 'planned_quantity' => 60, 'approved_quantity' => 50, 'estimated_unit_price' => 90500, 'priority' => 'sedang', 'notes' => 'Kebutuhan IUD untuk klinik dan puskesmas.'],
                    ],
                ],
                [
                    'rko_number' => 'RKO-202604-0003',
                    'period_month' => 4,
                    'period_year' => 2026,
                    'funding_source_code' => 'BOKB',
                    'total_budget' => 12777500,
                    'status' => 'approved',
                    'submitted_at' => '2026-04-15',
                    'approved_at' => '2026-04-16',
                    'notes' => 'RKO cadangan gudang menjelang akhir April.',
                    'items' => [
                        ['medicine_code' => 'SNT001', 'planned_quantity' => 170, 'approved_quantity' => 170, 'estimated_unit_price' => 18750, 'priority' => 'sedang', 'notes' => 'Suntik KB untuk buffer stok.'],
                        ['medicine_code' => 'IMP001', 'planned_quantity' => 140, 'approved_quantity' => 130, 'estimated_unit_price' => 77500, 'priority' => 'sedang', 'notes' => 'Implant untuk kebutuhan semester I.'],
                    ],
                ],
                [
                    'rko_number' => 'RKO-202605-0001',
                    'period_month' => 5,
                    'period_year' => 2026,
                    'funding_source_code' => 'DAK',
                    'total_budget' => 9580000,
                    'status' => 'submitted',
                    'submitted_at' => '2026-05-01',
                    'approved_at' => null,
                    'notes' => 'Usulan kebutuhan obat bulan Mei yang masih menunggu persetujuan pimpinan.',
                    'items' => [
                        ['medicine_code' => 'PIL001', 'planned_quantity' => 260, 'approved_quantity' => null, 'estimated_unit_price' => 12200, 'priority' => 'tinggi', 'notes' => 'Rencana layanan pil KB bulan Mei.'],
                        ['medicine_code' => 'SNT001', 'planned_quantity' => 190, 'approved_quantity' => null, 'estimated_unit_price' => 18800, 'priority' => 'tinggi', 'notes' => 'Rencana penguatan stok suntik KB.'],
                        ['medicine_code' => 'IUD001', 'planned_quantity' => 40, 'approved_quantity' => null, 'estimated_unit_price' => 91000, 'priority' => 'rendah', 'notes' => 'Tambahan IUD untuk permintaan faskes.'],
                    ],
                ],
            ];

            foreach ($headers as $index => $headerData) {
                $timestamp = Carbon::create(
                    $headerData['period_year'],
                    $headerData['period_month'],
                    min($index + 1, 28),
                    9,
                    0,
                    0
                );

                $headerId = DB::table('rko_headers')->insertGetId([
                    'rko_number' => $headerData['rko_number'],
                    'period_month' => $headerData['period_month'],
                    'period_year' => $headerData['period_year'],
                    'funding_source_id' => $fundingSourceIds[$headerData['funding_source_code']] ?? null,
                    'total_budget' => $headerData['total_budget'],
                    'status' => $headerData['status'],
                    'submitted_at' => $headerData['submitted_at'],
                    'approved_at' => $headerData['approved_at'],
                    'submitted_by' => $userId,
                    'approved_by' => $headerData['status'] === 'approved' ? $userId : null,
                    'notes' => $headerData['notes'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                foreach ($headerData['items'] as $itemData) {
                    $medicineId = $medicineIds[$itemData['medicine_code']] ?? null;

                    if (! $medicineId) {
                        continue;
                    }

                    DB::table('rko_details')->insert([
                        'rko_header_id' => $headerId,
                        'medicine_id' => $medicineId,
                        'planned_quantity' => $itemData['planned_quantity'],
                        'approved_quantity' => $itemData['approved_quantity'],
                        'estimated_unit_price' => $itemData['estimated_unit_price'],
                        'total_estimate' => $itemData['planned_quantity'] * $itemData['estimated_unit_price'],
                        'priority' => $itemData['priority'],
                        'notes' => $itemData['notes'],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            }
        });
    }
}
