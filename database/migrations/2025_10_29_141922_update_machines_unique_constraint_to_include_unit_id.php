<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Atualiza a constraint de unicidade para permitir o mesmo identificador
     * de máquina em unidades diferentes.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Remove a constraint antiga que não incluía unit_id
            $table->dropUnique(['identifier']);

            // Cria nova constraint incluindo unit_id
            // Isso permite o mesmo identificador em unidades diferentes
            $table->unique(['identifier', 'unit_id'], 'machines_identifier_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Remove a constraint nova
            $table->dropUnique('machines_identifier_unit_unique');

            // Restaura a constraint antiga (sem unit_id)
            $table->unique(['identifier']);
        });
    }
};
