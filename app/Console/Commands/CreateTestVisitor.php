<?php

namespace App\Console\Commands;

use App\Models\Visitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CreateTestVisitor extends Command
{
    protected $signature = 'visitor:create {ip?}';
    protected $description = 'Create a test visitor with geolocation data';

    public function handle()
    {
        $ip = $this->argument('ip') ?: '1.1.1.1'; // Cloudflare DNS as test IP
        
        $this->info("Creating test visitor with IP: {$ip}");
        
        // Get geolocation data
        $geoData = $this->getGeoLocation($ip);
        
        $visitor = Visitor::create([
            'session_id' => Str::random(40),
            'visitor_id' => Str::uuid()->toString(),
            'ip_address' => $ip,
            'user_agent' => 'Test User Agent (Chrome/91.0)',
            'device_type' => 'Desktop',
            'device_name' => 'Computer',
            'browser_name' => 'Chrome',
            'browser_version' => '91.0',
            'os_name' => 'Windows',
            'os_version' => '10',
            'country' => $geoData['country'],
            'region' => $geoData['region'],
            'city' => $geoData['city'],
            'latitude' => $geoData['latitude'],
            'longitude' => $geoData['longitude'],
            'timezone' => $geoData['timezone'],
            'isp' => $geoData['isp'],
            'referrer_url' => null,
            'referrer_domain' => null,
            'traffic_source' => 'direct',
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'current_url' => 'https://example.com/',
            'landing_page' => 'https://example.com/',
            'language' => 'en',
            'is_mobile' => false,
            'is_tablet' => false,
            'is_desktop' => true,
            'is_bot' => false,
            'pages_visited' => ['/'],
            'page_views' => 1,
            'first_visit_at' => now(),
            'last_activity' => now(),
        ]);
        
        $this->info("✅ Test visitor created!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $visitor->id],
                ['IP', $visitor->ip_address],
                ['Location', $visitor->location],
                ['Country', $visitor->country],
                ['City', $visitor->city],
                ['ISP', $visitor->isp],
                ['Device', $visitor->device_type],
            ]
        );
    }
    
    private function getGeoLocation(string $ipAddress): array
    {
        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}");
            
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
            $this->error("Geo API Error: " . $e->getMessage());
        }

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
}