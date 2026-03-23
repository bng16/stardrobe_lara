<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    /**
     * Display the open market feed with filters.
     */
    public function index(Request $request): Response
    {
        $query = Product::active()
            ->with(['creator.creatorShop', 'images']);

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('max_price')) {
            $query->where('reserve_price', '<=', $request->max_price);
        }

        if ($request->filled('ending_soon')) {
            $query->where('auction_end', '<=', now()->addHours(24));
        }

        $products = $query->latest('auction_end')->paginate(20);

        return Inertia::render('Marketplace/Index', [
            'products' => $products,
            'filters' => $request->only(['category', 'max_price', 'ending_soon']),
        ]);
    }

    /**
     * Display the personalized "For You" feed from followed creators.
     */
    public function forYou(Request $request): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $followedCreatorIds = $user->follows()->pluck('creator_id');

        $products = Product::active()
            ->whereIn('creator_id', $followedCreatorIds)
            ->with(['creator.creatorShop', 'images'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Marketplace/ForYou', [
            'products' => $products,
            'hasFollows' => $followedCreatorIds->isNotEmpty(),
        ]);
    }

    /**
     * Display a specific product detail page.
     */
    public function show(Request $request, Product $product): Response
    {
        $product->load(['creator.creatorShop', 'images']);

        $userBid = null;
        $bidData = null;

        if ($request->user()) {
            $userBid = $product->bids()
                ->where('user_id', $request->user()->id)
                ->first();

            if ($userBid) {
                // Use gate to conditionally include bid amount
                if ($request->user()->can('view-bid-amount', $userBid)) {
                    $bidData = [
                        'amount' => $userBid->amount,
                        'rank' => $userBid->getRank(),
                    ];
                } else {
                    $bidData = [
                        'rank' => $userBid->getRank(),
                    ];
                }
            }
        }

        return Inertia::render('Marketplace/Show', [
            'product' => $product,
            'userBid' => $bidData,
        ]);
    }
}
