<?php

namespace Database\Factories;

use App\Enums\AuctionStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $auctionStart = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $auctionEnd = $this->faker->dateTimeBetween($auctionStart, '+2 weeks');

        return [
            'creator_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['Art', 'Collectibles', 'Fashion', 'Electronics', 'Other']),
            'reserve_price' => $this->faker->randomFloat(2, 10, 1000),
            'auction_start' => $auctionStart,
            'auction_end' => $auctionEnd,
            'status' => AuctionStatus::Active,
        ];
    }

    /**
     * Indicate that the auction is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::Draft,
        ]);
    }

    /**
     * Indicate that the auction is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::Active,
            'auction_start' => now()->subHour(),
            'auction_end' => now()->addHour(),
        ]);
    }

    /**
     * Indicate that the auction has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::Ended,
            'auction_start' => now()->subHours(2),
            'auction_end' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the auction is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::Sold,
            'auction_start' => now()->subHours(2),
            'auction_end' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the auction is unsold.
     */
    public function unsold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::Unsold,
            'auction_start' => now()->subHours(2),
            'auction_end' => now()->subHour(),
        ]);
    }
}
