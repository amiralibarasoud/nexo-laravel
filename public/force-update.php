<?php
/**
 * force-update.php — جایگزینی قطعی public/build از GitHub + پاکسازی کش + اصلاح دیتابیس
 * آدرس: https://nexovest.ir/force-update.php?token=force2026
 * بعد از اجرا این فایل را حذف کنید.
 */

if (($_GET['token'] ?? '') !== 'force2026') {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    exit('<h2 style="font-family:Tahoma">دسترسی غیرمجاز</h2>');
}

header('Content-Type: text/html; charset=utf-8');
set_time_limit(300);
ini_set('memory_limit', '512M');

$public = __DIR__;
$root = dirname($public);
$tmp = $public . '/_force_update_tmp';
$zipFile = $public . '/_force_update.zip';
$repoZip = 'https://github.com/amiralibarasoud/nexo-laravel/archive/refs/heads/main.zip';

$logs = [];
$ok = true;

function log_line(array &$logs, string $msg, string $type = 'ok'): void
{
    $logs[] = [$type, $msg];
    echo '<p class="' . $type . '">• ' . htmlspecialchars($msg) . '</p>';
    if (function_exists('ob_flush')) {
        @ob_flush();
    }
    @flush();
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) {
        $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
    }
    @rmdir($dir);
}

function rcopy(string $src, string $dst): int
{
    $count = 0;
    if (!is_dir($src)) {
        return 0;
    }
    if (!is_dir($dst)) {
        @mkdir($dst, 0755, true);
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $target = $dst . DIRECTORY_SEPARATOR . $it->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target)) {
                @mkdir($target, 0755, true);
            }
        } else {
            if (@copy($item->getPathname(), $target)) {
                $count++;
            }
        }
    }
    return $count;
}

function wipe_files(string $dir, string $pattern): int
{
    $n = 0;
    foreach (glob(rtrim($dir, '/\\') . '/' . $pattern) ?: [] as $file) {
        if (is_file($file) && @unlink($file)) {
            $n++;
        }
    }
    return $n;
}

function wipe_dir_files(string $dir): int
{
    $n = 0;
    if (!is_dir($dir)) {
        return 0;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if ($file->isFile() && @unlink($file->getPathname())) {
            $n++;
        } elseif ($file->isDir()) {
            @rmdir($file->getPathname());
        }
    }
    return $n;
}

echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8">
<style>
body{font-family:Tahoma,sans-serif;max-width:900px;margin:30px auto;padding:20px;background:#f8fafc}
.ok{color:#16a34a}.err{color:#dc2626}.warn{color:#d97706}.info{color:#2563eb}
.box{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin:12px 0}
h1,h2{color:#0f172a} code{background:#e2e8f0;padding:2px 6px;border-radius:4px}
</style></head><body>';
echo '<h1>به‌روزرسانی قطعی نکسووست</h1>';
echo '<p class="info">root: <code>' . htmlspecialchars($root) . '</code></p>';
echo '<div class="box"><h2>۱) دانلود بیلد از GitHub</h2>';

// Cleanup previous temp
@unlink($zipFile);
rrmdir($tmp);

// Download zip
$zipData = false;
if (function_exists('curl_init')) {
    $ch = curl_init($repoZip);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_USERAGENT => 'Nexovest-Force-Update',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $zipData = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($zipData === false || $code !== 200) {
        log_line($logs, "curl ناموفق (HTTP {$code}): {$cerr}", 'err');
        $zipData = false;
        $ok = false;
    }
} elseif (ini_get('allow_url_fopen')) {
    $zipData = @file_get_contents($repoZip);
    if ($zipData === false) {
        log_line($logs, 'file_get_contents ناموفق بود', 'err');
        $ok = false;
    }
} else {
    log_line($logs, 'نه curl و نه allow_url_fopen فعال است', 'err');
    $ok = false;
}

if ($zipData !== false) {
    if (@file_put_contents($zipFile, $zipData) === false) {
        log_line($logs, 'نتوانست zip را ذخیره کند', 'err');
        $ok = false;
    } else {
        log_line($logs, 'دانلود zip موفق (' . number_format(strlen($zipData)) . ' بایت)');
    }
}

echo '</div><div class="box"><h2>۲) جایگزینی public/build</h2>';

if ($ok && class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true) {
        @mkdir($tmp, 0755, true);
        $zip->extractTo($tmp);
        $zip->close();
        log_line($logs, 'استخراج zip موفق');

        // Find extracted folder: nexo-laravel-main/...
        $extractedRoot = null;
        foreach (scandir($tmp) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $candidate = $tmp . '/' . $name;
            if (is_dir($candidate) && is_dir($candidate . '/public/build')) {
                $extractedRoot = $candidate;
                break;
            }
        }

        if (!$extractedRoot) {
            log_line($logs, 'پوشه public/build داخل zip پیدا نشد', 'err');
            $ok = false;
        } else {
            $srcBuild = $extractedRoot . '/public/build';
            $dstBuild = $public . '/build';

            // Remove old build assets (keep folder)
            wipe_dir_files($dstBuild);
            $copied = rcopy($srcBuild, $dstBuild);
            log_line($logs, "build جایگزین شد — {$copied} فایل کپی شد");

            // Verify new app hash
            $manifest = $dstBuild . '/manifest.json';
            if (is_file($manifest)) {
                $m = file_get_contents($manifest);
                if (str_contains($m, 'Faq') && str_contains($m, 'app-CHD1nLya.js')) {
                    log_line($logs, 'تأیید: Faq و app-CHD1nLya.js در manifest هستند ✅');
                } elseif (str_contains($m, 'Faq')) {
                    log_line($logs, 'تأیید: Faq در manifest هست ✅');
                } else {
                    log_line($logs, 'هشدار: Faq در manifest نیست', 'warn');
                }
                if (str_contains($m, 'app-wMzF5M8w.js')) {
                    log_line($logs, 'هنوز بیلد قدیمی است!', 'err');
                    $ok = false;
                }
            }

            // Sync critical PHP/Vue source that terms/faq need
            $syncMap = [
                'resources/views/app.blade.php' => 'resources/views/app.blade.php',
                'resources/js/Pages/Pages/Faq.vue' => 'resources/js/Pages/Pages/Faq.vue',
                'resources/js/Pages/Pages/Terms.vue' => 'resources/js/Pages/Pages/Terms.vue',
                'app/Http/Middleware/HandleInertiaRequests.php' => 'app/Http/Middleware/HandleInertiaRequests.php',
                'app/Models/Setting.php' => 'app/Models/Setting.php',
                'app/Http/Controllers/PageController.php' => 'app/Http/Controllers/PageController.php',
            ];
            foreach ($syncMap as $rel) {
                $from = $extractedRoot . '/' . $rel;
                $to = $root . '/' . $rel;
                if (is_file($from)) {
                    $dir = dirname($to);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    if (@copy($from, $to)) {
                        log_line($logs, "همگام‌سازی: {$rel}");
                    }
                }
            }
        }
    } else {
        log_line($logs, 'باز کردن zip ناموفق', 'err');
        $ok = false;
    }
} elseif ($ok) {
    log_line($logs, 'افزونه ZipArchive روی هاست نیست', 'err');
    $ok = false;
}

echo '</div><div class="box"><h2>۳) پاکسازی کش</h2>';
$n = wipe_files($root . '/bootstrap/cache', '*.php');
log_line($logs, "bootstrap/cache → {$n} فایل");
$n = wipe_dir_files($root . '/storage/framework/cache/data');
log_line($logs, "cache/data → {$n} فایل");
$n = wipe_files($root . '/storage/framework/views', '*.php');
log_line($logs, "views → {$n} فایل");

echo '</div><div class="box"><h2>۴) اصلاح .env و دیتابیس</h2>';

$envPath = $root . '/.env';
if (is_file($envPath) && is_writable($envPath)) {
    $env = file_get_contents($envPath);
    $env = preg_replace('/^APP_NAME=.*/m', 'APP_NAME="نکسووست"', $env);
    file_put_contents($envPath, $env);
    log_line($logs, 'APP_NAME در .env = نکسووست');
}

if (is_file($envPath)) {
    $cfg = [];
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $cfg[trim($k)] = trim($v, " \t\"'");
    }

    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['DB_HOST'] ?? '127.0.0.1', $cfg['DB_PORT'] ?? '3306', $cfg['DB_DATABASE'] ?? ''),
            $cfg['DB_USERNAME'] ?? '',
            $cfg['DB_PASSWORD'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $upd = $pdo->prepare('UPDATE settings SET value = ?, updated_at = NOW() WHERE `key` = ?');
        foreach ([
            'site_name' => 'نکسووست',
            'header_site_name' => 'نکسو',
            'header_site_name_highlight' => 'وست',
            'footer_site_name' => 'نکسووست',
            'faq_seo_title' => 'سوالات متداول',
            'faq_page_title' => 'سوالات متداول',
            'terms_seo_title' => 'قوانین و مقررات',
            'terms_page_title' => 'قوانین و مقررات',
        ] as $key => $value) {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE `key` = ?');
            $exists->execute([$key]);
            if ((int) $exists->fetchColumn() > 0) {
                $upd->execute([$value, $key]);
            } else {
                $ins = $pdo->prepare('INSERT INTO settings (`key`, value, `group`, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
                $group = str_starts_with($key, 'site_') ? 'site' : 'theme';
                $ins->execute([$key, $value, $group]);
            }
        }

        $pdo->exec("UPDATE settings SET value = REPLACE(value, 'نکسو کورس', 'نکسووست'), updated_at = NOW() WHERE value LIKE '%نکسو کورس%'");

        // Ensure faq_items exists
        $faqCheck = $pdo->query("SELECT COUNT(*) FROM settings WHERE `key`='faq_items'")->fetchColumn();
        if (!(int) $faqCheck) {
            $faqItems = json_encode([
                ['question' => 'چطور در یک دوره ثبت‌نام کنم؟', 'answer' => 'دوره را انتخاب و پرداخت کنید. دسترسی فوری فعال می‌شود.'],
                ['question' => 'آیا می‌توانم محتوا را دانلود کنم؟', 'answer' => 'خیر. محتوا فقط آنلاین قابل مشاهده است.'],
                ['question' => 'دسترسی تا کی فعال است؟', 'answer' => 'پس از خرید، دسترسی مادام‌العمر است.'],
                ['question' => 'مشکل پرداخت؟', 'answer' => 'از صفحه تماس با ما پیام بگذارید.'],
            ], JSON_UNESCAPED_UNICODE);
            $pdo->prepare('INSERT INTO settings (`key`, value, `group`, created_at, updated_at) VALUES (?,?,?,NOW(),NOW())')
                ->execute(['faq_items', $faqItems, 'theme']);
            log_line($logs, 'faq_items ایجاد شد');
        }

        // Ensure terms_content exists
        $termsCheck = $pdo->query("SELECT COUNT(*) FROM settings WHERE `key`='terms_content'")->fetchColumn();
        if (!(int) $termsCheck) {
            $pdo->prepare('INSERT INTO settings (`key`, value, `group`, created_at, updated_at) VALUES (?,?,?,NOW(),NOW())')
                ->execute(['terms_content', '<h2>قوانین و مقررات</h2><p>با استفاده از خدمات نکسووست، این قوانین را می‌پذیرید.</p>', 'theme']);
            log_line($logs, 'terms_content ایجاد شد');
        } else {
            log_line($logs, 'terms_content موجود است (محتوای ادمین حفظ شد)');
        }

        log_line($logs, 'دیتابیس به‌روز شد ✅');
    } catch (Throwable $e) {
        log_line($logs, 'خطای دیتابیس: ' . $e->getMessage(), 'err');
    }
}

// Cleanup temp
@unlink($zipFile);
rrmdir($tmp);
log_line($logs, 'فایل‌های موقت پاک شدند');

echo '</div><div class="box">';
if ($ok) {
    echo '<h2 class="ok">✅ به‌روزرسانی انجام شد</h2>';
    echo '<ol>';
    echo '<li>در مرورگر <b>Ctrl+Shift+R</b> بزنید (یا کش مرورگر را پاک کنید)</li>';
    echo '<li>آدرس <code>/build/manifest.json</code> را باز کنید و مطمئن شوید <code>app-CHD1nLya</code> و <code>Faq</code> هست</li>';
    echo '<li>صفحه سوالات متداول و قوانین را دوباره تست کنید</li>';
    echo '<li class="err"><b>این فایل را حذف کنید:</b> public/force-update.php</li>';
    echo '</ol>';
} else {
    echo '<h2 class="err">❌ بخشی از به‌روزرسانی ناموفق بود</h2>';
    echo '<p>اگر دانلود از GitHub روی هاست بسته است، پوشه <code>public/build</code> را از سیستم محلی خودتان آپلود کنید:</p>';
    echo '<p><code>c:\\xampp\\htdocs\\nexo-course\\public\\build</code> → <code>/home/modiryat/nexovest.ir/public/build</code></p>';
}
echo '</div></body></html>';
