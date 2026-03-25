<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatorProductFormsBladeTest extends TestCase
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

        // Fake storage for file uploads
        Storage::fake('s3');
    }

    // ========== CREATE FORM TESTS ==========

    public function test_creator_can_view_create_form(): void
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('creator.products.create');
    }

    public function test_create_form_displays_all_required_fields(): void
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.create'));

        $response->assertStatus(200);
        $response->assertSee('Product Title');
        $response->assertSee('Description');
        $response->assertSee('Category');
        $response->assertSee('Reserve Price');
        $response->assertSee('Auction Start');
        $response->assertSee('Auction End');
        $response->assertSee('Product Images');
    }

    public function test_create_form_includes_csrf_protection(): void
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.create'));

        $response->assertStatus(200);
        $response->assertSee('csrf');
    }

    public function test_create_form_has_correct_action_and_method(): void
    {
        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.create'));

        $response->assertStatus(200);
        $response->assertSee(route('creator.products.store'));
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_creator_can_submit_valid_product_creation_form(): void
    {
        $image = UploadedFile::fake()->image('product.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'New Test Product',
                'description' => 'This is a detailed description of the product.',
                'category' => 'Art',
                'reserve_price' => 150.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertRedirect(route('creator.products.index'));
        $response->assertSessionHas('success', 'Product listed successfully!');

        $this->assertDatabaseHas('products', [
            'creator_id' => $this->creator->id,
            'title' => 'New Test Product',
            'description' => 'This is a detailed description of the product.',
            'category' => 'Art',
            'reserve_price' => 150.00,
            'status' => AuctionStatus::Active->value,
        ]);
    }

    public function test_product_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'description',
            'reserve_price',
            'auction_start',
            'auction_end',
            'images',
        ]);
    }

    public function test_product_creation_validates_title_max_length(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => str_repeat('a', 256),
                'description' => 'Valid description',
                'reserve_price' => 100.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_product_creation_validates_description_max_length(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => str_repeat('a', 5001),
                'reserve_price' => 100.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_product_creation_validates_reserve_price_minimum(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => 'Valid description',
                'reserve_price' => 0.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertSessionHasErrors('reserve_price');
    }

    public function test_product_creation_validates_reserve_price_maximum(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => 'Valid description',
                'reserve_price' => 1000000.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertSessionHasErrors('reserve_price');
    }

    public function test_product_creation_validates_auction_start_is_future(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => 'Valid description',
                'reserve_price' => 100.00,
                'auction_start' => now()->subDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertSessionHasErrors('auction_start');
    }

    public function test_product_creation_validates_auction_end_after_start(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => 'Valid description',
                'reserve_price' => 100.00,
                'auction_start' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDay()->format('Y-m-d H:i:s'),
                'images' => [$image],
            ]);

        $response->assertSessionHasErrors('auction_end');
    }

    public function test_product_creation_validates_images_required(): void
    {
        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => 'Valid description',
                'reserve_price' => 100.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => [],
            ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_product_creation_validates_maximum_images(): void
    {
        $images = [];
        for ($i = 0; $i < 6; $i++) {
            $images[] = UploadedFile::fake()->image("product{$i}.jpg");
        }

        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Valid Title',
                'description' => 'Valid description',
                'reserve_price' => 100.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => $images,
            ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_product_creation_preserves_input_on_validation_error(): void
    {
        $response = $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Test Title',
                'description' => 'Test Description',
            ]);

        $response->assertSessionHasErrors();
        $response->assertSessionHasInput('title', 'Test Title');
        $response->assertSessionHasInput('description', 'Test Description');
    }

    public function test_product_creation_displays_validation_errors(): void
    {
        $this->actingAs($this->creator)
            ->post(route('creator.products.store'), []);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.create'));

        $response->assertStatus(200);
        $response->assertSee('The title field is required');
    }

    public function test_product_creation_creates_product_images(): void
    {
        $images = [
            UploadedFile::fake()->image('product1.jpg'),
            UploadedFile::fake()->image('product2.jpg'),
        ];

        $this->actingAs($this->creator)
            ->post(route('creator.products.store'), [
                'title' => 'Product with Images',
                'description' => 'Description',
                'reserve_price' => 100.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'images' => $images,
            ]);

        $product = Product::where('title', 'Product with Images')->first();
        $this->assertNotNull($product);
        $this->assertEquals(2, $product->images()->count());
        
        // Check that first image is marked as primary
        $primaryImage = $product->images()->where('is_primary', true)->first();
        $this->assertNotNull($primaryImage);
        $this->assertEquals(0, $primaryImage->display_order);
    }

    // ========== EDIT FORM TESTS ==========

    public function test_creator_can_view_edit_form(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('creator.products.edit');
        $response->assertViewHas('product', $product);
    }

    public function test_edit_form_displays_existing_product_data(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Existing Product',
            'description' => 'Existing Description',
            'category' => 'Art',
            'reserve_price' => 250.00,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('Existing Product');
        $response->assertSee('Existing Description');
        $response->assertSee('Art');
        $response->assertSee('250');
    }

    public function test_edit_form_displays_existing_images(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
            'image_path' => 'https://example.com/image1.jpg',
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
            'image_path' => 'https://example.com/image2.jpg',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('Current Images');
        $response->assertSee('https://example.com/image1.jpg');
        $response->assertSee('https://example.com/image2.jpg');
        $response->assertSee('Primary');
    }

    public function test_edit_form_includes_csrf_protection(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('csrf');
    }

    public function test_edit_form_uses_put_method(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('_method');
        $response->assertSee('PUT');
    }

    public function test_creator_can_update_product(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => 'Old Title',
            'description' => 'Old Description',
        ]);

        $response = $this->actingAs($this->creator)
            ->put(route('creator.products.update', $product), [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'category' => 'Updated Category',
                'reserve_price' => 200.00,
                'auction_start' => $product->auction_start->format('Y-m-d H:i:s'),
                'auction_end' => $product->auction_end->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('creator.products.index'));
        $response->assertSessionHas('success', 'Product updated successfully!');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'category' => 'Updated Category',
            'reserve_price' => 200.00,
        ]);
    }

    public function test_product_update_validates_required_fields(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->put(route('creator.products.update', $product), []);

        $response->assertSessionHasErrors([
            'title',
            'description',
            'reserve_price',
            'auction_start',
            'auction_end',
        ]);
    }

    public function test_product_update_preserves_input_on_validation_error(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->put(route('creator.products.update', $product), [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ]);

        $response->assertSessionHasErrors();
        $response->assertSessionHasInput('title', 'Updated Title');
        $response->assertSessionHasInput('description', 'Updated Description');
    }

    public function test_creator_cannot_edit_other_creators_product(): void
    {
        $otherCreator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $otherCreator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $otherCreator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(403);
    }

    public function test_creator_cannot_update_other_creators_product(): void
    {
        $otherCreator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $otherCreator->id]);
        
        $product = Product::factory()->create([
            'creator_id' => $otherCreator->id,
        ]);

        $response = $this->actingAs($this->creator)
            ->put(route('creator.products.update', $product), [
                'title' => 'Hacked Title',
                'description' => 'Hacked Description',
                'reserve_price' => 100.00,
                'auction_start' => now()->addDay()->format('Y-m-d H:i:s'),
                'auction_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(403);
    }

    public function test_non_creator_cannot_access_create_form(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)
            ->get(route('creator.products.create'));

        $response->assertStatus(403);
    }

    public function test_non_creator_cannot_access_edit_form(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->actingAs($buyer)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_create_form(): void
    {
        $response = $this->get(route('creator.products.create'));

        // Unauthenticated users should get a 401 or 403 response
        $this->assertContains($response->status(), [401, 403]);
    }

    public function test_unauthenticated_user_cannot_access_edit_form(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->get(route('creator.products.edit', $product));

        // Unauthenticated users should get a 401 or 403 response
        $this->assertContains($response->status(), [401, 403]);
    }

    public function test_forms_escape_xss_content(): void
    {
        $product = Product::factory()->create([
            'creator_id' => $this->creator->id,
            'title' => '<script>alert("XSS")</script>',
            'description' => '<img src=x onerror=alert("XSS")>',
        ]);

        $response = $this->actingAs($this->creator)
            ->get(route('creator.products.edit', $product));

        $response->assertStatus(200);
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }
}
