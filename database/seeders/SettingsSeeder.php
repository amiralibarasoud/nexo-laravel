<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // SMS Settings
            ['key' => 'sms_api_key',       'value' => '',       'group' => 'sms'],
            ['key' => 'sms_template_id',   'value' => '238380', 'group' => 'sms'],
            ['key' => 'sms_sandbox',       'value' => '1',      'group' => 'sms'],
            ['key' => 'sms_sandbox_code',  'value' => '12345',  'group' => 'sms'],

            // Site Settings
            ['key' => 'site_name',         'value' => 'نکسو کورس', 'group' => 'site'],
            ['key' => 'site_support_phone','value' => '',           'group' => 'site'],
            ['key' => 'site_support_email','value' => 'info@nexocourse.ir', 'group' => 'site'],
        ];

        foreach ($defaults as $item) {
            Setting::firstOrCreate(['key' => $item['key']], $item);
        }

        $this->command->info('✅ تنظیمات پیش‌فرض ثبت شدند.');
    }
}
