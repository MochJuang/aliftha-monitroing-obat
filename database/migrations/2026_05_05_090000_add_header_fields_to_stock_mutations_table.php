<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->string('mutation_number', 50)->nullable()->after('id');
            $table->foreignId('rko_header_id')->nullable()->after('medicine_id')->constrained('rko_headers')->nullOnDelete();
            $table->boolean('is_auto_generated')->default(false)->after('rko_header_id');
            $table->index(['rko_header_id', 'mutation_type']);
            $table->unique(['mutation_number']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rko_header_id');
            $table->dropColumn(['mutation_number', 'is_auto_generated']);
        });
    }
};
