<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can access the password change page.
     */
    public function test_authenticated_user_can_access_password_change_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        $response = $this->actingAs($user)->get(route('profile.password'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.password');
    }

    /**
     * Test that guests cannot access the password change page.
     */
    public function test_guest_cannot_access_password_change_page(): void
    {
        $response = $this->get(route('profile.password'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that user can change their password with valid data.
     */
    public function test_user_can_change_password_with_valid_data(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Password changed successfully.');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /**
     * Test that current password is required.
     */
    public function test_current_password_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => '',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    /**
     * Test that new password is required.
     */
    public function test_new_password_is_required(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test that new password must be at least 8 characters.
     */
    public function test_new_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test that new password confirmation must match.
     */
    public function test_new_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test that current password must be correct.
     */
    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertStringContainsString('incorrect', $response->getSession()->get('errors')->first('current_password'));
    }

    /**
     * Test that password is hashed before saving.
     */
    public function test_password_is_hashed_before_saving(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $user->refresh();
        
        // Password should not be stored in plain text
        $this->assertNotEquals('new-password', $user->password);
        
        // Password should be hashed and verifiable
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /**
     * Test that old password no longer works after change.
     */
    public function test_old_password_no_longer_works_after_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $user->refresh();
        
        // Old password should no longer work
        $this->assertFalse(Hash::check('old-password', $user->password));
        
        // New password should work
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /**
     * Test that guests cannot change password.
     */
    public function test_guest_cannot_change_password(): void
    {
        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that password change page has CSRF protection.
     */
    public function test_password_change_page_has_csrf_protection(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.password'));

        $response->assertStatus(200);
        $response->assertSee('csrf');
    }

    /**
     * Test that validation errors are displayed on the form.
     */
    public function test_validation_errors_are_displayed_on_form(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        // Should have validation errors for password (too short and doesn't match confirmation)
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test that all user roles can change their password.
     */
    public function test_all_user_roles_can_change_password(): void
    {
        $roles = [UserRole::Admin, UserRole::Creator, UserRole::Buyer];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'password' => Hash::make('old-password'),
            ]);

            $response = $this->actingAs($user)->put(route('profile.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

            $response->assertRedirect(route('profile.show'));
            $response->assertSessionHas('success');

            $user->refresh();
            $this->assertTrue(Hash::check('new-password', $user->password));
        }
    }

    /**
     * Test that password change link is visible on profile edit page.
     */
    public function test_password_change_link_is_visible_on_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Change Password');
        $response->assertSee(route('profile.password'));
    }

    /**
     * Test that password change form has all required fields.
     */
    public function test_password_change_form_has_all_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.password'));

        $response->assertStatus(200);
        $response->assertSee('current_password');
        $response->assertSee('password');
        $response->assertSee('password_confirmation');
        $response->assertSee('Current Password');
        $response->assertSee('New Password');
        $response->assertSee('Confirm New Password');
    }

    /**
     * Test that success message is displayed after password change.
     */
    public function test_success_message_is_displayed_after_password_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Password changed successfully.');
    }

    /**
     * Test that password with exactly 8 characters is accepted.
     */
    public function test_password_with_exactly_8_characters_is_accepted(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('12345678', $user->password));
    }

    /**
     * Test that password with more than 8 characters is accepted.
     */
    public function test_password_with_more_than_8_characters_is_accepted(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'very-long-secure-password-123',
            'password_confirmation' => 'very-long-secure-password-123',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('very-long-secure-password-123', $user->password));
    }

    /**
     * Test that password change form has cancel button linking to profile.
     */
    public function test_password_change_form_has_cancel_button(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.password'));

        $response->assertStatus(200);
        $response->assertSee('Cancel');
        $response->assertSee(route('profile.show'));
    }
}
