<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentJob;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:buyer']);
    }

    /**
     * Display the payment form for the winner.
     */
    public function show(Product $product): Response
    {
        $winningBid = $product->bids()
            ->where('user_id', auth()->id())
            ->where('id', $product->winning_bid_id)
            ->firstOrFail();

        // Check payment deadline (48 hours from auction close)
        $paymentDeadline = $product->updated_at->addHours(48);
        $isExpired = now()->greaterThan($paymentDeadline);

        return Inertia::render('Payment/Show', [
            'product' => $product->load(['creator.creatorShop', 'images']),
            'bid' => $winningBid,
            'paymentDeadline' => $paymentDeadline,
            'isExpired' => $isExpired,
        ]);
    }

    /**
     * Process the payment.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $winningBid = $product->bids()
            ->where('user_id', auth()->id())
            ->where('id', $product->winning_bid_id)
            ->firstOrFail();

        // Check payment deadline
        $paymentDeadline = $product->updated_at->addHours(48);
        if (now()->greaterThan($paymentDeadline)) {
            return redirect()->back()->withErrors([
                'payment' => 'Payment deadline has expired.',
            ]);
        }

        // Dispatch payment processing job
        ProcessPaymentJob::dispatch($winningBid, $validated['payment_method_id']);

        return redirect()->route('marketplace.index')
            ->with('success', 'Payment is being processed. You will receive a confirmation email shortly.');
    }
}
