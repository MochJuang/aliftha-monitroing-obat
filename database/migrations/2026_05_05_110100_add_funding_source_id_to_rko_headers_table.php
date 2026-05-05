<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rko_headers', function (Blueprint $table) {
            $table->foreignId('funding_source_id')
                ->nullable()
                ->after('period_year')
                ->constrained('funding_sources')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rko_headers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('funding_source_id');
        });
    }
};
