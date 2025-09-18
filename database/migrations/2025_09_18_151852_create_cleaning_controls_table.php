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
        Schema::create('cleaning_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('cleaning_date');
            $table->enum('shift', ['morning', 'afternoon', 'night']);
            $table->time('cleaning_time');

            // Tipos de limpeza
            $table->boolean('daily_cleaning')->default(false);
            $table->boolean('weekly_cleaning')->default(false);
            $table->boolean('monthly_cleaning')->default(false);
            $table->boolean('special_cleaning')->default(false);

            // Produtos utilizados
            $table->string('cleaning_products_used')->nullable();
            $table->text('cleaning_procedure')->nullable();

            // Verificações
            $table->boolean('external_cleaning_done')->default(false);
            $table->boolean('internal_cleaning_done')->default(false);
            $table->boolean('filter_replacement')->default(false);
            $table->boolean('system_disinfection')->default(false);

            $table->text('observations')->nullable();
            $table->string('responsible_signature')->nullable();

            $table->timestamps();

            // Índice para consultas eficientes
            $table->index(['machine_id', 'cleaning_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_controls');
    }
};
