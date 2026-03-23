<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Bid;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'bid_id' => Bid::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'stripe_payment_id' => 'pi_' . $this->faker->uuid(),
            'status' => OrderStatus::Completed,
            'payment_deadline' => now()->addHours(48),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Pending,
            'payment_deadline' => now()->addHours(48),
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
        ]);
    }

    /**
     * Indicate that the order is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Expired,
            'payment_deadline' => now()->subHours(1),
        ]);
    }

    /**
     * Indicate that the order is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Refunded,
        ]);
    }
}
