<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('amount'); // in Toman
            $table->enum('content_type', ['text', 'audio', 'both'])->default('text');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway')->comment('zarinpal|zibal');
            $table->string('authority')->nullable();
            $table->string('ref_id')->nullable()->comment('Reference ID from gateway');
            $table->string('card_number')->nullable();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['initiated', 'success', 'failed', 'cancelled'])->default('initiated');
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('content_type', ['text', 'audio', 'both'])->default('text');
            $table->timestamp('enrolled_at');
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('orders');
    }
};
