<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action', 64);
            $table->unsignedTinyInteger('status_code')->default(200);
            $table->json('response_body');
            $table->timestamps();

            $table->index(['user_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
