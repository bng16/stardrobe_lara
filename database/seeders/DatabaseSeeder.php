<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\CreatorShop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create 10 creator users with shops
        $creators = User::factory()
            ->creator()
            ->count(10)
            ->create()
            ->each(function ($creator) {
                CreatorShop::factory()->create([
                    'user_id' => $creator->id,
                ]);
            });

        // Create 20 buyer users
        $buyers = User::factory()
            ->buyer()
            ->count(20)
            ->create();

        // Create 30 products with various statuses across creators
        $products = collect();
        foreach ($creators as $creator) {
            // Each creator gets 3 products with different statuses
            $products->push(
                Product::factory()->active()->create([
                    'creator_id' => $creator->id,
                ])
            );
            $products->push(
                Product::factory()->ended()->create([
                    'creator_id' => $creator->id,
                ])
            );
            $products->push(
                Product::factory()->draft()->create([
                    'creator_id' => $creator->id,
                ])
            );
        }

        // Create 100 bids across active and ended products
        $biddableProducts = $products->filter(function ($product) {
            return in_array($product->status->value, ['active', 'ended']);
        });

        foreach ($biddableProducts as $product) {
            // Each product gets 3-7 random bids from different buyers
            $bidCount = rand(3, 7);
            $randomBuyers = $buyers->random(min($bidCount, $buyers->count()));
            
            foreach ($randomBuyers as $buyer) {
                Bid::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => $buyer->id,
                    'amount' => fake()->randomFloat(2, $product->reserve_price, $product->reserve_price * 3),
                ]);
            }
        }

        // Create follow relationships (each buyer follows 2-5 random creators)
        foreach ($buyers as $buyer) {
            $followCount = rand(2, 5);
            $creatorsToFollow = $creators->random(min($followCount, $creators->count()));
            
            foreach ($creatorsToFollow as $creator) {
                $buyer->follows()->attach($creator->id);
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info("Created: 1 admin, {$creators->count()} creators, {$buyers->count()} buyers");
        $this->command->info("Created: {$products->count()} products with bids and follow relationships");
    }
}
