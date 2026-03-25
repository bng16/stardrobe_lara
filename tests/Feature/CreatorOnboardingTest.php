<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatorOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_view_onboarding_page(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->get(route('creator.onboarding'));

        $response->assertOk();
        $response->assertViewIs('creator.onboarding');
    }

    public function test_non_creator_cannot_access_onboarding(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)->get(route('creator.onboarding'));

        $response->assertForbidden();
    }

    public function test_creator_can_complete_onboarding_with_minimal_data(): void
    {
        Storage::fake('s3');
        
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'My Awesome Shop',
            'bio' => 'This is my shop bio',
        ]);

        $response->assertRedirect(route('creator.products.index'));
        $response->assertSessionHas('success');

        $shop->refresh();
        $this->assertEquals('My Awesome Shop', $shop->shop_name);
        $this->assertEquals('This is my shop bio', $shop->bio);
        $this->assertTrue($shop->is_onboarded);
    }

    public function test_creator_can_complete_onboarding_with_images(): void
    {
        Storage::fake('s3');
        
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $profileImage = UploadedFile::fake()->image('profile.jpg')->size(1024); // 1MB
        $bannerImage = UploadedFile::fake()->image('banner.jpg')->size(2048); // 2MB

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'My Awesome Shop',
            'bio' => 'This is my shop bio',
            'profile_image' => $profileImage,
            'banner_image' => $bannerImage,
        ]);

        $response->assertRedirect(route('creator.products.index'));

        $shop->refresh();
        $this->assertTrue($shop->is_onboarded);
        $this->assertNotNull($shop->profile_image);
        $this->assertNotNull($shop->banner_image);
        
        // Verify files were uploaded to S3
        Storage::disk('s3')->assertExists('creator-profiles/' . $profileImage->hashName());
        Storage::disk('s3')->assertExists('creator-banners/' . $bannerImage->hashName());
    }

    public function test_onboarding_validates_shop_name_is_required(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
    }

    public function test_onboarding_validates_shop_name_is_unique(): void
    {
        $existingCreator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $existingCreator->id,
            'shop_name' => 'Existing Shop',
        ]);

        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Existing Shop',
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
    }

    public function test_onboarding_validates_shop_name_max_length(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => str_repeat('a', 256), // 256 characters, exceeds max of 255
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
    }

    public function test_onboarding_validates_bio_max_length(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Test Shop',
            'bio' => str_repeat('a', 1001), // 1001 characters, exceeds max of 1000
        ]);

        $response->assertSessionHasErrors('bio');
    }

    public function test_onboarding_validates_profile_image_size(): void
    {
        Storage::fake('s3');
        
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB, exceeds 2MB limit

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Test Shop',
            'bio' => 'Test bio',
            'profile_image' => $largeImage,
        ]);

        $response->assertSessionHasErrors('profile_image');
    }

    public function test_onboarding_validates_banner_image_size(): void
    {
        Storage::fake('s3');
        
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB, exceeds 5MB limit

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Test Shop',
            'bio' => 'Test bio',
            'banner_image' => $largeImage,
        ]);

        $response->assertSessionHasErrors('banner_image');
    }

    public function test_onboarding_marks_creator_as_onboarded(): void
    {
        Storage::fake('s3');
        
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
            'shop_name' => null,
        ]);

        $this->assertFalse($shop->is_onboarded);

        $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Test Shop',
            'bio' => 'Test bio',
        ]);

        $shop->refresh();
        $this->assertTrue($shop->is_onboarded);
    }
}
