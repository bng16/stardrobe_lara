<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS in production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Define authorization gates
        Gate::define('view-bid-amount', function (User $user, Bid $bid) {
            return $user->id === $bid->user_id || $user->role === UserRole::Admin;
        });

        Gate::define('place-bid', function (User $user, Product $product) {
            return $user->role === UserRole::Buyer 
                && $product->isActive() 
                && !$product->hasEnded();
        });

        Gate::define('manage-creator-shop', function (User $user, CreatorShop $shop) {
            return $user->id === $shop->user_id || $user->role === UserRole::Admin;
        });

        Gate::define('admin-dashboard', function (User $user) {
            return $user->role === UserRole::Admin;
        });
    }
}
