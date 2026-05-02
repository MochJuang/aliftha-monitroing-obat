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
        Schema::create('stock_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('source_type', 50);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->timestamps();

            $table->index(['source_type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_sources');
    }
};
