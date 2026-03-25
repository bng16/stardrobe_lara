<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceControllerBladeConversionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that MarketplaceController index method returns Blade view, not Inertia.
     */
    public function test_marketplace_index_returns_blade_view(): void
    {
        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewIs('marketplace.index');
        
        // Ensure it's not an Inertia response
        $this->assertStringNotContainsString('X-Inertia', $response->headers->get('Vary', ''));
    }

    /**
     * Test that MarketplaceController show method returns Blade view, not Inertia.
     */
    public function test_marketplace_show_returns_blade_view(): void
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
        $response->assertViewIs('marketplace.show');
        
        // Ensure it's not an Inertia response
        $this->assertStringNotContainsString('X-Inertia', $response->headers->get('Vary', ''));
    }

    /**
     * Test that MarketplaceController forYou method returns Blade view, not Inertia.
     */
    public function test_marketplace_for_you_returns_blade_view(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        // User follows the creator
        $user->follows()->attach($creator->id);

        Product::factory()->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.for-you'));

        $response->assertStatus(200);
        $response->assertViewIs('marketplace.for-you');
        
        // Ensure it's not an Inertia response
        $this->assertStringNotContainsString('X-Inertia', $response->headers->get('Vary', ''));
    }

    /**
     * Test that MarketplaceController forYou redirects unauthenticated users to login.
     */
    public function test_marketplace_for_you_redirects_guests_to_login(): void
    {
        $response = $this->get(route('marketplace.for-you'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that MarketplaceController index passes correct data structure to view.
     */
    public function test_marketplace_index_passes_correct_data_to_view(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        Product::factory()->count(3)->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 3 
                && $products->first()->relationLoaded('creator')
                && $products->first()->relationLoaded('images');
        });
    }

    /**
     * Test that MarketplaceController show passes correct data structure to view.
     */
    public function test_marketplace_show_passes_correct_data_to_view(): void
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
        $response->assertViewHas('product', function ($viewProduct) use ($product) {
            return $viewProduct->id === $product->id
                && $viewProduct->relationLoaded('creator')
                && $viewProduct->relationLoaded('images');
        });
        $response->assertViewHas('userBid');
    }

    /**
     * Test that MarketplaceController forYou passes correct data structure to view.
     */
    public function test_marketplace_for_you_passes_correct_data_to_view(): void
    {
        $user = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id]);

        $user->follows()->attach($creator->id);

        Product::factory()->count(2)->create([
            'creator_id' => $creator->id,
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subDay(),
            'auction_end' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.for-you'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 2;
        });
        $response->assertViewHas('hasFollows', true);
    }

    /**
     * Test that MarketplaceController does not use Inertia namespace.
     */
    public function test_marketplace_controller_does_not_use_inertia(): void
    {
        $controllerContent = file_get_contents(app_path('Http/Controllers/MarketplaceController.php'));

        $this->assertStringNotContainsString('use Inertia\Inertia', $controllerContent);
        $this->assertStringNotContainsString('Inertia::render', $controllerContent);
        $this->assertStringNotContainsString('use Inertia\Response', $controllerContent);
    }

    /**
     * Test that MarketplaceController uses proper return types.
     */
    public function test_marketplace_controller_uses_proper_return_types(): void
    {
        $controllerContent = file_get_contents(app_path('Http/Controllers/MarketplaceController.php'));

        // Should use View return type
        $this->assertStringContainsString('use Illuminate\View\View', $controllerContent);
        
        // Should use RedirectResponse for forYou method
        $this->assertStringContainsString('use Illuminate\Http\RedirectResponse', $controllerContent);
    }

    /**
     * Test that all MarketplaceController methods use view() helper.
     */
    public function test_marketplace_controller_methods_use_view_helper(): void
    {
        $controllerContent = file_get_contents(app_path('Http/Controllers/MarketplaceController.php'));

        // Count occurrences of view() calls
        $viewCallCount = substr_count($controllerContent, "view('marketplace.");
        
        // Should have at least 3 view() calls (index, show, forYou)
        $this->assertGreaterThanOrEqual(3, $viewCallCount);
    }
}
