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
        Schema::table('cleaning_controls', function (Blueprint $table) {
            // Novos itens de limpeza conforme solicitado
            // Valores: true (Conforme), false (Não Conforme), null (N/A)
            $table->boolean('hd_machine_cleaning')->nullable()->after('system_disinfection');
            $table->boolean('osmosis_cleaning')->nullable()->after('hd_machine_cleaning');
            $table->boolean('serum_support_cleaning')->nullable()->after('osmosis_cleaning');
            $table->boolean('chemical_disinfection')->nullable()->after('serum_support_cleaning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaning_controls', function (Blueprint $table) {
            $table->dropColumn([
                'hd_machine_cleaning',
                'osmosis_cleaning',
                'serum_support_cleaning',
                'chemical_disinfection'
            ]);
        });
    }
};
