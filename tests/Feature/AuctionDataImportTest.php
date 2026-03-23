<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\AuctionImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionDataImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $creator;
    private User $buyer;
    private AuctionImportService $importService;

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

        $this->importService = new AuctionImportService();
    }

    public function test_admin_can_import_auction_from_valid_json(): void
    {
        $json = json_encode([
            'title' => 'Imported Auction',
            'description' => 'This is an imported auction',
            'category' => 'Art',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'winning_bid_id' => null,
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'bids' => [],
            'images' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Auction imported successfully',
            ])
            ->assertJsonStructure([
                'auction' => [
                    'id',
                    'title',
                    'description',
                    'reserve_price',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Imported Auction',
            'description' => 'This is an imported auction',
            'creator_id' => $this->creator->id,
        ]);
    }

    public function test_import_validates_json_structure(): void
    {
        $invalidJson = 'not valid json {';

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $invalidJson);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid JSON format',
            ]);
    }

    public function test_import_validates_required_fields(): void
    {
        $json = json_encode([
            'title' => 'Test',
            // Missing required fields
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure(['errors']);
    }

    public function test_import_validates_auction_time_constraint(): void
    {
        $json = json_encode([
            'title' => 'Invalid Auction',
            'description' => 'End time before start time',
            'reserve_price' => 100.00,
            'auction_start' => now()->addDays(7)->toIso8601String(),
            'auction_end' => now()->toIso8601String(), // End before start
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(422)
            ->assertJsonPath('errors.auction_end', function ($errors) {
                return in_array('Auction end time must be after start time', $errors);
            });
    }

    public function test_import_validates_reserve_price_minimum(): void
    {
        $json = json_encode([
            'title' => 'Invalid Price',
            'description' => 'Price too low',
            'reserve_price' => 0.00, // Below minimum
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(422)
            ->assertJsonPath('errors.reserve_price', function ($errors) {
                return in_array('Reserve price must be at least 0.01', $errors);
            });
    }

    public function test_import_validates_auction_status(): void
    {
        $json = json_encode([
            'title' => 'Invalid Status',
            'description' => 'Invalid status value',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'invalid_status', // Invalid status
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(422)
            ->assertJsonPath('errors.status', function ($errors) {
                return in_array('Invalid auction status', $errors);
            });
    }

    public function test_import_handles_missing_creator_gracefully(): void
    {
        $json = json_encode([
            'title' => 'Test Auction',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => 'non-existent-id',
                'name' => 'Non Existent',
                'email' => 'nonexistent@example.com',
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_import_creates_product_images(): void
    {
        $json = json_encode([
            'title' => 'Auction with Images',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'images' => [
                [
                    'image_path' => 'path/to/image1.jpg',
                    'is_primary' => true,
                    'display_order' => 0,
                ],
                [
                    'image_path' => 'path/to/image2.jpg',
                    'is_primary' => false,
                    'display_order' => 1,
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(201);

        $product = Product::where('title', 'Auction with Images')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);
        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'image_path' => 'path/to/image1.jpg',
            'is_primary' => true,
        ]);
    }

    public function test_import_creates_bids(): void
    {
        $json = json_encode([
            'title' => 'Auction with Bids',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'bids' => [
                [
                    'user_id' => $this->buyer->id,
                    'amount' => 150.00,
                    'created_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(201);

        $product = Product::where('title', 'Auction with Bids')->first();
        $this->assertNotNull($product);
        $this->assertCount(1, $product->bids);
        $this->assertDatabaseHas('bids', [
            'product_id' => $product->id,
            'user_id' => $this->buyer->id,
            'amount' => 150.00,
        ]);
    }

    public function test_import_skips_bids_for_nonexistent_users(): void
    {
        $json = json_encode([
            'title' => 'Auction with Invalid Bid',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'bids' => [
                [
                    'user_id' => 'non-existent-user-id',
                    'amount' => 150.00,
                    'created_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(201);

        $product = Product::where('title', 'Auction with Invalid Bid')->first();
        $this->assertNotNull($product);
        $this->assertCount(0, $product->bids); // Bid should be skipped
    }

    public function test_non_admin_cannot_import_auctions(): void
    {
        $json = json_encode([
            'title' => 'Test Auction',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_import_auctions(): void
    {
        $json = json_encode([
            'title' => 'Test Auction',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
        ]);

        $response = $this->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(401);
    }

    public function test_import_handles_empty_request_body(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], '');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Request body is empty',
            ]);
    }

    public function test_import_service_parses_valid_json(): void
    {
        $json = json_encode([
            'title' => 'Service Test',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
        ]);

        $product = $this->importService->importFromJson($json);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Service Test', $product->title);
        $this->assertEquals($this->creator->id, $product->creator_id);
    }

    public function test_import_service_throws_exception_for_invalid_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON format');

        $this->importService->importFromJson('invalid json {');
    }

    public function test_import_service_throws_exception_for_empty_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON string cannot be empty');

        $this->importService->importFromJson('');
    }

    public function test_import_returns_descriptive_error_messages(): void
    {
        $json = json_encode([
            'title' => '', // Empty title
            'description' => 'Test',
            'reserve_price' => -10, // Negative price
            'auction_start' => 'invalid-date',
            'auction_end' => now()->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => 'invalid-email', // Invalid email
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'title',
                    'reserve_price',
                    'auction_start',
                    'creator.email',
                ]
            ]);
    }

    public function test_import_uses_database_transaction(): void
    {
        // Create invalid JSON that will fail during bid creation
        $json = json_encode([
            'title' => 'Transaction Test',
            'description' => 'Test description',
            'reserve_price' => 100.00,
            'auction_start' => now()->toIso8601String(),
            'auction_end' => now()->addDays(7)->toIso8601String(),
            'status' => 'active',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'bids' => [
                [
                    'user_id' => $this->buyer->id,
                    'amount' => 'invalid', // This will cause validation to fail
                    'created_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.auctions.import'), [], [], [], $json);

        $response->assertStatus(422);

        // Product should not be created due to transaction rollback
        $this->assertDatabaseMissing('products', [
            'title' => 'Transaction Test',
        ]);
    }
}
