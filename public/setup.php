<?php

/**
 * اسکریپت راه‌اندازی از طریق مرورگر (مخصوص هاست اشتراکی بدون Terminal).
 *
 * این نسخه لاراول را مستقیماً داخل همین پروسه‌ی PHP بوت می‌کند و دستورهای artisan
 * را به‌صورت برنامه‌نویسی اجرا می‌کند. بنابراین نیازی به shell_exec یا php CLI ندارد
 * (که روی اکثر هاست‌های اشتراکی غیرفعال است و دلیل کار نکردن نسخه‌ی قبلی بود).
 *
 * استفاده:  https://your-domain/setup.php?run=nexo2024
 * لاگ خطا:   storage/logs/setup.log
 * عیب‌یابی:  اگر 500 می‌دهد، اول host-debug.php?run=nexo2024 را بزنید.
 * هشدار:   پس از اتمام، این فایل را حتماً حذف کنید.
 */

if (!isset($_GET['run']) || $_GET['run'] !== 'nexo2024') {
    http_response_code(403);
    exit('Access denied');
}

@set_time_limit(300);
header('Content-Type: text/html; charset=utf-8');

$baseDir = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
$logFile = $baseDir . '/storage/logs/setup.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$setupLog = static function (string $message, string $level = 'INFO') use ($logFile): void {
    if (!is_dir(dirname($logFile)) || !is_writable(dirname($logFile))) {
        return;
    }
    $line = '[' . date('Y-m-d H:i:s') . "] [{$level}] {$message}" . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
};

register_shutdown_function(static function () use ($setupLog): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $setupLog("FATAL: {$error['message']} in {$error['file']}:{$error['line']}", 'FATAL');
    }
});

set_exception_handler(static function (Throwable $e) use ($setupLog): void {
    $setupLog('UNCAUGHT: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');
    $setupLog($e->getTraceAsString(), 'TRACE');
    http_response_code(500);
    echo '<pre style="font-family:monospace;background:#1a1a1a;color:#ff4d4d;padding:20px">';
    echo "SETUP ERROR\n\n" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n\n";
    echo "See: storage/logs/setup.log\n";
    echo '</pre>';
    exit;
});

$setupLog('=== setup.php started ===');

echo "<pre style='font-family:monospace;direction:ltr;text-align:left;background:#1a1a1a;color:#00ff00;padding:20px;line-height:1.6'>";

echo "=== PHP Version ===\n" . PHP_VERSION . "\n";
$setupLog('PHP ' . PHP_VERSION);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    $msg = 'vendor/autoload.php MISSING — ابتدا composer install را اجرا کنید.';
    $setupLog($msg, 'FAIL');
    echo $msg . " ✗\n</pre>";
    exit;
}
echo "vendor: OK ✓\n";

try {
    require $autoload;

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
    echo "username:   " . config("database.connections.{$conn}.username") . "\n";

    $setupLog("DB: {$conn}@" . config("database.connections.{$conn}.host") . '/' . config("database.connections.{$conn}.database"));

    // تست اتصال قبل از migrate
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "db ping:    OK ✓\n";
        $setupLog('DB connection OK', 'OK');
    } catch (\Throwable $e) {
        echo "db ping:    FAILED ✗ — " . $e->getMessage() . "\n";
        $setupLog('DB connection FAILED: ' . $e->getMessage(), 'FAIL');
    }

    $run = static function (string $command, array $params = []) use ($kernel, $setupLog): void {
        echo "\n=== artisan {$command} ===\n";
        $setupLog("artisan {$command} ...");
        try {
            $kernel->call($command, $params);
            $out = trim($kernel->output());
            echo ($out === '') ? "done.\n" : $out . "\n";
            $setupLog("artisan {$command} OK");
        } catch (\Throwable $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $setupLog("artisan {$command} ERROR: " . $e->getMessage(), 'ERROR');
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
    $setupLog('=== setup finished OK ===', 'OK');
} catch (\Throwable $e) {
    $setupLog('FATAL: ' . $e->getMessage(), 'FATAL');
    $setupLog($e->getTraceAsString(), 'TRACE');
    echo "\nFATAL: " . $e->getMessage() . "\n\n";
    echo $e->getTraceAsString() . "\n";
    echo "\nLog: storage/logs/setup.log\n";
}

echo "</pre>";
echo "<p style='color:#ff4d4d;font-weight:bold;font-family:sans-serif'>⚠️ این فایل را پس از اتمام کار حذف کنید!</p>";
