<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitness_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('age');
            $table->enum('gender', ['male', 'female']);
            $table->decimal('height_cm', 5, 1);
            $table->decimal('weight_kg', 5, 1);
            $table->enum('activity_level', [
                'sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extra_active',
            ]);
            $table->enum('goal', ['lose_weight', 'maintain', 'gain_muscle']);
            $table->enum('program_type', ['PPL', 'upper_lower']);
            $table->decimal('bmi', 4, 1);
            $table->unsignedSmallInteger('calorie_target');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_profiles');
    }
};
