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
        Schema::table('stock_receipts', function (Blueprint $table) {
            $table->foreignId('rko_header_id')
                ->nullable()
                ->after('source_id')
                ->constrained('rko_headers')
                ->nullOnDelete();

            $table->index(['rko_header_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rko_header_id');
        });
    }
};
