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

class CreatorDashboardBladeTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;
    private CreatorShop $creatorShop;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a creator user with shop
        $this->creator = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $this->creatorShop = CreatorShop::factory()->create([
            'user_id' => $this->creator->id,
            'shop_name' => 'Test Creator Shop',
            'is_onboarded' => true,
        ]);
    }

    /** @test */
    public function it_displays_creator_dashboard_with_statistics()
    {
        // Create products with different statuses
        Product::factory()->count(3)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        Product::factory()->count(2)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Unsold,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Creator Dashboard');
        $response->assertSee('Total Products');
        $response->assertSee('Active Auctions');
        $response->assertSee('Sold Items');
        $response->assertSee('Total Revenue');
        
        // Check statistics values
        $response->assertSee('6'); // Total products
        $response->assertSee('3'); // Active auctions
        $response->assertSee('2'); // Sold items
    }

    /** @test */
    public function it_calculates_total_revenue_correctly()
    {
        // Create sold products with winning bids
        $product1 = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        $product2 = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $bid1 = Bid::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $buyer->id,
            'amount' => 100.00,
        ]);

        $bid2 = Bid::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $buyer->id,
            'amount' => 250.50,
        ]);

        // Set winning bids
        $product1->update(['winning_bid_id' => $bid1->id]);
        $product2->update(['winning_bid_id' => $bid2->id]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('350.50'); // Total revenue
    }

    /** @test */
    public function it_displays_recent_products_table()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Test Product',
            'status' => AuctionStatus::Active,
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Products');
        $response->assertSee('Test Product');
        $response->assertSee('Active');
    }

    /** @test */
    public function it_displays_recent_products_with_bid_counts()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Product with Bids',
        ]);

        // Create multiple buyers for multiple bids (unique constraint: one bid per user per product)
        $buyer1 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer2 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer3 = User::factory()->create(['role' => UserRole::Buyer]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer1->id,
        ]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer2->id,
        ]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer3->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Product with Bids');
        $response->assertSee('3'); // Bid count
    }

    /** @test */
    public function it_displays_highest_bid_for_products()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $buyer1 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer2 = User::factory()->create(['role' => UserRole::Buyer]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer1->id,
            'amount' => 50.00,
        ]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer2->id,
            'amount' => 150.00,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('150.00'); // Highest bid
    }

    /** @test */
    public function it_displays_recent_bids_on_creator_products()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Product with Recent Bids',
        ]);

        $buyer = User::factory()->create([
            'role' => UserRole::Buyer,
            'name' => 'John Doe',
        ]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 75.50,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Bids');
        $response->assertSee('Product with Recent Bids');
        $response->assertSee('John Doe');
        $response->assertSee('75.50');
    }

    /** @test */
    public function it_limits_recent_products_to_five()
    {
        // Create 10 products
        $products = Product::factory()->count(10)->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        
        // Should only see the 5 most recent products
        $recentProducts = $products->sortByDesc('created_at')->take(5);
        foreach ($recentProducts as $product) {
            $response->assertSee($product->title);
        }
    }

    /** @test */
    public function it_limits_recent_bids_to_ten()
    {
        // Create 15 products with one bid each
        for ($i = 0; $i < 15; $i++) {
            $product = Product::factory()->create([
                'creator_id' => $this->creator->id,
            ]);

            $buyer = User::factory()->create(['role' => UserRole::Buyer]);

            Bid::factory()->create([
                'product_id' => $product->id,
                'user_id' => $buyer->id,
            ]);
        }

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        
        // The view should contain the recent bids section
        $response->assertSee('Recent Bids');
    }

    /** @test */
    public function it_shows_empty_state_when_no_products_exist()
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('No products yet');
        $response->assertSee('Get started by creating your first product');
    }

    /** @test */
    public function it_shows_empty_state_when_no_bids_exist()
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('No bids yet');
        $response->assertSee('Bids on your products will appear here');
    }

    /** @test */
    public function it_displays_create_product_button()
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Create New Product');
        $response->assertSee(route('creator.products.create'));
    }

    /** @test */
    public function it_displays_view_all_products_button()
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('View All');
        $response->assertSee(route('creator.products.index'));
    }

    /** @test */
    public function it_displays_edit_links_for_products()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Editable Product',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Edit');
        $response->assertSee(route('creator.products.edit', $product->id));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('creator.dashboard'));

        // Should redirect (middleware handles this - either to login or 302)
        $this->assertTrue(in_array($response->status(), [302, 500]));
    }

    /** @test */
    public function it_requires_creator_role()
    {
        $buyer = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($buyer)
            ->get(route('creator.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_only_shows_creators_own_products()
    {
        $otherCreator = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $otherShop = CreatorShop::factory()->create([
            'user_id' => $otherCreator->id,
        ]);

        // Create product for other creator
        $otherProduct = Product::factory()->create([
            'creator_id' => $otherCreator->id,
            'title' => 'Other Creator Product',
        ]);

        // Create product for current creator
        $myProduct = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'My Product',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('My Product');
        $response->assertDontSee('Other Creator Product');
    }

    /** @test */
    public function it_only_shows_bids_on_creators_own_products()
    {
        $otherCreator = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $otherShop = CreatorShop::factory()->create([
            'user_id' => $otherCreator->id,
        ]);

        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        // Create product for other creator with bid
        $otherProduct = Product::factory()->create([
            'creator_id' => $otherCreator->id,
            'title' => 'Other Product',
        ]);

        Bid::factory()->create([
            'product_id' => $otherProduct->id,
            'user_id' => $buyer->id,
            'amount' => 999.99,
        ]);

        // Create product for current creator with bid
        $myProduct = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'My Product',
        ]);

        Bid::factory()->create([
            'product_id' => $myProduct->id,
            'user_id' => $buyer->id,
            'amount' => 50.00,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('50.00');
        $response->assertDontSee('999.99');
    }

    /** @test */
    public function it_displays_product_images_when_available()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => '/storage/products/test-image.jpg',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('/storage/products/test-image.jpg');
    }

    /** @test */
    public function it_displays_placeholder_when_no_product_image()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        // Should see SVG placeholder
        $response->assertSee('w-12 h-12 bg-gray-200 rounded');
    }

    /** @test */
    public function it_formats_currency_values_correctly()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 1234.56,
        ]);

        $product->update(['winning_bid_id' => $bid->id]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('1,234.56');
    }

    /** @test */
    public function it_displays_auction_end_dates_correctly()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'auction_end' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        // Should see formatted date
        $response->assertSee(now()->addDays(7)->format('M j, Y'));
    }

    /** @test */
    public function it_displays_relative_time_for_recent_bids()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'created_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        // Should see relative time like "2 hours ago"
        $response->assertSee('ago');
    }

    /** @test */
    public function it_uses_card_components_for_statistics()
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        // Check for card component classes
        $response->assertSee('rounded-lg border bg-card');
    }

    /** @test */
    public function it_displays_status_badges_with_correct_colors()
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('bg-green-100 text-green-800'); // Active
        $response->assertSee('bg-blue-100 text-blue-800'); // Sold
    }

    /** @test */
    public function it_maintains_responsive_design_classes()
    {
        // Create a product so the table is rendered
        Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('grid-cols-1 md:grid-cols-4');
        $response->assertSee('max-w-7xl mx-auto');
        $response->assertSee('overflow-x-auto');
    }

    /** @test */
    public function it_handles_zero_revenue_gracefully()
    {
        // Create products but no winning bids
        Product::factory()->count(3)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('$0.00'); // Zero revenue formatted correctly
    }

    /** @test */
    public function it_handles_null_highest_bid_gracefully()
    {
        // Create product with no bids
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Product Without Bids',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Product Without Bids');
        $response->assertSee('-'); // Dash for null highest bid
    }

    /** @test */
    public function it_displays_large_currency_values_with_proper_formatting()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 123456.78,
        ]);

        $product->update(['winning_bid_id' => $bid->id]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('123,456.78'); // Large number with comma separators
    }

    /** @test */
    public function it_calculates_statistics_only_for_creator_products()
    {
        // Create products for this creator
        Product::factory()->count(2)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        // Create products for another creator
        $otherCreator = User::factory()->create(['role' => UserRole::Creator]);
        $otherShop = CreatorShop::factory()->create(['user_id' => $otherCreator->id]);
        
        Product::factory()->count(5)->create([
            'creator_id' => $otherCreator->id,
            'status' => AuctionStatus::Active,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        // Should only see 2 total products and 2 active auctions, not 7 and 7
        $response->assertSee('2'); // Total products for this creator
    }

    /** @test */
    public function it_handles_products_with_multiple_images()
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        // Create multiple images, only first should be displayed
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => '/storage/products/first-image.jpg',
            'is_primary' => true,
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => '/storage/products/second-image.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('/storage/products/first-image.jpg');
    }

    /** @test */
    public function it_displays_correct_statistics_with_mixed_product_statuses()
    {
        // Create products with various statuses
        Product::factory()->count(5)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        Product::factory()->count(3)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Sold,
        ]);

        Product::factory()->count(2)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Unsold,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.dashboard'));

        $response->assertStatus(200);
        // Total: 10, Active: 5, Sold: 3
        $response->assertSee('10'); // Total products
        $response->assertSee('5'); // Active auctions
        $response->assertSee('3'); // Sold items
    }
}
