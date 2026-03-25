<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CreatorController;
use App\Http\Controllers\Admin\BidController;
use App\Http\Controllers\Creator\OnboardingController;
use App\Http\Controllers\Creator\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\CreatorShopController;
use App\Http\Controllers\FollowController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password reset routes
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password-reset-sent', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showEmailSent'])->name('password.email.sent');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [\App\Http\Controllers\ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/profile/settings', [\App\Http\Controllers\ProfileController::class, 'settings'])->name('profile.settings');
    Route::put('/profile/settings', [\App\Http\Controllers\ProfileController::class, 'updateSettings'])->name('profile.settings.update');
});

// Test route for button component
Route::get('/test-button', function () {
    return view('test-button');
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
    
    // Bid management routes
    Route::get('/products/{product}/bids', [BidController::class, 'index'])->name('bids.index');
    Route::get('/products/{product}/bids/export/json', [BidController::class, 'exportJson'])->name('bids.export.json');
    Route::get('/products/{product}/bids/export/csv', [BidController::class, 'exportCsv'])->name('bids.export.csv');
});

// Creator onboarding routes
Route::middleware(['auth', 'role:creator'])->prefix('creator')->name('creator.')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/step', [OnboardingController::class, 'processStep'])->name('onboarding.step');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    
    // Product management routes
    Route::resource('products', ProductController::class);
    
    // Creator dashboard route
    Route::get('/dashboard', [\App\Http\Controllers\Creator\DashboardController::class, 'index'])->name('dashboard');
});

// Marketplace routes
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/for-you', [MarketplaceController::class, 'forYou'])->name('marketplace.for-you');
Route::get('/marketplace/{product}', [MarketplaceController::class, 'show'])->name('marketplace.show');

// Creator shop routes (public browsing)
Route::get('/creator-shops', [CreatorShopController::class, 'index'])->name('creator-shop.index');
Route::get('/creator-shop/{creator}', [CreatorShopController::class, 'show'])->name('creator-shop.show');

// Follow/unfollow creators
Route::middleware('auth')->group(function () {
    Route::post('/creators/{creator}/follow', [FollowController::class, 'store'])->name('creators.follow');
    Route::delete('/creators/{creator}/follow', [FollowController::class, 'destroy'])->name('creators.unfollow');
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
