<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonitoringSnapshotSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('stock_mutations')->delete();
            DB::table('medicine_stocks')->delete();

            $receiptMutations = DB::table('stock_receipt_items')
                ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
                ->where('stock_receipts.status', 'posted')
                ->get([
                    'stock_receipt_items.medicine_id',
                    'stock_receipts.received_date as mutation_date',
                    'stock_receipt_items.quantity',
                    'stock_receipts.id as reference_id',
                    'stock_receipts.receipt_number as reference_number',
                    'stock_receipt_items.notes',
                ]);

            foreach ($receiptMutations as $mutation) {
                DB::table('stock_mutations')->insert([
                    'medicine_id' => $mutation->medicine_id,
                    'mutation_date' => $mutation->mutation_date,
                    'mutation_type' => 'MASUK',
                    'quantity' => $mutation->quantity,
                    'reference' => "Realisasi Pengadaan / {$mutation->reference_number}",
                    'notes' => $mutation->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $distributionMutations = DB::table('stock_distribution_items')
                ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.distribution_id')
                ->where('stock_distributions.status', 'posted')
                ->get([
                    'stock_distribution_items.medicine_id',
                    'stock_distributions.distributed_date as mutation_date',
                    'stock_distribution_items.quantity',
                    'stock_distributions.id as reference_id',
                    'stock_distributions.distribution_number as reference_number',
                    'stock_distribution_items.notes',
                ]);

            foreach ($distributionMutations as $mutation) {
                DB::table('stock_mutations')->insert([
                    'medicine_id' => $mutation->medicine_id,
                    'mutation_date' => $mutation->mutation_date,
                    'mutation_type' => 'KELUAR',
                    'quantity' => $mutation->quantity,
                    'reference' => "Distribusi Obat / {$mutation->reference_number}",
                    'notes' => $mutation->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $stocks = DB::table('medicines')
                ->leftJoin('medicine_batches', 'medicine_batches.medicine_id', '=', 'medicines.id')
                ->selectRaw('medicines.id as medicine_id, medicines.minimum_stock, COALESCE(SUM(medicine_batches.qty_remaining), 0) as quantity')
                ->groupBy('medicines.id', 'medicines.minimum_stock')
                ->get();

            foreach ($stocks as $stock) {
                $statusNote = match (true) {
                    (int) $stock->quantity < (int) $stock->minimum_stock => 'Kurang',
                    (int) $stock->quantity > ((int) $stock->minimum_stock * 2) => 'Berlebih',
                    default => 'Aman',
                };

                DB::table('medicine_stocks')->insert([
                    'medicine_id' => $stock->medicine_id,
                    'period' => '2026-04',
                    'quantity' => (int) $stock->quantity,
                    'input_date' => '2026-04-30',
                    'status_note' => $statusNote,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
