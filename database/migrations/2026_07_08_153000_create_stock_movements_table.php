<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_center_id');
            $table->unsignedBigInteger('product_id');
            $table->string('direction', 10); // in | out
            $table->unsignedInteger('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reason', 64); // sale, sale_cancel, adjustment, ...
            $table->string('reference_type', 64)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_center_id', 'product_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
