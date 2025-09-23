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
        // First update existing values to temporary ones
        DB::table('safety_checklists')
            ->where('shift', 'morning')
            ->update(['shift' => 'temp_matutino']);

        DB::table('safety_checklists')
            ->where('shift', 'afternoon')
            ->update(['shift' => 'temp_vespertino']);

        DB::table('safety_checklists')
            ->where('shift', 'night')
            ->update(['shift' => 'temp_noturno']);

        // Modify the column to use Portuguese values
        DB::statement("ALTER TABLE safety_checklists MODIFY COLUMN shift ENUM('matutino', 'vespertino', 'noturno') NOT NULL");

        // Update values to final Portuguese form
        DB::table('safety_checklists')
            ->where('shift', 'temp_matutino')
            ->update(['shift' => 'matutino']);

        DB::table('safety_checklists')
            ->where('shift', 'temp_vespertino')
            ->update(['shift' => 'vespertino']);

        DB::table('safety_checklists')
            ->where('shift', 'temp_noturno')
            ->update(['shift' => 'noturno']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the process
        DB::table('safety_checklists')
            ->where('shift', 'matutino')
            ->update(['shift' => 'temp_morning']);

        DB::table('safety_checklists')
            ->where('shift', 'vespertino')
            ->update(['shift' => 'temp_afternoon']);

        DB::table('safety_checklists')
            ->where('shift', 'noturno')
            ->update(['shift' => 'temp_night']);

        // Modify the column back to English
        DB::statement("ALTER TABLE safety_checklists MODIFY COLUMN shift ENUM('morning', 'afternoon', 'night') NOT NULL");

        // Update values back to English
        DB::table('safety_checklists')
            ->where('shift', 'temp_morning')
            ->update(['shift' => 'morning']);

        DB::table('safety_checklists')
            ->where('shift', 'temp_afternoon')
            ->update(['shift' => 'afternoon']);

        DB::table('safety_checklists')
            ->where('shift', 'temp_night')
            ->update(['shift' => 'night']);
    }
};
