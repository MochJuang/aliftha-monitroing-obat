<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('medicine_type', 100)->nullable()->after('name');
            $table->decimal('standard_price', 15, 2)->default(0)->after('minimum_stock');
        });

        Schema::table('rko_headers', function (Blueprint $table) {
            $table->decimal('total_budget', 15, 2)->default(0)->after('period_year');
            $table->date('submitted_at')->nullable()->after('status');
            $table->date('approved_at')->nullable()->after('submitted_at');
        });

        Schema::table('rko_details', function (Blueprint $table) {
            $table->decimal('estimated_unit_price', 15, 2)->default(0)->after('approved_quantity');
            $table->decimal('total_estimate', 15, 2)->default(0)->after('estimated_unit_price');
            $table->string('priority', 20)->default('sedang')->after('total_estimate');
        });

        Schema::create('medicine_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('period', 20);
            $table->unsignedInteger('quantity');
            $table->date('input_date');
            $table->string('status_note', 20);
            $table->timestamps();

            $table->unique(['medicine_id', 'period']);
            $table->index(['period', 'status_note']);
        });

        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->date('mutation_date');
            $table->enum('mutation_type', ['MASUK', 'KELUAR']);
            $table->unsignedInteger('quantity');
            $table->string('reference', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['medicine_id', 'mutation_date']);
            $table->index(['mutation_type', 'mutation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('medicine_stocks');

        Schema::table('rko_details', function (Blueprint $table) {
            $table->dropColumn(['estimated_unit_price', 'total_estimate', 'priority']);
        });

        Schema::table('rko_headers', function (Blueprint $table) {
            $table->dropColumn(['total_budget', 'submitted_at', 'approved_at']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['medicine_type', 'standard_price']);
        });
    }
};
