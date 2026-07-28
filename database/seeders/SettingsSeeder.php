<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::seedDefaults();

        // Sync denormalized course counters with real enrollments/lessons.
        if (class_exists(\App\Models\Course::class)) {
            \App\Models\Course::query()->each(fn ($course) => $course->syncCounters());
        }

        $this->command->info('✅ تنظیمات پیش‌فرض ثبت شدند.');
    }
}
