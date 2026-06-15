<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->unsignedInteger('value'); // درصد یا مبلغ تومان
            $table->unsignedBigInteger('max_amount')->nullable(); // سقف تخفیف برای نوع درصدی
            $table->unsignedBigInteger('min_order_amount')->default(0); // حداقل مبلغ سفارش
            $table->unsignedInteger('usage_limit')->nullable(); // حداکثر تعداد استفاده
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('per_user_limit')->default(1); // حداکثر استفاده هر کاربر
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('discount_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
