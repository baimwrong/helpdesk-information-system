<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('customer')->index()->after('email');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('role');
            $table->string('phone', 50)->nullable()->after('department_id');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->index()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['role', 'department_id', 'phone', 'avatar', 'is_active']);
        });
    }
};
