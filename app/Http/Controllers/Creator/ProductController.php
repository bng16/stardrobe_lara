<?php

namespace App\Http\Controllers\Creator;

use App\Enums\AuctionStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Rules\SecureImageUpload;
use App\Services\FileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:creator', 'creator-onboarded']);
    }

    /**
     * Display a listing of the creator's products.
     */
    public function index(Request $request): Response
    {
        $products = Product::where('creator_id', $request->user()->id)
            ->with('images')
            ->latest()
            ->paginate(20);

        return Inertia::render('Creator/Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): Response
    {
        return Inertia::render('Creator/Products/Create');
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request, FileSecurityService $securityService): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'reserve_price' => 'required|numeric|min:0.01|max:999999.99',
            'auction_start' => 'required|date|after:now',
            'auction_end' => 'required|date|after:auction_start',
            'images' => 'required|array|min:1|max:5',
            'images.*' => [new SecureImageUpload(5120)], // 5MB limit for product images
        ]);

        // Perform security scan on all uploaded images
        foreach ($request->file('images') as $image) {
            $scanResult = $securityService->scanFile($image);
            
            if (!$scanResult['safe']) {
                return back()->withErrors([
                    'images' => 'File upload rejected: ' . implode(', ', $scanResult['issues'])
                ])->withInput();
            }
        }

        $product = Product::create([
            'id' => Str::uuid(),
            'creator_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'] ?? null,
            'reserve_price' => $validated['reserve_price'],
            'auction_start' => $validated['auction_start'],
            'auction_end' => $validated['auction_end'],
            'status' => AuctionStatus::Active,
        ]);

        // Handle image uploads
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('product-images', 's3');
            
            ProductImage::create([
                'id' => Str::uuid(),
                'product_id' => $product->id,
                'image_path' => Storage::disk('s3')->url($path),
                'is_primary' => $index === 0,
                'display_order' => $index,
            ]);
        }

        return redirect()->route('creator.products.index')
            ->with('success', 'Product listed successfully!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): Response
    {
        $this->authorize('manage-creator-shop', $product->creator->creatorShop);

        $product->load('images');

        return Inertia::render('Creator/Products/Edit', [
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('manage-creator-shop', $product->creator->creatorShop);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'reserve_price' => 'required|numeric|min:0.01|max:999999.99',
            'auction_start' => 'required|date',
            'auction_end' => 'required|date|after:auction_start',
        ]);

        $product->update($validated);

        return redirect()->route('creator.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('manage-creator-shop', $product->creator->creatorShop);

        $product->delete();

        return redirect()->route('creator.products.index')
            ->with('success', 'Product deleted successfully!');
    }
}

    /**
     * Display the leaderboard for a closed auction.
     */
    public function leaderboard(Request $request, Product $product): Response
    {
        // Load all bids for the closed auction
        $bids = $product->bids()
            ->with('user')
            ->orderBy('amount', 'desc')
            ->get();

        $isAdmin = $request->user() && $request->user()->role === \App\Enums\UserRole::Admin;

        // Calculate ranks and conditionally show amounts
        $leaderboardData = $bids->map(function ($bid, $index) use ($isAdmin) {
            $data = [
                'rank' => $index + 1,
                'user_name' => $bid->user->name,
                'is_winner' => $index === 0,
            ];

            // Only show amounts to admins
            if ($isAdmin) {
                $data['amount'] = $bid->amount;
            }

            return $data;
        });

        return Inertia::render('Products/Leaderboard', [
            'product' => $product,
            'leaderboard' => $leaderboardData,
            'isAdmin' => $isAdmin,
        ]);
    }
