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
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('employee_id');
            $table->index('type');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('is_locked');
        });

        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->index('date');
        });

        Schema::table('employee_blocks', function (Blueprint $table) {
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['type']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['is_locked']);
        });

        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });

        Schema::table('employee_blocks', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
        });
    }
};
