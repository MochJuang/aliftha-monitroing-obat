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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('username', 50)->nullable()->after('name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');

            $table->unique('username');
            $table->index(['role_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_id', 'is_active']);
            $table->dropUnique(['username']);
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['username', 'phone', 'is_active', 'last_login_at']);
        });
    }
};
