<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreatorShopBladeTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_shops_index_renders(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        $response = $this->get(route('creator-shop.index'));

        $response->assertOk();
        $response->assertSee('Creator Shops');
    }

    public function test_creator_shop_show_renders_for_onboarded_creator(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $shop = CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        $response = $this->get(route('creator-shop.show', $creator->id));

        $response->assertOk();
        $response->assertSee($shop->shop_name);
        $response->assertSee('Active Auctions');
    }

    public function test_authenticated_user_can_follow_and_unfollow_creator_from_shop_page(): void
    {
        Mail::fake();

        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        $follow = $this->actingAs($buyer)->post(route('creators.follow', $creator->id));
        $follow->assertRedirect();
        $this->assertTrue($buyer->fresh()->follows()->whereKey($creator->id)->exists());

        $unfollow = $this->actingAs($buyer)->delete(route('creators.unfollow', $creator->id));
        $unfollow->assertRedirect();
        $this->assertFalse($buyer->fresh()->follows()->whereKey($creator->id)->exists());
    }
}

