<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $stats = $this->getAnalyticsStats();
        $visitors = $this->getRecentVisitors();
        $dailyVisits = $this->getDailyVisits('7d');
        $deviceBreakdown = $this->getDeviceBreakdown();
        
        return Inertia::render('Analytics', [
            'stats' => $stats,
            'visitors' => $visitors,
            'dailyVisits' => $dailyVisits,
            'deviceBreakdown' => $deviceBreakdown
        ]);
    }

    public function visitors(Request $request)
    {
        $query = Visitor::query()->nonBots()->orderBy('created_at', 'desc');
        
        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        
        if ($request->filled('traffic_source')) {
            $query->where('traffic_source', $request->traffic_source);
        }
        
        if ($request->filled('device_type')) {
            if ($request->device_type === 'mobile') {
                $query->where('is_mobile', true);
            } elseif ($request->device_type === 'desktop') {
                $query->where('is_desktop', true);
            } elseif ($request->device_type === 'tablet') {
                $query->where('is_tablet', true);
            }
        }

        $visitors = $query->paginate(50)->withQueryString();
        
        // Get filter options
        $countries = Visitor::nonBots()
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();
            
        $trafficSources = Visitor::nonBots()
            ->whereNotNull('traffic_source')
            ->distinct()
            ->pluck('traffic_source')
            ->sort()
            ->values();

        return Inertia::render('Analytics', [
            'visitors' => $visitors,
            'filters' => [
                'countries' => $countries,
                'traffic_sources' => $trafficSources,
            ],
            'currentFilters' => $request->only(['date_from', 'date_to', 'country', 'traffic_source', 'device_type']),
            'activeView' => 'visitors'
        ]);
    }

    public function charts(Request $request)
    {
        $period = $request->get('period', '7d'); // 7d, 30d, 90d
        
        $chartData = [
            'daily_visits' => $this->getDailyVisits($period),
            'traffic_sources' => $this->getTrafficSourcesChart(),
            'devices' => $this->getDeviceChart(),
            'top_countries' => $this->getTopCountries(),
            'top_pages' => $this->getTopPages()
        ];

        return response()->json($chartData);
    }

    private function getAnalyticsStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_visitors' => Visitor::nonBots()->count(),
            'todays_visitors' => Visitor::nonBots()->whereDate('created_at', $today)->count(),
            'weekly_visitors' => Visitor::nonBots()->where('created_at', '>=', $thisWeek)->count(),
            'monthly_visitors' => Visitor::nonBots()->where('created_at', '>=', $thisMonth)->count(),
            'online_visitors' => Visitor::nonBots()->where('last_activity', '>=', Carbon::now()->subMinutes(15))->count(),
            'top_countries' => Visitor::nonBots()
                ->whereNotNull('country')
                ->select('country', DB::raw('COUNT(*) as count'))
                ->groupBy('country')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'country' => $item->country,
                        'count' => $item->count
                    ];
                }),
            'traffic_sources' => Visitor::nonBots()
                ->whereNotNull('traffic_source')
                ->select('traffic_source', DB::raw('COUNT(*) as count'))
                ->groupBy('traffic_source')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'source' => $item->traffic_source,
                        'count' => $item->count
                    ];
                }),
            'avg_session_duration' => Visitor::nonBots()
                ->whereNotNull('visit_duration')
                ->avg('visit_duration'),
            'bounce_rate' => $this->calculateBounceRate(),
        ];
    }

    private function getRecentVisitors(int $limit = 10): array
    {
        return Visitor::nonBots()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($visitor) {
                return [
                    'id' => $visitor->id,
                    'location' => $visitor->location,
                    'country' => $visitor->country,
                    'device_type' => $visitor->device_type,
                    'browser' => $visitor->browser_name . ' ' . $visitor->browser_version,
                    'os' => $visitor->os_name . ' ' . $visitor->os_version,
                    'traffic_source' => $visitor->traffic_source,
                    'landing_page' => $visitor->landing_page,
                    'page_views' => $visitor->page_views,
                    'visit_duration' => $visitor->visit_duration_formatted,
                    'visited_at' => $visitor->created_at->format('M j, Y H:i'),
                    'last_activity' => $visitor->last_activity?->format('M j, Y H:i'),
                ];
            })
            ->toArray();
    }

    private function getDailyVisits(string $period): array
    {
        $days = match ($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7
        };

        $startDate = Carbon::now()->subDays($days);
        
        return Visitor::nonBots()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as visitors, COUNT(DISTINCT visitor_id) as unique_visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'visitors' => $item->visitors,
                    'unique_visitors' => $item->unique_visitors,
                ];
            })
            ->toArray();
    }

    private function getTrafficSourcesChart(): array
    {
        return Visitor::nonBots()
            ->whereNotNull('traffic_source')
            ->select('traffic_source', DB::raw('COUNT(*) as count'))
            ->groupBy('traffic_source')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst($item->traffic_source),
                    'value' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getDeviceChart(): array
    {
        $total = Visitor::nonBots()->count();
        
        return [
            [
                'name' => 'Mobile',
                'value' => Visitor::nonBots()->where('is_mobile', true)->count(),
                'percentage' => $total > 0 ? round((Visitor::nonBots()->where('is_mobile', true)->count() / $total) * 100, 1) : 0
            ],
            [
                'name' => 'Desktop',
                'value' => Visitor::nonBots()->where('is_desktop', true)->count(),
                'percentage' => $total > 0 ? round((Visitor::nonBots()->where('is_desktop', true)->count() / $total) * 100, 1) : 0
            ],
            [
                'name' => 'Tablet',
                'value' => Visitor::nonBots()->where('is_tablet', true)->count(),
                'percentage' => $total > 0 ? round((Visitor::nonBots()->where('is_tablet', true)->count() / $total) * 100, 1) : 0
            ],
        ];
    }

    private function getTopCountries(int $limit = 10): array
    {
        return Visitor::nonBots()
            ->whereNotNull('country')
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->country,
                    'value' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getTopPages(int $limit = 10): array
    {
        return Visitor::nonBots()
            ->whereNotNull('landing_page')
            ->select('landing_page', DB::raw('COUNT(*) as count'))
            ->groupBy('landing_page')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'page' => parse_url($item->landing_page, PHP_URL_PATH) ?: '/',
                    'visits' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getDeviceBreakdown(): array
    {
        return [
            [
                'device' => 'Desktop',
                'count' => Visitor::nonBots()->where('is_desktop', true)->count(),
            ],
            [
                'device' => 'Mobile',
                'count' => Visitor::nonBots()->where('is_mobile', true)->count(),
            ],
            [
                'device' => 'Tablet',
                'count' => Visitor::nonBots()->where('is_tablet', true)->count(),
            ],
        ];
    }

    private function calculateBounceRate(): float
    {
        $totalVisitors = Visitor::nonBots()->count();
        $singlePageVisitors = Visitor::nonBots()->where('page_views', 1)->count();
        
        return $totalVisitors > 0 ? round(($singlePageVisitors / $totalVisitors) * 100, 1) : 0;
    }

    public function getRealTimeStats()
    {
        $stats = [
            'online_visitors' => Visitor::nonBots()->where('last_activity', '>=', Carbon::now()->subMinutes(15))->count(),
            'todays_visitors' => Visitor::nonBots()->whereDate('created_at', Carbon::today())->count(),
            'total_visitors' => Visitor::nonBots()->count(),
            'last_updated' => Carbon::now()->toISOString()
        ];

        return response()->json($stats);
    }
}
