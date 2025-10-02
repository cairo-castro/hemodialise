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
        Schema::create('cleaning_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('checklist_date');
            $table->enum('shift', ['1', '2', '3', '4'])->comment('Turno: 1º, 2º, 3º, 4º');

            // Chemical Disinfection
            $table->time('chemical_disinfection_time')->nullable();
            $table->boolean('chemical_disinfection_completed')->default(false);

            // Surface Cleaning Conformities (C=Conforme, NC=Não Conforme, NA=Não se Aplica)
            $table->enum('hd_machine_cleaning', ['C', 'NC', 'NA'])->nullable()->comment('Máquina de HD');
            $table->enum('osmosis_cleaning', ['C', 'NC', 'NA'])->nullable()->comment('Osmose');
            $table->enum('serum_support_cleaning', ['C', 'NC', 'NA'])->nullable()->comment('Suporte de Soro');

            $table->text('observations')->nullable();

            $table->timestamps();

            // Unique constraint: one checklist per machine, date, and shift
            $table->unique(['machine_id', 'checklist_date', 'shift']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_checklists');
    }
};
