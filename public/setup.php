<?php

/**
 * اسکریپت راه‌اندازی/عیب‌یابی از طریق مرورگر (مخصوص هاست اشتراکی بدون Terminal).
 *
 * این نسخه:
 *  - نمایش خطاها را روشن می‌کند (هاست‌ها معمولاً display_errors را خاموش دارند).
 *  - خطاهای کشنده‌ی غیرقابل‌catch (مثل کمبود حافظه یا نبود افزونه) را با shutdown handler می‌گیرد.
 *  - افزونه‌های PHP، memory_limit و .env را بررسی می‌کند تا علت دقیق ارور ۵۰۰ مشخص شود.
 *  - لاراول را داخل همین پروسه بوت می‌کند و migrate/seed را بدون shell_exec اجرا می‌کند.
 *
 * استفاده:  https://your-domain/setup.php?run=nexo2024
 * هشدار:   پس از اتمام، این فایل را حتماً حذف کنید.
 */

if (!isset($_GET['run']) || $_GET['run'] !== 'nexo2024') {
    http_response_code(403);
    exit('Access denied');
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
@set_time_limit(300);

header('Content-Type: text/html; charset=utf-8');
echo "<pre style='font-family:monospace;direction:ltr;text-align:left;background:#1a1a1a;color:#00ff00;padding:20px;line-height:1.6'>";

// گرفتن خطاهای کشنده‌ای که با try/catch قابل گرفتن نیستند (کمبود حافظه، نبود افزونه و ...)
register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e !== null && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        echo "\n\n*** خطای کشنده (shutdown handler) ***\n";
        echo "{$e['message']}\n";
        echo "در {$e['file']} خط {$e['line']}\n";
        echo "</pre>";
    }
});

echo "=== PHP ===\n";
echo 'version:      ' . PHP_VERSION . "\n";
echo 'memory_limit: ' . ini_get('memory_limit') . "\n";
echo 'max_exec:     ' . ini_get('max_execution_time') . "\n\n";

echo "=== Extensions (لازم برای لاراول/فیلامنت) ===\n";
$required = ['intl', 'zip', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'ctype', 'json', 'fileinfo', 'curl', 'gd', 'bcmath', 'xml', 'dom'];
$missing = [];
foreach ($required as $ext) {
    $ok = extension_loaded($ext);
    if (!$ok) {
        $missing[] = $ext;
    }
    echo str_pad($ext, 12) . ($ok ? "OK ✓\n" : "MISSING ✗\n");
}
echo "\n";

$autoload = __DIR__ . '/../vendor/autoload.php';
$envPath  = __DIR__ . '/../.env';
echo 'vendor/autoload.php: ' . (file_exists($autoload) ? "OK ✓\n" : "MISSING ✗\n");
echo '.env:                ' . (file_exists($envPath) ? "OK ✓\n" : "MISSING ✗\n");

if ($missing !== []) {
    echo "\n⚠️ افزونه‌های زیر روی هاست نصب/فعال نیستند: " . implode(', ', $missing) . "\n";
    echo "این می‌تواند دلیل ارور ۵۰۰ باشد. از بخش «Select PHP Version» در cPanel آن‌ها را فعال کنید.\n";
}

if (!file_exists($autoload)) {
    echo "\nبدون vendor نمی‌توان ادامه داد (composer install لازم است).\n</pre>";
    exit;
}

try {
    require $autoload;
    echo "\nautoload loaded ✓\n";

    /** @var \Illuminate\Foundation\Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "app created ✓\n";

    /** @var \Illuminate\Contracts\Console\Kernel $kernel */
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "kernel bootstrapped ✓\n";

    $conn = config('database.default');
    echo "\n=== DB Config ===\n";
    echo "connection: {$conn}\n";
    echo 'host:       ' . config("database.connections.{$conn}.host") . "\n";
    echo 'database:   ' . config("database.connections.{$conn}.database") . "\n";

    $run = static function (string $command, array $params = []) use ($kernel): void {
        echo "\n=== artisan {$command} ===\n";
        try {
            $kernel->call($command, $params);
            $out = trim($kernel->output());
            echo ($out === '') ? "done.\n" : $out . "\n";
        } catch (\Throwable $e) {
            echo 'ERROR: ' . $e->getMessage() . "\n";
        }
    };

    $run('optimize:clear');
    $run('migrate', ['--force' => true]);
    $run('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
    $run('db:seed', ['--class' => 'BlogSeeder', '--force' => true]);
    $run('storage:link');

    echo "\n=== DONE ✓ ===\n";
} catch (\Throwable $e) {
    echo "\nFATAL: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p style='color:#ff4d4d;font-weight:bold;font-family:sans-serif'>⚠️ این فایل را پس از اتمام کار حذف کنید!</p>";
