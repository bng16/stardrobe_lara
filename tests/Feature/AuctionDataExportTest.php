<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionDataExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $creator;
    private User $buyer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);

        // Create creator with shop
        $this->creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $this->creator->id,
            'is_onboarded' => true,
        ]);

        // Create buyer
        $this->buyer = User::factory()->create(['role' => UserRole::Buyer]);

        // Create product with bids and images
        $this->product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => true,
        ]);

        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'amount' => 100.00,
        ]);
    }

    public function test_admin_can_export_single_auction(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.auctions.export.single', $this->product->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'auction' => [
                    'id',
                    'title',
                    'description',
                    'category',
                    'reserve_price',
                    'auction_start',
                    'auction_end',
                    'status',
                    'winning_bid_id',
                    'creator' => ['id', 'name', 'email'],
                    'bids' => [
                        '*' => ['id', 'user_id', 'amount', 'created_at']
                    ],
                    'images' => [
                        '*' => ['id', 'image_path', 'is_primary', 'display_order']
                    ],
                    'created_at',
                    'updated_at',
                ]
            ]);

        $data = $response->json('auction');
        $this->assertEquals($this->product->id, $data['id']);
        $this->assertEquals($this->product->title, $data['title']);
        $this->assertCount(1, $data['bids']);
        $this->assertCount(1, $data['images']);
    }

    public function test_admin_can_export_multiple_auctions(): void
    {
        // Create additional products
        Product::factory()->count(3)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.auctions.export'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'auctions' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'creator',
                        'bids',
                        'images',
                    ]
                ],
                'total',
                'exported_at',
            ]);

        $this->assertGreaterThanOrEqual(4, $response->json('total'));
    }

    public function test_export_filters_by_status(): void
    {
        // Create products with different statuses
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Unsold,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.auctions.export', ['status' => AuctionStatus::Sold->value]));

        $response->assertStatus(200);
        
        $auctions = $response->json('auctions');
        foreach ($auctions as $auction) {
            $this->assertEquals(AuctionStatus::Sold->value, $auction['status']);
        }
    }

    public function test_export_respects_limit_parameter(): void
    {
        // Create many products
        Product::factory()->count(50)->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.auctions.export', ['limit' => 10]));

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(10, $response->json('total'));
    }

    public function test_non_admin_cannot_export_auctions(): void
    {
        $response = $this->actingAs($this->buyer)
            ->getJson(route('admin.auctions.export.single', $this->product->id));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_export_auctions(): void
    {
        $response = $this->getJson(route('admin.auctions.export.single', $this->product->id));

        $response->assertStatus(401);
    }

    public function test_export_includes_all_bid_data(): void
    {
        // Create multiple bids
        Bid::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.auctions.export.single', $this->product->id));

        $response->assertStatus(200);
        
        $bids = $response->json('auction.bids');
        $this->assertCount(4, $bids); // 1 from setUp + 3 new ones
        
        foreach ($bids as $bid) {
            $this->assertArrayHasKey('id', $bid);
            $this->assertArrayHasKey('user_id', $bid);
            $this->assertArrayHasKey('amount', $bid);
            $this->assertArrayHasKey('created_at', $bid);
        }
    }

    public function test_export_data_format_is_valid_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.auctions.export.single', $this->product->id));

        $response->assertStatus(200);
        
        $json = $response->getContent();
        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('auction', $decoded);
    }

    public function test_to_export_array_method_returns_correct_structure(): void
    {
        $this->product->load(['creator', 'bids', 'images']);
        
        $exportArray = $this->product->toExportArray();

        $this->assertIsArray($exportArray);
        $this->assertArrayHasKey('id', $exportArray);
        $this->assertArrayHasKey('title', $exportArray);
        $this->assertArrayHasKey('description', $exportArray);
        $this->assertArrayHasKey('reserve_price', $exportArray);
        $this->assertArrayHasKey('auction_start', $exportArray);
        $this->assertArrayHasKey('auction_end', $exportArray);
        $this->assertArrayHasKey('status', $exportArray);
        $this->assertArrayHasKey('creator', $exportArray);
        $this->assertArrayHasKey('bids', $exportArray);
        $this->assertArrayHasKey('images', $exportArray);
        
        // Verify nested structures
        $this->assertIsArray($exportArray['creator']);
        $this->assertIsArray($exportArray['bids']);
        $this->assertIsArray($exportArray['images']);
    }
}
