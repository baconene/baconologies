<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessProfile extends Model
{
    protected $fillable = [
        'user_id', 'age', 'gender', 'height_cm', 'weight_kg',
        'activity_level', 'goal', 'program_type', 'bmi', 'calorie_target',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function calculateBmi(float $weightKg, float $heightCm): float
    {
        $heightM = $heightCm / 100;
        return round($weightKg / ($heightM ** 2), 1);
    }

    public static function calculateCalorieTarget(
        float $weightKg,
        float $heightCm,
        int $age,
        string $gender,
        string $activityLevel,
        string $goal,
    ): int {
        // Mifflin-St Jeor BMR
        $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);
        $bmr += $gender === 'male' ? 5 : -161;

        $multipliers = [
            'sedentary'          => 1.2,
            'lightly_active'     => 1.375,
            'moderately_active'  => 1.55,
            'very_active'        => 1.725,
            'extra_active'       => 1.9,
        ];

        $tdee = $bmr * ($multipliers[$activityLevel] ?? 1.55);

        $adjustments = [
            'lose_weight'  => -500,
            'maintain'     => 0,
            'gain_muscle'  => 300,
        ];

        return (int) round($tdee + ($adjustments[$goal] ?? 0));
    }
}
