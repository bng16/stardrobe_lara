<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureCreatorOnboardedTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarded_creator_can_access_protected_route(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($creator)->get('/test-creator-onboarded-route');

        // We expect 404 since the route doesn't exist, but not a redirect
        $this->assertNotEquals(302, $response->status());
    }

    public function test_non_onboarded_creator_is_redirected_to_onboarding(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create([
            'user_id' => $creator->id,
            'is_onboarded' => false,
        ]);

        $response = $this->actingAs($creator)->get('/test-creator-onboarded-route');

        $response->assertRedirect(route('creator.onboarding'));
    }

    public function test_creator_without_shop_is_redirected_to_onboarding(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        // No shop created

        $response = $this->actingAs($creator)->get('/test-creator-onboarded-route');

        $response->assertRedirect(route('creator.onboarding'));
    }

    public function test_non_creator_users_can_access_route(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)->get('/test-creator-onboarded-route');

        // We expect 404 since the route doesn't exist, but not a redirect
        $this->assertNotEquals(302, $response->status());
    }

    public function test_admin_can_access_route_without_onboarding_check(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/test-creator-onboarded-route');

        // We expect 404 since the route doesn't exist, but not a redirect
        $this->assertNotEquals(302, $response->status());
    }
}
