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
        Schema::create('creator_private_info', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_shop_id')->unique();
            $table->string('stripe_account_id')->nullable();
            $table->text('tax_id')->nullable(); // Will be encrypted at application level
            $table->string('payout_email')->nullable();
            $table->timestamps();
            
            $table->foreign('creator_shop_id')->references('id')->on('creator_shops')->onDelete('cascade');
            $table->index('creator_shop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_private_info');
    }
};
