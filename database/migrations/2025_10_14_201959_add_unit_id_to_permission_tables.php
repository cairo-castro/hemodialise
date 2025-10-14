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
        // Adicionar unit_id em model_has_permissions para permissões por unidade
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('model_type')->constrained()->onDelete('cascade');
            $table->index('unit_id');
        });

        // Adicionar unit_id em model_has_roles para roles por unidade
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('model_type')->constrained()->onDelete('cascade');
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
