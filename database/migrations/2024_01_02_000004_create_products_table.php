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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_id');
            $table->string('title');
            $table->text('description');
            $table->string('category', 100)->nullable();
            $table->decimal('reserve_price', 10, 2);
            $table->timestamp('auction_start');
            $table->timestamp('auction_end');
            $table->enum('status', ['draft', 'active', 'ended', 'sold', 'unsold'])->default('draft');
            $table->uuid('winning_bid_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('creator_id');
            $table->index('auction_end');
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
