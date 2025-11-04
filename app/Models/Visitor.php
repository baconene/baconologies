<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'visitor_id',
        'ip_address',
        'user_agent',
        'device_type',
        'device_name',
        'browser_name',
        'browser_version',
        'os_name',
        'os_version',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'isp',
        'referrer_url',
        'referrer_domain',
        'traffic_source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'current_url',
        'landing_page',
        'screen_width',
        'screen_height',
        'language',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'is_bot',
        'visit_duration',
        'pages_visited',
        'page_views',
        'first_visit_at',
        'last_activity'
    ];

    protected $casts = [
        'pages_visited' => 'array',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'is_bot' => 'boolean',
        'first_visit_at' => 'datetime',
        'last_activity' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    // Scopes for analytics
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeUniqueVisitors($query)
    {
        return $query->distinct('visitor_id');
    }

    public function scopeNonBots($query)
    {
        return $query->where('is_bot', false);
    }

    public function scopeMobile($query)
    {
        return $query->where('is_mobile', true);
    }

    public function scopeDesktop($query)
    {
        return $query->where('is_desktop', true);
    }

    // Accessors
    public function getDeviceTypeAttribute($value)
    {
        if ($this->is_mobile) return 'Mobile';
        if ($this->is_tablet) return 'Tablet';
        if ($this->is_desktop) return 'Desktop';
        return $value ?: 'Unknown';
    }

    public function getLocationAttribute()
    {
        $location = [];
        if ($this->city) $location[] = $this->city;
        if ($this->region) $location[] = $this->region;
        if ($this->country) $location[] = $this->country;
        
        return implode(', ', $location) ?: 'Unknown';
    }

    public function getVisitDurationFormattedAttribute()
    {
        if (!$this->visit_duration) return '0s';
        
        $hours = floor($this->visit_duration / 3600);
        $minutes = floor(($this->visit_duration % 3600) / 60);
        $seconds = $this->visit_duration % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m {$seconds}s";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }
        
        return "{$seconds}s";
    }
}
