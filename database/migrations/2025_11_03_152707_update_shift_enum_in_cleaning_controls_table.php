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
        // Passo 1: Expandir o enum para incluir tanto valores em inglês quanto em português
        DB::statement("ALTER TABLE cleaning_controls MODIFY COLUMN shift ENUM('morning', 'afternoon', 'night', 'matutino', 'vespertino', 'noturno') NOT NULL");

        // Passo 2: Converter valores existentes de inglês para português
        DB::table('cleaning_controls')->where('shift', 'morning')->update(['shift' => 'matutino']);
        DB::table('cleaning_controls')->where('shift', 'afternoon')->update(['shift' => 'vespertino']);
        DB::table('cleaning_controls')->where('shift', 'night')->update(['shift' => 'noturno']);

        // Passo 3: Remover valores em inglês do enum, mantendo apenas os em português
        DB::statement("ALTER TABLE cleaning_controls MODIFY COLUMN shift ENUM('matutino', 'vespertino', 'noturno') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os valores originais em inglês
        DB::statement("ALTER TABLE cleaning_controls MODIFY COLUMN shift ENUM('morning', 'afternoon', 'night') NOT NULL");
    }
};
