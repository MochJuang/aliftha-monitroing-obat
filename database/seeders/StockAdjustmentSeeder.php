<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockAdjustmentSeeder extends Seeder
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

        DB::transaction(function () use ($userId) {
            DB::table('stock_adjustment_items')->delete();
            DB::table('stock_adjustments')->delete();

            $adjustments = [
                [
                    'adjustment_number' => 'ADJ-20260425-001',
                    'adjustment_date' => '2026-04-25',
                    'adjustment_type' => 'opname',
                    'notes' => 'Hasil stock opname akhir minggu keempat April.',
                    'items' => [
                        [
                            'batch_number' => 'SNT-2604-B',
                            'actual_qty' => 45,
                            'reason' => 'Selisih kurang setelah pengecekan fisik di gudang.',
                        ],
                        [
                            'batch_number' => 'IMP-2604-A',
                            'actual_qty' => 78,
                            'reason' => 'Penyesuaian lebih setelah verifikasi stok fisik.',
                        ],
                    ],
                ],
                [
                    'adjustment_number' => 'ADJ-20260426-001',
                    'adjustment_date' => '2026-04-26',
                    'adjustment_type' => 'rusak',
                    'notes' => 'Penyesuaian barang rusak pada saat pemeriksaan kondisi kemasan.',
                    'items' => [
                        [
                            'batch_number' => 'IUD-2604-A',
                            'actual_qty' => 32,
                            'reason' => 'Terdapat kerusakan kemasan pada sebagian unit IUD.',
                        ],
                    ],
                ],
            ];

            foreach ($adjustments as $adjustmentData) {
                $adjustedAt = Carbon::parse($adjustmentData['adjustment_date'])->endOfDay();

                $adjustmentId = DB::table('stock_adjustments')->insertGetId([
                    'adjustment_number' => $adjustmentData['adjustment_number'],
                    'adjustment_date' => $adjustmentData['adjustment_date'],
                    'adjustment_type' => $adjustmentData['adjustment_type'],
                    'created_by' => $userId,
                    'notes' => $adjustmentData['notes'],
                    'created_at' => $adjustedAt,
                    'updated_at' => $adjustedAt,
                ]);

                foreach ($adjustmentData['items'] as $itemData) {
                    $batch = DB::table('medicine_batches')
                        ->where('batch_number', $itemData['batch_number'])
                        ->lockForUpdate()
                        ->first();

                    if (! $batch) {
                        continue;
                    }

                    $systemQty = (int) $batch->qty_remaining;
                    $actualQty = (int) $itemData['actual_qty'];
                    $differenceQty = $actualQty - $systemQty;

                    DB::table('stock_adjustment_items')->insert([
                        'adjustment_id' => $adjustmentId,
                        'batch_id' => $batch->id,
                        'medicine_id' => $batch->medicine_id,
                        'system_qty' => $systemQty,
                        'actual_qty' => $actualQty,
                        'difference_qty' => $differenceQty,
                        'reason' => $itemData['reason'],
                        'created_at' => $adjustedAt,
                        'updated_at' => $adjustedAt,
                    ]);

                    DB::table('medicine_batches')
                        ->where('id', $batch->id)
                        ->update([
                            'qty_remaining' => $actualQty,
                            'updated_at' => $adjustedAt,
                        ]);
                }
            }
        });
    }
}
