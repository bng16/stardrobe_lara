<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BidController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display all bids for a specific auction.
     */
    public function index(Product $product): View
    {
        $this->authorize('admin-dashboard');

        // Get filter and sort parameters
        $filters = request()->only(['bidder', 'date_from', 'date_to', 'min_amount', 'max_amount']);
        $sortBy = request()->input('sort_by', 'amount');
        $sortOrder = request()->input('sort_order', 'desc');

        // Build query with filters
        $query = $product->bids()->with('user');

        // Filter by bidder name or email
        if (!empty($filters['bidder'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['bidder'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['bidder'] . '%');
            });
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filter by amount range
        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }
        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        // Apply sorting
        if ($sortBy === 'bidder') {
            $query->join('users', 'bids.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('bids.*');
        } elseif ($sortBy === 'date') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            // Default: sort by amount
            $query->orderBy('amount', $sortOrder);
        }

        // Get all bids and transform to array
        $bids = $query->get()
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

        // Load product with relationships
        $product->load(['creator.creatorShop', 'images']);

        return view('admin.bids.index', [
            'product' => $product,
            'bids' => $bids,
            'filters' => $filters,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    /**
     * Export bids for a specific auction in JSON format.
     * Respects current filters when exporting.
     */
    public function exportJson(Product $product): JsonResponse
    {
        $this->authorize('admin-dashboard');

        // Get filter and sort parameters (same as index method)
        $filters = request()->only(['bidder', 'date_from', 'date_to', 'min_amount', 'max_amount']);
        $sortBy = request()->input('sort_by', 'amount');
        $sortOrder = request()->input('sort_order', 'desc');

        // Build query with filters (same logic as index method)
        $query = $product->bids()->with('user');

        // Apply filters
        if (!empty($filters['bidder'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['bidder'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['bidder'] . '%');
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }
        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        // Apply sorting
        if ($sortBy === 'bidder') {
            $query->join('users', 'bids.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('bids.*');
        } elseif ($sortBy === 'date') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            $query->orderBy('amount', $sortOrder);
        }

        // Get all bids and transform to export format
        $bids = $query->get()
            ->map(function ($bid, $index) {
                return [
                    'id' => $bid->id,
                    'rank' => $index + 1,
                    'product_id' => $bid->product_id,
                    'product_title' => $bid->product->title,
                    'bidder_id' => $bid->user_id,
                    'bidder_name' => $bid->user->name,
                    'bidder_email' => $bid->user->email,
                    'amount' => (float) $bid->amount,
                    'status' => $bid->product->status->value,
                    'created_at' => $bid->created_at->toIso8601String(),
                    'updated_at' => $bid->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'status' => $product->status->value,
            ],
            'bids' => $bids->toArray(),
            'total' => $bids->count(),
            'filters_applied' => !empty(array_filter($filters)),
            'exported_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Export bids for a specific auction in CSV format.
     * Respects current filters when exporting.
     */
    public function exportCsv(Product $product): Response
    {
        $this->authorize('admin-dashboard');

        // Get filter and sort parameters (same as index method)
        $filters = request()->only(['bidder', 'date_from', 'date_to', 'min_amount', 'max_amount']);
        $sortBy = request()->input('sort_by', 'amount');
        $sortOrder = request()->input('sort_order', 'desc');

        // Build query with filters (same logic as index method)
        $query = $product->bids()->with('user');

        // Apply filters
        if (!empty($filters['bidder'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['bidder'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['bidder'] . '%');
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }
        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        // Apply sorting
        if ($sortBy === 'bidder') {
            $query->join('users', 'bids.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('bids.*');
        } elseif ($sortBy === 'date') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            $query->orderBy('amount', $sortOrder);
        }

        // Get all bids
        $bids = $query->get();

        // Generate CSV content
        $csv = "Rank,Product Title,Bidder Name,Bidder Email,Amount,Status,Submitted At,Updated At\n";
        
        foreach ($bids as $index => $bid) {
            $rank = $index + 1;
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",%.2f,\"%s\",\"%s\",\"%s\"\n",
                $rank,
                str_replace('"', '""', $product->title),
                str_replace('"', '""', $bid->user->name),
                str_replace('"', '""', $bid->user->email),
                (float) $bid->amount,
                $product->status->value,
                $bid->created_at->format('Y-m-d H:i:s'),
                $bid->updated_at->format('Y-m-d H:i:s')
            );
        }

        // Generate filename
        $filename = sprintf(
            'bids-%s-%s.csv',
            $product->id,
            now()->format('Y-m-d-His')
        );

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
