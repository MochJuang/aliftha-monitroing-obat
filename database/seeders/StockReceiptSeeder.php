<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockReceiptSeeder extends Seeder
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
        $sourceIds = DB::table('stock_sources')->pluck('id', 'name');
        $rkoHeaderIds = DB::table('rko_headers')->pluck('id', 'rko_number');

        if ($medicineIds->isEmpty() || $sourceIds->isEmpty()) {
            $this->command?->warn('Master data obat atau sumber stok belum tersedia.');

            return;
        }

        DB::transaction(function () use ($userId, $medicineIds, $sourceIds) {
            // Reset sample transaksi agar seeder bisa dijalankan ulang dengan hasil yang konsisten.
            DB::table('stock_distribution_items')->delete();
            DB::table('stock_distributions')->delete();
            DB::table('stock_adjustment_items')->delete();
            DB::table('stock_adjustments')->delete();
            DB::table('medicine_batches')->delete();
            DB::table('stock_receipt_items')->delete();
            DB::table('stock_receipts')->delete();

            $receipts = [
                [
                    'receipt_number' => 'RCV-20260401-001',
                    'source_name' => 'BKKBN Provinsi Jawa Barat',
                    'rko_number' => 'RKO-202604-0001',
                    'received_date' => '2026-04-01',
                    'status' => 'posted',
                    'notes' => 'Penerimaan rutin triwulan I dari provinsi.',
                    'items' => [
                        [
                            'medicine_code' => 'PIL001',
                            'batch_number' => 'PIL-2604-A',
                            'expired_at' => '2027-01-31',
                            'quantity' => 250,
                            'unit_cost' => 12000,
                            'notes' => 'Pil KB Andalan batch awal April.',
                        ],
                        [
                            'medicine_code' => 'SNT001',
                            'batch_number' => 'SNT-2604-A',
                            'expired_at' => '2026-11-30',
                            'quantity' => 180,
                            'unit_cost' => 18500,
                            'notes' => 'Suntik KB 3 bulan batch provinsi.',
                        ],
                        [
                            'medicine_code' => 'IMP001',
                            'batch_number' => 'IMP-2604-A',
                            'expired_at' => '2028-03-31',
                            'quantity' => 120,
                            'unit_cost' => 76000,
                            'notes' => 'Implant 2 batang penerimaan awal.',
                        ],
                    ],
                ],
                [
                    'receipt_number' => 'RCV-20260410-001',
                    'source_name' => 'Dinas Kesehatan Kota Sukabumi',
                    'rko_number' => 'RKO-202604-0002',
                    'received_date' => '2026-04-10',
                    'status' => 'posted',
                    'notes' => 'Penambahan stok kebutuhan bulan April.',
                    'items' => [
                        [
                            'medicine_code' => 'PIL001',
                            'batch_number' => 'PIL-2604-B',
                            'expired_at' => '2027-03-31',
                            'quantity' => 200,
                            'unit_cost' => 12100,
                            'notes' => 'Pil KB Andalan tambahan pertengahan bulan.',
                        ],
                        [
                            'medicine_code' => 'IUD001',
                            'batch_number' => 'IUD-2604-A',
                            'expired_at' => '2028-02-29',
                            'quantity' => 50,
                            'unit_cost' => 90500,
                            'notes' => 'IUD untuk distribusi puskesmas dan klinik.',
                        ],
                    ],
                ],
                [
                    'receipt_number' => 'RCV-20260418-001',
                    'source_name' => 'PT Supplier Sehat Sentosa',
                    'rko_number' => 'RKO-202604-0003',
                    'received_date' => '2026-04-18',
                    'status' => 'posted',
                    'notes' => 'Pemenuhan stok cadangan gudang kota.',
                    'items' => [
                        [
                            'medicine_code' => 'SNT001',
                            'batch_number' => 'SNT-2604-B',
                            'expired_at' => '2027-02-28',
                            'quantity' => 150,
                            'unit_cost' => 18750,
                            'notes' => 'Cadangan stok suntik KB.',
                        ],
                        [
                            'medicine_code' => 'IMP001',
                            'batch_number' => 'IMP-2604-B',
                            'expired_at' => '2028-06-30',
                            'quantity' => 100,
                            'unit_cost' => 77500,
                            'notes' => 'Cadangan implant semester I.',
                        ],
                    ],
                ],
            ];

            foreach ($receipts as $receiptData) {
                $receivedAt = Carbon::parse($receiptData['received_date'])->endOfDay();
                $receiptId = DB::table('stock_receipts')->insertGetId([
                    'receipt_number' => $receiptData['receipt_number'],
                    'source_id' => $sourceIds[$receiptData['source_name']] ?? null,
                    'rko_header_id' => isset($receiptData['rko_number']) ? ($rkoHeaderIds[$receiptData['rko_number']] ?? null) : null,
                    'received_date' => $receiptData['received_date'],
                    'received_by' => $userId,
                    'notes' => $receiptData['notes'],
                    'status' => $receiptData['status'],
                    'created_at' => $receivedAt,
                    'updated_at' => $receivedAt,
                ]);

                foreach ($receiptData['items'] as $itemData) {
                    $medicineId = $medicineIds[$itemData['medicine_code']] ?? null;

                    if (! $medicineId) {
                        continue;
                    }

                    $receiptItemId = DB::table('stock_receipt_items')->insertGetId([
                        'receipt_id' => $receiptId,
                        'medicine_id' => $medicineId,
                        'batch_number' => $itemData['batch_number'],
                        'expired_at' => $itemData['expired_at'],
                        'quantity' => $itemData['quantity'],
                        'unit_cost' => $itemData['unit_cost'],
                        'notes' => $itemData['notes'],
                        'created_at' => $receivedAt,
                        'updated_at' => $receivedAt,
                    ]);

                    DB::table('medicine_batches')->insert([
                        'medicine_id' => $medicineId,
                        'receipt_item_id' => $receiptItemId,
                        'batch_number' => $itemData['batch_number'],
                        'expired_at' => $itemData['expired_at'],
                        'qty_received' => $itemData['quantity'],
                        'qty_remaining' => $itemData['quantity'],
                        'created_at' => $receivedAt,
                        'updated_at' => $receivedAt,
                    ]);
                }
            }
        });
    }
}
