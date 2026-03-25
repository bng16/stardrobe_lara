<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceListingBladeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test marketplace index page displays active products.
     */
    public function test_marketplace_index_displays_active_products(): void
    {
        // Create a creator with shop
        $creator = User::factory()->create(['role' => 'creator']);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'shop_name' => 'Test Shop',
        ]);

        // Create active products
        $activeProduct = Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Active Product',
            'description' => 'This is an active product',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        ProductImage::factory()->create([
            'product_id' => $activeProduct->id,
            'is_primary' => true,
        ]);

        // Create an inactive product (should not appear)
        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Sold Product',
            'status' => AuctionStatus::Sold,
            'auction_start' => now()->subDays(2),
            'auction_end' => now()->subDay(),
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewIs('marketplace.index');
        $response->assertViewHas('products');
        $response->assertSee('Active Product');
        $response->assertSee('Test Shop');
        $response->assertDontSee('Sold Product');
    }

    /**
     * Test marketplace filters by category.
     */
    public function test_marketplace_filters_by_category(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Art Product',
            'category' => 'Art',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Electronics Product',
            'category' => 'Electronics',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.index', ['category' => 'Art']));

        $response->assertStatus(200);
        $response->assertSee('Art Product');
        $response->assertDontSee('Electronics Product');
    }

    /**
     * Test marketplace filters by max price.
     */
    public function test_marketplace_filters_by_max_price(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Cheap Product',
            'reserve_price' => 50.00,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Expensive Product',
            'reserve_price' => 500.00,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.index', ['max_price' => 100]));

        $response->assertStatus(200);
        $response->assertSee('Cheap Product');
        $response->assertDontSee('Expensive Product');
    }

    /**
     * Test marketplace filters by ending soon.
     */
    public function test_marketplace_filters_by_ending_soon(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Ending Soon',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addHours(12),
        ]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Ending Later',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDays(5),
        ]);

        $response = $this->get(route('marketplace.index', ['ending_soon' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Ending Soon');
        $response->assertDontSee('Ending Later');
    }

    /**
     * Test marketplace displays empty state when no products.
     */
    public function test_marketplace_displays_empty_state_when_no_products(): void
    {
        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('No products available at the moment');
    }

    /**
     * Test marketplace pagination works correctly.
     */
    public function test_marketplace_pagination_works(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        // Create 25 active products (more than one page)
        Product::factory()->count(25)->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->total() === 25 && $products->perPage() === 20;
        });
    }

    /**
     * Test marketplace displays product images correctly.
     */
    public function test_marketplace_displays_product_images(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $product = Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Product with Image',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => '/storage/test-image.jpg',
            'is_primary' => true,
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('/storage/test-image.jpg');
    }

    /**
     * Test marketplace displays creator shop information.
     */
    public function test_marketplace_displays_creator_shop_info(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'shop_name' => 'Amazing Creator Shop',
        ]);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Test Product',
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Amazing Creator Shop');
    }
}
