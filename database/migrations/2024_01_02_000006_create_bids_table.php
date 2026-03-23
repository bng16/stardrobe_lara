<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('user_id');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['user_id', 'product_id'], 'unique_user_product_bid');
            $table->index('product_id');
            $table->index('user_id');
            $table->index('amount');
        });
        
        // Add foreign key for winning_bid_id after bids table exists
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('winning_bid_id')->references('id')->on('bids')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['winning_bid_id']);
        });
        
        Schema::dropIfExists('bids');
    }
};
