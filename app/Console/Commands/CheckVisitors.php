<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Visitor;

class CheckVisitors extends Command
{
    protected $signature = 'visitors:check';
    protected $description = 'Check visitor data';

    public function handle()
    {
        $count = Visitor::count();
        $this->info("Total visitors: {$count}");

        if ($count > 0) {
            $visitor = Visitor::latest()->first();
            $this->info("Latest visitor:");
            $this->info("IP: {$visitor->ip_address}");
            $this->info("Device: {$visitor->device_type}");
            $this->info("Browser: {$visitor->browser_name} {$visitor->browser_version}");
            $this->info("OS: {$visitor->os_name} {$visitor->os_version}");
            $this->info("Location: {$visitor->location}");
            $this->info("Landing Page: {$visitor->landing_page}");
            $this->info("UTM Source: {$visitor->utm_source}");
            $this->info("Traffic Source: {$visitor->traffic_source}");
            
            $this->info("\n--- All Traffic Sources ---");
            $sources = Visitor::select('traffic_source', 'utm_source')
                ->whereNotNull('traffic_source')
                ->orWhereNotNull('utm_source')
                ->get();
                
            foreach ($sources as $source) {
                $this->info("Traffic: {$source->traffic_source}, UTM: {$source->utm_source}");
            }
        }
    }
}