<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBidExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $creator;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        // Create creator user with shop
        $this->creator = User::factory()->create([
            'role' => UserRole::Creator,
        ]);
        CreatorShop::factory()->create([
            'user_id' => $this->creator->id,
            'shop_name' => 'Test Creator Shop',
        ]);

        // Create a product
        $this->product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Test Auction Product',
            'status' => AuctionStatus::Active,
            'reserve_price' => 100.00,
        ]);
    }

    public function test_admin_can_export_bids_as_json(): void
    {
        // Create bidders and bids
        $bidder1 = User::factory()->create(['name' => 'Bidder One', 'email' => 'bidder1@example.com']);
        $bidder2 = User::factory()->create(['name' => 'Bidder Two', 'email' => 'bidder2@example.com']);

        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder1->id,
            'amount' => 150.00,
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder2->id,
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.export.json', $this->product));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'product' => ['id', 'title', 'status'],
            'bids' => [
                '*' => [
                    'id',
                    'rank',
                    'product_id',
                    'product_title',
                    'bidder_id',
                    'bidder_name',
                    'bidder_email',
                    'amount',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ],
            'total',
            'filters_applied',
            'exported_at',
        ]);

        $data = $response->json();
        $this->assertEquals(2, $data['total']);
        $this->assertEquals('Test Auction Product', $data['product']['title']);
    }

    public function test_admin_can_export_bids_as_csv(): void
    {
        // Create bidders and bids
        $bidder1 = User::factory()->create(['name' => 'Bidder One', 'email' => 'bidder1@example.com']);
        $bidder2 = User::factory()->create(['name' => 'Bidder Two', 'email' => 'bidder2@example.com']);

        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder1->id,
            'amount' => 150.00,
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder2->id,
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.export.csv', $this->product));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $content = $response->getContent();
        
        // Check CSV header
        $this->assertStringContainsString('Rank,Product Title,Bidder Name,Bidder Email,Amount,Status,Submitted At,Updated At', $content);
        
        // Check CSV data
        $this->assertStringContainsString('Bidder One', $content);
        $this->assertStringContainsString('Bidder Two', $content);
        $this->assertStringContainsString('bidder1@example.com', $content);
        $this->assertStringContainsString('bidder2@example.com', $content);
        $this->assertStringContainsString('150.00', $content);
        $this->assertStringContainsString('200.00', $content);
    }

    public function test_json_export_respects_filters(): void
    {
        // Create bidders and bids
        $bidder1 = User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
        $bidder2 = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);

        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder1->id,
            'amount' => 150.00,
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder2->id,
            'amount' => 200.00,
        ]);

        // Export with filter for minimum amount
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.export.json', ['product' => $this->product, 'min_amount' => 175]));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(1, $data['total']);
        $this->assertEquals('Bob', $data['bids'][0]['bidder_name']);
        $this->assertTrue($data['filters_applied']);
    }

    public function test_csv_export_respects_filters(): void
    {
        // Create bidders and bids
        $bidder1 = User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
        $bidder2 = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);

        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder1->id,
            'amount' => 150.00,
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder2->id,
            'amount' => 200.00,
        ]);

        // Export with filter for bidder name
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.export.csv', ['product' => $this->product, 'bidder' => 'Bob']));

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('Bob', $content);
        $this->assertStringNotContainsString('Alice', $content);
    }

    public function test_non_admin_cannot_export_bids_json(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)
            ->get(route('admin.bids.export.json', $this->product));

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_export_bids_csv(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)
            ->get(route('admin.bids.export.csv', $this->product));

        $response->assertStatus(403);
    }

    public function test_json_export_includes_all_relevant_bid_data(): void
    {
        $bidder = User::factory()->create(['name' => 'Test Bidder', 'email' => 'test@example.com']);
        
        $bid = Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder->id,
            'amount' => 150.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.export.json', $this->product));

        $response->assertStatus(200);
        
        $data = $response->json();
        $exportedBid = $data['bids'][0];
        
        $this->assertEquals($bid->id, $exportedBid['id']);
        $this->assertEquals(1, $exportedBid['rank']);
        $this->assertEquals($this->product->id, $exportedBid['product_id']);
        $this->assertEquals('Test Auction Product', $exportedBid['product_title']);
        $this->assertEquals($bidder->id, $exportedBid['bidder_id']);
        $this->assertEquals('Test Bidder', $exportedBid['bidder_name']);
        $this->assertEquals('test@example.com', $exportedBid['bidder_email']);
        $this->assertEquals(150.00, $exportedBid['amount']);
        $this->assertEquals('active', $exportedBid['status']);
    }

    public function test_csv_export_handles_special_characters_in_names(): void
    {
        $bidder = User::factory()->create([
            'name' => 'Test "Special" Bidder',
            'email' => 'test@example.com'
        ]);
        
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder->id,
            'amount' => 150.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.export.csv', $this->product));

        $response->assertStatus(200);
        
        $content = $response->getContent();
        // CSV should properly escape quotes
        $this->assertStringContainsString('Test ""Special"" Bidder', $content);
    }
}
