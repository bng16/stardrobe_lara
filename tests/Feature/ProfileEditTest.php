<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileEditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can access the profile edit page.
     */
    public function test_authenticated_user_can_access_profile_edit_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user', $user);
    }

    /**
     * Test that guests cannot access the profile edit page.
     */
    public function test_guest_cannot_access_profile_edit_page(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that buyer can update their basic profile information.
     */
    public function test_buyer_can_update_basic_profile_information(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
    }

    /**
     * Test that creator can update their basic profile information.
     */
    public function test_creator_can_update_basic_profile_information(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'shop_name' => 'Old Shop',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'shop_name' => 'New Shop',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
    }

    /**
     * Test that creator can update their shop information.
     */
    public function test_creator_can_update_shop_information(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'shop_name' => 'Old Shop',
            'bio' => 'Old bio',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => 'New Shop Name',
            'bio' => 'New bio description',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $creatorShop->refresh();
        $this->assertEquals('New Shop Name', $creatorShop->shop_name);
        $this->assertEquals('New bio description', $creatorShop->bio);
    }

    /**
     * Test that creator can upload a profile image.
     */
    public function test_creator_can_upload_profile_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->image('profile.jpg', 500, 500);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'profile_image' => $file,
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $creatorShop->refresh();
        $this->assertNotNull($creatorShop->profile_image);
        Storage::disk('public')->assertExists($creatorShop->profile_image);
    }

    /**
     * Test that creator can upload a banner image.
     */
    public function test_creator_can_upload_banner_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->image('banner.jpg', 1200, 400);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'banner_image' => $file,
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $creatorShop->refresh();
        $this->assertNotNull($creatorShop->banner_image);
        Storage::disk('public')->assertExists($creatorShop->banner_image);
    }

    /**
     * Test that old profile image is deleted when uploading a new one.
     */
    public function test_old_profile_image_is_deleted_when_uploading_new_one(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        // Create old profile image
        $oldFile = UploadedFile::fake()->image('old-profile.jpg');
        $oldPath = $oldFile->store('creator-profiles', 'public');

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'profile_image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // Upload new profile image
        $newFile = UploadedFile::fake()->image('new-profile.jpg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'profile_image' => $newFile,
        ]);

        $response->assertRedirect(route('profile.show'));

        // Old image should be deleted
        Storage::disk('public')->assertMissing($oldPath);

        // New image should exist
        $creatorShop->refresh();
        Storage::disk('public')->assertExists($creatorShop->profile_image);
    }

    /**
     * Test that old banner image is deleted when uploading a new one.
     */
    public function test_old_banner_image_is_deleted_when_uploading_new_one(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        // Create old banner image
        $oldFile = UploadedFile::fake()->image('old-banner.jpg');
        $oldPath = $oldFile->store('creator-banners', 'public');

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'banner_image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // Upload new banner image
        $newFile = UploadedFile::fake()->image('new-banner.jpg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'banner_image' => $newFile,
        ]);

        $response->assertRedirect(route('profile.show'));

        // Old image should be deleted
        Storage::disk('public')->assertMissing($oldPath);

        // New image should exist
        $creatorShop->refresh();
        Storage::disk('public')->assertExists($creatorShop->banner_image);
    }

    /**
     * Test that name is required.
     */
    public function test_name_is_required(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => '',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test that email is required.
     */
    public function test_email_is_required(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that email must be valid.
     */
    public function test_email_must_be_valid(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that email must be unique.
     */
    public function test_email_must_be_unique(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'email' => 'user@example.com',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that user can keep their own email.
     */
    public function test_user_can_keep_their_own_email(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'email' => 'user@example.com',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');
    }

    /**
     * Test that shop name is required for creators.
     */
    public function test_shop_name_is_required_for_creators(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'shop_name' => '',
        ]);

        $response->assertSessionHasErrors('shop_name');
    }

    /**
     * Test that bio is optional for creators.
     */
    public function test_bio_is_optional_for_creators(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'shop_name' => 'Test Shop',
            'bio' => '',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $creatorShop->refresh();
        $this->assertNull($creatorShop->bio);
    }

    /**
     * Test that bio has maximum length validation.
     */
    public function test_bio_has_maximum_length_validation(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'shop_name' => 'Test Shop',
            'bio' => str_repeat('a', 1001), // Exceeds 1000 character limit
        ]);

        $response->assertSessionHasErrors('bio');
    }

    /**
     * Test that profile image must be an image file.
     */
    public function test_profile_image_must_be_image_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'profile_image' => $file,
        ]);

        $response->assertSessionHasErrors('profile_image');
    }

    /**
     * Test that banner image must be an image file.
     */
    public function test_banner_image_must_be_image_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'banner_image' => $file,
        ]);

        $response->assertSessionHasErrors('banner_image');
    }

    /**
     * Test that profile image size is validated.
     */
    public function test_profile_image_size_is_validated(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create a file larger than 5MB (5120KB)
        $file = UploadedFile::fake()->create('large-image.jpg', 6000);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'shop_name' => $creatorShop->shop_name,
            'profile_image' => $file,
        ]);

        $response->assertSessionHasErrors('profile_image');
    }

    /**
     * Test that validation errors preserve form input.
     */
    public function test_validation_errors_preserve_form_input(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('name', 'Test User');
    }

    /**
     * Test that guests cannot update profile.
     */
    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->put(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that profile edit page displays current user data.
     */
    public function test_profile_edit_page_displays_current_user_data(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'shop_name' => 'Test Shop',
            'bio' => 'Test bio',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('test@example.com');
        $response->assertSee('Test Shop');
        $response->assertSee('Test bio');
    }

    /**
     * Test that buyer profile edit page does not show creator fields.
     */
    public function test_buyer_profile_edit_page_does_not_show_creator_fields(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertDontSee('Shop Name');
        $response->assertDontSee('Bio');
        $response->assertDontSee('Profile Image');
        $response->assertDontSee('Banner Image');
    }
}
