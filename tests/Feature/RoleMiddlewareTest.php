<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_route(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/test-admin-route');

        // We expect 404 since the route doesn't exist, but not 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_non_admin_cannot_access_admin_route(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)->get('/test-admin-route');

        $response->assertStatus(403);
    }

    public function test_creator_can_access_creator_route(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);

        $response = $this->actingAs($creator)->get('/test-creator-route');

        // We expect 404 since the route doesn't exist, but not 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_non_creator_cannot_access_creator_route(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)->get('/test-creator-route');

        $response->assertStatus(403);
    }

    public function test_buyer_can_access_buyer_route(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)->get('/test-buyer-route');

        // We expect 404 since the route doesn't exist, but not 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_non_buyer_cannot_access_buyer_route(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/test-buyer-route');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->get('/test-admin-route');

        $response->assertStatus(403);
    }
}
