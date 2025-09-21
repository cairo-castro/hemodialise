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
        Schema::table('users', function (Blueprint $table) {
            // Alterar enum para incluir novos roles
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',        // Administrador do sistema
                'gestor',       // Gestor - acesso total
                'coordenador',  // Coordenador - gestão operacional
                'supervisor',   // Supervisor - supervisão técnica
                'tecnico'       // Técnico - apenas mobile
            ])->default('tecnico');

            // Adicionar preferência de view padrão
            $table->enum('default_view', ['mobile', 'desktop', 'admin'])->default('desktop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'default_view']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'field_user'])->default('field_user');
        });
    }
};