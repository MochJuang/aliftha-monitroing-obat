<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockDistribution;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockDistributionService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, int $userId, ?string $ipAddress = null): StockDistribution
    {
        return DB::transaction(function () use ($data, $userId, $ipAddress) {
            $distribution = StockDistribution::create([
                'distribution_number' => $data['distribution_number'],
                'destination_id' => $data['destination_id'],
                'distributed_date' => $data['distributed_date'],
                'distributed_by' => $userId,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'],
            ]);

            $this->syncItems($distribution, $data['items'], $distribution->status === 'posted');

            $this->activityLogService->log(
                $userId,
                'stock_distributions',
                'create',
                "Membuat transaksi stok keluar {$distribution->distribution_number}",
                $ipAddress
            );

            return $distribution->load(['destination', 'distributor', 'items.medicine', 'items.batch']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(StockDistribution $distribution, array $data, int $userId, ?string $ipAddress = null): StockDistribution
    {
        if ($distribution->status !== 'draft') {
            throw new RuntimeException('Hanya transaksi draft yang bisa diubah.');
        }

        return DB::transaction(function () use ($distribution, $data, $userId, $ipAddress) {
            $distribution->update([
                'distribution_number' => $data['distribution_number'],
                'destination_id' => $data['destination_id'],
                'distributed_date' => $data['distributed_date'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'],
            ]);

            $distribution->items()->delete();
            $this->syncItems($distribution, $data['items'], $distribution->status === 'posted');

            $this->activityLogService->log(
                $userId,
                'stock_distributions',
                'update',
                "Memperbarui transaksi stok keluar {$distribution->distribution_number}",
                $ipAddress
            );

            return $distribution->fresh(['destination', 'distributor', 'items.medicine', 'items.batch']);
        });
    }

    public function deleteDraft(StockDistribution $distribution, int $userId, ?string $ipAddress = null): void
    {
        if ($distribution->status !== 'draft') {
            throw new RuntimeException('Hanya transaksi draft yang bisa dihapus.');
        }

        DB::transaction(function () use ($distribution, $userId, $ipAddress) {
            $distributionNumber = $distribution->distribution_number;
            $distribution->delete();

            $this->activityLogService->log(
                $userId,
                'stock_distributions',
                'delete',
                "Menghapus transaksi stok keluar {$distributionNumber}",
                $ipAddress
            );
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(StockDistribution $distribution, array $items, bool $applyStockReduction): void
    {
        foreach ($items as $item) {
            $medicine = Medicine::query()->findOrFail($item['medicine_id']);
            $requestedQuantity = (int) $item['quantity'];

            $batchesQuery = MedicineBatch::query()
                ->where('medicine_id', $medicine->id)
                ->available()
                ->whereDate('expired_at', '>=', now()->toDateString())
                ->fefo();

            if ($applyStockReduction) {
                $batchesQuery->lockForUpdate();
            }

            $batches = $batchesQuery->get();
            $availableQuantity = (int) $batches->sum('qty_remaining');

            if ($availableQuantity < $requestedQuantity) {
                throw new RuntimeException(
                    "Stok {$medicine->name} tidak mencukupi. Tersedia {$availableQuantity}, diminta {$requestedQuantity}."
                );
            }

            $remainingQuantity = $requestedQuantity;

            foreach ($batches as $batch) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $takenQuantity = min($remainingQuantity, (int) $batch->qty_remaining);

                $distribution->items()->create([
                    'batch_id' => $batch->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $takenQuantity,
                    'notes' => $item['notes'] ?? null,
                ]);

                if ($applyStockReduction) {
                    $batch->decrement('qty_remaining', $takenQuantity);
                }

                $remainingQuantity -= $takenQuantity;
            }
        }
    }
}
