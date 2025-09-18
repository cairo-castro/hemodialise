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
        Schema::create('safety_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('session_date');
            $table->enum('shift', ['morning', 'afternoon', 'night']);

            // Checklist pré-diálise
            $table->boolean('machine_disinfected')->default(false);
            $table->boolean('capillary_lines_identified')->default(false);
            $table->boolean('patient_identification_confirmed')->default(false);
            $table->boolean('vascular_access_evaluated')->default(false);
            $table->boolean('vital_signs_checked')->default(false);
            $table->boolean('medications_reviewed')->default(false);
            $table->boolean('dialyzer_membrane_checked')->default(false);
            $table->boolean('equipment_functioning_verified')->default(false);

            // Observações
            $table->text('observations')->nullable();
            $table->text('incidents')->nullable();

            $table->timestamps();

            // Índice único para evitar duplicatas
            $table->unique(['patient_id', 'machine_id', 'session_date', 'shift'], 'safety_checklist_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_checklists');
    }
};
