<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can access the settings page.
     */
    public function test_authenticated_user_can_access_settings_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.settings');
        $response->assertViewHas('user', $user);
    }

    /**
     * Test that guests cannot access the settings page.
     */
    public function test_guest_cannot_access_settings_page(): void
    {
        $response = $this->get(route('profile.settings'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that settings page displays notification preferences.
     */
    public function test_settings_page_displays_notification_preferences(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('Email Notifications');
        $response->assertSee('New Products');
        $response->assertSee('Auction Won');
    }

    /**
     * Test that settings page displays creator-specific notifications for creators.
     */
    public function test_settings_page_displays_creator_notifications_for_creators(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('New Followers');
        $response->assertSee('Auction Sold');
    }

    /**
     * Test that settings page does not display creator notifications for buyers.
     */
    public function test_settings_page_does_not_display_creator_notifications_for_buyers(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertDontSee('notify_new_followers');
        $response->assertDontSee('notify_auction_sold');
    }

    /**
     * Test that settings page displays privacy settings.
     */
    public function test_settings_page_displays_privacy_settings(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('Privacy Settings');
        $response->assertSee('Profile Visibility');
    }

    /**
     * Test that user can update notification preferences.
     */
    public function test_user_can_update_notification_preferences(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'notify_new_products' => true,
            'notify_auction_won' => true,
        ]);

        $response->assertRedirect(route('profile.settings'));
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $user->refresh();
        $this->assertTrue($user->getPreference('notifications.new_products'));
        $this->assertTrue($user->getPreference('notifications.auction_won'));
        $this->assertFalse($user->getPreference('notifications.new_followers'));
        $this->assertFalse($user->getPreference('notifications.auction_sold'));
    }

    /**
     * Test that creator can update creator-specific notification preferences.
     */
    public function test_creator_can_update_creator_notification_preferences(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'notify_new_products' => true,
            'notify_new_followers' => true,
            'notify_auction_won' => true,
            'notify_auction_sold' => true,
        ]);

        $response->assertRedirect(route('profile.settings'));
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $user->refresh();
        $this->assertTrue($user->getPreference('notifications.new_products'));
        $this->assertTrue($user->getPreference('notifications.new_followers'));
        $this->assertTrue($user->getPreference('notifications.auction_won'));
        $this->assertTrue($user->getPreference('notifications.auction_sold'));
    }

    /**
     * Test that user can update privacy settings.
     */
    public function test_user_can_update_privacy_settings(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'profile_visibility' => 'private',
        ]);

        $response->assertRedirect(route('profile.settings'));
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $user->refresh();
        $this->assertEquals('private', $user->getPreference('privacy.profile_visibility'));
    }

    /**
     * Test that unchecked checkboxes are saved as false.
     */
    public function test_unchecked_checkboxes_are_saved_as_false(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'preferences' => [
                'notifications' => [
                    'new_products' => true,
                    'auction_won' => true,
                ],
            ],
        ]);

        // Submit form with no checkboxes checked
        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'profile_visibility' => 'public',
        ]);

        $response->assertRedirect(route('profile.settings'));

        $user->refresh();
        $this->assertFalse($user->getPreference('notifications.new_products'));
        $this->assertFalse($user->getPreference('notifications.auction_won'));
    }

    /**
     * Test that profile visibility defaults to public.
     */
    public function test_profile_visibility_defaults_to_public(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'notify_new_products' => true,
        ]);

        $response->assertRedirect(route('profile.settings'));

        $user->refresh();
        $this->assertEquals('public', $user->getPreference('privacy.profile_visibility'));
    }

    /**
     * Test that invalid profile visibility value is rejected.
     */
    public function test_invalid_profile_visibility_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'profile_visibility' => 'invalid',
        ]);

        $response->assertSessionHasErrors('profile_visibility');
    }

    /**
     * Test that settings page shows current preferences.
     */
    public function test_settings_page_shows_current_preferences(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'preferences' => [
                'notifications' => [
                    'new_products' => true,
                    'auction_won' => true,
                ],
                'privacy' => [
                    'profile_visibility' => 'private',
                ],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        // Check that checkboxes are checked
        $response->assertSee('notify_new_products');
        $response->assertSee('notify_auction_won');
        // Check that private is selected
        $response->assertSee('selected', false);
    }

    /**
     * Test that guests cannot update settings.
     */
    public function test_guest_cannot_update_settings(): void
    {
        $response = $this->put(route('profile.settings.update'), [
            'notify_new_products' => true,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that settings page has link back to profile.
     */
    public function test_settings_page_has_link_back_to_profile(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee(route('profile.show'));
        $response->assertSee('Back to Profile');
    }

    /**
     * Test that profile show page has link to settings.
     */
    public function test_profile_show_page_has_link_to_settings(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee(route('profile.settings'));
        $response->assertSee('Account Settings');
        $response->assertSee('Manage notifications and privacy preferences');
    }

    /**
     * Test that user can toggle individual notification preferences.
     */
    public function test_user_can_toggle_individual_notification_preferences(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
            'preferences' => [
                'notifications' => [
                    'new_products' => true,
                    'auction_won' => false,
                ],
            ],
        ]);

        // Toggle: turn off new_products, turn on auction_won
        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'notify_auction_won' => true,
        ]);

        $response->assertRedirect(route('profile.settings'));

        $user->refresh();
        $this->assertFalse($user->getPreference('notifications.new_products'));
        $this->assertTrue($user->getPreference('notifications.auction_won'));
    }

    /**
     * Test that preferences are stored as JSON in database.
     */
    public function test_preferences_are_stored_as_json_in_database(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $this->actingAs($user)->put(route('profile.settings.update'), [
            'notify_new_products' => true,
            'notify_auction_won' => true,
            'profile_visibility' => 'private',
        ]);

        $user->refresh();

        // Check that preferences is an array (cast from JSON)
        $this->assertIsArray($user->preferences);
        $this->assertArrayHasKey('notifications', $user->preferences);
        $this->assertArrayHasKey('privacy', $user->preferences);
    }

    /**
     * Test that getPreference method returns default value when preference not set.
     */
    public function test_get_preference_returns_default_when_not_set(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $this->assertFalse($user->getPreference('notifications.new_products', false));
        $this->assertEquals('public', $user->getPreference('privacy.profile_visibility', 'public'));
        $this->assertNull($user->getPreference('nonexistent.key'));
    }

    /**
     * Test that setPreference method updates preferences.
     */
    public function test_set_preference_updates_preferences(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $user->setPreference('notifications.new_products', true);
        $user->save();

        $user->refresh();
        $this->assertTrue($user->getPreference('notifications.new_products'));
    }

    /**
     * Test that settings form has CSRF protection.
     */
    public function test_settings_form_has_csrf_protection(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('csrf');
    }

    /**
     * Test that validation errors are displayed.
     */
    public function test_validation_errors_are_displayed(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->put(route('profile.settings.update'), [
            'profile_visibility' => 'invalid_value',
        ]);

        $response->assertSessionHasErrors('profile_visibility');
    }

    /**
     * Test that all notification options are available for buyers.
     */
    public function test_all_buyer_notification_options_are_available(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('notify_new_products');
        $response->assertSee('notify_auction_won');
    }

    /**
     * Test that all notification options are available for creators.
     */
    public function test_all_creator_notification_options_are_available(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        CreatorShop::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('notify_new_products');
        $response->assertSee('notify_new_followers');
        $response->assertSee('notify_auction_won');
        $response->assertSee('notify_auction_sold');
    }
}
