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
        Schema::create('stock_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('distribution_number', 50)->unique();
            $table->foreignId('destination_id')->constrained('distribution_destinations')->restrictOnDelete();
            $table->date('distributed_date');
            $table->foreignId('distributed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index(['distributed_date', 'status']);
            $table->index(['destination_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_distributions');
    }
};
