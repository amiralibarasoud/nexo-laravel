<?php
/**
 * clear-cache.php — پاکسازی قطعی کش بدون نیاز به SSH / artisan
 * آدرس: https://nexovest.ir/clear-cache.php?token=clear2026
 * بعد از اجرا این فایل را حذف کنید.
 */

if (($_GET['token'] ?? '') !== 'clear2026') {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    exit('<h2 style="font-family:Tahoma">دسترسی غیرمجاز</h2>');
}

header('Content-Type: text/html; charset=utf-8');
set_time_limit(120);

$root = dirname(__DIR__);
$deleted = 0;
$logs = [];

function wipe_dir(string $dir, int &$deleted): int
{
    $count = 0;
    if (!is_dir($dir)) {
        return 0;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if ($file->isFile()) {
            if (@unlink($file->getPathname())) {
                $count++;
                $deleted++;
            }
        } elseif ($file->isDir()) {
            @rmdir($file->getPathname());
        }
    }
    return $count;
}

function wipe_files(string $dir, string $pattern, int &$deleted): int
{
    $count = 0;
    foreach (glob($dir . '/' . $pattern) ?: [] as $file) {
        if (is_file($file) && @unlink($file)) {
            $count++;
            $deleted++;
        }
    }
    return $count;
}

echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8">
<style>
body{font-family:Tahoma,sans-serif;max-width:800px;margin:40px auto;padding:20px;background:#f8fafc}
.ok{color:#16a34a}.err{color:#dc2626}.box{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin:12px 0}
h1{color:#0f172a}
</style></head><body>';
echo '<h1>پاکسازی قطعی کش نکسووست</h1>';
echo '<p>مسیر پروژه: <code>' . htmlspecialchars($root) . '</code></p>';

// 1) Laravel bootstrap cache (config/routes) — همین باعث تایتل قدیمی صفحه اصلی بود
$n = wipe_files($root . '/bootstrap/cache', '*.php', $deleted);
$logs[] = "bootstrap/cache/*.php → {$n} فایل حذف شد";

// 2) Application file cache
$n = wipe_dir($root . '/storage/framework/cache/data', $deleted);
$logs[] = "storage/framework/cache/data → {$n} فایل حذف شد";

// 3) Compiled views
$n = wipe_files($root . '/storage/framework/views', '*.php', $deleted);
$logs[] = "storage/framework/views → {$n} فایل حذف شد";

// 4) Fix APP_NAME in .env
$envPath = $root . '/.env';
if (is_file($envPath) && is_writable($envPath)) {
    $env = file_get_contents($envPath);
    $env = preg_replace('/^APP_NAME=.*/m', 'APP_NAME="نکسووست"', $env);
    if (file_put_contents($envPath, $env) !== false) {
        $logs[] = '.env → APP_NAME="نکسووست" تنظیم شد';
    } else {
        $logs[] = '.env → نوشتن ناموفق';
    }
} else {
    $logs[] = '.env → یافت نشد یا قابل نوشتن نیست';
}

// 5) Fix brand in DB via PDO (بدون Laravel)
$siteNameUpdated = false;
if (is_file($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $cfg = [];
    foreach ($envLines as $line) {
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $cfg[trim($k)] = trim($v, " \t\"'");
    }
    $dbHost = $cfg['DB_HOST'] ?? '127.0.0.1';
    $dbPort = $cfg['DB_PORT'] ?? '3306';
    $dbName = $cfg['DB_DATABASE'] ?? '';
    $dbUser = $cfg['DB_USERNAME'] ?? '';
    $dbPass = $cfg['DB_PASSWORD'] ?? '';

    if ($dbName !== '') {
        try {
            $pdo = new PDO(
                "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare("UPDATE settings SET value = ?, updated_at = NOW() WHERE `key` = ?");
            foreach ([
                'site_name' => 'نکسووست',
                'header_site_name' => 'نکسو',
                'header_site_name_highlight' => 'وست',
                'footer_site_name' => 'نکسووست',
            ] as $key => $value) {
                $stmt->execute([$value, $key]);
            }

            $pdo->exec("UPDATE settings SET value = REPLACE(value, 'نکسو کورس', 'نکسووست'), updated_at = NOW() WHERE value LIKE '%نکسو کورس%'");
            $siteNameUpdated = true;
            $logs[] = 'دیتابیس → برند نکسووست به‌روز شد';
        } catch (Throwable $e) {
            $logs[] = 'دیتابیس → خطا: ' . $e->getMessage();
        }
    }
}

echo '<div class="box">';
foreach ($logs as $line) {
    echo '<p class="ok">• ' . htmlspecialchars($line) . '</p>';
}
echo '<p class="ok"><b>جمع فایل‌های حذف‌شده: ' . $deleted . '</b></p>';
echo '</div>';

echo '<div class="box">';
echo '<p class="ok"><b>✅ تمام شد.</b></p>';
echo '<p>۱) صفحه اصلی را با Ctrl+Shift+R رفرش کنید.</p>';
echo '<p class="err"><b>۲) این فایل را همین الان از File Manager حذف کنید:</b> public/clear-cache.php</p>';
if (!$siteNameUpdated) {
    echo '<p class="err">اگر عنوان هنوز اشتباه است، SQL برند را در phpMyAdmin هم اجرا کنید.</p>';
}
echo '</div></body></html>';
