<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class BidController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display all bids for a specific auction.
     */
    public function index(Product $product): Response
    {
        $this->authorize('admin-dashboard');

        // Load all bids with amounts (admin has full visibility)
        $bids = $product->bids()
            ->with('user')
            ->orderBy('amount', 'desc')
            ->get()
            ->map(function ($bid, $index) {
                return [
                    'id' => $bid->id,
                    'user_name' => $bid->user->name,
                    'user_email' => $bid->user->email,
                    'amount' => $bid->amount,
                    'rank' => $index + 1,
                    'created_at' => $bid->created_at,
                    'updated_at' => $bid->updated_at,
                ];
            });

        return Inertia::render('Admin/Auctions/Show', [
            'product' => $product->load(['creator.creatorShop', 'images']),
            'bids' => $bids,
        ]);
    }
}
