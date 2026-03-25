<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AuctionImportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Middleware is handled by routes, not in constructor
    }

    /**
     * Display the admin dashboard with auction statistics.
     */
    public function index(): View
    {
        $this->authorize('admin-dashboard');

        // Get auction statistics
        $statistics = [
            'total_auctions' => Product::count(),
            'active_auctions' => Product::where('status', 'active')->count(),
            'sold_auctions' => Product::where('status', 'sold')->count(),
            'unsold_auctions' => Product::where('status', 'unsold')->count(),
        ];

        // Get all auctions with bid counts and highest bids
        $auctions = Product::withCount('bids')
            ->with(['creator.creatorShop', 'images'])
            ->addSelect([
                'highest_bid' => DB::table('bids')
                    ->selectRaw('MAX(amount)')
                    ->whereColumn('product_id', 'products.id')
            ])
            ->latest()
            ->paginate(20);

        return view('admin.dashboard', compact('statistics', 'auctions'));
    }

    /**
     * Export auction data in JSON format.
     * Allows admins to export single or multiple auctions.
     */
    public function exportAuction(Request $request, ?string $productId = null): JsonResponse
    {
        $this->authorize('admin-dashboard');

        if ($productId) {
            // Export single auction
            $product = Product::with(['creator', 'bids', 'images'])
                ->findOrFail($productId);

            return response()->json([
                'auction' => $product->toExportArray(),
            ]);
        }

        // Export multiple auctions based on filters
        $query = Product::with(['creator', 'bids', 'images']);

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->where('auction_start', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('auction_end', '<=', $request->to_date);
        }

        // Limit to prevent excessive data export
        $limit = min($request->get('limit', 100), 1000);
        $products = $query->latest()->limit($limit)->get();

        return response()->json([
            'auctions' => $products->map(fn($product) => $product->toExportArray())->toArray(),
            'total' => $products->count(),
            'exported_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Import auction data from JSON format.
     * Allows admins to import auction data.
     */
    public function importAuction(Request $request, AuctionImportService $importService): JsonResponse
    {
        $this->authorize('admin-dashboard');

        try {
            // Get JSON from request body
            $json = $request->getContent();

            if (empty($json)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request body is empty',
                ], 400);
            }

            // Import the auction
            $product = $importService->importFromJson($json);

            return response()->json([
                'success' => true,
                'message' => 'Auction imported successfully',
                'auction' => $product->toExportArray(),
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON format',
                'error' => $e->getMessage(),
            ], 400);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
