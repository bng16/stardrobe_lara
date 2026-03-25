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

class MarketplaceProductDetailBladeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product detail page displays correctly.
     */
    public function test_product_detail_page_displays_correctly(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'shop_name' => 'Test Creator Shop',
            'bio' => 'This is a test creator bio',
        ]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Amazing Product',
            'description' => 'This is an amazing product description',
            'category' => 'Art',
            'reserve_price' => 100.00,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertViewIs('marketplace.show');
        $response->assertViewHas('product');
        $response->assertSee('Amazing Product');
        $response->assertSee('This is an amazing product description');
        $response->assertSee('Art');
        $response->assertSee('$100.00');
        $response->assertSee('Test Creator Shop');
    }

    /**
     * Test product detail page displays all images.
     */
    public function test_product_detail_displays_all_images(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => '/storage/primary-image.jpg',
            'is_primary' => true,
            'display_order' => 1,
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => '/storage/secondary-image.jpg',
            'is_primary' => false,
            'display_order' => 2,
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('/storage/primary-image.jpg');
        $response->assertSee('/storage/secondary-image.jpg');
    }

    /**
     * Test product detail page shows placeholder when no images.
     */
    public function test_product_detail_shows_placeholder_when_no_images(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        // Should show SVG placeholder
        $response->assertSee('viewBox="0 0 24 24"', false);
    }

    /**
     * Test product detail page displays auction timing correctly.
     */
    public function test_product_detail_displays_auction_timing(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $auctionStart = now()->subDay();
        $auctionEnd = now()->addDay();

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => $auctionStart,
            'auction_end' => $auctionEnd,
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Auction Starts:');
        $response->assertSee('Auction Ends:');
        $response->assertSee($auctionStart->format('M j, Y'));
        $response->assertSee($auctionEnd->format('M j, Y'));
    }

    /**
     * Test product detail page shows bidding form for authenticated users on active auctions.
     */
    public function test_product_detail_shows_bidding_form_for_authenticated_users(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
            'reserve_price' => 50.00,
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Place Your Bid');
        // Since bids.store route doesn't exist yet, we show "coming soon" message
        $response->assertSee('Bidding functionality coming soon');
    }

    /**
     * Test product detail page shows login prompt for guests.
     */
    public function test_product_detail_shows_login_prompt_for_guests(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Please log in to place a bid');
        $response->assertSee('Log In');
        $response->assertDontSee('Place Your Bid');
    }

    /**
     * Test product detail page does not show bidding form for ended auctions.
     */
    public function test_product_detail_does_not_show_bidding_form_for_ended_auctions(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Ended,
            'auction_start' => now()->subDays(2),
            'auction_end' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('This auction has ended');
        $response->assertDontSee('Place Your Bid');
    }

    /**
     * Test product detail page does not show bidding form for not-yet-started auctions.
     */
    public function test_product_detail_does_not_show_bidding_form_for_future_auctions(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('This auction has not started yet');
        $response->assertDontSee('Place Your Bid');
    }

    /**
     * Test product detail page displays user's existing bid with amount for bid owner.
     */
    public function test_product_detail_displays_user_bid_with_amount_for_owner(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'amount' => 150.00,
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Your Bid');
        $response->assertSee('$150.00');
        $response->assertSee('Current Rank:');
    }

    /**
     * Test product detail page displays user's bid rank correctly.
     */
    public function test_product_detail_displays_user_bid_rank(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $otherUser = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        // Other user has higher bid
        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $otherUser->id,
            'amount' => 200.00,
        ]);

        // Current user has lower bid
        Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'amount' => 150.00,
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Your Bid');
        $response->assertSee('#2'); // Should be rank 2
    }

    /**
     * Test product detail page displays creator information.
     */
    public function test_product_detail_displays_creator_information(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'shop_name' => 'Amazing Creator Shop',
            'bio' => 'We create amazing products for amazing people',
        ]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Creator');
        $response->assertSee('Amazing Creator Shop');
        $response->assertSee('We create amazing products');
        // Visit Shop link only shows if route exists
    }

    /**
     * Test product detail page displays creator profile image.
     */
    public function test_product_detail_displays_creator_profile_image(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'shop_name' => 'Test Shop',
            'profile_image' => '/storage/creator-profile.jpg',
        ]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('/storage/creator-profile.jpg');
    }

    /**
     * Test product detail page shows back to marketplace link.
     */
    public function test_product_detail_shows_back_to_marketplace_link(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Back to Marketplace');
        $response->assertSee(route('marketplace.index'), false);
    }

    /**
     * Test product detail page displays product status badge.
     */
    public function test_product_detail_displays_status_badge(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Test Product',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Active');
        $response->assertSee('bg-green-100', false);
    }

    /**
     * Test product detail page returns 404 for non-existent product.
     */
    public function test_product_detail_returns_404_for_non_existent_product(): void
    {
        $response = $this->get(route('marketplace.show', 'non-existent-id'));

        $response->assertStatus(404);
    }

    /**
     * Test product detail page displays time remaining for active auctions.
     */
    public function test_product_detail_displays_time_remaining_for_active_auctions(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addHours(12),
        ]);

        $response = $this->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Time remaining:');
    }

    /**
     * Test product detail page displays minimum bid requirement.
     */
    public function test_product_detail_displays_minimum_bid_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
            'reserve_price' => 75.50,
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.show', $product->id));

        $response->assertStatus(200);
        // Since bidding form is not yet implemented, we just check the reserve price is displayed
        $response->assertSee('$75.50');
    }
}
