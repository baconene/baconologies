<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip tracking for certain conditions
        if ($this->shouldSkipTracking($request)) {
            return $next($request);
        }

        try {
            $this->trackVisitor($request);
        } catch (\Exception $e) {
            Log::error('Visitor tracking failed: ' . $e->getMessage());
        }

        return $next($request);
    }

    private function shouldSkipTracking(Request $request): bool
    {
        // Skip API routes, admin routes, or specific paths
        return $request->is('api/*') || 
               $request->is('admin/*') || 
               $request->is('_debugbar/*') ||
               $request->ajax() && !$request->is('/');
    }

    private function trackVisitor(Request $request): void
    {
        $agent = new Agent();
        $sessionId = $request->session()->getId();
        $visitorId = $this->getVisitorId($request);
        $ipAddress = $this->getClientIpAddress($request);
        
        // Get or create visitor record
        $visitor = Visitor::where('session_id', $sessionId)
                         ->where('visitor_id', $visitorId)
                         ->first();

        if (!$visitor) {
            $visitor = $this->createNewVisitor($request, $agent, $sessionId, $visitorId, $ipAddress);
        } else {
            $this->updateExistingVisitor($visitor, $request);
        }
    }

    private function getVisitorId(Request $request): string
    {
        // Try to get visitor ID from cookie, otherwise generate new one
        $visitorId = $request->cookie('visitor_id');
        
        if (!$visitorId) {
            $visitorId = Str::uuid()->toString();
            // Set cookie for 30 days
            cookie()->queue('visitor_id', $visitorId, 60 * 24 * 30);
        }
        
        return $visitorId;
    }

    private function getClientIpAddress(Request $request): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    private function createNewVisitor(Request $request, Agent $agent, string $sessionId, string $visitorId, string $ipAddress): Visitor
    {
        $geoData = $this->getGeoLocation($ipAddress);
        $trafficData = $this->analyzeTrafficSource($request);
        $deviceData = $this->getDeviceInfo($agent, $request);

        return Visitor::create(array_merge([
            'session_id' => $sessionId,
            'visitor_id' => $visitorId,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'current_url' => $request->fullUrl(),
            'landing_page' => $request->fullUrl(),
            'language' => $request->getPreferredLanguage(),
            'pages_visited' => [$request->path()],
            'page_views' => 1,
            'first_visit_at' => now(),
            'last_activity' => now(),
        ], $geoData, $trafficData, $deviceData));
    }

    private function updateExistingVisitor(Visitor $visitor, Request $request): void
    {
        $pagesVisited = $visitor->pages_visited ?? [];
        $currentPath = $request->path();
        
        if (!in_array($currentPath, $pagesVisited)) {
            $pagesVisited[] = $currentPath;
        }

        $visitor->update([
            'current_url' => $request->fullUrl(),
            'pages_visited' => $pagesVisited,
            'page_views' => $visitor->page_views + 1,
            'last_activity' => now(),
            'visit_duration' => now()->diffInSeconds($visitor->first_visit_at)
        ]);
    }

    private function getGeoLocation(string $ipAddress): array
    {
        // Skip localhost/private IPs
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || 
            filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return $this->getDefaultGeoData();
        }

        try {
            // Using ip-api.com (free tier: 1000 requests/hour)
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ipAddress}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'city' => $data['city'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'isp' => $data['isp'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Geo location API failed: ' . $e->getMessage());
        }

        return $this->getDefaultGeoData();
    }

    private function getDefaultGeoData(): array
    {
        return [
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => null,
            'isp' => null,
        ];
    }

    private function analyzeTrafficSource(Request $request): array
    {
        $referrer = $request->header('referer');
        $utmSource = $request->get('utm_source');
        $utmMedium = $request->get('utm_medium');
        
        $trafficSource = 'direct';
        $referrerDomain = null;

        if ($referrer) {
            $referrerDomain = parse_url($referrer, PHP_URL_HOST);
            $trafficSource = $this->categorizeTrafficSource($referrerDomain);
        }

        if ($utmSource) {
            $trafficSource = $this->categorizeTrafficSourceByUtm($utmSource, $utmMedium);
        }

        return [
            'referrer_url' => $referrer,
            'referrer_domain' => $referrerDomain,
            'traffic_source' => $trafficSource,
            'utm_source' => $request->get('utm_source'),
            'utm_medium' => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'utm_term' => $request->get('utm_term'),
            'utm_content' => $request->get('utm_content'),
        ];
    }

    private function categorizeTrafficSource(?string $domain): string
    {
        if (!$domain) return 'direct';

        $socialNetworks = [
            'facebook.com', 'twitter.com', 'linkedin.com', 'instagram.com',
            'youtube.com', 'tiktok.com', 'pinterest.com', 'snapchat.com',
            'reddit.com', 'tumblr.com'
        ];

        $searchEngines = [
            'google.com', 'bing.com', 'yahoo.com', 'duckduckgo.com',
            'baidu.com', 'yandex.com'
        ];

        foreach ($socialNetworks as $social) {
            if (str_contains($domain, $social)) {
                return 'social';
            }
        }

        foreach ($searchEngines as $search) {
            if (str_contains($domain, $search)) {
                return 'search';
            }
        }

        return 'referral';
    }

    private function categorizeTrafficSourceByUtm(?string $utmSource, ?string $utmMedium): string
    {
        if (!$utmSource) return 'direct';

        $source = strtolower($utmSource);
        $medium = strtolower($utmMedium ?? '');

        if (in_array($source, ['google', 'bing', 'yahoo']) || $medium === 'cpc' || $medium === 'ppc') {
            return 'search';
        }

        if (in_array($source, ['facebook', 'twitter', 'linkedin', 'instagram']) || $medium === 'social') {
            return 'social';
        }

        if ($medium === 'email' || $source === 'newsletter') {
            return 'email';
        }

        return 'campaign';
    }

    private function getDeviceInfo(Agent $agent, Request $request): array
    {
        return [
            'device_name' => $agent->device() ?: 'Unknown',
            'browser_name' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'os_name' => $agent->platform(),
            'os_version' => $agent->version($agent->platform()),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_desktop' => $agent->isDesktop(),
            'is_bot' => $agent->isRobot(),
            'screen_width' => $request->header('X-Screen-Width'),
            'screen_height' => $request->header('X-Screen-Height'),
        ];
    }
}
