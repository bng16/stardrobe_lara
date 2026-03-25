<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorProductListingBladeTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;
    private CreatorShop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a creator user with shop
        $this->creator = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $this->shop = CreatorShop::factory()->create([
            'user_id' => $this->creator->id,
        ]);
    }

    public function test_creator_can_view_products_index_page(): void
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('creator.products.index');
        $response->assertViewHas('products');
    }

    public function test_products_index_displays_creator_products(): void
    {
        // Create products for this creator
        $product1 = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Test Product 1',
            'status' => AuctionStatus::Active,
        ]);

        $product2 = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Test Product 2',
            'status' => AuctionStatus::Sold,
        ]);

        // Create a product for another creator (should not appear)
        $otherCreator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $otherCreator->id]);
        Product::factory()->create([
            'creator_id' => $otherCreator->id,
            'title' => 'Other Creator Product',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Product 1');
        $response->assertSee('Test Product 2');
        $response->assertDontSee('Other Creator Product');
    }

    public function test_products_index_displays_product_details(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Detailed Product',
            'description' => 'This is a detailed description',
            'category' => 'Art',
            'reserve_price' => 100.50,
            'status' => AuctionStatus::Active,
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Detailed Product');
        $response->assertSee('Art');
        $response->assertSee('100.50');
        $response->assertSee('Active');
    }

    public function test_products_index_search_filters_by_title(): void
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Vintage Camera',
        ]);

        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Modern Painting',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index', ['search' => 'Camera']));

        $response->assertStatus(200);
        $response->assertSee('Vintage Camera');
        $response->assertDontSee('Modern Painting');
    }

    public function test_products_index_filters_by_status(): void
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Active Product',
            'status' => AuctionStatus::Active,
        ]);

        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Sold Product',
            'status' => AuctionStatus::Sold,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertSee('Active Product');
        $response->assertDontSee('Sold Product');
    }

    public function test_products_index_filters_by_category(): void
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Art Piece',
            'category' => 'Art',
        ]);

        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Tech Gadget',
            'category' => 'Technology',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index', ['category' => 'Art']));

        $response->assertStatus(200);
        $response->assertSee('Art Piece');
        $response->assertDontSee('Tech Gadget');
    }

    public function test_products_index_displays_pagination(): void
    {
        // Create 25 products (more than the 20 per page limit)
        Product::factory()->count(25)->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->total() === 25 && $products->perPage() === 20;
        });
    }

    public function test_products_index_displays_bids_count(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Product with Bids',
        ]);

        // The withCount('bids') should be included in the query
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->first()->bids_count !== null;
        });
    }

    public function test_products_index_includes_action_buttons(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Product with Actions',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertSee(route('creator.products.edit', $product));
        $response->assertSee(route('creator.products.destroy', $product));
    }

    public function test_products_index_displays_empty_state_when_no_products(): void
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertSee('No products found');
        $response->assertSee('Create Your First Product');
    }

    public function test_products_index_displays_empty_state_with_filters(): void
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Existing Product',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index', ['search' => 'NonExistent']));

        $response->assertStatus(200);
        $response->assertSee('No products match your filters');
    }

    public function test_products_index_includes_csrf_protection(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        $response->assertSee('csrf_token');
    }

    public function test_products_index_escapes_user_content(): void
    {
        Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => '<script>alert("XSS")</script>',
            'description' => '<img src=x onerror=alert("XSS")>',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index'));

        $response->assertStatus(200);
        // Blade should escape the content
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }

    public function test_non_creator_cannot_access_products_index(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($buyer)
            ->get(route('creator.products.index'));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_products_index(): void
    {
        $response = $this->get(route('creator.products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_products_index_maintains_query_string_in_pagination(): void
    {
        Product::factory()->count(25)->create([
            'creator_id' => $this->creator->id,
            'status' => AuctionStatus::Active,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.index', ['status' => 'active', 'page' => 2]));

        $response->assertStatus(200);
        $response->assertSee('status=active');
    }
}
