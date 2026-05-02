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
        Schema::create('rko_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rko_header_id')->constrained('rko_headers')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->unsignedInteger('planned_quantity');
            $table->unsignedInteger('approved_quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['rko_header_id', 'medicine_id']);
            $table->index(['medicine_id', 'planned_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rko_details');
    }
};
