<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('stock_distributions')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('medicine_batches')->restrictOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['distribution_id', 'medicine_id']);
            $table->index(['batch_id', 'medicine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_distribution_items');
    }
};
