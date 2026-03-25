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

class AdminBidListingBladeTest extends TestCase
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

    public function test_admin_can_view_bid_listing_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.index', $this->product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.bids.index');
        $response->assertViewHas('product');
        $response->assertViewHas('bids');
    }

    public function test_bid_listing_displays_product_information(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.index', $this->product));

        $response->assertSee($this->product->title);
        $response->assertSee('Test Creator Shop');
        $response->assertSee('$100.00');
    }

    public function test_bid_listing_displays_all_bids_with_amounts(): void
    {
        // Create bidders
        $bidder1 = User::factory()->create(['name' => 'Bidder One', 'email' => 'bidder1@example.com']);
        $bidder2 = User::factory()->create(['name' => 'Bidder Two', 'email' => 'bidder2@example.com']);
        $bidder3 = User::factory()->create(['name' => 'Bidder Three', 'email' => 'bidder3@example.com']);

        // Create bids (different amounts to test ranking)
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder1->id,
            'amount' => 150.00,
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder2->id,
            'amount' => 200.00, // Highest bid
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder3->id,
            'amount' => 175.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.index', $this->product));

        // Check all bidders are displayed
        $response->assertSee('Bidder One');
        $response->assertSee('Bidder Two');
        $response->assertSee('Bidder Three');

        // Check all emails are displayed
        $response->assertSee('bidder1@example.com');
        $response->assertSee('bidder2@example.com');
        $response->assertSee('bidder3@example.com');

        // Check all amounts are displayed
        $response->assertSee('$150.00');
        $response->assertSee('$200.00');
        $response->assertSee('$175.00');

        // Check ranking
        $response->assertSee('#1');
        $response->assertSee('#2');
        $response->assertSee('#3');
        $response->assertSee('Winner'); // Highest bid should be marked as winner
    }

    public function test_bid_listing_shows_empty_state_when_no_bids(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.index', $this->product));

        $response->assertSee('No bids have been placed yet.');
    }

    public function test_non_admin_cannot_access_bid_listing(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)
            ->get(route('admin.bids.index', $this->product));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_bid_listing(): void
    {
        $response = $this->get(route('admin.bids.index', $this->product));

        $response->assertRedirect(route('login'));
    }

    public function test_bid_listing_displays_correct_bid_order(): void
    {
        // Create bidders
        $bidder1 = User::factory()->create(['name' => 'Low Bidder']);
        $bidder2 = User::factory()->create(['name' => 'High Bidder']);

        // Create bids
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder1->id,
            'amount' => 100.00,
        ]);
        Bid::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $bidder2->id,
            'amount' => 500.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bids.index', $this->product));

        $content = $response->getContent();

        // High bidder should appear before low bidder in the HTML
        $highBidderPos = strpos($content, 'High Bidder');
        $lowBidderPos = strpos($content, 'Low Bidder');

        $this->assertNotFalse($highBidderPos);
        $this->assertNotFalse($lowBidderPos);
        $this->assertLessThan($lowBidderPos, $highBidderPos, 'High bidder should appear before low bidder');
    }
}
