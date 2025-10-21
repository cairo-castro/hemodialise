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
            // Índice para busca por nome completo
            $table->index('full_name');

            // Índice composto para ordenação por active + created_at (últimos pacientes ativos)
            $table->index(['active', 'created_at']);

            // Índice composto para busca com filtro ativo
            $table->index(['active', 'full_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['full_name']);
            $table->dropIndex(['active', 'created_at']);
            $table->dropIndex(['active', 'full_name']);
        });
    }
};
