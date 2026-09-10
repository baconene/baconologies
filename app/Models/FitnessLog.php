<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessLog extends Model
{
    protected $fillable = [
        'user_id', 'log_date', 'calories_consumed', 'weight_kg', 'workout_completed', 'notes',
    ];

    protected $casts = [
        'log_date'          => 'date',
        'workout_completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
