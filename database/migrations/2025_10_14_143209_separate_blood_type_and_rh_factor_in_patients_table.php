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
            // Adiciona novos campos
            $table->enum('blood_group', ['A', 'B', 'AB', 'O'])->nullable()->after('birth_date');
            $table->enum('rh_factor', ['+', '-'])->nullable()->after('blood_group');

            // Remove o campo antigo blood_type
            $table->dropColumn('blood_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Restaura o campo antigo
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('birth_date');

            // Remove os novos campos
            $table->dropColumn(['blood_group', 'rh_factor']);
        });
    }
};
