<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adiciona unit_id denormalizado nas tabelas de procedimentos para:
     * 1. Melhorar performance (queries diretas sem JOIN)
     * 2. Adicionar camada extra de segurança
     * 3. Facilitar queries e relatórios
     * 4. Usar índices otimizados
     */
    public function up(): void
    {
        // 1. Safety Checklists
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('machine_id')
                ->constrained('units')
                ->onDelete('cascade');

            // Índice composto para queries rápidas
            $table->index(['unit_id', 'created_at']);
            $table->index(['unit_id', 'current_phase']);
        });

        // Popular unit_id existente através da máquina
        DB::statement('
            UPDATE safety_checklists sc
            INNER JOIN machines m ON sc.machine_id = m.id
            SET sc.unit_id = m.unit_id
            WHERE sc.unit_id IS NULL
        ');

        // Tornar NOT NULL após popular
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });

        // 2. Cleaning Controls
        Schema::table('cleaning_controls', function (Blueprint $table) {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('machine_id')
                ->constrained('units')
                ->onDelete('cascade');

            $table->index(['unit_id', 'cleaning_date']);
            $table->index(['unit_id', 'shift']);
        });

        DB::statement('
            UPDATE cleaning_controls cc
            INNER JOIN machines m ON cc.machine_id = m.id
            SET cc.unit_id = m.unit_id
            WHERE cc.unit_id IS NULL
        ');

        Schema::table('cleaning_controls', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });

        // 3. Chemical Disinfections
        Schema::table('chemical_disinfections', function (Blueprint $table) {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('machine_id')
                ->constrained('units')
                ->onDelete('cascade');

            $table->index(['unit_id', 'disinfection_date']);
        });

        DB::statement('
            UPDATE chemical_disinfections cd
            INNER JOIN machines m ON cd.machine_id = m.id
            SET cd.unit_id = m.unit_id
            WHERE cd.unit_id IS NULL
        ');

        Schema::table('chemical_disinfections', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });

        // 4. Cleaning Checklists
        Schema::table('cleaning_checklists', function (Blueprint $table) {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('machine_id')
                ->constrained('units')
                ->onDelete('cascade');

            $table->index(['unit_id', 'checklist_date']);
            $table->index(['unit_id', 'shift']);
        });

        DB::statement('
            UPDATE cleaning_checklists cl
            INNER JOIN machines m ON cl.machine_id = m.id
            SET cl.unit_id = m.unit_id
            WHERE cl.unit_id IS NULL
        ');

        Schema::table('cleaning_checklists', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id', 'created_at']);
            $table->dropIndex(['unit_id', 'current_phase']);
            $table->dropColumn('unit_id');
        });

        Schema::table('cleaning_controls', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id', 'cleaning_date']);
            $table->dropIndex(['unit_id', 'shift']);
            $table->dropColumn('unit_id');
        });

        Schema::table('chemical_disinfections', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id', 'disinfection_date']);
            $table->dropColumn('unit_id');
        });

        Schema::table('cleaning_checklists', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id', 'checklist_date']);
            $table->dropIndex(['unit_id', 'shift']);
            $table->dropColumn('unit_id');
        });
    }
};
