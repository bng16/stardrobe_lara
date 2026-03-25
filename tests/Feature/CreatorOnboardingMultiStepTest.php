<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatorOnboardingMultiStepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    public function test_onboarding_starts_at_step_1(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->get(route('creator.onboarding'));

        $response->assertOk();
        $response->assertViewIs('creator.onboarding');
        $response->assertViewHas('currentStep', 1);
    }

    public function test_step_1_validates_shop_name_required(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $this->assertStringContainsString('Please enter a shop name', session('errors')->first('shop_name'));
    }

    public function test_step_1_validates_shop_name_min_length(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'AB', // Only 2 characters
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $this->assertStringContainsString('at least 3 characters', session('errors')->first('shop_name'));
    }

    public function test_step_1_validates_shop_name_format(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'Invalid@Shop!Name', // Contains invalid characters
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $this->assertStringContainsString('letters, numbers, spaces, hyphens, and underscores', session('errors')->first('shop_name'));
    }

    public function test_step_1_validates_shop_name_uniqueness(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);
        
        // Create another shop with existing name
        $existingCreator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $existingCreator->id,
            'shop_name' => 'Existing Shop',
        ]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'Existing Shop',
            'bio' => 'Test bio',
        ]);

        $response->assertSessionHasErrors('shop_name');
    }

    public function test_step_1_validates_bio_max_length(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'Test Shop',
            'bio' => str_repeat('a', 1001), // Exceeds 1000 character limit
        ]);

        $response->assertSessionHasErrors('bio');
        $this->assertStringContainsString('cannot exceed 1000 characters', session('errors')->first('bio'));
    }

    public function test_step_1_validates_bio_min_length(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'Test Shop',
            'bio' => 'Short', // Less than 10 characters
        ]);

        $response->assertSessionHasErrors('bio');
        $this->assertStringContainsString('at least 10 characters', session('errors')->first('bio'));
    }

    public function test_step_1_submission_advances_to_step_2(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'My Awesome Shop',
            'bio' => 'This is my shop bio',
        ]);

        $response->assertRedirect(route('creator.onboarding'));
        $response->assertSessionHas('onboarding_step', 2);
        $response->assertSessionHas('onboarding_data.shop_name', 'My Awesome Shop');
        $response->assertSessionHas('onboarding_data.bio', 'This is my shop bio');
    }

    public function test_step_2_displays_with_stored_data(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        // Set up session data
        $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 2,
                'onboarding_data' => [
                    'shop_name' => 'My Shop',
                    'bio' => 'My bio',
                ],
            ])
            ->get(route('creator.onboarding'));

        $response = $this->get(route('creator.onboarding'));

        $response->assertOk();
        $response->assertViewHas('currentStep', 2);
        $response->assertViewHas('formData', [
            'shop_name' => 'My Shop',
            'bio' => 'My bio',
        ]);
    }

    public function test_step_2_can_go_back_to_step_1(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 2,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Bio'],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 2,
                'action' => 'previous',
            ]);

        $response->assertRedirect(route('creator.onboarding'));
        $response->assertSessionHas('onboarding_step', 1);
    }

    public function test_step_2_submission_advances_to_step_3(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 2,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Bio'],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 2,
                'action' => 'next',
            ]);

        $response->assertRedirect(route('creator.onboarding'));
        $response->assertSessionHas('onboarding_step', 3);
    }

    public function test_step_2_validates_profile_image_size(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB, exceeds 2MB limit

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 2,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Bio'],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 2,
                'action' => 'next',
                'profile_image' => $largeImage,
            ]);

        $response->assertSessionHasErrors('profile_image');
    }

    public function test_step_3_can_go_back_to_step_2(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 3,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Bio'],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 3,
                'action' => 'previous',
            ]);

        $response->assertRedirect(route('creator.onboarding'));
        $response->assertSessionHas('onboarding_step', 2);
    }

    public function test_step_3_validates_banner_image_size(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB, exceeds 5MB limit

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 3,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Bio'],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 3,
                'action' => 'submit',
                'banner_image' => $largeImage,
            ]);

        $response->assertSessionHasErrors('banner_image');
    }

    public function test_step_3_final_submission_completes_onboarding(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $profileImage = UploadedFile::fake()->image('profile.jpg')->size(1024);
        $bannerImage = UploadedFile::fake()->image('banner.jpg')->size(2048);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 3,
                'onboarding_data' => [
                    'shop_name' => 'My Awesome Shop',
                    'bio' => 'This is my shop bio',
                ],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 3,
                'action' => 'submit',
                'profile_image' => $profileImage,
                'banner_image' => $bannerImage,
            ]);

        $response->assertRedirect(route('creator.products.index'));
        $response->assertSessionHas('success', 'Your shop has been set up successfully!');

        // Verify shop was updated
        $shop->refresh();
        $this->assertEquals('My Awesome Shop', $shop->shop_name);
        $this->assertEquals('This is my shop bio', $shop->bio);
        $this->assertTrue($shop->is_onboarded);
        $this->assertNotNull($shop->profile_image);
        $this->assertNotNull($shop->banner_image);

        // Verify session was cleared
        $this->assertNull(session('onboarding_step'));
        $this->assertNull(session('onboarding_data'));
    }

    public function test_step_3_submission_without_images_completes_onboarding(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 3,
                'onboarding_data' => [
                    'shop_name' => 'My Shop',
                    'bio' => 'My bio',
                ],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 3,
                'action' => 'submit',
            ]);

        $response->assertRedirect(route('creator.products.index'));

        $shop->refresh();
        $this->assertEquals('My Shop', $shop->shop_name);
        $this->assertTrue($shop->is_onboarded);
    }

    public function test_form_state_persists_across_steps(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        // Submit step 1
        $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'Test Shop',
            'bio' => 'Test Bio',
        ]);

        // Submit step 2
        $this->post(route('creator.onboarding.step'), [
            'current_step' => 2,
            'action' => 'next',
        ]);

        // Verify data persists
        $this->assertEquals('Test Shop', session('onboarding_data.shop_name'));
        $this->assertEquals('Test Bio', session('onboarding_data.bio'));
    }

    public function test_going_back_preserves_form_data(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        // Set up at step 2 with data
        $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 2,
                'onboarding_data' => [
                    'shop_name' => 'Original Shop',
                    'bio' => 'Original Bio',
                ],
            ])
            ->post(route('creator.onboarding.step'), [
                'current_step' => 2,
                'action' => 'previous',
            ]);

        // Verify data is still there
        $this->assertEquals('Original Shop', session('onboarding_data.shop_name'));
        $this->assertEquals('Original Bio', session('onboarding_data.bio'));
    }

    public function test_onboarding_page_displays_progress_indicators(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->get(route('creator.onboarding'));

        $response->assertOk();
        $response->assertSee('Step 1 of 3');
        $response->assertSee('Shop Information');
    }

    public function test_step_2_displays_progress_indicators(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 2,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Test bio'],
            ])
            ->get(route('creator.onboarding'));

        $response->assertOk();
        $response->assertSee('Step 2 of 3');
        $response->assertSee('Profile Image');
    }

    public function test_step_3_displays_progress_indicators(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)
            ->withSession([
                'onboarding_step' => 3,
                'onboarding_data' => ['shop_name' => 'Test', 'bio' => 'Test bio'],
            ])
            ->get(route('creator.onboarding'));

        $response->assertOk();
        $response->assertSee('Step 3 of 3');
        $response->assertSee('Banner Image');
    }

    public function test_validation_errors_display_inline(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'AB', // Too short
            'bio' => 'Short', // Too short
        ]);

        $response->assertSessionHasErrors(['shop_name', 'bio']);
        
        // Follow the redirect to see the errors displayed
        $response = $this->get(route('creator.onboarding'));
        $response->assertSee('at least 3 characters');
        $response->assertSee('at least 10 characters');
    }

    public function test_form_preserves_input_on_validation_error(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        $response = $this->actingAs($creator)->post(route('creator.onboarding.step'), [
            'current_step' => 1,
            'action' => 'next',
            'shop_name' => 'AB', // Invalid
            'bio' => 'This is a valid bio that should be preserved',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $response->assertSessionHasInput('bio', 'This is a valid bio that should be preserved');
    }
}
