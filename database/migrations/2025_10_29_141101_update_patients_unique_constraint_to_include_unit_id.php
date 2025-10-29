<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Atualiza a constraint de unicidade para permitir o mesmo paciente
     * (nome + data de nascimento) em unidades diferentes.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Remove a constraint antiga que não incluía unit_id
            $table->dropUnique('patients_name_birth_unique');

            // Cria nova constraint incluindo unit_id
            // Isso permite o mesmo paciente em unidades diferentes
            $table->unique(['full_name', 'birth_date', 'unit_id'], 'patients_name_birth_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Remove a constraint nova
            $table->dropUnique('patients_name_birth_unit_unique');

            // Restaura a constraint antiga (sem unit_id)
            $table->unique(['full_name', 'birth_date'], 'patients_name_birth_unique');
        });
    }
};
