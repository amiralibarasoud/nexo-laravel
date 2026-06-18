<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        Cache::forget("setting:{$key}");
        Cache::forget("settings:group:{$group}");
    }

    public static function setMany(array $data, string $group = 'general'): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $val = static::get($key);
        if ($val === null) {
            return $default;
        }

        return in_array($val, ['1', 'true', true, 1], true);
    }

    public static function getJson(string $key, array $default = []): array
    {
        $val = static::get($key);

        if ($val === null || $val === '') {
            return $default;
        }

        $decoded = json_decode($val, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function getGroup(string $group): array
    {
        return Cache::remember("settings:group:{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function defaultNavLinks(): array
    {
        return [
            ['label' => 'خانه', 'route_name' => 'home', 'url' => '', 'visible' => true],
            ['label' => 'دوره‌ها', 'route_name' => 'courses.index', 'url' => '', 'visible' => true],
            ['label' => 'بلاگ', 'route_name' => 'blog.index', 'url' => '', 'visible' => true],
            ['label' => 'درباره ما', 'route_name' => 'about', 'url' => '', 'visible' => true],
            ['label' => 'تماس', 'route_name' => 'contact', 'url' => '', 'visible' => true],
        ];
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            ['key' => 'sms_api_key', 'value' => '', 'group' => 'sms'],
            ['key' => 'sms_template_id', 'value' => '238380', 'group' => 'sms'],
            ['key' => 'sms_sandbox', 'value' => '1', 'group' => 'sms'],
            ['key' => 'sms_sandbox_code', 'value' => '12345', 'group' => 'sms'],
            ['key' => 'site_name', 'value' => 'نکسو کورس', 'group' => 'site'],
            ['key' => 'site_support_phone', 'value' => '', 'group' => 'site'],
            ['key' => 'site_support_email', 'value' => 'info@nexocourse.ir', 'group' => 'site'],
            ['key' => 'header_logo', 'value' => '', 'group' => 'theme'],
            ['key' => 'header_logo_letter', 'value' => 'N', 'group' => 'theme'],
            ['key' => 'header_show_text_logo', 'value' => '1', 'group' => 'theme'],
            ['key' => 'header_site_name', 'value' => 'نکسو', 'group' => 'theme'],
            ['key' => 'header_site_name_highlight', 'value' => 'کورس', 'group' => 'theme'],
            ['key' => 'header_logo_position', 'value' => 'start', 'group' => 'theme'],
            ['key' => 'header_sticky', 'value' => '1', 'group' => 'theme'],
            ['key' => 'header_login_text', 'value' => 'ورود / ثبت‌نام', 'group' => 'theme'],
            ['key' => 'header_nav_links', 'value' => json_encode(static::defaultNavLinks(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'header_announcement_enabled', 'value' => '0', 'group' => 'theme'],
            ['key' => 'header_announcement_text', 'value' => '', 'group' => 'theme'],
            ['key' => 'header_announcement_link', 'value' => '', 'group' => 'theme'],
            ['key' => 'header_widgets', 'value' => '[]', 'group' => 'theme'],
        ];

        foreach ($defaults as $item) {
            static::firstOrCreate(['key' => $item['key']], $item);
        }
    }

    public static function headerConfig(): array
    {
        $logo = static::get('header_logo');

        return [
            'logo'                  => $logo ? asset('storage/' . ltrim($logo, '/')) : null,
            'logo_letter'           => static::get('header_logo_letter', 'N'),
            'show_text_logo'        => static::getBool('header_show_text_logo', true),
            'site_name'             => static::get('header_site_name', 'نکسو'),
            'site_name_highlight'   => static::get('header_site_name_highlight', 'کورس'),
            'logo_position'         => static::get('header_logo_position', 'start'),
            'sticky'                => static::getBool('header_sticky', true),
            'login_text'            => static::get('header_login_text', 'ورود / ثبت‌نام'),
            'nav_links'             => static::getJson('header_nav_links', static::defaultNavLinks()),
            'announcement'          => [
                'enabled' => static::getBool('header_announcement_enabled', false),
                'text'    => static::get('header_announcement_text', ''),
                'link'    => static::get('header_announcement_link', ''),
            ],
            'widgets'               => static::getJson('header_widgets', []),
        ];
    }
}
