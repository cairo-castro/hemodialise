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
        // Adiciona 'madrugada' ao enum shift da tabela safety_checklists
        // De acordo com a Secretaria de Saúde, são 4 turnos:
        // - Matutino: 06:00 - 12:00
        // - Vespertino: 12:00 - 18:00
        // - Noturno: 18:00 - 00:00
        // - Madrugada: 00:00 - 06:00
        DB::statement("ALTER TABLE safety_checklists MODIFY COLUMN shift ENUM('matutino', 'vespertino', 'noturno', 'madrugada') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'madrugada' do enum (volta para 3 turnos)
        DB::statement("ALTER TABLE safety_checklists MODIFY COLUMN shift ENUM('matutino', 'vespertino', 'noturno') NOT NULL");
    }
};
