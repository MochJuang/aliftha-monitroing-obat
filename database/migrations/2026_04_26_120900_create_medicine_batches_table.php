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
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->foreignId('receipt_item_id')->unique()->constrained('stock_receipt_items')->restrictOnDelete();
            $table->string('batch_number', 100);
            $table->date('expired_at');
            $table->unsignedInteger('qty_received');
            $table->unsignedInteger('qty_remaining');
            $table->timestamps();

            $table->index(['medicine_id', 'expired_at']);
            $table->index(['medicine_id', 'qty_remaining']);
            $table->index(['batch_number', 'expired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_batches');
    }
};
