<?php

namespace App\Http\Controllers;

use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreatorShopController extends Controller
{
    /**
     * Browse creator shops.
     */
    public function index(Request $request): View
    {
        $query = CreatorShop::query()
            ->where('is_onboarded', true)
            ->with('creator')
            ->withCount('followers')
            ->withCount([
                'products as active_products_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', '%' . $search . '%')
                    ->orWhere('bio', 'like', '%' . $search . '%');
            });
        }

        $shops = $query
            ->orderByDesc('followers_count')
            ->orderBy('shop_name')
            ->paginate(18)
            ->withQueryString();

        $hasActiveFilters = $request->filled('search');

        return view('creator-shop.index', [
            'shops' => $shops,
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }

    /**
     * Display the public shop page for a creator.
     */
    public function show(User $creator): View
    {
        $shop = $creator->creatorShop()
            ->where('is_onboarded', true)
            ->firstOrFail();

        $shop->load('creator');

        $products = $shop->products()
            ->where('status', 'active')
            ->with('images')
            ->latest('auction_end')
            ->paginate(12);

        // Get follower count
        $followerCount = $shop->getFollowerCount();

        // Check if current user is following
        $isFollowing = false;
        if (auth()->check()) {
            $isFollowing = auth()->user()->follows()->where('creator_id', $shop->user_id)->exists();
        }

        return view('creator-shop.show', [
            'shop' => $shop,
            'creator' => $creator,
            'products' => $products,
            'followerCount' => $followerCount,
            'isFollowing' => $isFollowing,
        ]);
    }
}
