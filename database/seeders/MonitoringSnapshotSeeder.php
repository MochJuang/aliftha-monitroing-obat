<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitoringSnapshotSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Schema::disableForeignKeyConstraints();

            try {
                DB::table('stock_mutation_items')->delete();
                DB::table('stock_mutations')->delete();
                DB::table('medicine_stocks')->delete();
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            $medicineIds = DB::table('medicines')->pluck('id', 'code');
            $destinationIds = DB::table('distribution_destinations')->pluck('id', 'code');
            $rkoIds = DB::table('rko_headers')->pluck('id', 'rko_number');
            $adminUserId = DB::table('users')->where('email', 'admin@dppkb.go.id')->value('id');

            $mutations = [
                [
                    'mutation_number' => 'MTS-2026-0001',
                    'mutation_date' => '2026-04-02',
                    'mutation_type' => 'MASUK',
                    'rko_number' => 'RKO-202604-0001',
                    'reference' => 'RKO Disetujui / RKO-202604-0001',
                    'notes' => 'Mutasi masuk otomatis dari RKO April tahap 1.',
                    'is_auto_generated' => true,
                    'items' => [
                        ['medicine_code' => 'PIL001', 'quantity' => 250, 'notes' => 'Realisasi pil KB tahap pertama.'],
                        ['medicine_code' => 'SNT001', 'quantity' => 180, 'notes' => 'Realisasi suntik KB tahap pertama.'],
                        ['medicine_code' => 'IMP001', 'quantity' => 120, 'notes' => 'Realisasi implant tahap pertama.'],
                    ],
                ],
                [
                    'mutation_number' => 'MTS-2026-0002',
                    'mutation_date' => '2026-04-09',
                    'mutation_type' => 'MASUK',
                    'rko_number' => 'RKO-202604-0002',
                    'reference' => 'RKO Disetujui / RKO-202604-0002',
                    'notes' => 'Mutasi masuk otomatis dari RKO April tahap 2.',
                    'is_auto_generated' => true,
                    'items' => [
                        ['medicine_code' => 'PIL001', 'quantity' => 200, 'notes' => 'Tambahan pil KB.'],
                        ['medicine_code' => 'IUD001', 'quantity' => 50, 'notes' => 'Tambahan IUD.'],
                    ],
                ],
                [
                    'mutation_number' => 'MTS-2026-0003',
                    'mutation_date' => '2026-04-16',
                    'mutation_type' => 'MASUK',
                    'rko_number' => 'RKO-202604-0003',
                    'reference' => 'RKO Disetujui / RKO-202604-0003',
                    'notes' => 'Mutasi masuk otomatis dari RKO April tahap 3.',
                    'is_auto_generated' => true,
                    'items' => [
                        ['medicine_code' => 'SNT001', 'quantity' => 170, 'notes' => 'Buffer suntik KB.'],
                        ['medicine_code' => 'IMP001', 'quantity' => 130, 'notes' => 'Buffer implant.'],
                    ],
                ],
                [
                    'mutation_number' => 'MTS-2026-0004',
                    'mutation_date' => '2026-04-20',
                    'mutation_type' => 'KELUAR',
                    'distribution_destination_code' => 'PKM001',
                    'reference' => 'Mutasi Obat / Penyaluran ke Puskesmas Cikole',
                    'notes' => 'Distribusi rutin ke Puskesmas Cikole.',
                    'is_auto_generated' => false,
                    'items' => [
                        ['medicine_code' => 'PIL001', 'quantity' => 90, 'notes' => 'Pelayanan rutin April.'],
                        ['medicine_code' => 'SNT001', 'quantity' => 60, 'notes' => 'Pelayanan suntik KB.'],
                    ],
                ],
                [
                    'mutation_number' => 'MTS-2026-0005',
                    'mutation_date' => '2026-04-24',
                    'mutation_type' => 'KELUAR',
                    'distribution_destination_code' => 'KLN001',
                    'reference' => 'Mutasi Obat / Penyaluran ke Klinik Keluarga Sehat',
                    'notes' => 'Distribusi obat untuk klinik mitra.',
                    'is_auto_generated' => false,
                    'items' => [
                        ['medicine_code' => 'IMP001', 'quantity' => 40, 'notes' => 'Permintaan implant.'],
                        ['medicine_code' => 'IUD001', 'quantity' => 20, 'notes' => 'Permintaan IUD.'],
                    ],
                ],
            ];

            foreach ($mutations as $index => $mutationData) {
                $items = collect($mutationData['items'])
                    ->map(function (array $item) use ($medicineIds) {
                        $medicineId = $medicineIds[$item['medicine_code']] ?? null;

                        if (! $medicineId) {
                            return null;
                        }

                        return [
                            'medicine_id' => $medicineId,
                            'quantity' => (int) $item['quantity'],
                            'notes' => $item['notes'] ?? null,
                        ];
                    })
                    ->filter()
                    ->values();

                if ($items->isEmpty()) {
                    continue;
                }

                $createdAt = now()->subDays(count($mutations) - $index);

                $mutationId = DB::table('stock_mutations')->insertGetId([
                    'medicine_id' => $items->first()['medicine_id'],
                    'mutation_number' => $mutationData['mutation_number'],
                    'rko_header_id' => isset($mutationData['rko_number']) ? ($rkoIds[$mutationData['rko_number']] ?? null) : null,
                    'distribution_destination_id' => isset($mutationData['distribution_destination_code'])
                        ? ($destinationIds[$mutationData['distribution_destination_code']] ?? null)
                        : null,
                    'created_by' => $adminUserId,
                    'mutation_date' => $mutationData['mutation_date'],
                    'mutation_type' => $mutationData['mutation_type'],
                    'quantity' => (int) $items->sum('quantity'),
                    'reference' => $mutationData['reference'],
                    'notes' => $mutationData['notes'],
                    'is_auto_generated' => $mutationData['is_auto_generated'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($items as $item) {
                    DB::table('stock_mutation_items')->insert([
                        'stock_mutation_id' => $mutationId,
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }

            $stocks = DB::table('medicines')
                ->leftJoin('stock_mutation_items', 'stock_mutation_items.medicine_id', '=', 'medicines.id')
                ->leftJoin('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
                ->selectRaw("
                    medicines.id as medicine_id,
                    medicines.minimum_stock,
                    COALESCE(SUM(
                        CASE
                            WHEN stock_mutations.mutation_type = 'MASUK' THEN stock_mutation_items.quantity
                            WHEN stock_mutations.mutation_type = 'KELUAR' THEN -stock_mutation_items.quantity
                            ELSE 0
                        END
                    ), 0) as quantity
                ")
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
                    'quantity' => max(0, (int) $stock->quantity),
                    'input_date' => '2026-04-30',
                    'status_note' => $statusNote,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
