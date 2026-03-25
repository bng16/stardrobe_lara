<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the creator dashboard with statistics and recent activity.
     */
    public function index(): View
    {
        $creator = Auth::user();

        // Get creator statistics
        $statistics = [
            'total_products' => $creator->creatorShop->products()->count(),
            'active_auctions' => $creator->creatorShop->products()->where('status', 'active')->count(),
            'sold_items' => $creator->creatorShop->products()->where('status', 'sold')->count(),
            'total_revenue' => $creator->creatorShop->products()
                ->where('status', 'sold')
                ->join('bids', 'products.winning_bid_id', '=', 'bids.id')
                ->sum('bids.amount'),
        ];

        // Get recent products with bid counts and highest bids
        $recentProducts = $creator->creatorShop->products()
            ->withCount('bids')
            ->with(['images'])
            ->addSelect([
                'highest_bid' => DB::table('bids')
                    ->selectRaw('MAX(amount)')
                    ->whereColumn('product_id', 'products.id')
            ])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent bids on creator's products
        $recentBids = DB::table('bids')
            ->join('products', 'bids.product_id', '=', 'products.id')
            ->join('users', 'bids.user_id', '=', 'users.id')
            ->where('products.creator_id', $creator->id)
            ->select(
                'bids.id',
                'bids.amount',
                'bids.created_at',
                'products.id as product_id',
                'products.title as product_title',
                'users.name as bidder_name'
            )
            ->orderBy('bids.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('creator.dashboard', compact('statistics', 'recentProducts', 'recentBids'));
    }
}
