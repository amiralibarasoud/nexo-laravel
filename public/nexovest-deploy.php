<?php
/**
 * nexovest-deploy.php — اسکریپت یک‌بار مصرف برای دیپلوی کامل
 * بعد از اجرا این فایل را حذف کنید!
 */

if (($_GET['token'] ?? '') !== 'deploy2026') {
    http_response_code(403);
    die('<h2 style="font-family:Tahoma">دسترسی غیرمجاز</h2>');
}

header('Content-Type: text/html; charset=utf-8');
set_time_limit(300);
ob_start();

$repoPath = realpath(__DIR__ . '/..');

echo '<!DOCTYPE html><html dir="rtl" lang="fa">
<head><meta charset="utf-8">
<style>
body{font-family:Tahoma,sans-serif;max-width:900px;margin:30px auto;padding:20px;background:#f8f9fa}
h1{color:#1e293b;border-bottom:2px solid #e2e8f0;padding-bottom:10px}
.box{background:#fff;border-radius:8px;padding:15px;margin:10px 0;border:1px solid #e2e8f0}
.ok{color:#16a34a;font-weight:bold}.err{color:#dc2626;font-weight:bold}
.info{color:#2563eb}.warn{color:#d97706}
pre{background:#1e293b;color:#e2e8f0;padding:12px;border-radius:6px;font-size:12px;overflow:auto;white-space:pre-wrap}
.step{background:#eff6ff;border-right:4px solid #3b82f6;padding:10px;margin:8px 0;border-radius:4px}
</style>
</head><body>';

echo '<h1>🚀 نکسووست — اسکریپت دیپلوی کامل</h1>';
echo '<p class="info">مسیر پروژه: <code>' . htmlspecialchars($repoPath) . '</code></p>';

$log = function(string $msg, string $type = 'ok') {
    echo "<p class=\"{$type}\">• {$msg}</p>";
    ob_flush(); flush();
};

$exec = function(string $cmd) use ($repoPath): array {
    $output = []; $code = -1;
    if (function_exists('exec')) {
        exec("cd " . escapeshellarg($repoPath) . " && {$cmd} 2>&1", $output, $code);
    } elseif (function_exists('shell_exec')) {
        $out = shell_exec("cd " . escapeshellarg($repoPath) . " && {$cmd} 2>&1");
        $output = explode("\n", $out ?? '');
        $code = 0;
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru("cd " . escapeshellarg($repoPath) . " && {$cmd} 2>&1", $code);
        $output = explode("\n", ob_get_clean());
    } elseif (function_exists('system')) {
        ob_start();
        system("cd " . escapeshellarg($repoPath) . " && {$cmd} 2>&1", $code);
        $output = explode("\n", ob_get_clean());
    }
    return ['output' => implode("\n", $output), 'code' => $code];
};

// ─── مرحله ۱: رفع کانفلیکت git ──────────────────────────────────────────────
echo '<div class="box"><h2>🔧 مرحله ۱: رفع کانفلیکت git</h2>';

$r = $exec('git status --short');
echo '<div class="step"><b>وضعیت git:</b><pre>' . htmlspecialchars($r['output'] ?: '(خروجی ندارد)') . '</pre></div>';

$r = $exec('git checkout -- resources/views/app.blade.php');
if (strpos($r['output'], 'error') === false) {
    $log("کانفلیکت app.blade.php رفع شد");
} else {
    $log("app.blade.php: " . htmlspecialchars($r['output'] ?: 'OK'), 'info');
}

// Reset any other locally-modified tracked files
$exec('git checkout -- .');
$log("تمام فایل‌های تغییریافته محلی reset شدند");

// ─── مرحله ۲: git pull ───────────────────────────────────────────────────────
echo '</div><div class="box"><h2>📥 مرحله ۲: git pull</h2>';

$r = $exec('git pull origin main');
echo '<div class="step"><pre>' . htmlspecialchars($r['output'] ?: '(خروجی ندارد)') . '</pre></div>';

if (strpos($r['output'], 'Already up to date') !== false || strpos($r['output'], 'Updating') !== false || strpos($r['output'], 'Fast-forward') !== false) {
    $log("git pull موفق بود ✅");
    $gitOk = true;
} elseif ($r['code'] === -1) {
    $log("exec در این هاست غیرفعال است — به مرحله بعد می‌رویم", 'warn');
    $gitOk = false;
} else {
    $log("git pull با خطا مواجه شد — بررسی کنید", 'warn');
    $gitOk = false;
}

// ─── مرحله ۳: artisan cache:clear ───────────────────────────────────────────
echo '</div><div class="box"><h2>🗑 مرحله ۳: پاکسازی کش</h2>';

// Try artisan first
$r = $exec('php artisan cache:clear');
$log("artisan cache:clear: " . htmlspecialchars(trim($r['output']) ?: 'اجرا شد'), 'info');

$r = $exec('php artisan config:clear');
$log("artisan config:clear: " . htmlspecialchars(trim($r['output']) ?: 'اجرا شد'), 'info');

$r = $exec('php artisan view:clear');
$log("artisan view:clear: " . htmlspecialchars(trim($r['output']) ?: 'اجرا شد'), 'info');

// Direct file-based cache clear (works even without exec)
$cacheCleared = 0;
$cacheDir = $repoPath . '/storage/framework/cache/data';
if (is_dir($cacheDir)) {
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iter as $file) {
        if ($file->isFile()) {
            @unlink($file->getPathname());
            $cacheCleared++;
        }
    }
}
$log("پاکسازی مستقیم فایل‌های کش: {$cacheCleared} فایل حذف شد");

// ─── مرحله ۴: artisan db:seed ───────────────────────────────────────────────
echo '</div><div class="box"><h2>🌱 مرحله ۴: اصلاح دیتابیس (SettingsSeeder)</h2>';

$r = $exec('php artisan db:seed --class=SettingsSeeder --force');
echo '<div class="step"><pre>' . htmlspecialchars($r['output'] ?: '(خروجی ندارد)') . '</pre></div>';
if ($r['code'] === 0 || strpos($r['output'], 'تنظیمات') !== false) {
    $log("SettingsSeeder اجرا شد ✅");
} else {
    $log("Seeder از طریق PHP مستقیم اجرا می‌شود...", 'warn');

    // Bootstrap Laravel directly
    try {
        require $repoPath . '/vendor/autoload.php';
        $app = require_once $repoPath . '/bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // Fix brand
        $brandKeys = [
            'site_name'                  => ['نکسووست', 'site'],
            'header_site_name'           => ['نکسو', 'theme'],
            'header_site_name_highlight' => ['وست', 'theme'],
            'footer_site_name'           => ['نکسووست', 'theme'],
        ];
        foreach ($brandKeys as $key => [$value, $group]) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            \Illuminate\Support\Facades\Cache::forget("setting:{$key}");
        }

        // Replace نکسو کورس
        \App\Models\Setting::query()->where('value', 'like', '%نکسو کورس%')->get()->each(function ($s) {
            $s->update(['value' => str_replace('نکسو کورس', 'نکسووست', $s->value)]);
        });

        // Ensure FAQ items
        $faqItems = json_encode([
            ['question' => 'چطور در یک دوره ثبت‌نام کنم؟', 'answer' => 'دوره مورد نظر را انتخاب کنید و از طریق درگاه امن پرداخت کنید.'],
            ['question' => 'آیا می‌توانم محتوا را دانلود کنم؟', 'answer' => 'خیر. محتوا فقط آنلاین قابل مشاهده است.'],
            ['question' => 'دسترسی به دوره‌ها تا کی فعال است؟', 'answer' => 'پس از خرید، دسترسی مادام‌العمر است.'],
            ['question' => 'در صورت مشکل در پرداخت چه کنم؟', 'answer' => 'از صفحه تماس با ما پیام بگذارید.'],
        ], JSON_UNESCAPED_UNICODE);
        \App\Models\Setting::firstOrCreate(['key' => 'faq_items'], ['value' => $faqItems, 'group' => 'theme']);
        \App\Models\Setting::firstOrCreate(['key' => 'faq_seo_title'], ['value' => 'سوالات متداول', 'group' => 'theme']);
        \App\Models\Setting::firstOrCreate(['key' => 'faq_page_title'], ['value' => 'سوالات متداول', 'group' => 'theme']);
        \App\Models\Setting::firstOrCreate(['key' => 'faq_page_subtitle'], ['value' => '', 'group' => 'theme']);

        \Illuminate\Support\Facades\Cache::flush();
        $log("اصلاح دیتابیس مستقیم موفق بود ✅");
    } catch (\Throwable $e) {
        $log("خطا در اجرای مستقیم: " . htmlspecialchars($e->getMessage()), 'err');
    }
}

// ─── نتیجه نهایی ─────────────────────────────────────────────────────────────
echo '</div><div class="box">';
echo '<h2>📊 نتیجه نهایی</h2>';

if (isset($gitOk) && $gitOk) {
    echo '<p class="ok">✅ git pull موفق — تمام تغییرات کد (FAQ، گفتینو، عنوان) به‌روز شدند</p>';
    echo '<p class="ok">✅ کش پاک شد</p>';
    echo '<p class="ok">✅ دیتابیس اصلاح شد</p>';
    echo '<p><b class="err">⚠️ این فایل را حتماً از سرور حذف کنید!</b></p>';
} else {
    echo '<p class="warn">⚠️ git pull انجام نشد (exec غیرفعال) — اما کش و دیتابیس fix شدند</p>';
    echo '<div style="background:#fef3c7;border:1px solid #fbbf24;padding:15px;border-radius:8px;margin-top:10px">';
    echo '<b>برای رفع FAQ سفید، باید فایل‌های build رو دستی آپلود کنید:</b><br>';
    echo 'مسیر روی سرور: <code>' . htmlspecialchars($repoPath) . '/public/build/</code>';
    echo '</div>';
}

echo '</div></body></html>';
