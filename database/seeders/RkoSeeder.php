<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($userId, $medicineIds) {
            DB::table('rko_details')->delete();
            DB::table('rko_headers')->delete();

            $headers = [
                [
                    'rko_number' => 'RKO-202604-0001',
                    'period_month' => 4,
                    'period_year' => 2026,
                    'status' => 'approved',
                    'notes' => 'Rencana kebutuhan obat untuk penerimaan awal April dari provinsi.',
                    'items' => [
                        ['medicine_code' => 'PIL001', 'planned_quantity' => 275, 'approved_quantity' => 250, 'notes' => 'Kebutuhan pil KB pelayanan rutin.'],
                        ['medicine_code' => 'SNT001', 'planned_quantity' => 200, 'approved_quantity' => 180, 'notes' => 'Kebutuhan suntik KB layanan reguler.'],
                        ['medicine_code' => 'IMP001', 'planned_quantity' => 140, 'approved_quantity' => 120, 'notes' => 'Cadangan implant awal bulan.'],
                    ],
                ],
                [
                    'rko_number' => 'RKO-202604-0002',
                    'period_month' => 4,
                    'period_year' => 2026,
                    'status' => 'approved',
                    'notes' => 'Rencana tambahan pertengahan April untuk penguatan stok fasilitas kesehatan.',
                    'items' => [
                        ['medicine_code' => 'PIL001', 'planned_quantity' => 220, 'approved_quantity' => 200, 'notes' => 'Tambahan pil KB untuk distribusi lanjutan.'],
                        ['medicine_code' => 'IUD001', 'planned_quantity' => 60, 'approved_quantity' => 50, 'notes' => 'Kebutuhan IUD untuk klinik dan puskesmas.'],
                    ],
                ],
                [
                    'rko_number' => 'RKO-202604-0003',
                    'period_month' => 4,
                    'period_year' => 2026,
                    'status' => 'approved',
                    'notes' => 'RKO cadangan gudang menjelang akhir April.',
                    'items' => [
                        ['medicine_code' => 'SNT001', 'planned_quantity' => 170, 'approved_quantity' => 170, 'notes' => 'Suntik KB untuk buffer stok.'],
                        ['medicine_code' => 'IMP001', 'planned_quantity' => 140, 'approved_quantity' => 130, 'notes' => 'Implant untuk kebutuhan semester I.'],
                    ],
                ],
                [
                    'rko_number' => 'RKO-202605-0001',
                    'period_month' => 5,
                    'period_year' => 2026,
                    'status' => 'submitted',
                    'notes' => 'Usulan kebutuhan obat bulan Mei yang masih menunggu persetujuan pimpinan.',
                    'items' => [
                        ['medicine_code' => 'PIL001', 'planned_quantity' => 260, 'approved_quantity' => null, 'notes' => 'Rencana layanan pil KB bulan Mei.'],
                        ['medicine_code' => 'SNT001', 'planned_quantity' => 190, 'approved_quantity' => null, 'notes' => 'Rencana penguatan stok suntik KB.'],
                        ['medicine_code' => 'IUD001', 'planned_quantity' => 40, 'approved_quantity' => null, 'notes' => 'Tambahan IUD untuk permintaan faskes.'],
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
                    'status' => $headerData['status'],
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
                        'notes' => $itemData['notes'],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            }
        });
    }
}
