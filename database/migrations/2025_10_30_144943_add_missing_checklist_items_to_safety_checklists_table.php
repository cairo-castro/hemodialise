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
        Schema::table('safety_checklists', function (Blueprint $table) {
            // PRÉ-DIÁLISE - 5 novos campos (adicionar após capillary_lines_identified)
            $table->boolean('reagent_test_performed')->default(false)->after('capillary_lines_identified');
            $table->boolean('pressure_sensors_verified')->default(false)->after('reagent_test_performed');
            $table->boolean('air_bubble_detector_verified')->default(false)->after('pressure_sensors_verified');

            // Adicionar após vascular_access_evaluated
            $table->boolean('av_fistula_arm_washed')->default(false)->after('vascular_access_evaluated');
            $table->boolean('patient_weighed')->default(false)->after('av_fistula_arm_washed');

            // DURANTE A SESSÃO - 4 novos campos (adicionar após dialysis_parameters_verified)
            $table->boolean('heparin_double_checked')->default(false)->after('dialysis_parameters_verified');
            $table->boolean('antisepsis_performed')->default(false)->after('heparin_double_checked');
            $table->boolean('vascular_access_monitored')->default(false)->after('antisepsis_performed');
            $table->boolean('vital_signs_monitored_during')->default(false)->after('vascular_access_monitored');

            // PÓS-DIÁLISE - 1 novo campo (adicionar após patient_vital_signs_stable)
            $table->boolean('complications_assessed')->default(false)->after('patient_vital_signs_stable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->dropColumn([
                // Pré-diálise
                'reagent_test_performed',
                'pressure_sensors_verified',
                'air_bubble_detector_verified',
                'av_fistula_arm_washed',
                'patient_weighed',
                // Durante a sessão
                'heparin_double_checked',
                'antisepsis_performed',
                'vascular_access_monitored',
                'vital_signs_monitored_during',
                // Pós-diálise
                'complications_assessed',
            ]);
        });
    }
};
