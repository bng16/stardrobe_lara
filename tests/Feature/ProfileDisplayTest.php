<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CreatorShop;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for profile display functionality.
 * 
 * Tests Requirements:
 * - REQ-1.1.3: Implement a consistent layout system using Blade layouts and sections
 * - REQ-1.2.1: Replace all Inertia::render() calls with view() calls in controllers
 * - REQ-2.2.2: Implement XSS protection using Blade's automatic escaping
 * - REQ-4.1.1: Display user information in card format
 * - Task 5.2.1: Profile page displays user information correctly
 */
class ProfileDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function profile_page_requires_authentication()
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_profile_page()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user');
    }

    /** @test */
    public function profile_page_displays_basic_user_information()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('My Profile');
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('Buyer');
    }

    /** @test */
    public function profile_page_displays_account_creation_date()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'created_at' => now()->subMonths(3),
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Member Since');
        $response->assertSee($user->created_at->format('F j, Y'));
    }

    /** @test */
    public function profile_page_displays_user_role_badge()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Account Role');
        $response->assertSee('Creator');
        // Check for badge styling
        $response->assertSee('bg-blue-100', false);
    }

    /** @test */
    public function profile_page_displays_admin_role_correctly()
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Admin');
        $response->assertSee('bg-purple-100', false);
    }

    /** @test */
    public function profile_page_displays_edit_profile_button()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Edit Profile');
        // Note: Edit profile route will be implemented in a future task
        $response->assertSee('href="#"', false);
    }

    /** @test */
    public function profile_page_displays_creator_shop_information_for_creators()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'shop_name' => 'Amazing Art Shop',
            'bio' => 'Creating beautiful artwork for collectors',
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Creator Information');
        $response->assertSee('Amazing Art Shop');
        $response->assertSee('Creating beautiful artwork for collectors');
        $response->assertSee('Onboarding Status');
        $response->assertSee('Completed');
    }

    /** @test */
    public function profile_page_does_not_display_creator_information_for_non_creators()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        // Check that the creator information card heading is not displayed
        $response->assertDontSee('<h2 class="text-xl font-semibold text-gray-900">Creator Information</h2>', false);
        $response->assertDontSee('Shop Name');
    }

    /** @test */
    public function profile_page_displays_pending_onboarding_status_for_incomplete_creators()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'is_onboarded' => false,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Pending');
        $response->assertSee('bg-yellow-100', false);
    }

    /** @test */
    public function profile_page_displays_creator_bio_when_available()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'bio' => 'I create unique digital art pieces inspired by nature and technology.',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Bio');
        $response->assertSee('I create unique digital art pieces inspired by nature and technology.');
    }

    /** @test */
    public function profile_page_does_not_display_bio_section_when_empty()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'bio' => null,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        // Bio label should not appear when bio is null
        $content = $response->getContent();
        $this->assertStringNotContainsString('<dt class="text-sm font-medium text-gray-500">Bio</dt>', $content);
    }

    /** @test */
    public function profile_page_displays_profile_image_when_available()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'profile_image' => 'profiles/test-profile.jpg',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Profile Image');
        $response->assertSee('profiles/test-profile.jpg', false);
    }

    /** @test */
    public function profile_page_displays_banner_image_when_available()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'banner_image' => 'banners/test-banner.jpg',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Banner Image');
        $response->assertSee('banners/test-banner.jpg', false);
    }

    /** @test */
    public function profile_page_displays_complete_onboarding_action_for_incomplete_creators()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'is_onboarded' => false,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Complete Onboarding');
        $response->assertSee('Finish setting up your creator shop');
        $response->assertSee(route('creator.onboarding'), false);
    }

    /** @test */
    public function profile_page_does_not_display_onboarding_action_for_completed_creators()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertDontSee('Complete Onboarding');
    }

    /** @test */
    public function profile_page_displays_creator_dashboard_link_for_creators()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Creator Dashboard');
        $response->assertSee('Manage your products and sales');
        $response->assertSee(route('creator.dashboard'), false);
    }

    /** @test */
    public function profile_page_displays_admin_dashboard_link_for_admins()
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Manage platform and users');
        $response->assertSee(route('admin.dashboard'), false);
    }

    /** @test */
    public function profile_page_does_not_display_admin_dashboard_link_for_non_admins()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertDontSee('Admin Dashboard');
    }

    /** @test */
    public function profile_page_uses_blade_ui_components()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        // Check for card component usage
        $response->assertSee('rounded-lg border bg-card', false);
        // Check for button component usage
        $response->assertSee('inline-flex items-center justify-center', false);
    }

    /** @test */
    public function profile_page_extends_app_layout()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        // Check for layout elements
        $response->assertSee('<!DOCTYPE html>', false);
        $response->assertSee('<html lang="en">', false);
    }

    /** @test */
    public function profile_page_displays_account_actions_section()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Account Actions');
        $response->assertSee('Manage your account settings and preferences');
        $response->assertSee('Update Profile');
    }

    /** @test */
    public function profile_controller_returns_view_not_inertia()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        // Ensure it's a view response, not Inertia
        $response->assertStatus(200);
        $this->assertInstanceOf(\Illuminate\View\View::class, $response->original);
    }

    /** @test */
    public function profile_page_escapes_user_input_for_xss_protection()
    {
        $user = User::factory()->create([
            'name' => '<script>alert("xss")</script>John',
            'role' => UserRole::Creator,
        ]);

        $creatorShop = CreatorShop::factory()->create([
            'user_id' => $user->id,
            'bio' => '<script>alert("xss")</script>Bio content',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        // Should see escaped content, not raw script tags
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;John', false);
        $response->assertDontSee('<script>alert("xss")</script>', false);
    }

    /** @test */
    public function profile_page_handles_creator_without_shop_gracefully()
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);
        // No creator shop created

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        // Should not display creator information section heading
        $response->assertDontSee('<h2 class="text-xl font-semibold text-gray-900">Creator Information</h2>', false);
    }

    /** @test */
    public function profile_page_displays_all_role_types_correctly()
    {
        $roles = [
            'admin' => ['role' => UserRole::Admin, 'display' => 'Admin'],
            'creator' => ['role' => UserRole::Creator, 'display' => 'Creator'],
            'buyer' => ['role' => UserRole::Buyer, 'display' => 'Buyer'],
        ];

        foreach ($roles as $roleData) {
            $user = User::factory()->create([
                'role' => $roleData['role'],
            ]);

            $response = $this->actingAs($user)->get(route('profile.show'));

            $response->assertStatus(200);
            $response->assertSee($roleData['display']);
        }
    }
}
