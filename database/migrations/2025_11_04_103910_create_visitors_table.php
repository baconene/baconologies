<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('visitor_id'); // unique visitor identifier
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->string('browser_name', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('os_name', 50)->nullable();
            $table->string('os_version', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('isp', 200)->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('referrer_domain', 100)->nullable();
            $table->string('traffic_source', 50)->nullable(); // direct, social, search, referral, email
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('current_url')->nullable();
            $table->string('landing_page')->nullable();
            $table->integer('screen_width')->nullable();
            $table->integer('screen_height')->nullable();
            $table->string('language', 10)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_tablet')->default(false);
            $table->boolean('is_desktop')->default(false);
            $table->boolean('is_bot')->default(false);
            $table->integer('visit_duration')->nullable(); // in seconds
            $table->json('pages_visited')->nullable();
            $table->integer('page_views')->default(1);
            $table->timestamp('first_visit_at')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
            
            // Add indexes
            $table->index('session_id');
            $table->index('visitor_id');
            $table->index('ip_address');
            $table->index('country');
            $table->index('referrer_domain');
            $table->index('traffic_source');
            $table->index('landing_page');
            $table->index('is_mobile');
            $table->index('is_bot');
            $table->index(['ip_address', 'created_at']);
            $table->index(['traffic_source', 'created_at']);
            $table->index(['country', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
