<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a buyer can follow a creator through the User model.
     */
    public function test_user_can_follow_creator(): void
    {
        // Create a buyer and a creator
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $creatorShop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        // Buyer follows the creator
        $buyer->follows()->attach($creator->id);

        // Assert the relationship exists
        $this->assertTrue($buyer->follows->contains($creator));
        $this->assertEquals(1, $buyer->follows()->count());
    }

    /**
     * Test that a creator shop can retrieve its followers.
     */
    public function test_creator_shop_can_retrieve_followers(): void
    {
        // Create a creator with a shop
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $creatorShop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        // Create multiple buyers who follow the creator
        $buyer1 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer2 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer3 = User::factory()->create(['role' => UserRole::Buyer]);

        // Buyers follow the creator
        $buyer1->follows()->attach($creator->id);
        $buyer2->follows()->attach($creator->id);
        $buyer3->follows()->attach($creator->id);

        // Assert the creator shop can retrieve all followers
        $followers = $creatorShop->followers;
        $this->assertEquals(3, $followers->count());
        $this->assertTrue($followers->contains($buyer1));
        $this->assertTrue($followers->contains($buyer2));
        $this->assertTrue($followers->contains($buyer3));
    }

    /**
     * Test that getFollowerCount method returns accurate count.
     */
    public function test_get_follower_count_returns_accurate_count(): void
    {
        // Create a creator with a shop
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $creatorShop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        // Initially, no followers
        $this->assertEquals(0, $creatorShop->getFollowerCount());

        // Add followers
        $buyer1 = User::factory()->create(['role' => UserRole::Buyer]);
        $buyer2 = User::factory()->create(['role' => UserRole::Buyer]);

        $buyer1->follows()->attach($creator->id);
        $buyer2->follows()->attach($creator->id);

        // Refresh the model to get updated count
        $creatorShop->refresh();

        // Assert count is correct
        $this->assertEquals(2, $creatorShop->getFollowerCount());
    }

    /**
     * Test that unfollowing works correctly.
     */
    public function test_user_can_unfollow_creator(): void
    {
        // Create a buyer and a creator
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        $creatorShop = CreatorShop::factory()->create(['user_id' => $creator->id]);

        // Buyer follows the creator
        $buyer->follows()->attach($creator->id);
        $this->assertEquals(1, $buyer->follows()->count());

        // Buyer unfollows the creator
        $buyer->follows()->detach($creator->id);
        $this->assertEquals(0, $buyer->follows()->count());

        // Creator shop should have no followers
        $this->assertEquals(0, $creatorShop->getFollowerCount());
    }

    /**
     * Test that the unique constraint prevents duplicate follows.
     */
    public function test_unique_constraint_prevents_duplicate_follows(): void
    {
        // Create a buyer and a creator
        $buyer = User::factory()->create(['role' => UserRole::Buyer]);
        $creator = User::factory()->create(['role' => UserRole::Creator]);

        // Buyer follows the creator
        $buyer->follows()->attach($creator->id);

        // Attempting to follow again should not create a duplicate
        // Using syncWithoutDetaching to avoid exception
        $buyer->follows()->syncWithoutDetaching([$creator->id]);

        // Should still have only 1 follow relationship
        $this->assertEquals(1, $buyer->follows()->count());
    }
}
