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
        // Simply update existing values to Portuguese
        // SQLite doesn't support ENUM or MODIFY COLUMN
        DB::table('safety_checklists')
            ->where('shift', 'morning')
            ->update(['shift' => 'matutino']);

        DB::table('safety_checklists')
            ->where('shift', 'afternoon')
            ->update(['shift' => 'vespertino']);

        DB::table('safety_checklists')
            ->where('shift', 'night')
            ->update(['shift' => 'noturno']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the values back to English
        DB::table('safety_checklists')
            ->where('shift', 'matutino')
            ->update(['shift' => 'morning']);

        DB::table('safety_checklists')
            ->where('shift', 'vespertino')
            ->update(['shift' => 'afternoon']);

        DB::table('safety_checklists')
            ->where('shift', 'noturno')
            ->update(['shift' => 'night']);
    }
};
