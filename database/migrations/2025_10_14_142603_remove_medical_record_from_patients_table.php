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
        Schema::table('patients', function (Blueprint $table) {
            // Check if column exists before trying to drop it
            if (Schema::hasColumn('patients', 'medical_record')) {
                // Try to drop unique constraint if it exists (suppress error if it doesn't)
                try {
                    $table->dropUnique(['medical_record']);
                } catch (\Exception $e) {
                    // Constraint doesn't exist, continue
                }
                // Remove the medical_record column
                $table->dropColumn('medical_record');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Add the medical_record column back
            $table->string('medical_record')->unique()->after('birth_date');
        });
    }
};
