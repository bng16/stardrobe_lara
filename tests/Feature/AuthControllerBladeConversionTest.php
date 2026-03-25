<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite to verify authentication controllers use Blade views instead of Inertia.
 * 
 * Tests Requirements:
 * - REQ-1.2.1: Replace all Inertia::render() calls with view() calls in controllers
 * - REQ-1.2.2: Modify data passing from Inertia props format to Blade view data format
 * - REQ-1.2.3: Ensure all existing controller functionality remains intact
 * - REQ-4.3.4: Redirect users appropriately after authentication
 */
class AuthControllerBladeConversionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_controller_returns_blade_view_not_inertia()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        
        // Ensure it's not an Inertia response
        $this->assertFalse($response->headers->has('X-Inertia'));
        $this->assertFalse($response->headers->has('X-Inertia-Version'));
    }

    /** @test */
    public function register_controller_returns_blade_view_not_inertia()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        
        // Ensure it's not an Inertia response
        $this->assertFalse($response->headers->has('X-Inertia'));
        $this->assertFalse($response->headers->has('X-Inertia-Version'));
    }

    /** @test */
    public function login_controller_handles_authentication_correctly()
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
        
        // Ensure it's a standard redirect, not an Inertia redirect
        $this->assertFalse($response->headers->has('X-Inertia-Location'));
    }

    /** @test */
    public function register_controller_handles_registration_correctly()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('welcome'));
        
        // Ensure it's a standard redirect, not an Inertia redirect
        $this->assertFalse($response->headers->has('X-Inertia-Location'));
        
        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function login_controller_redirects_based_on_role()
    {
        // Test admin redirect
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

        // Logout
        $this->post(route('logout'));

        // Test creator redirect
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
    public function register_controller_redirects_creator_to_onboarding()
    {
        $response = $this->post(route('register'), [
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'creator',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('creator.onboarding'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function logout_controller_handles_logout_correctly()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'You have been logged out successfully.');
        
        // Ensure it's a standard redirect, not an Inertia redirect
        $this->assertFalse($response->headers->has('X-Inertia-Location'));
    }

    /** @test */
    public function login_form_uses_traditional_post_not_inertia()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        
        // Check for traditional form action
        $response->assertSee('action="' . route('login') . '"', false);
        $response->assertSee('method="POST"', false);
        
        // Ensure no Inertia form components
        $this->assertStringNotContainsString('useForm', $response->getContent());
        $this->assertStringNotContainsString('@inertia', $response->getContent());
    }

    /** @test */
    public function register_form_uses_traditional_post_not_inertia()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        
        // Check for traditional form action
        $response->assertSee('action="' . route('register') . '"', false);
        $response->assertSee('method="POST"', false);
        
        // Ensure no Inertia form components
        $this->assertStringNotContainsString('useForm', $response->getContent());
        $this->assertStringNotContainsString('@inertia', $response->getContent());
    }

    /** @test */
    public function authentication_controllers_have_proper_csrf_protection()
    {
        // Login form
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertSee('name="_token"', false);

        // Register form
        $registerResponse = $this->get(route('register'));
        $registerResponse->assertSee('name="_token"', false);
    }

    /** @test */
    public function authentication_responses_are_html_not_json()
    {
        // Login page
        $loginResponse = $this->get(route('login'));
        $this->assertStringStartsWith('text/html', $loginResponse->headers->get('Content-Type'));

        // Register page
        $registerResponse = $this->get(route('register'));
        $this->assertStringStartsWith('text/html', $registerResponse->headers->get('Content-Type'));
    }
}
