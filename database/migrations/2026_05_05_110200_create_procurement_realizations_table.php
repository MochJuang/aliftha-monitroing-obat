<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_realizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rko_header_id')->constrained('rko_headers')->cascadeOnDelete();
            $table->foreignId('funding_source_id')->constrained('funding_sources')->restrictOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->date('realization_date');
            $table->unsignedInteger('realized_quantity');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_year', 'period_month'], 'pr_period_idx');
            $table->index(['funding_source_id', 'realization_date'], 'pr_funding_date_idx');
            $table->index(['rko_header_id', 'medicine_id'], 'pr_rko_medicine_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_realizations');
    }
};
