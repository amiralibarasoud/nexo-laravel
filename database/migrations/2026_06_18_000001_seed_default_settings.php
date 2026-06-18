<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::seedDefaults();
    }

    public function down(): void
    {
        // تنظیمات ممکن است توسط ادمین ویرایش شده باشند؛ در rollback حذف نمی‌شوند.
    }
};
