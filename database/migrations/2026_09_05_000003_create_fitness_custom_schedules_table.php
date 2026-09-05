<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitness_custom_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('program_type', ['PPL', 'upper_lower']);
            $table->json('schedule_data');
            $table->timestamps();
            $table->unique(['user_id', 'program_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_custom_schedules');
    }
};
