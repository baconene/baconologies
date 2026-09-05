<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\FitnessController;

Route::get('/', function () {
    return Inertia::render('LandingPage', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/demo', function () {
    return Inertia::render('DemoSystems');
})->name('demo');

Route::get('/ganbatteneMira', function () {
    return Inertia::render('GanbatteneMira');
})->name('ganbatteneMira');

Route::get('/congrats', function () {
    return Inertia::render('Congrats');
})->name('congrats');

Route::get('/sampleNWSForecast', function () {
    return Inertia::render('SampleNWSForecast');
})->name('sampleNWSForecast');

Route::get('/carmel', function () {
    return Inertia::render('Carmel');
})->name('carmel');

Route::get('/WriteOffForecastAndActualComparator', function () {
    return Inertia::render('WriteOffForecastAndActualComparator');
})->name('writeOffComparator');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Analytics Routes (protected by auth)
Route::middleware(['auth', 'verified'])->prefix('analytics')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('analytics.dashboard');
    Route::get('/visitors', [AnalyticsController::class, 'visitors'])->name('analytics.visitors');
    Route::get('/charts', [AnalyticsController::class, 'charts'])->name('analytics.charts');
    Route::get('/real-time-stats', [AnalyticsController::class, 'getRealTimeStats'])->name('analytics.realtime');
});

// Fitness routes
Route::prefix('fitness')->name('fitness.')->group(function () {
    Route::get('/', [FitnessController::class, 'landing'])->name('home');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/onboarding', [FitnessController::class, 'onboarding'])->name('onboarding');
        Route::post('/onboarding', [FitnessController::class, 'storeProfile'])->name('onboarding.store');
        Route::get('/dashboard', [FitnessController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedule', [FitnessController::class, 'schedule'])->name('schedule');
        Route::post('/schedule/save', [FitnessController::class, 'saveSchedule'])->name('schedule.save');
        Route::post('/schedule/reset', [FitnessController::class, 'resetSchedule'])->name('schedule.reset');
        Route::post('/log', [FitnessController::class, 'storeLog'])->name('log');
    });
});

require __DIR__.'/settings.php';
