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
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('interrupted_at');
            $table->timestamp('resumed_at')->nullable()->after('paused_at');
            $table->index(['paused_at', 'current_phase']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->dropIndex(['paused_at', 'current_phase']);
            $table->dropColumn(['paused_at', 'resumed_at']);
        });
    }
};
