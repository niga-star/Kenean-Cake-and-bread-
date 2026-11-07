<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id('banner_id');
            $table->string('title_en');
            $table->string('title_am')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->string('subtitle_am')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_am')->nullable();
            $table->string('image_url')->nullable();
            $table->string('link_url')->default('/shop');
            $table->string('button_text_en')->default('Shop Now');
            $table->string('button_text_am')->default('አሁን ይግዙ');
            $table->string('badge_en')->default('Fresh Daily');
            $table->string('badge_am')->default('በየቀኑ ትኩስ');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id('wishlist_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id('subscription_id');
            $table->string('email')->unique();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('banners');
    }
};
