<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitness_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->unsignedSmallInteger('calories_consumed')->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->boolean('workout_completed')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_logs');
    }
};
