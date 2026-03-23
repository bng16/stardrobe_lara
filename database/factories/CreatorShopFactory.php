<?php

namespace Database\Factories;

use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreatorShop>
 */
class CreatorShopFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CreatorShop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shop_name' => fake()->company(),
            'bio' => fake()->paragraph(),
            'profile_image' => null,
            'banner_image' => null,
            'is_onboarded' => true,
        ];
    }

    /**
     * Indicate that the creator shop is not yet onboarded.
     */
    public function notOnboarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_onboarded' => false,
        ]);
    }
}
