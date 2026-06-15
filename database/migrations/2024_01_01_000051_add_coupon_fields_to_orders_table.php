<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('original_amount')->nullable()->after('amount');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('original_amount');
            $table->foreignId('coupon_id')->nullable()->after('discount_amount')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['original_amount', 'discount_amount', 'coupon_id']);
        });
    }
};
