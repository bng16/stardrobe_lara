<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Models\Bid;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuctionImportService
{
    /**
     * Parse and import auction data from JSON.
     *
     * @param string $json The JSON string to parse
     * @return Product The imported product
     * @throws \InvalidArgumentException If JSON is invalid
     * @throws ValidationException If data validation fails
     */
    public function importFromJson(string $json): Product
    {
        // Parse JSON
        $data = $this->parseJson($json);

        // Validate structure and data types
        $this->validateAuctionData($data);

        // Import the auction within a transaction
        return DB::transaction(function () use ($data) {
            return $this->createAuctionFromData($data);
        });
    }

    /**
     * Parse JSON string and handle errors gracefully.
     *
     * @param string $json
     * @return array
     * @throws \InvalidArgumentException
     */
    private function parseJson(string $json): array
    {
        if (empty($json)) {
            throw new \InvalidArgumentException('JSON string cannot be empty');
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                'Invalid JSON format: ' . json_last_error_msg()
            );
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException('JSON must decode to an array');
        }

        return $data;
    }

    /**
     * Validate auction data structure and types.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    private function validateAuctionData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:100',
            'reserve_price' => 'required|numeric|min:0.01',
            'auction_start' => 'required|date',
            'auction_end' => 'required|date|after:auction_start',
            'status' => 'required|string|in:draft,active,ended,sold,unsold',
            'winning_bid_id' => 'nullable|string',
            'creator' => 'required|array',
            'creator.id' => 'required|string',
            'creator.name' => 'required|string',
            'creator.email' => 'required|email',
            'bids' => 'nullable|array',
            'bids.*.user_id' => 'required|string',
            'bids.*.amount' => 'required|numeric|min:0',
            'bids.*.created_at' => 'required|date',
            'images' => 'nullable|array',
            'images.*.image_path' => 'required|string',
            'images.*.is_primary' => 'required|boolean',
            'images.*.display_order' => 'required|integer|min:0',
        ], [
            'title.required' => 'Auction title is required',
            'description.required' => 'Auction description is required',
            'reserve_price.required' => 'Reserve price is required',
            'reserve_price.min' => 'Reserve price must be at least 0.01',
            'auction_start.required' => 'Auction start time is required',
            'auction_start.date' => 'Auction start must be a valid date',
            'auction_end.required' => 'Auction end time is required',
            'auction_end.date' => 'Auction end must be a valid date',
            'auction_end.after' => 'Auction end time must be after start time',
            'status.required' => 'Auction status is required',
            'status.in' => 'Invalid auction status',
            'creator.required' => 'Creator information is required',
            'creator.id.required' => 'Creator ID is required',
            'creator.email.email' => 'Creator email must be valid',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Create auction and related records from validated data.
     *
     * @param array $data
     * @return Product
     */
    private function createAuctionFromData(array $data): Product
    {
        // Find or create creator
        $creator = $this->findOrCreateCreator($data['creator']);

        // Create product
        $product = Product::create([
            'creator_id' => $creator->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'reserve_price' => $data['reserve_price'],
            'auction_start' => $data['auction_start'],
            'auction_end' => $data['auction_end'],
            'status' => AuctionStatus::from($data['status']),
            'winning_bid_id' => $data['winning_bid_id'] ?? null,
        ]);

        // Import images if present
        if (!empty($data['images'])) {
            $this->importImages($product, $data['images']);
        }

        // Import bids if present
        if (!empty($data['bids'])) {
            $this->importBids($product, $data['bids']);
        }

        return $product->fresh(['creator', 'bids', 'images']);
    }

    /**
     * Find existing creator or create a new one.
     *
     * @param array $creatorData
     * @return User
     */
    private function findOrCreateCreator(array $creatorData): User
    {
        // Try to find by email first (most reliable)
        $creator = User::where('email', $creatorData['email'])->first();

        if (!$creator) {
            // If not found, try by ID (in case of UUID match)
            $creator = User::find($creatorData['id']);
        }

        if (!$creator) {
            throw new \InvalidArgumentException(
                "Creator with email '{$creatorData['email']}' not found. Please create the creator account first."
            );
        }

        return $creator;
    }

    /**
     * Import product images.
     *
     * @param Product $product
     * @param array $images
     * @return void
     */
    private function importImages(Product $product, array $images): void
    {
        foreach ($images as $imageData) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $imageData['image_path'],
                'is_primary' => $imageData['is_primary'],
                'display_order' => $imageData['display_order'],
            ]);
        }
    }

    /**
     * Import bids for the product.
     *
     * @param Product $product
     * @param array $bids
     * @return void
     */
    private function importBids(Product $product, array $bids): void
    {
        foreach ($bids as $bidData) {
            // Find the user for this bid
            $user = User::find($bidData['user_id']);

            if (!$user) {
                // Skip bids for non-existent users
                continue;
            }

            Bid::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'amount' => $bidData['amount'],
                'created_at' => $bidData['created_at'],
            ]);
        }
    }
}
