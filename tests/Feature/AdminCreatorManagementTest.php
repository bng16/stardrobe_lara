<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\SendCreatorInviteEmail;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminCreatorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_creator_management_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.creators.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_creator_management(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);

        $response = $this->actingAs($buyer)->get(route('admin.creators.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_creator_account_with_secure_password(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.creators.store'), [
            'name' => 'Test Creator',
            'email' => 'creator@example.com',
        ]);

        $response->assertRedirect(route('admin.creators.index'));
        $response->assertSessionHas('success');

        // Verify user was created with creator role
        $creator = User::where('email', 'creator@example.com')->first();
        $this->assertNotNull($creator);
        $this->assertEquals(UserRole::Creator, $creator->role);
        $this->assertEquals('Test Creator', $creator->name);

        // Verify password is hashed and secure (at least 16 characters when generated)
        $this->assertTrue(Hash::needsRehash($creator->password) === false);
    }

    public function test_creator_shop_is_created_when_account_is_created(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.creators.store'), [
            'name' => 'Test Creator',
            'email' => 'creator@example.com',
        ]);

        $creator = User::where('email', 'creator@example.com')->first();
        $shop = CreatorShop::where('user_id', $creator->id)->first();

        $this->assertNotNull($shop);
        $this->assertFalse($shop->is_onboarded);
    }

    public function test_invite_email_is_queued_when_creator_account_is_created(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.creators.store'), [
            'name' => 'Test Creator',
            'email' => 'creator@example.com',
        ]);

        Queue::assertPushed(SendCreatorInviteEmail::class, function ($job) {
            return $job->user->email === 'creator@example.com'
                && strlen($job->temporaryPassword) >= 16;
        });
    }

    public function test_creator_account_creation_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.creators.store'), [
            'name' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_creator_account_creation_validates_unique_email(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.creators.store'), [
            'name' => 'Test Creator',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_creator_list_displays_all_creators_with_shop_info(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        
        $creator1 = User::factory()->create(['role' => UserRole::Creator]);
        $shop1 = CreatorShop::factory()->create([
            'user_id' => $creator1->id,
            'shop_name' => 'Shop One',
            'is_onboarded' => true,
        ]);

        $creator2 = User::factory()->create(['role' => UserRole::Creator]);
        $shop2 = CreatorShop::factory()->create([
            'user_id' => $creator2->id,
            'shop_name' => null,
            'is_onboarded' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.creators.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Creators/Index')
            ->has('creators.data', 2)
        );
    }
}
