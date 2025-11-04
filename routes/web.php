<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return Inertia::render('LandingPage', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/demo', function () {
    return Inertia::render('DemoSystems');
})->name('demo');



Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Analytics Routes (protected by auth)
Route::middleware(['auth', 'verified'])->prefix('analytics')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('analytics.dashboard');
    Route::get('/visitors', [AnalyticsController::class, 'visitors'])->name('analytics.visitors');
    Route::get('/charts', [AnalyticsController::class, 'charts'])->name('analytics.charts');
});

require __DIR__.'/settings.php';
