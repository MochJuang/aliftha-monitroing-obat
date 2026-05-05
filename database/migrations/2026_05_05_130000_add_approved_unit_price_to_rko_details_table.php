<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rko_details', function (Blueprint $table) {
            $table->decimal('approved_unit_price', 15, 2)->nullable()->after('estimated_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('rko_details', function (Blueprint $table) {
            $table->dropColumn('approved_unit_price');
        });
    }
};
