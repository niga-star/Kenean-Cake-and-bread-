<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('received_stocks', function (Blueprint $table) {
            $table->id('received_stock_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->string('category_name');
            $table->integer('quantity_received');
            $table->date('date_received');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('branch_id')->on('branches')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('received_by')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['branch_id', 'date_received']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('received_stocks');
    }
};

