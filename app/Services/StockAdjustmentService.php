<?php

namespace App\Services;

use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, int $userId, ?string $ipAddress = null): StockAdjustment
    {
        return DB::transaction(function () use ($data, $userId, $ipAddress) {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => $data['adjustment_number'],
                'adjustment_date' => $data['adjustment_date'],
                'adjustment_type' => $data['adjustment_type'],
                'created_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $batch = MedicineBatch::query()
                    ->with('medicine')
                    ->lockForUpdate()
                    ->findOrFail($item['batch_id']);

                $systemQty = (int) $batch->qty_remaining;
                $actualQty = (int) $item['actual_qty'];
                $differenceQty = $actualQty - $systemQty;

                $adjustment->items()->create([
                    'batch_id' => $batch->id,
                    'medicine_id' => $batch->medicine_id,
                    'system_qty' => $systemQty,
                    'actual_qty' => $actualQty,
                    'difference_qty' => $differenceQty,
                    'reason' => $item['reason'] ?? null,
                ]);

                $batch->update([
                    'qty_remaining' => $actualQty,
                ]);
            }

            $this->activityLogService->log(
                $userId,
                'stock_adjustments',
                'create',
                "Membuat penyesuaian stok {$adjustment->adjustment_number}",
                $ipAddress
            );

            return $adjustment->load(['creator', 'items.medicine', 'items.batch']);
        });
    }
}
