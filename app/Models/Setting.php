<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

    public static function defaultFooterLinks(): array
    {
        return [
            ['label' => 'خانه', 'route_name' => 'home', 'url' => '', 'visible' => true],
            ['label' => 'دوره‌ها', 'route_name' => 'courses.index', 'url' => '', 'visible' => true],
            ['label' => 'درباره ما', 'route_name' => 'about', 'url' => '', 'visible' => true],
            ['label' => 'قوانین و مقررات', 'route_name' => 'terms', 'url' => '', 'visible' => true],
        ];
    }

    public static function defaultHomeStats(): array
    {
        return [
            ['type' => 'dynamic_courses', 'value' => '', 'label' => 'دوره آموزشی', 'suffix' => '+'],
            ['type' => 'dynamic_students', 'value' => '', 'label' => 'دانش‌آموز', 'suffix' => '+'],
            ['type' => 'manual', 'value' => '2', 'label' => 'فرمت محتوا', 'suffix' => ''],
        ];
    }

    public static function defaultHomeSteps(): array
    {
        return [
            ['emoji' => '🔍', 'title' => 'دوره مورد نظر را انتخاب کن', 'desc' => 'از میان دوره‌های متنوع، دوره‌ای که به آن نیاز داری را پیدا کن.', 'bg' => 'bg-blue-50'],
            ['emoji' => '💳', 'title' => 'فرمت و پرداخت', 'desc' => 'نوع محتوا (متنی یا صوتی) را انتخاب کن و با درگاه امن پرداخت کن.', 'bg' => 'bg-green-50'],
            ['emoji' => '🚀', 'title' => 'شروع یادگیری', 'desc' => 'فوری دسترسی بگیر و با هر دستگاهی یاد بگیر.', 'bg' => 'bg-purple-50'],
        ];
    }

    public static function defaultHomeContentCards(): array
    {
        return [
            [
                'emoji' => '📄',
                'title' => 'محتوای متنی',
                'items' => ['خواندن راحت و سریع', 'امکان جستجو و مرور مجدد', 'مناسب برای محیط‌های ساکت'],
            ],
            [
                'emoji' => '🎧',
                'title' => 'محتوای صوتی',
                'items' => ['یادگیری در حین رانندگی', 'پخش آنلاین بدون دانلود', 'صدای واضح و حرفه‌ای استاد'],
            ],
        ];
    }

    public static function defaultContactInfoItems(): array
    {
        return [
            ['icon' => '📧', 'title' => 'ایمیل', 'value' => 'info@nexocourse.ir', 'bg' => 'bg-blue-50', 'visible' => true],
            ['icon' => '📱', 'title' => 'پشتیبانی', 'value' => 'از طریق فرم تماس', 'bg' => 'bg-green-50', 'visible' => true],
            ['icon' => '⏰', 'title' => 'ساعت پاسخگویی', 'value' => 'شنبه تا پنجشنبه ۹ تا ۱۸', 'bg' => 'bg-purple-50', 'visible' => true],
        ];
    }

    public static function defaultAboutStats(): array
    {
        return [
            ['value' => '۱۰+', 'label' => 'دوره آموزشی'],
            ['value' => '۵۰۰+', 'label' => 'دانش‌آموز'],
            ['value' => '۲', 'label' => 'فرمت محتوا'],
            ['value' => '۱۰۰٪', 'label' => 'فارسی'],
        ];
    }

    public static function defaultAboutValues(): array
    {
        return [
            ['icon' => '🎯', 'title' => 'کاربردی', 'desc' => 'محتوای ما بر اساس نیاز واقعی بازار کار طراحی شده.'],
            ['icon' => '🌍', 'title' => 'فارسی‌زبان', 'desc' => 'تمام محتوا به زبان فارسی و متناسب با فرهنگ ایران.'],
            ['icon' => '💡', 'title' => 'نوآورانه', 'desc' => 'اولین پلتفرم با محتوای همزمان متنی و صوتی.'],
            ['icon' => '🤝', 'title' => 'حمایت‌گر', 'desc' => 'پشتیبانی کامل در طول مسیر یادگیری شما.'],
        ];
    }

    public static function pageThemeSeedDefaults(): array
    {
        return [
            ['key' => 'contact_seo_title', 'value' => 'تماس با ما', 'group' => 'theme'],
            ['key' => 'contact_page_title', 'value' => 'تماس با ما', 'group' => 'theme'],
            ['key' => 'contact_page_subtitle', 'value' => 'هر سوالی داری، خوشحال می‌شیم پاسخ بدیم.', 'group' => 'theme'],
            ['key' => 'contact_info_items', 'value' => json_encode(static::defaultContactInfoItems(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'contact_form_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'contact_form_title', 'value' => 'ارسال پیام', 'group' => 'theme'],
            ['key' => 'contact_form_name_label', 'value' => 'نام و نام خانوادگی *', 'group' => 'theme'],
            ['key' => 'contact_form_mobile_label', 'value' => 'شماره موبایل *', 'group' => 'theme'],
            ['key' => 'contact_form_subject_label', 'value' => 'موضوع *', 'group' => 'theme'],
            ['key' => 'contact_form_message_label', 'value' => 'پیام *', 'group' => 'theme'],
            ['key' => 'contact_form_name_placeholder', 'value' => 'نام شما', 'group' => 'theme'],
            ['key' => 'contact_form_mobile_placeholder', 'value' => '09xxxxxxxxx', 'group' => 'theme'],
            ['key' => 'contact_form_subject_placeholder', 'value' => 'موضوع پیام', 'group' => 'theme'],
            ['key' => 'contact_form_message_placeholder', 'value' => 'پیام خود را بنویسید...', 'group' => 'theme'],
            ['key' => 'contact_form_submit_text', 'value' => 'ارسال پیام', 'group' => 'theme'],
            ['key' => 'contact_form_loading_text', 'value' => 'در حال ارسال...', 'group' => 'theme'],
            ['key' => 'contact_form_success_message', 'value' => 'پیام شما دریافت شد. به زودی با شما تماس می‌گیریم.', 'group' => 'theme'],
            ['key' => 'about_seo_title', 'value' => 'درباره ما', 'group' => 'theme'],
            ['key' => 'about_hero_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'about_hero_title', 'value' => 'درباره نکسو کورس', 'group' => 'theme'],
            ['key' => 'about_hero_description', 'value' => 'ما یک پلتفرم آموزشی ایرانی هستیم که هدفمان ارائه آموزش‌های کاربردی به زبان فارسی است.', 'group' => 'theme'],
            ['key' => 'about_hero_image', 'value' => '', 'group' => 'theme'],
            ['key' => 'about_mission_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'about_mission_title', 'value' => 'مأموریت ما', 'group' => 'theme'],
            ['key' => 'about_mission_paragraph1', 'value' => 'نکسو کورس با هدف دسترسی آسان‌تر به آموزش‌های باکیفیت فارسی تأسیس شد. ما بر این باوریم که هر فردی باید بتواند با هر بودجه و در هر شرایطی، به بهترین آموزش‌ها دسترسی داشته باشد.', 'group' => 'theme'],
            ['key' => 'about_mission_paragraph2', 'value' => 'نوآوری اصلی ما در ارائه محتوا به دو فرمت متنی و صوتی است — شما انتخاب می‌کنید که چطور یاد بگیرید.', 'group' => 'theme'],
            ['key' => 'about_mission_image', 'value' => '', 'group' => 'theme'],
            ['key' => 'about_mission_stats', 'value' => json_encode(static::defaultAboutStats(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'about_values_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'about_values_title', 'value' => 'ارزش‌های ما', 'group' => 'theme'],
            ['key' => 'about_values', 'value' => json_encode(static::defaultAboutValues(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'about_cta_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'about_cta_title', 'value' => 'آماده شروع هستی؟', 'group' => 'theme'],
            ['key' => 'about_cta_subtitle', 'value' => 'همین الان اولین دوره‌ات رو شروع کن.', 'group' => 'theme'],
            ['key' => 'about_cta_button_text', 'value' => 'مشاهده دوره‌ها', 'group' => 'theme'],
            ['key' => 'about_cta_button_route', 'value' => 'courses.index', 'group' => 'theme'],
        ];
    }

    public static function themeSeedDefaults(): array
    {
        return [
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
            ['key' => 'home_hero_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_hero_badge', 'value' => '✨ بهترین پلتفرم یادگیری آنلاین فارسی', 'group' => 'theme'],
            ['key' => 'home_hero_title_before', 'value' => 'یادگیری با', 'group' => 'theme'],
            ['key' => 'home_hero_highlight1', 'value' => 'صدای', 'group' => 'theme'],
            ['key' => 'home_hero_title_middle', 'value' => 'استاد یا', 'group' => 'theme'],
            ['key' => 'home_hero_highlight2', 'value' => 'متن', 'group' => 'theme'],
            ['key' => 'home_hero_title_suffix', 'value' => 'انتخاب با توست.', 'group' => 'theme'],
            ['key' => 'home_hero_description', 'value' => 'دوره‌های کاربردی به دو فرمت متنی و صوتی. بعد از خرید، هر طور که راحت‌تری یاد بگیر.', 'group' => 'theme'],
            ['key' => 'home_hero_cta_text', 'value' => 'مشاهده دوره‌ها', 'group' => 'theme'],
            ['key' => 'home_hero_cta_route', 'value' => 'courses.index', 'group' => 'theme'],
            ['key' => 'home_hero_image', 'value' => '', 'group' => 'theme'],
            ['key' => 'home_stats_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_stats', 'value' => json_encode(static::defaultHomeStats(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'home_steps_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_steps_title', 'value' => 'چطور کار می‌کنه؟', 'group' => 'theme'],
            ['key' => 'home_steps_subtitle', 'value' => 'در چند قدم ساده شروع کن', 'group' => 'theme'],
            ['key' => 'home_steps', 'value' => json_encode(static::defaultHomeSteps(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'home_categories_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_categories_title', 'value' => 'دسته‌بندی‌ها', 'group' => 'theme'],
            ['key' => 'home_featured_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_featured_title', 'value' => 'دوره‌های ویژه', 'group' => 'theme'],
            ['key' => 'home_featured_subtitle', 'value' => 'بهترین دوره‌ها برای شما', 'group' => 'theme'],
            ['key' => 'home_featured_link_text', 'value' => 'مشاهده همه', 'group' => 'theme'],
            ['key' => 'home_blog_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_blog_title', 'value' => 'آخرین مقالات', 'group' => 'theme'],
            ['key' => 'home_blog_subtitle', 'value' => 'بخوان، یاد بگیر، رشد کن', 'group' => 'theme'],
            ['key' => 'home_blog_link_text', 'value' => 'همه مقالات', 'group' => 'theme'],
            ['key' => 'home_content_types_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'home_content_types_title', 'value' => 'دو راه برای یادگیری', 'group' => 'theme'],
            ['key' => 'home_content_types_subtitle', 'value' => 'بعد از خرید، خودت انتخاب می‌کنی', 'group' => 'theme'],
            ['key' => 'home_content_cards', 'value' => json_encode(static::defaultHomeContentCards(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'footer_logo', 'value' => '', 'group' => 'theme'],
            ['key' => 'footer_logo_letter', 'value' => 'N', 'group' => 'theme'],
            ['key' => 'footer_site_name', 'value' => 'نکسو کورس', 'group' => 'theme'],
            ['key' => 'footer_description', 'value' => 'پلتفرم یادگیری آنلاین با بهترین دوره‌های متنی و صوتی. یادگیری را به شیوه‌ای جدید تجربه کنید.', 'group' => 'theme'],
            ['key' => 'footer_links_title', 'value' => 'دسترسی سریع', 'group' => 'theme'],
            ['key' => 'footer_links', 'value' => json_encode(static::defaultFooterLinks(), JSON_UNESCAPED_UNICODE), 'group' => 'theme'],
            ['key' => 'footer_contact_title', 'value' => 'تماس با ما', 'group' => 'theme'],
            ['key' => 'footer_email', 'value' => 'info@nexocourse.ir', 'group' => 'theme'],
            ['key' => 'footer_phone', 'value' => '', 'group' => 'theme'],
            ['key' => 'footer_address', 'value' => 'قم ـ بلوار جمهوری اسلامی ـ کوچه ۳۶ ـ ساختمان آسیا ـ طبقه دوم', 'group' => 'theme'],
            ['key' => 'footer_show_contact_link', 'value' => '1', 'group' => 'theme'],
            ['key' => 'footer_contact_link_text', 'value' => 'فرم تماس', 'group' => 'theme'],
            ['key' => 'footer_enamad_enabled', 'value' => '1', 'group' => 'theme'],
            ['key' => 'footer_enamad_html', 'value' => '', 'group' => 'theme'],
            ['key' => 'footer_copyright', 'value' => 'تمامی حقوق برای نکسو کورس محفوظ است © {year}', 'group' => 'theme'],
            ...static::pageThemeSeedDefaults(),
        ];
    }

    protected static function storageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    protected static function trustSealHtml(string $key): string
    {
        $html = (string) static::get($key, '');
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (str_contains($html, '&lt;') || str_contains($html, '&gt;')) {
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return trim($html);
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

    public static function paymentConfig(): array
    {
        $zarinpalMerchant = static::get('zarinpal_merchant_id');
        $zibalMerchant    = static::get('zibal_merchant');

        return [
            'zarinpal' => [
                'enabled'     => static::paymentGatewayEnabled('zarinpal'),
                'merchant_id' => ! empty($zarinpalMerchant) ? $zarinpalMerchant : (string) (config('services.zarinpal.merchant_id') ?? ''),
                'sandbox'     => static::get('zarinpal_sandbox') !== null
                    ? static::getBool('zarinpal_sandbox', true)
                    : (bool) (config('services.zarinpal.sandbox') ?? true),
            ],
            'zibal' => [
                'enabled'  => static::paymentGatewayEnabled('zibal'),
                'merchant' => ! empty($zibalMerchant) ? $zibalMerchant : (string) (config('services.zibal.merchant') ?? 'zibal'),
            ],
        ];
    }

    public static function paymentGatewayEnabled(string $gateway): bool
    {
        $key = "{$gateway}_enabled";

        if (static::get($key) !== null) {
            return static::getBool($key, false);
        }

        return match ($gateway) {
            'zarinpal' => (bool) (config('services.zarinpal.enabled') ?? false),
            'zibal'    => true,
            default    => false,
        };
    }

    public static function enabledGateways(): array
    {
        $config   = static::paymentConfig();
        $gateways = [];

        if ($config['zibal']['enabled']) {
            $gateways[] = 'zibal';
        }

        if ($config['zarinpal']['enabled']) {
            $gateways[] = 'zarinpal';
        }

        return $gateways;
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            ['key' => 'sms_api_key', 'value' => '', 'group' => 'sms'],
            ['key' => 'sms_template_id', 'value' => '238380', 'group' => 'sms'],
            ['key' => 'sms_sandbox', 'value' => '1', 'group' => 'sms'],
            ['key' => 'sms_sandbox_code', 'value' => '12345', 'group' => 'sms'],
            ['key' => 'zarinpal_enabled', 'value' => '0', 'group' => 'payment'],
            ['key' => 'zarinpal_merchant_id', 'value' => '', 'group' => 'payment'],
            ['key' => 'zarinpal_sandbox', 'value' => '1', 'group' => 'payment'],
            ['key' => 'zibal_enabled', 'value' => '1', 'group' => 'payment'],
            ['key' => 'zibal_merchant', 'value' => 'zibal', 'group' => 'payment'],
            ['key' => 'site_name', 'value' => 'نکسو کورس', 'group' => 'site'],
            ['key' => 'site_support_phone', 'value' => '', 'group' => 'site'],
            ['key' => 'site_support_email', 'value' => 'info@nexocourse.ir', 'group' => 'site'],
            ...static::themeSeedDefaults(),
        ];

        foreach ($defaults as $item) {
            static::firstOrCreate(['key' => $item['key']], $item);
        }
    }

    public static function headerConfig(): array
    {
        $logo = static::get('header_logo');

        return [
            'logo'                  => static::storageUrl($logo),
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

    public static function homepageConfig(): array
    {
        return [
            'hero' => [
                'enabled'        => static::getBool('home_hero_enabled', true),
                'badge'          => static::get('home_hero_badge', ''),
                'title_before'   => static::get('home_hero_title_before', 'یادگیری با'),
                'highlight1'     => static::get('home_hero_highlight1', 'صدای'),
                'title_middle'   => static::get('home_hero_title_middle', 'استاد یا'),
                'highlight2'     => static::get('home_hero_highlight2', 'متن'),
                'title_suffix'   => static::get('home_hero_title_suffix', 'انتخاب با توست.'),
                'description'    => static::get('home_hero_description', ''),
                'cta_text'       => static::get('home_hero_cta_text', 'مشاهده دوره‌ها'),
                'cta_route'      => static::get('home_hero_cta_route', 'courses.index'),
                'image'          => static::storageUrl(static::get('home_hero_image')),
            ],
            'stats' => [
                'enabled' => static::getBool('home_stats_enabled', true),
                'items'   => static::getJson('home_stats', static::defaultHomeStats()),
            ],
            'steps' => [
                'enabled'  => static::getBool('home_steps_enabled', true),
                'title'    => static::get('home_steps_title', 'چطور کار می‌کنه؟'),
                'subtitle' => static::get('home_steps_subtitle', 'در چند قدم ساده شروع کن'),
                'items'    => static::getJson('home_steps', static::defaultHomeSteps()),
            ],
            'categories' => [
                'enabled' => static::getBool('home_categories_enabled', true),
                'title'   => static::get('home_categories_title', 'دسته‌بندی‌ها'),
            ],
            'featured' => [
                'enabled'   => static::getBool('home_featured_enabled', true),
                'title'     => static::get('home_featured_title', 'دوره‌های ویژه'),
                'subtitle'  => static::get('home_featured_subtitle', 'بهترین دوره‌ها برای شما'),
                'link_text' => static::get('home_featured_link_text', 'مشاهده همه'),
            ],
            'blog' => [
                'enabled'   => static::getBool('home_blog_enabled', true),
                'title'     => static::get('home_blog_title', 'آخرین مقالات'),
                'subtitle'  => static::get('home_blog_subtitle', 'بخوان، یاد بگیر، رشد کن'),
                'link_text' => static::get('home_blog_link_text', 'همه مقالات'),
            ],
            'content_types' => [
                'enabled'  => static::getBool('home_content_types_enabled', true),
                'title'    => static::get('home_content_types_title', 'دو راه برای یادگیری'),
                'subtitle' => static::get('home_content_types_subtitle', 'بعد از خرید، خودت انتخاب می‌کنی'),
                'cards'    => static::getJson('home_content_cards', static::defaultHomeContentCards()),
            ],
        ];
    }

    public static function footerConfig(): array
    {
        $logo = static::get('footer_logo');

        return [
            'logo'               => static::storageUrl($logo),
            'logo_letter'        => static::get('footer_logo_letter', 'N'),
            'site_name'          => static::get('footer_site_name', 'نکسو کورس'),
            'description'        => static::get('footer_description', ''),
            'links_title'        => static::get('footer_links_title', 'دسترسی سریع'),
            'links'              => static::getJson('footer_links', static::defaultFooterLinks()),
            'contact_title'      => static::get('footer_contact_title', 'تماس با ما'),
            'email'              => static::get('footer_email', ''),
            'phone'              => static::get('footer_phone', ''),
            'address'            => static::get('footer_address', 'قم ـ بلوار جمهوری اسلامی ـ کوچه ۳۶ ـ ساختمان آسیا ـ طبقه دوم'),
            'show_contact_link'  => static::getBool('footer_show_contact_link', true),
            'contact_link_text'  => static::get('footer_contact_link_text', 'فرم تماس'),
            'enamad_enabled'     => static::getBool('footer_enamad_enabled', true),
            'enamad_html'        => static::trustSealHtml('footer_enamad_html'),
            'copyright'          => static::get('footer_copyright', 'تمامی حقوق برای نکسو کورس محفوظ است © {year}'),
        ];
    }

    public static function contactConfig(): array
    {
        return [
            'seo_title'    => static::get('contact_seo_title', 'تماس با ما'),
            'page_title'   => static::get('contact_page_title', 'تماس با ما'),
            'subtitle'     => static::get('contact_page_subtitle', ''),
            'info_items'   => static::getJson('contact_info_items', static::defaultContactInfoItems()),
            'form'         => [
                'enabled'              => static::getBool('contact_form_enabled', true),
                'title'                => static::get('contact_form_title', 'ارسال پیام'),
                'name_label'           => static::get('contact_form_name_label', 'نام و نام خانوادگی *'),
                'mobile_label'         => static::get('contact_form_mobile_label', 'شماره موبایل *'),
                'subject_label'        => static::get('contact_form_subject_label', 'موضوع *'),
                'message_label'        => static::get('contact_form_message_label', 'پیام *'),
                'name_placeholder'     => static::get('contact_form_name_placeholder', 'نام شما'),
                'mobile_placeholder'   => static::get('contact_form_mobile_placeholder', '09xxxxxxxxx'),
                'subject_placeholder'  => static::get('contact_form_subject_placeholder', 'موضوع پیام'),
                'message_placeholder'  => static::get('contact_form_message_placeholder', 'پیام خود را بنویسید...'),
                'submit_text'          => static::get('contact_form_submit_text', 'ارسال پیام'),
                'loading_text'         => static::get('contact_form_loading_text', 'در حال ارسال...'),
                'success_message'      => static::get('contact_form_success_message', 'پیام شما دریافت شد. به زودی با شما تماس می‌گیریم.'),
            ],
        ];
    }

    public static function aboutConfig(): array
    {
        return [
            'seo_title' => static::get('about_seo_title', 'درباره ما'),
            'hero'      => [
                'enabled'     => static::getBool('about_hero_enabled', true),
                'title'       => static::get('about_hero_title', 'درباره نکسو کورس'),
                'description' => static::get('about_hero_description', ''),
                'image'       => static::storageUrl(static::get('about_hero_image')),
            ],
            'mission'   => [
                'enabled'    => static::getBool('about_mission_enabled', true),
                'title'      => static::get('about_mission_title', 'مأموریت ما'),
                'paragraph1' => static::get('about_mission_paragraph1', ''),
                'paragraph2' => static::get('about_mission_paragraph2', ''),
                'image'      => static::storageUrl(static::get('about_mission_image')),
                'stats'      => static::getJson('about_mission_stats', static::defaultAboutStats()),
            ],
            'values'    => [
                'enabled' => static::getBool('about_values_enabled', true),
                'title'   => static::get('about_values_title', 'ارزش‌های ما'),
                'items'   => static::getJson('about_values', static::defaultAboutValues()),
            ],
            'cta'       => [
                'enabled'     => static::getBool('about_cta_enabled', true),
                'title'       => static::get('about_cta_title', 'آماده شروع هستی؟'),
                'subtitle'    => static::get('about_cta_subtitle', ''),
                'button_text' => static::get('about_cta_button_text', 'مشاهده دوره‌ها'),
                'button_route'=> static::get('about_cta_button_route', 'courses.index'),
            ],
        ];
    }
}
