<?php

namespace App\Http\Controllers;

use App\Models\CreatorShop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatorShopController extends Controller
{
    /**
     * Display the public shop page for a creator.
     */
    public function show(CreatorShop $shop): Response
    {
        // Load creator with shop and active products
        $shop->load([
            'creator',
            'products' => function ($query) {
                $query->where('status', 'active')
                    ->with('images')
                    ->latest();
            },
        ]);

        // Get follower count
        $followerCount = $shop->getFollowerCount();

        // Check if current user is following
        $isFollowing = false;
        if (auth()->check()) {
            $isFollowing = auth()->user()->follows()->where('creator_id', $shop->user_id)->exists();
        }

        // NEVER load CreatorPrivateInfo on this public route
        return Inertia::render('CreatorShop/Show', [
            'shop' => $shop,
            'followerCount' => $followerCount,
            'isFollowing' => $isFollowing,
        ]);
    }
}
