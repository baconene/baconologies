<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessCustomSchedule extends Model
{
    protected $fillable = ['user_id', 'program_type', 'schedule_data'];

    protected $casts = ['schedule_data' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
