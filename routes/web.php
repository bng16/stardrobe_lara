<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CreatorController;
use App\Http\Controllers\Creator\OnboardingController;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/auctions/export', [DashboardController::class, 'exportAuction'])->name('auctions.export');
    Route::get('/auctions/{product}/export', [DashboardController::class, 'exportAuction'])->name('auctions.export.single');
    Route::post('/auctions/import', [DashboardController::class, 'importAuction'])->name('auctions.import');
    
    // Creator management routes
    Route::get('/creators', [CreatorController::class, 'index'])->name('creators.index');
    Route::post('/creators', [CreatorController::class, 'store'])->name('creators.store');
});

// Creator onboarding routes
Route::middleware(['auth', 'role:creator'])->prefix('creator')->name('creator.')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

// Test routes for RoleMiddleware
if (app()->environment('testing')) {
    Route::middleware(['auth', 'role:admin'])->get('/test-admin-route', function () {
        return response()->json(['message' => 'Admin access granted']);
    });

    Route::middleware(['auth', 'role:creator'])->get('/test-creator-route', function () {
        return response()->json(['message' => 'Creator access granted']);
    });

    Route::middleware(['auth', 'role:buyer'])->get('/test-buyer-route', function () {
        return response()->json(['message' => 'Buyer access granted']);
    });

    Route::middleware(['auth', 'creator.onboarded'])->get('/test-creator-onboarded-route', function () {
        return response()->json(['message' => 'Onboarded creator access granted']);
    });
}
