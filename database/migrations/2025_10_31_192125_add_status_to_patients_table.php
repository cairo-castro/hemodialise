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
        Schema::table('patients', function (Blueprint $table) {
            // Adiciona o campo status com valores: ativo, inativo, transferido, alta, obito
            $table->enum('status', ['ativo', 'inativo', 'transferido', 'alta', 'obito'])
                ->default('ativo')
                ->after('observations');
        });

        // Migra dados existentes: active=1 -> status='ativo', active=0 -> status='inativo'
        DB::statement("UPDATE patients SET status = 'ativo' WHERE active = 1");
        DB::statement("UPDATE patients SET status = 'inativo' WHERE active = 0");

        // Remove a coluna active (mantida por enquanto para compatibilidade)
        // Schema::table('patients', function (Blueprint $table) {
        //     $table->dropColumn('active');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Restaura a coluna active se foi removida
            // if (!Schema::hasColumn('patients', 'active')) {
            //     $table->boolean('active')->default(true)->after('observations');
            // }

            // Remove o campo status
            $table->dropColumn('status');
        });
    }
};
