<?php
/**
 * nexovest-fix.php — اسکریپت یک‌بار مصرف برای اصلاح دیتابیس و کش
 * بعد از اجرا این فایل را حذف کنید!
 * آدرس: https://yourdomain.com/nexovest-fix.php?token=nexovest2026
 */

if (($_GET['token'] ?? '') !== 'nexovest2026') {
    http_response_code(403);
    die('<h2 style="font-family:Arial">دسترسی غیرمجاز — توکن اشتباه است.</h2>');
}

header('Content-Type: text/html; charset=utf-8');

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo '<!DOCTYPE html><html dir="rtl" lang="fa">
<head><meta charset="utf-8">
<style>body{font-family:Tahoma,sans-serif;max-width:800px;margin:40px auto;padding:20px}
.ok{color:#16a34a}.err{color:#dc2626}.info{color:#2563eb}h1{color:#1e293b}</style>
</head><body>';

echo '<h1>🔧 اصلاح پایگاه‌داده نکسووست</h1>';
echo '<p class="info">در حال اجرا...</p><hr>';

$log = function(string $msg, string $type = 'ok') {
    echo "<p class=\"{$type}\">• {$msg}</p>";
    ob_flush();
    flush();
};

try {
    // ۱. اصلاح نام برند
    $brandKeys = [
        'site_name'                  => ['value' => 'نکسووست',     'group' => 'site'],
        'header_site_name'           => ['value' => 'نکسو',         'group' => 'theme'],
        'header_site_name_highlight' => ['value' => 'وست',          'group' => 'theme'],
        'footer_site_name'           => ['value' => 'نکسووست',     'group' => 'theme'],
        'footer_copyright'           => ['value' => 'تمامی حقوق برای نکسووست محفوظ است © ' . idate('Y'), 'group' => 'theme'],
        'about_hero_title'           => ['value' => 'درباره نکسووست', 'group' => 'theme'],
    ];
    foreach ($brandKeys as $key => $data) {
        Setting::updateOrCreate(['key' => $key], ['value' => $data['value'], 'group' => $data['group']]);
        Cache::forget("setting:{$key}");
    }
    $log("نام برند به نکسووست تغییر یافت");

    // جایگزینی نکسو کورس با نکسووست در تمام ردیف‌ها
    $count = DB::table('settings')->where('value', 'like', '%نکسو کورس%')->count();
    if ($count > 0) {
        DB::table('settings')
            ->where('value', 'like', '%نکسو کورس%')
            ->get()
            ->each(function ($row) {
                DB::table('settings')
                    ->where('key', $row->key)
                    ->update(['value' => str_replace('نکسو کورس', 'نکسووست', $row->value)]);
                Cache::forget("setting:{$row->key}");
            });
        $log("{$count} ردیف جایگزینی نکسو کورس → نکسووست انجام شد");
    } else {
        $log("نیاز به جایگزینی نکسو کورس نبود");
    }

    // ۲. تنظیمات سوالات متداول
    $faqItems = json_encode([
        ['question' => 'چطور در یک دوره ثبت‌نام کنم؟',
         'answer'   => 'دوره مورد نظر را انتخاب کنید و از طریق درگاه امن پرداخت کنید. دسترسی بلافاصله فعال می‌شود.'],
        ['question' => 'آیا می‌توانم محتوای صوتی را دانلود کنم؟',
         'answer'   => 'خیر. محتوا فقط به صورت آنلاین پخش می‌شود و امکان دانلود آن وجود ندارد.'],
        ['question' => 'دسترسی به دوره‌ها تا چه زمانی فعال است؟',
         'answer'   => 'پس از خرید، دسترسی شما به دوره مادام‌العمر است و از هر دستگاهی می‌توانید استفاده کنید.'],
        ['question' => 'در صورت مشکل در پرداخت چه کنم؟',
         'answer'   => 'از طریق صفحه تماس با ما پیام بگذارید تا تیم پشتیبانی در کوتاه‌ترین زمان پیگیری کند.'],
    ], JSON_UNESCAPED_UNICODE);

    $faqDefaults = [
        'faq_seo_title'       => ['val' => 'سوالات متداول',  'grp' => 'theme'],
        'faq_page_title'      => ['val' => 'سوالات متداول',  'grp' => 'theme'],
        'faq_page_subtitle'   => ['val' => 'پاسخ سوالات رایج', 'grp' => 'theme'],
        'faq_items'           => ['val' => $faqItems,         'grp' => 'theme'],
    ];
    foreach ($faqDefaults as $key => $d) {
        Setting::firstOrCreate(['key' => $key], ['value' => $d['val'], 'group' => $d['grp']]);
        Cache::forget("setting:{$key}");
    }
    $log("تنظیمات سوالات متداول تنظیم شد");

    // ۳. تنظیمات قوانین و مقررات
    $termsDefaults = [
        'terms_seo_title'     => ['val' => 'قوانین و مقررات', 'grp' => 'theme'],
        'terms_page_title'    => ['val' => 'قوانین و مقررات', 'grp' => 'theme'],
        'terms_page_subtitle' => ['val' => '',                 'grp' => 'theme'],
    ];
    foreach ($termsDefaults as $key => $d) {
        Setting::firstOrCreate(['key' => $key], ['value' => $d['val'], 'group' => $d['grp']]);
        Cache::forget("setting:{$key}");
    }
    // terms_content فقط اگر وجود نداشت اضافه می‌شود (برای حفظ محتوای ادمین)
    if (!Setting::where('key', 'terms_content')->exists()) {
        Setting::create([
            'key'   => 'terms_content',
            'value' => '<h2>قوانین و مقررات</h2><p>با استفاده از خدمات نکسووست، شما این قوانین را می‌پذیرید.</p>',
            'group' => 'theme',
        ]);
        $log("terms_content پیش‌فرض ایجاد شد");
    } else {
        $log("terms_content موجود است — تغییر نمی‌دهد");
    }

    // ۴. لینک‌های فوتر (terms و faq)
    $footerLinks = Setting::getJson('footer_links', []);
    $hasTerms = collect($footerLinks)->contains(fn($i) => ($i['route_name'] ?? '') === 'terms');
    $hasFaq   = collect($footerLinks)->contains(fn($i) => ($i['route_name'] ?? '') === 'faq');
    $changed  = false;
    if (!$hasTerms) {
        $footerLinks[] = ['label' => 'قوانین و مقررات', 'route_name' => 'terms', 'url' => '', 'visible' => true];
        $changed = true;
        $log("لینک قوانین و مقررات به فوتر اضافه شد");
    }
    if (!$hasFaq) {
        $footerLinks[] = ['label' => 'سوالات متداول', 'route_name' => 'faq', 'url' => '', 'visible' => true];
        $changed = true;
        $log("لینک سوالات متداول به فوتر اضافه شد");
    }
    if ($changed) {
        Setting::set('footer_links', json_encode($footerLinks, JSON_UNESCAPED_UNICODE), 'theme');
    }

    // ۵. پاکسازی کامل کش
    Cache::flush();
    $log("✅ تمام کش‌ها پاک شد");

    echo '<hr><h2 class="ok">✅ همه تغییرات اعمال شد!</h2>';
    echo '<p class="err"><strong>⚠️ این فایل را از سرور حذف کنید:</strong> public/nexovest-fix.php</p>';

} catch (\Throwable $e) {
    echo '<p class="err">❌ خطا: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre style="font-size:12px;color:#666">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '</body></html>';
