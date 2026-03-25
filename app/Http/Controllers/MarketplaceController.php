<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    /**
     * Display the open market feed with filters.
     */
    public function index(Request $request): View
    {
        $query = Product::active()
            ->with(['creator.creatorShop', 'images']);

        // Search by title and description
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('reserve_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('reserve_price', '<=', $request->max_price);
        }

        // Filter by auction status
        if ($request->filled('auction_status')) {
            $status = $request->auction_status;
            
            if ($status === 'active') {
                // Already filtered by active() scope
            } elseif ($status === 'ending_soon') {
                $query->where('auction_end', '<=', now()->addHours(24));
            } elseif ($status === 'closed') {
                // Override active scope to show closed auctions
                $query->getQuery()->wheres = [];
                $query->whereIn('status', ['sold', 'unsold', 'ended'])
                    ->orWhere('auction_end', '<=', now());
            }
        } elseif ($request->filled('ending_soon')) {
            // Maintain backward compatibility with existing ending_soon filter
            $query->where('auction_end', '<=', now()->addHours(24));
        }

        // Filter by creator name
        if ($request->filled('creator_name')) {
            $creatorName = $request->creator_name;
            $query->whereHas('creator.creatorShop', function ($q) use ($creatorName) {
                $q->where('shop_name', 'like', '%' . $creatorName . '%');
            });
        }

        $products = $query->latest('auction_end')->paginate(20)->withQueryString();

        // Determine if any filters are active
        $hasActiveFilters = $request->hasAny([
            'search', 'category', 'min_price', 'max_price', 
            'auction_status', 'ending_soon', 'creator_name'
        ]);

        return view('marketplace.index', compact('products', 'hasActiveFilters'));
    }

    /**
     * Display the personalized "For You" feed from followed creators.
     */
    public function forYou(Request $request): View|RedirectResponse
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

        return view('marketplace.for-you', [
            'products' => $products,
            'hasFollows' => $followedCreatorIds->isNotEmpty(),
        ]);
    }

    /**
     * Display a specific product detail page.
     */
    public function show(Request $request, Product $product): View
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

        return view('marketplace.show', [
            'product' => $product,
            'userBid' => $bidData,
        ]);
    }
}
