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
        Schema::create('chemical_disinfections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('disinfection_date');
            $table->enum('shift', ['morning', 'afternoon', 'night']);
            $table->time('start_time');
            $table->time('end_time')->nullable();

            // Produtos químicos utilizados
            $table->string('chemical_product');
            $table->decimal('concentration', 5, 2);
            $table->string('concentration_unit')->default('%');
            $table->integer('contact_time_minutes');

            // Verificações do processo
            $table->decimal('initial_temperature', 4, 1)->nullable();
            $table->decimal('final_temperature', 4, 1)->nullable();
            $table->boolean('circulation_verified')->default(false);
            $table->boolean('contact_time_completed')->default(false);
            $table->boolean('rinse_performed')->default(false);
            $table->boolean('system_tested')->default(false);

            // Controle de qualidade
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('effectiveness_verified')->default(false);

            $table->text('observations')->nullable();
            $table->string('responsible_signature');

            $table->timestamps();

            // Índices para consultas eficientes
            $table->index(['machine_id', 'disinfection_date']);
            $table->index(['chemical_product', 'disinfection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chemical_disinfections');
    }
};
