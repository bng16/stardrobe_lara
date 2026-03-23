<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class BidController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:buyer', 'throttle:10,1']);
    }

    /**
     * Store or update a bid for a product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('place-bid', $product);

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'gte:' . $product->reserve_price,
            ],
        ]);

        $bid = Bid::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
            ],
            [
                'id' => Str::uuid(),
                'amount' => $validated['amount'],
            ]
        );

        $rank = $bid->getRank();

        return redirect()->back()->with('success', "Your bid is currently ranked #{$rank}");
    }
}
