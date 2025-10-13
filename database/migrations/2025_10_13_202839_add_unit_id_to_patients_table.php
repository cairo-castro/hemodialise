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
            // Adiciona coluna unit_id para associar paciente à unidade
            $table->foreignId('unit_id')->nullable()->after('active')->constrained('units')->onDelete('cascade');
            
            // Adiciona índice composto para busca otimizada por unidade
            $table->index(['unit_id', 'active', 'created_at']);
            $table->index(['unit_id', 'full_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['unit_id', 'active', 'created_at']);
            $table->dropIndex(['unit_id', 'full_name']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
