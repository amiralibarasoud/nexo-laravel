<?php

/**
 * اسکریپت راه‌اندازی از طریق مرورگر (مخصوص هاست اشتراکی بدون Terminal).
 *
 * این نسخه لاراول را مستقیماً داخل همین پروسه‌ی PHP بوت می‌کند و دستورهای artisan
 * را به‌صورت برنامه‌نویسی اجرا می‌کند. بنابراین نیازی به shell_exec یا php CLI ندارد
 * (که روی اکثر هاست‌های اشتراکی غیرفعال است و دلیل کار نکردن نسخه‌ی قبلی بود).
 *
 * استفاده:  https://your-domain/setup.php?run=nexo2024
 * هشدار:   پس از اتمام، این فایل را حتماً حذف کنید.
 */

if (!isset($_GET['run']) || $_GET['run'] !== 'nexo2024') {
    http_response_code(403);
    exit('Access denied');
}

@set_time_limit(300);
header('Content-Type: text/html; charset=utf-8');

echo "<pre style='font-family:monospace;direction:ltr;text-align:left;background:#1a1a1a;color:#00ff00;padding:20px;line-height:1.6'>";

echo "=== PHP Version ===\n" . PHP_VERSION . "\n\n";

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    echo "vendor/autoload.php MISSING ✗  → ابتدا composer install را اجرا کنید.\n</pre>";
    exit;
}
echo "vendor: OK ✓\n";

require $autoload;

try {
    /** @var \Illuminate\Foundation\Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';

    /** @var \Illuminate\Contracts\Console\Kernel $kernel */
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    // اطلاعات اتصال دیتابیس (برای عیب‌یابی .env یا کانفیگ کش‌شده‌ی اشتباه)
    $conn = config('database.default');
    echo "\n=== DB Config ===\n";
    echo "connection: {$conn}\n";
    echo "host:       " . config("database.connections.{$conn}.host") . "\n";
    echo "database:   " . config("database.connections.{$conn}.database") . "\n";

    $run = static function (string $command, array $params = []) use ($kernel): void {
        echo "\n=== artisan {$command} ===\n";
        try {
            $kernel->call($command, $params);
            $out = trim($kernel->output());
            echo ($out === '') ? "done.\n" : $out . "\n";
        } catch (\Throwable $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    };

    // پاک‌سازی کش کانفیگ/روت/ویو (در صورت کش‌شدن مقادیر قدیمی)
    $run('optimize:clear');

    // اجرای مهاجرت‌ها — این همان چیزی است که جدول‌ها را روی هاست می‌سازد
    $run('migrate', ['--force' => true]);

    // سیدرها (idempotent هستند و با firstOrCreate رکورد تکراری نمی‌سازند)
    $run('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
    $run('db:seed', ['--class' => 'BlogSeeder', '--force' => true]);

    $run('storage:link');

    echo "\n=== DONE ✓ ===\n";
} catch (\Throwable $e) {
    echo "\nFATAL: " . $e->getMessage() . "\n\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p style='color:#ff4d4d;font-weight:bold;font-family:sans-serif'>⚠️ این فایل را پس از اتمام کار حذف کنید!</p>";
