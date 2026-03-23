<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Test suite for authorization gates defined in AppServiceProvider.
 * 
 * Tests Requirements:
 * - 8.1: Gate-based authorization for viewing bid amounts
 * - 8.2: Only bid owner and admins can view specific bid amounts
 * - 8.5: Never include bid amounts in API responses to unauthorized users
 * - 16.5: Gate-based authorization for sensitive data access
 * - 16.6: Never expose sealed bid amounts to unauthorized users
 */
class AuthorizationGateTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // view-bid-amount Gate Tests
    // ========================================

    public function test_bid_owner_can_view_their_own_bid_amount(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
        ]);
        
        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 100.00,
        ]);

        $this->actingAs($buyer);
        
        $this->assertTrue(Gate::allows('view-bid-amount', $bid));
    }

    public function test_admin_can_view_any_bid_amount(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
        ]);
        
        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 100.00,
        ]);

        $this->actingAs($admin);
        
        $this->assertTrue(Gate::allows('view-bid-amount', $bid));
    }

    public function test_other_buyer_cannot_view_bid_amount(): void
    {
        $buyer1 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer2 = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
        ]);
        
        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer1->id,
            'amount' => 100.00,
        ]);

        $this->actingAs($buyer2);
        
        $this->assertFalse(Gate::allows('view-bid-amount', $bid));
    }

    public function test_creator_cannot_view_bid_amount(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
        ]);
        
        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 100.00,
        ]);

        $this->actingAs($creator);
        
        $this->assertFalse(Gate::allows('view-bid-amount', $bid));
    }

    public function test_unauthenticated_user_cannot_view_bid_amount(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
        ]);
        
        $bid = Bid::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'amount' => 100.00,
        ]);

        $this->assertFalse(Gate::allows('view-bid-amount', $bid));
    }

    // ========================================
    // place-bid Gate Tests
    // ========================================

    public function test_buyer_can_place_bid_on_active_auction(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subHour(),
            'auction_end' => now()->addHour(),
        ]);

        $this->actingAs($buyer);
        
        $this->assertTrue(Gate::allows('place-bid', $product));
    }

    public function test_buyer_cannot_place_bid_on_expired_auction(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subHours(2),
            'auction_end' => now()->subHour(),
        ]);

        $this->actingAs($buyer);
        
        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    public function test_buyer_cannot_place_bid_on_draft_auction(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Draft,
            'auction_start' => now()->addHour(),
            'auction_end' => now()->addHours(2),
        ]);

        $this->actingAs($buyer);
        
        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    public function test_buyer_cannot_place_bid_on_ended_auction(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Ended,
            'auction_start' => now()->subHours(2),
            'auction_end' => now()->subHour(),
        ]);

        $this->actingAs($buyer);
        
        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    public function test_buyer_cannot_place_bid_on_sold_auction(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Sold,
            'auction_start' => now()->subHours(2),
            'auction_end' => now()->subHour(),
        ]);

        $this->actingAs($buyer);
        
        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    public function test_admin_cannot_place_bid(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subHour(),
            'auction_end' => now()->addHour(),
        ]);

        $this->actingAs($admin);
        
        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    public function test_creator_cannot_place_bid(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subHour(),
            'auction_end' => now()->addHour(),
        ]);

        $this->actingAs($creator);
        
        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    public function test_unauthenticated_user_cannot_place_bid(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subHour(),
            'auction_end' => now()->addHour(),
        ]);

        $this->assertFalse(Gate::allows('place-bid', $product));
    }

    // ========================================
    // manage-creator-shop Gate Tests
    // ========================================

    public function test_creator_can_manage_their_own_shop(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        $this->actingAs($creator);
        
        $this->assertTrue(Gate::allows('manage-creator-shop', $shop));
    }

    public function test_admin_can_manage_any_creator_shop(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        $this->actingAs($admin);
        
        $this->assertTrue(Gate::allows('manage-creator-shop', $shop));
    }

    public function test_other_creator_cannot_manage_shop(): void
    {
        $creator1 = User::factory()->create(['role' => UserRole::Creator]);
        $creator2 = User::factory()->create(['role' => UserRole::Creator]);
        $shop1 = CreatorShop::factory()->create(['user_id' => $creator1->id]);

        $this->actingAs($creator2);
        
        $this->assertFalse(Gate::allows('manage-creator-shop', $shop1));
    }

    public function test_buyer_cannot_manage_creator_shop(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        $this->actingAs($buyer);
        
        $this->assertFalse(Gate::allows('manage-creator-shop', $shop));
    }

    public function test_unauthenticated_user_cannot_manage_creator_shop(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        $this->assertFalse(Gate::allows('manage-creator-shop', $shop));
    }

    // ========================================
    // admin-dashboard Gate Tests
    // ========================================

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);
        
        $this->assertTrue(Gate::allows('admin-dashboard'));
    }

    public function test_creator_cannot_access_admin_dashboard(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);

        $this->actingAs($creator);
        
        $this->assertFalse(Gate::allows('admin-dashboard'));
    }

    public function test_buyer_cannot_access_admin_dashboard(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $this->actingAs($buyer);
        
        $this->assertFalse(Gate::allows('admin-dashboard'));
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $this->assertFalse(Gate::allows('admin-dashboard'));
    }
}
