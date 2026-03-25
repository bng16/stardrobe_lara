<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

/**
 * Test suite for password reset functionality.
 * 
 * Tests Requirements:
 * - REQ-4.3.2: Implement password reset functionality
 * - REQ-1.3.2: Implement proper CSRF protection on all forms
 * - REQ-1.3.3: Maintain form validation with error display
 * - REQ-4.4.1: Display validation errors inline with form fields
 * - REQ-4.4.2: Preserve user input on form submission errors
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** @test */
    public function password_request_page_displays_correctly()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.passwords.email');
        $response->assertSee('Forgot your password?');
        $response->assertSee('Enter your email address and we\'ll send you a link to reset your password');
    }

    /** @test */
    public function password_request_form_contains_required_fields()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        
        // Check for email field
        $response->assertSee('Email address');
        $response->assertSee('name="email"', false);
        $response->assertSee('type="email"', false);
        $response->assertSee('required', false);
        
        // Check for submit button
        $response->assertSee('Send Password Reset Link');
    }

    /** @test */
    public function password_request_form_has_csrf_protection()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
        $response->assertSee('type="hidden"', false);
    }

    /** @test */
    public function password_request_form_has_back_to_login_link()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Remember your password?');
        $response->assertSee('Back to login');
    }

    /** @test */
    public function user_can_request_password_reset_link()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('password.email.sent'));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function password_reset_link_request_shows_confirmation_page()
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('password.email.sent'));
        
        $followUp = $this->get(route('password.email.sent'));
        $followUp->assertStatus(200);
        $followUp->assertViewIs('auth.passwords.email-sent');
        $followUp->assertSee('Check your email');
    }

    /** @test */
    public function email_sent_page_displays_correctly()
    {
        $response = $this->get(route('password.email.sent'));

        $response->assertStatus(200);
        $response->assertSee('Check your email');
        $response->assertSee('If an account exists with that email address');
        $response->assertSee('Didn\'t receive the email?');
        $response->assertSee('Try again');
    }

    /** @test */
    public function password_reset_request_validates_email_required()
    {
        $response = $this->post(route('password.email'), [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_reset_request_validates_email_format()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_reset_request_with_nonexistent_email_still_shows_confirmation()
    {
        // For security, we don't reveal if email exists
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect(route('password.email.sent'));
    }

    /** @test */
    public function password_reset_form_displays_correctly()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $response->assertStatus(200);
        $response->assertViewIs('auth.passwords.reset');
        $response->assertSee('Reset your password');
        $response->assertSee('Enter your new password below');
    }

    /** @test */
    public function password_reset_form_contains_required_fields()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $response->assertStatus(200);
        
        // Check for hidden token field
        $response->assertSee('name="token"', false);
        $response->assertSee('type="hidden"', false);
        $response->assertSee($token, false);
        
        // Check for email field
        $response->assertSee('Email address');
        $response->assertSee('name="email"', false);
        $response->assertSee('type="email"', false);
        
        // Check for password field
        $response->assertSee('New Password');
        $response->assertSee('name="password"', false);
        $response->assertSee('type="password"', false);
        
        // Check for password confirmation field
        $response->assertSee('Confirm New Password');
        $response->assertSee('name="password_confirmation"', false);
        
        // Check for submit button
        $response->assertSee('Reset Password');
    }

    /** @test */
    public function password_reset_form_has_csrf_protection()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token]));

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
    }

    /** @test */
    public function user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        
        // Verify password was changed
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function password_reset_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_reset_validates_token_required()
    {
        $response = $this->post(route('password.update'), [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('token');
    }

    /** @test */
    public function password_reset_validates_email_required()
    {
        $response = $this->post(route('password.update'), [
            'token' => 'some-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_reset_validates_email_format()
    {
        $response = $this->post(route('password.update'), [
            'token' => 'some-token',
            'email' => 'not-an-email',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_reset_validates_password_required()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_reset_validates_password_confirmation()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_reset_validates_password_minimum_length()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_reset_form_preserves_email_on_validation_error()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertEquals('test@example.com', old('email'));
    }

    /** @test */
    public function password_reset_form_displays_validation_errors_inline()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => 'not-an-email',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasErrors(['email', 'password']);
        
        // Follow redirect to see errors
        $followUp = $this->get(route('password.reset', ['token' => $token]));
        $followUp->assertSee('text-red-600', false);
    }

    /** @test */
    public function password_reset_form_has_proper_accessibility_attributes()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token]));

        $response->assertStatus(200);
        
        // Check for required attributes
        $response->assertSee('required', false);
        
        // Check for autocomplete attributes
        $response->assertSee('autocomplete="email"', false);
        $response->assertSee('autocomplete="new-password"', false);
        
        // Check for autofocus on email field
        $response->assertSee('autofocus', false);
    }

    /** @test */
    public function password_reset_form_displays_error_messages_with_role_alert()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasErrors('password');
        
        // Follow redirect to see error
        $followUp = $this->get(route('password.reset', ['token' => $token]));
        $followUp->assertSee('role="alert"', false);
    }

    /** @test */
    public function password_reset_pages_use_auth_layout()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        
        // Check for auth layout elements
        $response->assertSee('bg-gradient-to-br from-blue-50 to-indigo-100', false);
        $response->assertSee('shadow-xl', false);
    }

    /** @test */
    public function password_reset_forms_have_loading_state_attribute()
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee('data-loading', false);

        $user = User::factory()->create();
        $token = Password::createToken($user);
        
        $response2 = $this->get(route('password.reset', ['token' => $token]));
        $response2->assertStatus(200);
        $response2->assertSee('data-loading', false);
    }

    /** @test */
    public function authenticated_user_cannot_access_password_reset_pages()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.request'));
        $response->assertRedirect('/');

        $response2 = $this->actingAs($user)->get(route('password.email.sent'));
        $response2->assertRedirect('/');

        $token = Password::createToken($user);
        $response3 = $this->actingAs($user)->get(route('password.reset', ['token' => $token]));
        $response3->assertRedirect('/');
    }

    /** @test */
    public function password_reset_email_prefills_from_query_parameter()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => 'test@example.com'
        ]));

        $response->assertStatus(200);
        $response->assertSee('value="test@example.com"', false);
    }

    /** @test */
    public function remember_token_is_regenerated_after_password_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'remember_token' => 'old-token',
        ]);

        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertNotEquals('old-token', $user->fresh()->remember_token);
    }

    /** @test */
    public function password_reset_success_message_is_displayed()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        
        $followUp = $this->get(route('login'));
        $followUp->assertSee('Your password has been reset successfully');
    }

    /** @test */
    public function password_reset_token_can_only_be_used_once()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        // First reset succeeds
        $response1 = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response1->assertRedirect(route('login'));

        // Second reset with same token fails
        $response2 = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'anotherpassword123',
            'password_confirmation' => 'anotherpassword123',
        ]);

        $response2->assertSessionHasErrors('email');
    }
}
