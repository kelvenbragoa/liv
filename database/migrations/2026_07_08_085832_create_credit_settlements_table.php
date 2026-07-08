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
        Schema::create('credit_settlements', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('payment_id');
            $table->integer('payment_method_id');
            $table->decimal('total', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('amount_remaining', 10, 2);
            $table->integer('user_id');
            $table->integer('customer_id');
            $table->integer('cash_register_id');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_settlements');
    }
};
