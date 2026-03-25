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

class ProductController extends Controller
{
    /**
     * Display a listing of the creator's products.
     */
    public function index(Request $request)
    {
        $query = Product::where('creator_id', $request->user()->id)
            ->withCount('bids')
            ->with('images');

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->category . '%');
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate(20)->withQueryString();

        return view('creator.products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('creator.products.create');
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
    public function edit(Product $product)
    {
        $this->authorize('manage-creator-shop', $product->creator->creatorShop);

        $product->load('images');

        return view('creator.products.edit', [
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
