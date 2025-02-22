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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('order_item_status_id');
            $table->unsignedBigInteger('user_id');
            $table->double('price',15,2);
            $table->double('total',15,2);
            $table->unsignedBigInteger('cash_register_id')->default(0);
            $table->unsignedBigInteger('prepared_by_user_id')->nullable();
            $table->unsignedBigInteger('ready_by_user_id')->nullable();
            $table->unsignedBigInteger('delivered_by_user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
