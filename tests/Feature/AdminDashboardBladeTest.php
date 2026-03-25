<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardBladeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_can_view_dashboard_blade_template(): void
    {
        // Create some test data
        Product::factory()->count(5)->create(['status' => 'active']);
        Product::factory()->count(3)->create(['status' => 'sold']);
        Product::factory()->count(2)->create(['status' => 'unsold']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas(['statistics', 'auctions']);
        
        // Check that the statistics are passed correctly
        $statistics = $response->viewData('statistics');
        $this->assertEquals(10, $statistics['total_auctions']);
        $this->assertEquals(5, $statistics['active_auctions']);
        $this->assertEquals(3, $statistics['sold_auctions']);
        $this->assertEquals(2, $statistics['unsold_auctions']);
    }

    public function test_dashboard_displays_auction_data(): void
    {
        // Create a product with creator and images
        $product = Product::factory()->create(['title' => 'Test Auction']);
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Test Auction');
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Export All Auctions (JSON)');
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        // Should not return 200 (success)
        $this->assertNotEquals(200, $response->getStatusCode());
    }
}