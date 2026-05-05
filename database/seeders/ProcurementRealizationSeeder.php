<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcurementRealizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Schema::disableForeignKeyConstraints();

            try {
                DB::table('procurement_realizations')->delete();
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            $approvedHeaders = DB::table('rko_headers')
                ->where('status', 'approved')
                ->whereNotNull('funding_source_id')
                ->get();

            foreach ($approvedHeaders as $header) {
                $details = DB::table('rko_details')
                    ->where('rko_header_id', $header->id)
                    ->get();

                foreach ($details as $detail) {
                    $realizedQuantity = (int) ($detail->approved_quantity ?? $detail->planned_quantity);

                    if ($realizedQuantity <= 0) {
                        continue;
                    }

                    $unitPrice = (float) ($detail->approved_unit_price ?? $detail->estimated_unit_price ?? 0);

                    DB::table('procurement_realizations')->insert([
                        'rko_header_id' => $header->id,
                        'funding_source_id' => $header->funding_source_id,
                        'medicine_id' => $detail->medicine_id,
                        'period_month' => $header->period_month,
                        'period_year' => $header->period_year,
                        'realization_date' => $header->approved_at ?? now()->toDateString(),
                        'realized_quantity' => $realizedQuantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $realizedQuantity * $unitPrice,
                        'notes' => $detail->notes,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
