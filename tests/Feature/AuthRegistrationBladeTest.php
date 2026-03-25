<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRegistrationBladeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_page_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        $response->assertSee(__('Create your account'));
        $response->assertSee(__('Full Name'));
        $response->assertSee(__('Email address'));
        $response->assertSee(__('Password'));
        $response->assertSee(__('Confirm Password'));
        $response->assertSee(__('I want to'));
    }

    /** @test */
    public function registration_form_has_csrf_protection()
    {
        $response = $this->get(route('register'));

        $response->assertSee('csrf-token', false);
        $response->assertSee('@csrf', false);
    }

    /** @test */
    public function registration_form_has_all_required_fields()
    {
        $response = $this->get(route('register'));

        // Check for name field
        $response->assertSee('name="name"', false);
        $response->assertSee('id="name"', false);
        $response->assertSee('required', false);

        // Check for email field
        $response->assertSee('name="email"', false);
        $response->assertSee('id="email"', false);
        $response->assertSee('type="email"', false);

        // Check for role field
        $response->assertSee('name="role"', false);
        $response->assertSee('id="role"', false);

        // Check for password field
        $response->assertSee('name="password"', false);
        $response->assertSee('id="password"', false);
        $response->assertSee('type="password"', false);

        // Check for password confirmation field
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee('id="password_confirmation"', false);
    }

    /** @test */
    public function registration_form_has_role_options()
    {
        $response = $this->get(route('register'));

        $response->assertSee(__('Sell my creations (Creator)'));
        $response->assertSee(__('Buy and bid on items (Buyer)'));
        $response->assertSee('value="creator"', false);
        $response->assertSee('value="buyer"', false);
    }

    /** @test */
    public function registration_form_has_login_link()
    {
        $response = $this->get(route('register'));

        $response->assertSee(__('Already have an account?'));
        $response->assertSee(route('login'));
        $response->assertSee(__('Sign in'));
    }

    /** @test */
    public function user_can_register_as_creator()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'creator',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Creator',
            'email' => 'creator@example.com',
            'role' => UserRole::Creator->value,
        ]);

        $user = User::where('email', 'creator@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->hasRole(UserRole::Creator));

        $response->assertRedirect(route('creator.onboarding'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function user_can_register_as_buyer()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'role' => UserRole::Buyer->value,
        ]);

        $user = User::where('email', 'buyer@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->hasRole(UserRole::Buyer));

        $response->assertRedirect(route('welcome'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function user_is_automatically_logged_in_after_registration()
    {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('test@example.com', auth()->user()->email);
    }

    /** @test */
    public function registration_requires_name()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_email()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_valid_email()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_unique_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_password()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_password_confirmation()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_role()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => '',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_valid_role()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid-role',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /** @test */
    public function registration_does_not_allow_admin_role()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /** @test */
    public function registration_form_preserves_input_on_validation_error()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect();

        // Follow the redirect to see the form with old input
        $followUp = $this->get($response->headers->get('Location'));
        $followUp->assertSee('value="Test User"', false);
        $followUp->assertSee('value="invalid-email"', false);
    }

    /** @test */
    public function registration_form_displays_validation_errors()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
            'role' => '',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    /** @test */
    public function authenticated_users_cannot_access_registration_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect();
    }

    /** @test */
    public function authenticated_users_cannot_register()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('register'), [
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', [
            'email' => 'another@example.com',
        ]);
    }

    /** @test */
    public function registration_form_uses_auth_layout()
    {
        $response = $this->get(route('register'));

        $response->assertSee('layouts.auth', false);
        $response->assertSee(config('app.name'));
    }

    /** @test */
    public function registration_form_has_proper_accessibility_attributes()
    {
        $response = $this->get(route('register'));

        // Check for required attributes
        $response->assertSee('required', false);
        
        // Check for autocomplete attributes
        $response->assertSee('autocomplete="name"', false);
        $response->assertSee('autocomplete="email"', false);
        $response->assertSee('autocomplete="new-password"', false);
        
        // Check for aria labels on error messages
        $response->assertSee('role="alert"', false);
    }

    /** @test */
    public function registration_form_has_password_requirements_hint()
    {
        $response = $this->get(route('register'));

        $response->assertSee(__('Must be at least 8 characters long.'));
    }

    /** @test */
    public function registration_form_has_role_selection_hint()
    {
        $response = $this->get(route('register'));

        $response->assertSee(__('You can change this later in your profile settings.'));
    }

    /** @test */
    public function registration_form_has_terms_and_privacy_notice()
    {
        $response = $this->get(route('register'));

        $response->assertSee(__('By creating an account, you agree to our'));
        $response->assertSee(__('Terms of Service'));
        $response->assertSee(__('Privacy Policy'));
    }

    /** @test */
    public function registration_form_has_loading_attribute()
    {
        $response = $this->get(route('register'));

        $response->assertSee('data-loading', false);
    }

    /** @test */
    public function registration_name_field_has_max_length()
    {
        $longName = str_repeat('a', 256);

        $response = $this->post(route('register'), [
            'name' => $longName,
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /** @test */
    public function registration_email_field_has_max_length()
    {
        $longEmail = str_repeat('a', 250) . '@example.com';

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => $longEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
