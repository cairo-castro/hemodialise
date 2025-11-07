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
        Schema::table('cleaning_controls', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('cleaning_date', 'idx_cleaning_date');
            $table->index(['unit_id', 'cleaning_date'], 'idx_unit_cleaning_date');
            $table->index(['machine_id', 'cleaning_date'], 'idx_machine_cleaning_date');
            $table->index('shift', 'idx_shift');
            $table->index('daily_cleaning', 'idx_daily_cleaning');
            $table->index('weekly_cleaning', 'idx_weekly_cleaning');
            $table->index('monthly_cleaning', 'idx_monthly_cleaning');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaning_controls', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_monthly_cleaning');
            $table->dropIndex('idx_weekly_cleaning');
            $table->dropIndex('idx_daily_cleaning');
            $table->dropIndex('idx_shift');
            $table->dropIndex('idx_machine_cleaning_date');
            $table->dropIndex('idx_unit_cleaning_date');
            $table->dropIndex('idx_cleaning_date');
        });
    }
};
