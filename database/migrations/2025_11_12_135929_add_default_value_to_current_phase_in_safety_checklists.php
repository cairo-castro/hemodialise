<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing NULL values to 'pre_dialysis'
        DB::table('safety_checklists')
            ->whereNull('current_phase')
            ->update([
                'current_phase' => 'pre_dialysis',
                'pre_dialysis_started_at' => DB::raw('COALESCE(pre_dialysis_started_at, created_at)')
            ]);

        // Then alter the column to have a default value and NOT NULL constraint
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->string('current_phase')->default('pre_dialysis')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->string('current_phase')->default(null)->nullable()->change();
        });
    }
};
