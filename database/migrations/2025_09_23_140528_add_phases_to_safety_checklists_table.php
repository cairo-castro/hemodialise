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
            // Controle de fases
            $table->enum('current_phase', ['pre_dialysis', 'during_session', 'post_dialysis', 'completed', 'interrupted'])
                  ->default('pre_dialysis')
                  ->after('shift');

            // Timestamps de cada fase
            $table->timestamp('pre_dialysis_started_at')->nullable()->after('current_phase');
            $table->timestamp('pre_dialysis_completed_at')->nullable()->after('pre_dialysis_started_at');
            $table->timestamp('during_session_started_at')->nullable()->after('pre_dialysis_completed_at');
            $table->timestamp('during_session_completed_at')->nullable()->after('during_session_started_at');
            $table->timestamp('post_dialysis_started_at')->nullable()->after('during_session_completed_at');
            $table->timestamp('post_dialysis_completed_at')->nullable()->after('post_dialysis_started_at');

            // Controle de interrupção
            $table->boolean('is_interrupted')->default(false)->after('post_dialysis_completed_at');
            $table->timestamp('interrupted_at')->nullable()->after('is_interrupted');
            $table->text('interruption_reason')->nullable()->after('interrupted_at');

            // Novos campos para fase "durante a sessão"
            $table->boolean('dialysis_parameters_verified')->default(false)->after('equipment_functioning_verified');
            $table->boolean('patient_comfort_assessed')->default(false)->after('dialysis_parameters_verified');
            $table->boolean('fluid_balance_monitored')->default(false)->after('patient_comfort_assessed');
            $table->boolean('alarms_responded')->default(false)->after('fluid_balance_monitored');

            // Novos campos para fase "pós-diálise"
            $table->boolean('session_completed_safely')->default(false)->after('alarms_responded');
            $table->boolean('vascular_access_secured')->default(false)->after('session_completed_safely');
            $table->boolean('patient_vital_signs_stable')->default(false)->after('vascular_access_secured');
            $table->boolean('equipment_cleaned')->default(false)->after('patient_vital_signs_stable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_checklists', function (Blueprint $table) {
            $table->dropColumn([
                'current_phase',
                'pre_dialysis_started_at',
                'pre_dialysis_completed_at',
                'during_session_started_at',
                'during_session_completed_at',
                'post_dialysis_started_at',
                'post_dialysis_completed_at',
                'is_interrupted',
                'interrupted_at',
                'interruption_reason',
                'dialysis_parameters_verified',
                'patient_comfort_assessed',
                'fluid_balance_monitored',
                'alarms_responded',
                'session_completed_safely',
                'vascular_access_secured',
                'patient_vital_signs_stable',
                'equipment_cleaned'
            ]);
        });
    }
};