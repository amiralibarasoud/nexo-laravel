<?php

/**
 * عیب‌یابی هاست — بدون بوت لاراول (برای وقتی setup.php یا سایت 500 می‌دهد).
 *
 * استفاده:  https://your-domain/host-debug.php?run=nexo2024
 * لاگ:       storage/logs/host-debug.log  (یا public/host-debug.log اگر storage قابل نوشتن نباشد)
 * هشدار:    پس از رفع مشکل این فایل را حذف کنید.
 */

if (!isset($_GET['run']) || $_GET['run'] !== 'nexo2024') {
    http_response_code(403);
    exit('Access denied');
}

@set_time_limit(120);
header('Content-Type: text/html; charset=utf-8');

$baseDir = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
$logCandidates = [
    $baseDir . '/storage/logs/host-debug.log',
    __DIR__ . '/host-debug.log',
];
$logFile = null;

foreach ($logCandidates as $candidate) {
    $dir = dirname($candidate);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (is_dir($dir) && is_writable($dir)) {
        $logFile = $candidate;
        break;
    }
}

$lines = [];

$log = static function (string $message, string $level = 'INFO') use (&$lines, &$logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . "] [{$level}] {$message}";
    $lines[] = $line;
    if ($logFile !== null) {
        @file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
};

set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) use ($log): bool {
    $log("PHP Error [{$errno}] {$errstr} in {$errfile}:{$errline}", 'ERROR');

    return false;
});

register_shutdown_function(static function () use ($log): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $log("FATAL: {$error['message']} in {$error['file']}:{$error['line']}", 'FATAL');
    }
});

$log('=== host-debug started ===');
$log('PHP ' . PHP_VERSION . ' | SAPI: ' . PHP_SAPI);
$log('base dir: ' . $baseDir);

// ─── PHP version ───────────────────────────────────────────────
$minPhp = '8.2.0';
if (version_compare(PHP_VERSION, $minPhp, '<')) {
    $log("PHP version too old (need >= {$minPhp})", 'FAIL');
} else {
    $log('PHP version OK', 'OK');
}

// ─── Extensions ────────────────────────────────────────────────
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'fileinfo', 'xml'];
foreach ($requiredExtensions as $ext) {
    $log(extension_loaded($ext) ? "ext {$ext}: OK" : "ext {$ext}: MISSING", extension_loaded($ext) ? 'OK' : 'FAIL');
}

// ─── Paths & permissions ───────────────────────────────────────
$paths = [
    '.env' => $baseDir . '/.env',
    'vendor/autoload.php' => $baseDir . '/vendor/autoload.php',
    'bootstrap/app.php' => $baseDir . '/bootstrap/app.php',
    'storage/' => $baseDir . '/storage',
    'storage/logs/' => $baseDir . '/storage/logs',
    'storage/framework/cache/' => $baseDir . '/storage/framework/cache',
    'storage/framework/sessions/' => $baseDir . '/storage/framework/sessions',
    'storage/framework/views/' => $baseDir . '/storage/framework/views',
    'bootstrap/cache/' => $baseDir . '/bootstrap/cache',
];

foreach ($paths as $label => $path) {
    if (!file_exists($path)) {
        $log("{$label}: NOT FOUND ({$path})", 'FAIL');
        continue;
    }

    $writable = is_writable($path);
    $log("{$label}: exists" . ($writable ? ', writable' : ', NOT writable'), $writable ? 'OK' : 'WARN');
}

// ─── Cached config (ممکن است .env را نادیده بگیرد) ─────────────
$cachedConfig = $baseDir . '/bootstrap/cache/config.php';
if (file_exists($cachedConfig)) {
    $log('bootstrap/cache/config.php EXISTS — اگر .env را تازه عوض کردید، این فایل را حذف کنید یا setup.php را بزنید', 'WARN');
}

// ─── Parse .env (بدون لاراول) ─────────────────────────────────
$envPath = $baseDir . '/.env';
$env = [];
if (!file_exists($envPath)) {
    $log('.env file MISSING — باید از .env.example کپی و APP_KEY را بسازید', 'FAIL');
} else {
    $log('.env file exists', 'OK');
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }

    $checks = ['APP_KEY', 'APP_ENV', 'APP_DEBUG', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($checks as $key) {
        $val = $env[$key] ?? '';
        if ($key === 'APP_KEY' && $val === '') {
            $log('APP_KEY is EMPTY — php artisan key:generate یا دستی در .env بگذارید', 'FAIL');
        } elseif ($key === 'DB_PASSWORD') {
            $log('DB_PASSWORD: ' . ($val === '' ? '(empty)' : '(set)'), 'INFO');
        } else {
            $log("{$key}=" . ($val === '' ? '(empty)' : $val), $val === '' && in_array($key, ['DB_DATABASE', 'DB_USERNAME'], true) ? 'WARN' : 'INFO');
        }
    }
}

// ─── Direct DB test (PDO) ──────────────────────────────────────
if (extension_loaded('pdo_mysql') && isset($env['DB_HOST'], $env['DB_DATABASE'], $env['DB_USERNAME'])) {
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $db = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        $log("DB connection OK — MySQL/MariaDB {$version}", 'OK');
    } catch (Throwable $e) {
        $log('DB connection FAILED: ' . $e->getMessage(), 'FAIL');
        $log('روی cPanel معمولاً DB_HOST باید localhost باشد نه 127.0.0.1', 'HINT');
    }
} else {
    $log('DB test skipped (missing pdo_mysql or .env DB vars)', 'WARN');
}

// ─── Vendor integrity (autoload vs files on disk) ──────────────
$autoloadFiles = $baseDir . '/vendor/composer/autoload_files.php';
$vendorOk = true;
if (file_exists($autoloadFiles)) {
    $files = require $autoloadFiles;
    $missing = [];
    foreach ($files as $path) {
        if (!file_exists($path)) {
            $missing[] = str_replace($baseDir . '/', '', $path);
        }
    }
    if ($missing !== []) {
        $vendorOk = false;
        $log('vendor INCOMPLETE — ' . count($missing) . ' autoload file(s) missing', 'FAIL');
        foreach (array_slice($missing, 0, 5) as $m) {
            $log("  missing: {$m}", 'FAIL');
        }
        if (count($missing) > 5) {
            $log('  ... and ' . (count($missing) - 5) . ' more', 'FAIL');
        }
        $log('علت: vendor با composer install --no-dev ساخته نشده یا deploy ناقص است', 'HINT');
        $log('روی لوکال: composer install --no-dev && commit vendor/composer/*', 'HINT');
    } else {
        $log('vendor autoload files OK (' . count($files) . ' files)', 'OK');
    }
}

// ─── Try Laravel bootstrap (optional deeper test) ──────────────
$autoload = $baseDir . '/vendor/autoload.php';
if (file_exists($autoload) && $vendorOk) {
    try {
        require $autoload;
        $app = require $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        $log('Laravel bootstrap OK', 'OK');
        $log('config DB host: ' . config('database.connections.' . config('database.default') . '.host'), 'INFO');
        $log('config DB name: ' . config('database.connections.' . config('database.default') . '.database'), 'INFO');
    } catch (Throwable $e) {
        $log('Laravel bootstrap FAILED: ' . $e->getMessage(), 'FAIL');
        $log($e->getTraceAsString(), 'TRACE');
    }
}

$log('=== host-debug finished ===');

echo "<pre style='font-family:monospace;direction:ltr;text-align:left;background:#1a1a1a;color:#00ff00;padding:20px;line-height:1.6'>";
foreach ($lines as $line) {
    $color = '#00ff00';
    if (str_contains($line, '[FAIL]') || str_contains($line, '[FATAL]')) {
        $color = '#ff4d4d';
    } elseif (str_contains($line, '[WARN]')) {
        $color = '#ffcc00';
    }
    echo '<span style="color:' . $color . '">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "</span>\n";
}
echo "\n";
if ($logFile !== null) {
    echo "Log file: " . htmlspecialchars($logFile, ENT_QUOTES, 'UTF-8') . "\n";
} else {
    echo "WARNING: could not write log file (check storage/logs permissions)\n";
}
echo "</pre>";
echo "<p style='color:#ff4d4d;font-weight:bold;font-family:sans-serif'>⚠️ این فایل را پس از رفع مشکل حذف کنید!</p>";
