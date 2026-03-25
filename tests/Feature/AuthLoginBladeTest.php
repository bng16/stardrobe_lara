<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for login page Blade template.
 * 
 * Tests Requirements:
 * - REQ-4.3.1: Maintain existing login and registration forms
 * - REQ-4.3.3: Show appropriate error messages for authentication failures
 * - REQ-4.3.4: Redirect users appropriately after authentication
 * - REQ-1.3.2: Implement proper CSRF protection on all forms
 * - REQ-4.4.1: Display validation errors inline with form fields
 */
class AuthLoginBladeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_displays_correctly()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Welcome back');
        $response->assertSee('Sign in to your account to continue');
    }

    /** @test */
    public function login_form_contains_required_fields()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        
        // Check for email field
        $response->assertSee('Email address');
        $response->assertSee('name="email"', false);
        $response->assertSee('type="email"', false);
        $response->assertSee('required', false);
        
        // Check for password field
        $response->assertSee('Password');
        $response->assertSee('name="password"', false);
        $response->assertSee('type="password"', false);
        
        // Check for remember me checkbox
        $response->assertSee('Remember me');
        $response->assertSee('name="remember"', false);
        $response->assertSee('type="checkbox"', false);
        
        // Check for submit button
        $response->assertSee('Sign in');
    }

    /** @test */
    public function login_form_has_csrf_protection()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
        $response->assertSee('type="hidden"', false);
    }

    /** @test */
    public function login_form_has_forgot_password_link()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Forgot your password?');
    }

    /** @test */
    public function login_form_has_register_link()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee("Don't have an account?");
        $response->assertSee('Sign up');
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::Buyer,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    /** @test */
    public function admin_redirects_to_admin_dashboard_after_login()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::Admin,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function creator_redirects_to_creator_dashboard_after_login()
    {
        $creator = User::factory()->create([
            'email' => 'creator@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::Creator,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'creator@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($creator);
        $response->assertRedirect(route('creator.dashboard'));
    }

    /** @test */
    public function login_fails_with_invalid_email()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function login_fails_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function login_validation_requires_email()
    {
        $response = $this->post(route('login'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function login_validation_requires_valid_email_format()
    {
        $response = $this->post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function login_validation_requires_password()
    {
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function login_displays_validation_errors_inline()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email', 'password']);
        
        // Follow redirect to see errors displayed
        $followUp = $this->get(route('login'));
        $followUp->assertSee('text-red-600', false);
    }

    /** @test */
    public function remember_me_functionality_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticatedAs($user);
        
        // Check that remember token is set
        $this->assertNotNull($user->fresh()->remember_token);
    }

    /** @test */
    public function authenticated_user_cannot_access_login_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect('/');
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'You have been logged out successfully.');
    }

    /** @test */
    public function login_form_preserves_email_on_validation_error()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('password');
        
        // Check that old email is preserved
        $this->assertEquals('test@example.com', old('email'));
    }

    /** @test */
    public function login_form_has_proper_accessibility_attributes()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        
        // Check for required attributes
        $response->assertSee('required', false);
        
        // Check for autocomplete attributes
        $response->assertSee('autocomplete="email"', false);
        $response->assertSee('autocomplete="current-password"', false);
        
        // Check for autofocus on email field
        $response->assertSee('autofocus', false);
    }

    /** @test */
    public function login_form_displays_error_messages_with_role_alert()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('login'));
        
        // Follow redirect to see error
        $followUp = $this->get(route('login'));
        $followUp->assertSee('role="alert"', false);
    }

    /** @test */
    public function login_page_uses_auth_layout()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        
        // Check for auth layout elements
        $response->assertSee('bg-gradient-to-br from-blue-50 to-indigo-100', false);
        $response->assertSee('shadow-xl', false);
    }

    /** @test */
    public function login_form_has_loading_state_attribute()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('data-loading', false);
    }

    /** @test */
    public function session_regenerates_after_successful_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Start a session
        $this->get(route('login'));
        $oldSessionId = session()->getId();

        // Login
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Session should be regenerated
        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /** @test */
    public function login_respects_intended_redirect()
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer,
        ]);

        // Try to access a protected page
        $this->get('/profile');

        // Login
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect to intended page
        $response->assertRedirect('/profile');
    }
}
