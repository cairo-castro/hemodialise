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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // checklist, success, warning, error, info, machine, patient
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (machine_id, patient_id, etc)
            $table->string('action_url')->nullable(); // URL to navigate when clicked
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'read_at']);
            $table->index(['unit_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
