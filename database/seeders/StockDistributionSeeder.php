<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockDistributionSeeder extends Seeder
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
        $destinationIds = DB::table('distribution_destinations')->pluck('id', 'code');

        if ($medicineIds->isEmpty() || $destinationIds->isEmpty()) {
            $this->command?->warn('Master data obat atau tujuan distribusi belum tersedia.');

            return;
        }

        DB::transaction(function () use ($userId, $medicineIds, $destinationIds) {
            DB::table('stock_distribution_items')->delete();
            DB::table('stock_distributions')->delete();

            $distributions = [
                [
                    'distribution_number' => 'DST-20260420-001',
                    'destination_code' => 'PKM001',
                    'distributed_date' => '2026-04-20',
                    'status' => 'posted',
                    'notes' => 'Distribusi rutin ke Puskesmas Cikole.',
                    'items' => [
                        [
                            'medicine_code' => 'PIL001',
                            'quantity' => 120,
                            'notes' => 'Permintaan pil KB untuk pelayanan bulanan.',
                        ],
                        [
                            'medicine_code' => 'SNT001',
                            'quantity' => 60,
                            'notes' => 'Kebutuhan suntik KB triwulan berjalan.',
                        ],
                    ],
                ],
                [
                    'distribution_number' => 'DST-20260422-001',
                    'destination_code' => 'KLN001',
                    'distributed_date' => '2026-04-22',
                    'status' => 'posted',
                    'notes' => 'Distribusi ke Klinik Keluarga Sehat.',
                    'items' => [
                        [
                            'medicine_code' => 'PIL001',
                            'quantity' => 170,
                            'notes' => 'Pengeluaran pil KB dengan alokasi FEFO lintas batch.',
                        ],
                        [
                            'medicine_code' => 'IMP001',
                            'quantity' => 40,
                            'notes' => 'Distribusi implant untuk pelayanan pemasangan.',
                        ],
                        [
                            'medicine_code' => 'IUD001',
                            'quantity' => 15,
                            'notes' => 'Distribusi IUD untuk klinik.',
                        ],
                    ],
                ],
                [
                    'distribution_number' => 'DST-20260424-001',
                    'destination_code' => 'BDN001',
                    'distributed_date' => '2026-04-24',
                    'status' => 'posted',
                    'notes' => 'Distribusi ke Bidan Praktik Mandiri.',
                    'items' => [
                        [
                            'medicine_code' => 'SNT001',
                            'quantity' => 100,
                            'notes' => 'Distribusi suntik KB untuk bidan mandiri.',
                        ],
                        [
                            'medicine_code' => 'IMP001',
                            'quantity' => 30,
                            'notes' => 'Tambahan implant untuk stok pelayanan.',
                        ],
                    ],
                ],
            ];

            foreach ($distributions as $distributionData) {
                $distributedAt = Carbon::parse($distributionData['distributed_date'])->endOfDay();
                $distributionId = DB::table('stock_distributions')->insertGetId([
                    'distribution_number' => $distributionData['distribution_number'],
                    'destination_id' => $destinationIds[$distributionData['destination_code']] ?? null,
                    'distributed_date' => $distributionData['distributed_date'],
                    'distributed_by' => $userId,
                    'notes' => $distributionData['notes'],
                    'status' => $distributionData['status'],
                    'created_at' => $distributedAt,
                    'updated_at' => $distributedAt,
                ]);

                foreach ($distributionData['items'] as $itemData) {
                    $medicineId = $medicineIds[$itemData['medicine_code']] ?? null;

                    if (! $medicineId) {
                        continue;
                    }

                    $this->allocateByFefo(
                        $distributionId,
                        $medicineId,
                        (int) $itemData['quantity'],
                        $itemData['notes'],
                        $distributedAt
                    );
                }
            }
        });
    }

    private function allocateByFefo(
        int $distributionId,
        int $medicineId,
        int $requestedQuantity,
        ?string $notes,
        Carbon $timestamp
    ): void {
        /** @var Collection<int, object> $batches */
        $batches = DB::table('medicine_batches')
            ->where('medicine_id', $medicineId)
            ->where('qty_remaining', '>', 0)
            ->whereDate('expired_at', '>=', $timestamp->toDateString())
            ->orderBy('expired_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $availableQuantity = (int) $batches->sum('qty_remaining');

        if ($availableQuantity < $requestedQuantity) {
            throw new RuntimeException(
                "Stok medicine_id={$medicineId} tidak mencukupi untuk sample distribusi. ".
                "Tersedia {$availableQuantity}, diminta {$requestedQuantity}."
            );
        }

        $remainingQuantity = $requestedQuantity;

        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $takenQuantity = min($remainingQuantity, (int) $batch->qty_remaining);

            DB::table('stock_distribution_items')->insert([
                'distribution_id' => $distributionId,
                'batch_id' => $batch->id,
                'medicine_id' => $medicineId,
                'quantity' => $takenQuantity,
                'notes' => $notes,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DB::table('medicine_batches')
                ->where('id', $batch->id)
                ->update([
                    'qty_remaining' => (int) $batch->qty_remaining - $takenQuantity,
                    'updated_at' => $timestamp,
                ]);

            $remainingQuantity -= $takenQuantity;
        }
    }
}
