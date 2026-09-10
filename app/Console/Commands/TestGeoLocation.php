<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGeoLocation extends Command
{
    protected $signature = 'geo:test {ip?}';
    protected $description = 'Test IP geolocation API';

    public function handle()
    {
        $ip = $this->argument('ip') ?: '8.8.8.8'; // Google's public DNS as test IP
        
        $this->info("Testing geolocation for IP: {$ip}");
        
        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    $this->info("✅ Geolocation API working!");
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['Country', $data['country'] ?? 'N/A'],
                            ['Region', $data['regionName'] ?? 'N/A'],
                            ['City', $data['city'] ?? 'N/A'],
                            ['ISP', $data['isp'] ?? 'N/A'],
                            ['Timezone', $data['timezone'] ?? 'N/A'],
                            ['Latitude', $data['lat'] ?? 'N/A'],
                            ['Longitude', $data['lon'] ?? 'N/A'],
                        ]
                    );
                } else {
                    $this->error("❌ API returned error: " . ($data['message'] ?? 'Unknown error'));
                }
            } else {
                $this->error("❌ HTTP request failed with status: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }
}