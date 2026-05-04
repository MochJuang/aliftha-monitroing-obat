<?php

namespace App\Services;

use App\Models\MedicineBatch;
use App\Models\StockMutation;
use App\Models\StockReceipt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockReceiptService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, int $userId, ?string $ipAddress = null): StockReceipt
    {
        return DB::transaction(function () use ($data, $userId, $ipAddress) {
            $receipt = StockReceipt::create([
                'receipt_number' => $data['receipt_number'],
                'source_id' => $data['source_id'],
                'rko_header_id' => $data['rko_header_id'] ?? null,
                'received_date' => $data['received_date'],
                'received_by' => $userId,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'],
            ]);

            $this->syncItems($receipt, $data['items']);

            if ($receipt->status === 'posted') {
                $this->createBatchesFromReceipt($receipt);
                $this->createMutationsFromReceipt($receipt->fresh('items'));
            }

            $this->activityLogService->log(
                $userId,
                'stock_receipts',
                'create',
                "Membuat transaksi stok masuk {$receipt->receipt_number}",
                $ipAddress
            );

            return $receipt->load(['source', 'receiver', 'items.medicine', 'items.batch']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(StockReceipt $receipt, array $data, int $userId, ?string $ipAddress = null): StockReceipt
    {
        if ($receipt->status !== 'draft') {
            throw new RuntimeException('Hanya transaksi draft yang bisa diubah.');
        }

        return DB::transaction(function () use ($receipt, $data, $userId, $ipAddress) {
            $newStatus = $data['status'];

            $receipt->update([
                'receipt_number' => $data['receipt_number'],
                'source_id' => $data['source_id'],
                'rko_header_id' => $data['rko_header_id'] ?? null,
                'received_date' => $data['received_date'],
                'notes' => $data['notes'] ?? null,
                'status' => $newStatus,
            ]);

            $receipt->items()->delete();
            $this->syncItems($receipt, $data['items']);

            if ($newStatus === 'posted') {
                $freshReceipt = $receipt->fresh('items');
                $this->createBatchesFromReceipt($freshReceipt);
                $this->createMutationsFromReceipt($freshReceipt);
            }

            $this->activityLogService->log(
                $userId,
                'stock_receipts',
                'update',
                "Memperbarui transaksi stok masuk {$receipt->receipt_number}",
                $ipAddress
            );

            return $receipt->fresh(['source', 'receiver', 'items.medicine', 'items.batch']);
        });
    }

    public function deleteDraft(StockReceipt $receipt, int $userId, ?string $ipAddress = null): void
    {
        if ($receipt->status !== 'draft') {
            throw new RuntimeException('Hanya transaksi draft yang bisa dihapus.');
        }

        DB::transaction(function () use ($receipt, $userId, $ipAddress) {
            $receiptNumber = $receipt->receipt_number;
            $receipt->delete();

            $this->activityLogService->log(
                $userId,
                'stock_receipts',
                'delete',
                "Menghapus transaksi stok masuk {$receiptNumber}",
                $ipAddress
            );
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(StockReceipt $receipt, array $items): void
    {
        foreach ($items as $index => $item) {
            $batchNumber = $item['batch_number'] ?? null;

            $receipt->items()->create([
                'medicine_id' => $item['medicine_id'],
                'batch_number' => $batchNumber ?: $this->generateInternalBatchNumber($receipt, $index),
                'expired_at' => $item['expired_at'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? 0,
                'total_realization' => (int) $item['quantity'] * (float) ($item['unit_cost'] ?? 0),
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function generateInternalBatchNumber(StockReceipt $receipt, int $index): string
    {
        return sprintf('AUTO-%s-%02d', $receipt->receipt_number, $index + 1);
    }

    private function createBatchesFromReceipt(StockReceipt $receipt): void
    {
        foreach ($receipt->items as $item) {
            MedicineBatch::create([
                'medicine_id' => $item->medicine_id,
                'receipt_item_id' => $item->id,
                'batch_number' => $item->batch_number,
                'expired_at' => $item->expired_at,
                'qty_received' => $item->quantity,
                'qty_remaining' => $item->quantity,
            ]);
        }
    }

    private function createMutationsFromReceipt(StockReceipt $receipt): void
    {
        foreach ($receipt->items as $item) {
            StockMutation::create([
                'medicine_id' => $item->medicine_id,
                'mutation_date' => $receipt->received_date,
                'mutation_type' => 'MASUK',
                'quantity' => (int) $item->quantity,
                'reference' => "Realisasi Pengadaan / {$receipt->receipt_number}",
                'notes' => $item->notes,
            ]);
        }
    }
}
