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
            // JSON para armazenar observações de cada item
            // Formato: {"item_key": "observação", ...}
            $table->json('item_observations')->nullable()->after('observations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->dropColumn('item_observations');
        });
    }
};
